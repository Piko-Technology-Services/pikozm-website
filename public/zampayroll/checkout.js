/**
 * ZamPayroll checkout.
 *  - Shows the panel for the chosen payment method.
 *  - Card and manual submit the form normally (server redirects to DPO / ZamPayroll sign-in).
 *  - Mobile money runs here: send prompt → wait for PIN approval (polling) → paid / failed / retry.
 */
(function () {
  'use strict';

  const root = document.querySelector('.zp-checkout');
  const form = document.getElementById('checkoutForm');
  if (!root || !form) return;

  const csrf = document.querySelector('meta[name="csrf-token"]').content;
  const networks = window.ZP_NETWORKS || {};
  const timeoutSeconds = Number(root.dataset.timeout) || 180;
  const momo = form.querySelector('.zp-momo');
  const numberInput = form.querySelector('[data-momo-number]');
  const networkHint = form.querySelector('[data-network-hint]');
  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  let reference = null;
  let pollTimer = null;
  let countdownTimer = null;
  let deadline = 0;

  /* ---------- Payment method panels ---------- */
  function currentMethod() {
    const checked = form.querySelector('input[name="payment_method"]:checked');
    return checked ? checked.value : 'mobile_money';
  }

  function showPanel() {
    const method = currentMethod();
    form.querySelectorAll('[data-panel]').forEach(function (p) { p.hidden = p.dataset.panel !== method; });
  }

  form.querySelectorAll('input[name="payment_method"]').forEach(function (r) { r.addEventListener('change', showPanel); });
  showPanel();

  form.addEventListener('submit', function (e) {
    if (currentMethod() === 'mobile_money') { e.preventDefault(); sendPrompt(); return; }
    const btn = form.querySelector('[data-panel="' + currentMethod() + '"] button[type="submit"]');
    stage(2);
    if (btn) { btn.disabled = true; btn.textContent = currentMethod() === 'card' ? 'Opening secure card page…' : 'Taking you to ZamPayroll…'; }
  });

  /* ---------- Employee band hint ---------- */
  const employees = form.querySelector('[data-employees]');
  const employeesHint = form.querySelector('[data-employees-hint]');
  const defaultHint = employeesHint.textContent;
  const max = Number(root.dataset.maxEmployees) || 0;
  employees.addEventListener('input', function () {
    const n = Number(employees.value);
    const over = max && n > max;
    employeesHint.classList.toggle('is-warn', over);
    employeesHint.innerHTML = over
      ? root.dataset.planName + ' covers up to ' + max + ' employees. <a href="' + root.dataset.pricingUrl + '">Choose a larger plan</a>.'
      : defaultHint;
  });

  /* ---------- Mobile number: format + detect network ---------- */
  function digitsLocal(value) {
    let d = String(value).replace(/\D+/g, '');
    if (d.startsWith('260')) d = '0' + d.slice(3);
    if (d.length === 9 && /^[579]/.test(d)) d = '0' + d;
    return d;
  }

  function detect(local) {
    const prefix = local.slice(0, 3);
    return Object.keys(networks).find(function (k) { return networks[k].prefixes.indexOf(prefix) !== -1; }) || null;
  }

  function validNumber(local) { return /^0[579]\d{8}$/.test(local); }

  numberInput.addEventListener('input', function () {
    const local = digitsLocal(numberInput.value);
    const key = local.length >= 3 ? detect(local) : null;
    if (key) {
      form.querySelector('input[name="network"][value="' + key + '"]').checked = true;
      networkHint.textContent = 'Looks like ' + networks[key].label + '.';
    } else {
      networkHint.textContent = local.length >= 3 ? 'Choose your network above.' : 'We’ll pick the network from your number.';
    }
    numberInput.removeAttribute('aria-invalid');
  });

  numberInput.addEventListener('blur', function () {
    const local = digitsLocal(numberInput.value);
    if (validNumber(local)) numberInput.value = local.slice(0, 3) + ' ' + local.slice(3, 6) + ' ' + local.slice(6);
  });

  // Prefill the wallet number from the contact phone, if it is a Zambian mobile.
  const contactPhone = form.querySelector('input[name="phone"]');
  contactPhone.addEventListener('change', function () {
    if (!numberInput.value && validNumber(digitsLocal(contactPhone.value))) {
      numberInput.value = contactPhone.value;
      numberInput.dispatchEvent(new Event('input'));
      numberInput.dispatchEvent(new Event('blur'));
    }
  });

  /* ---------- Progress stepper: Choose plan → Your details → Payment → Confirmation ---------- */
  const stepper = document.querySelector('[data-stepper]');
  function stage(n) {
    if (!stepper) return;
    stepper.querySelectorAll('li').forEach(function (li, i) {
      li.classList.toggle('is-done', i < n);
      li.classList.toggle('is-current', i === n);
      if (i === n) li.setAttribute('aria-current', 'step'); else li.removeAttribute('aria-current');
    });
  }
  form.querySelectorAll('#step-method input').forEach(function (i) { i.addEventListener('change', function () { stage(2); }); });

  /* ---------- Screens ---------- */
  function show(screen) {
    stage(screen === 'paid' ? 4 : 2);
    momo.querySelectorAll('[data-screen]').forEach(function (s) { s.hidden = s.dataset.screen !== screen; });
    const active = momo.querySelector('[data-screen="' + screen + '"]');
    const title = active && active.querySelector('.zp-momo__title');
    if (title && screen !== 'enter') { title.setAttribute('tabindex', '-1'); title.focus({ preventScroll: true }); }
    momo.scrollIntoView({ behavior: reduceMotion ? 'auto' : 'smooth', block: 'start' });
  }

  function setReference(ref) {
    reference = ref;
    const el = document.querySelector('[data-reference]');
    if (el && ref) { el.hidden = false; el.querySelector('strong').textContent = ref; }
  }

  function stopTimers() { clearTimeout(pollTimer); clearInterval(countdownTimer); }

  /* ---------- Validation ---------- */
  function clearErrors() {
    form.querySelectorAll('.zp-error').forEach(function (e) { e.remove(); });
    form.querySelectorAll('[aria-invalid]').forEach(function (e) { e.removeAttribute('aria-invalid'); });
  }

  function showErrors(errors) {
    let first = null;
    Object.keys(errors).forEach(function (name) {
      const input = form.querySelector('[name="' + name + '"]');
      if (!input) return;
      input.setAttribute('aria-invalid', 'true');
      const msg = document.createElement('span');
      msg.className = 'zp-error';
      msg.textContent = errors[name][0];
      input.closest('.zp-field, .zp-networks').appendChild(msg);
      first = first || input;
    });
    if (first) first.focus();
  }

  function detailsValid() {
    const fields = document.getElementById('step-details').querySelectorAll('input');
    for (const f of fields) { if (!f.checkValidity()) { f.reportValidity(); return false; } }
    return true;
  }

  /* ---------- Send prompt (also used for retries) ---------- */
  async function sendPrompt() {
    clearErrors();
    stopTimers();
    if (!detailsValid()) return;

    const local = digitsLocal(numberInput.value);
    if (!validNumber(local)) {
      show('enter');
      showErrors({ mobile_number: ['Enter a Zambian mobile number, for example 097 123 4567.'] });
      return;
    }

    const btn = momo.querySelector('[data-momo-send]');
    btn.disabled = true;
    const label = btn.textContent;
    btn.textContent = 'Sending prompt…';

    const body = Object.fromEntries(new FormData(form));
    body.plan = root.dataset.plan;
    body.mobile_number = local;
    if (reference) body.reference = reference;

    try {
      const res = await fetch(root.dataset.mobileUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
        body: JSON.stringify(body),
      });
      const data = await res.json().catch(function () { return {}; });

      if (res.status === 422 && data.errors) { show('enter'); showErrors(data.errors); return; }
      if (res.status === 419) { fail({ message: 'Your session expired. Refresh the page and try again.' }); return; }
      if (res.status === 429) { fail({ message: 'Too many attempts. Wait a minute, then try again.' }); return; }
      if (data.reference) setReference(data.reference);

      if (data.state === 'paid') { paid(data); return; }
      if (data.state === 'failed' || !res.ok) { fail(data); return; }

      waiting(data, local);
    } catch (err) {
      fail({ message: 'We couldn’t reach the payment service. Check your connection and try again.' });
    } finally {
      btn.disabled = false;
      btn.textContent = label;
    }
  }

  /* ---------- Waiting for PIN approval ---------- */
  function waiting(data, local) {
    momo.querySelector('[data-momo-display]').textContent = local.slice(0, 3) + ' ' + local.slice(3, 6) + ' ' + local.slice(6);
    const instr = momo.querySelector('[data-momo-instructions]');
    instr.hidden = !data.instructions;
    instr.textContent = data.instructions || '';
    show('waiting');

    deadline = Date.now() + timeoutSeconds * 1000;
    const countdown = momo.querySelector('[data-countdown]');
    const tick = function () {
      const left = Math.max(0, Math.round((deadline - Date.now()) / 1000));
      countdown.textContent = Math.floor(left / 60) + ':' + String(left % 60).padStart(2, '0');
      if (left === 0) { stopTimers(); show('timeout'); }
    };
    tick();
    countdownTimer = setInterval(tick, 1000);
    poll(data.status_url, 5000);
  }

  function poll(url, delay) {
    pollTimer = setTimeout(async function () {
      try {
        const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
        if (res.ok) {
          const data = await res.json();
          if (data.state === 'paid') { stopTimers(); paid(data); return; }
          if (data.state === 'failed') { stopTimers(); fail(data); return; }
        }
      } catch (e) { /* keep polling through brief network blips */ }
      if (Date.now() < deadline) poll(url, 4000);
    }, delay);
  }

  function statusUrl() { return reference ? root.dataset.mobileUrl + '/' + encodeURIComponent(reference) : null; }

  /* ---------- Outcomes ---------- */
  function fail(data) {
    stopTimers();
    momo.querySelector('[data-fail-message]').textContent = data.message || 'The payment was not completed.';
    momo.querySelector('[data-attempt]').textContent = data.attempt ? 'Attempt ' + data.attempt + (reference ? ' · Reference ' + reference : '') : '';
    show('failed');
  }

  function paid(data) {
    const r = data.receipt || {};
    momo.querySelector('[data-r="plan"]').textContent = r.plan || '';
    momo.querySelector('[data-r="amount"]').textContent = r.amount || '';
    momo.querySelector('[data-r="paid_at"]').textContent = r.paid_at || '';
    momo.querySelector('[data-r="reference"]').textContent = data.reference || '';
    form.querySelectorAll('#step-details input, #step-method input').forEach(function (i) { i.disabled = true; });
    show('paid');
    window.zpConfetti(momo.querySelector('[data-screen="paid"] .zp-confetti'));
  }

  /* ---------- Buttons ---------- */
  momo.querySelector('[data-momo-send]').addEventListener('click', sendPrompt);
  momo.querySelectorAll('[data-momo-resend]').forEach(function (b) { b.addEventListener('click', sendPrompt); });
  momo.querySelectorAll('[data-momo-change]').forEach(function (b) {
    b.addEventListener('click', function () { stopTimers(); show('enter'); numberInput.focus(); });
  });
  momo.querySelector('[data-momo-check]').addEventListener('click', async function () {
    const url = statusUrl();
    if (!url) return;
    try {
      const data = await (await fetch(url, { headers: { 'Accept': 'application/json' } })).json();
      if (data.state === 'paid') paid(data);
      else if (data.state === 'failed') fail(data);
      else { deadline = Date.now() + 60000; show('waiting'); poll(url, 3000); countdownTimer = setInterval(function () {
        const left = Math.max(0, Math.round((deadline - Date.now()) / 1000));
        momo.querySelector('[data-countdown]').textContent = Math.floor(left / 60) + ':' + String(left % 60).padStart(2, '0');
        if (left === 0) { stopTimers(); show('timeout'); }
      }, 1000); }
    } catch (e) { /* stay on the timeout screen */ }
  });
  momo.querySelector('[data-use-card]').addEventListener('click', function () {
    const card = form.querySelector('input[name="payment_method"][value="card"]');
    card.checked = true;
    showPanel();
    card.closest('.zp-method').scrollIntoView({ behavior: reduceMotion ? 'auto' : 'smooth', block: 'center' });
  });

})();

