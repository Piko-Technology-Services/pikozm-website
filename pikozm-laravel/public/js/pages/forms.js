/**
 * Shared helpers for AJAX forms (Digital Impact + PikoPay).
 * Sends Laravel's CSRF token and turns 422 validation errors into a readable message.
 */
window.PikoForms = (function () {
  'use strict';

  const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

  async function post(url, body) {
    const isFormData = body instanceof FormData;
    const res = await fetch(url, {
      method: 'POST',
      headers: Object.assign(
        { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' },
        isFormData ? {} : { 'Content-Type': 'application/json' }
      ),
      body: isFormData ? body : JSON.stringify(body),
    });

    let data = {};
    try { data = await res.json(); } catch (e) { /* non-JSON response */ }

    if (!res.ok) {
      const err = new Error(errorMessage(data, res.status));
      err.status = res.status;
      throw err;
    }
    return data;
  }

  function errorMessage(data, status) {
    if (data && data.errors) {
      return Object.values(data.errors).flat().join('\n');
    }
    if (status === 419) return 'Your session expired. Please refresh the page and try again.';
    if (status === 429) return 'Too many attempts. Please wait a minute and try again.';
    return (data && data.message) || 'Something went wrong. Please try again.';
  }

  function setBusy(form, busy, busyText) {
    const btn = form.querySelector('button[type="submit"]');
    if (!btn) return;
    if (busy) {
      btn.dataset.originalText = btn.innerText;
      btn.innerText = busyText || 'Submitting...';
    } else if (btn.dataset.originalText) {
      btn.innerText = btn.dataset.originalText;
    }
    btn.disabled = busy;
  }

  function closeModal(form) {
    const modalEl = form.closest('.modal');
    if (modalEl && window.bootstrap) {
      (bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl)).hide();
    }
  }

  function splitName(fullName) {
    const [firstName, ...rest] = String(fullName || '').trim().split(/\s+/);
    return { firstName: firstName || 'Customer', lastName: rest.join(' ') || ' ' };
  }

  return { post, setBusy, closeModal, splitName };
})();
