/**
 * Piko Digital Impact Programme – modal forms.
 *  - .js-ajax-form forms (partner, volunteer, support) post to their action URL.
 *  - The donate form creates a donation, opens the Lenco widget, then asks the
 *    server to verify the payment.
 */
(function () {
  'use strict';

  const { post, setBusy, closeModal, splitName } = window.PikoForms;

  document.querySelectorAll('form.js-ajax-form').forEach(function (form) {
    form.addEventListener('submit', async function (e) {
      e.preventDefault();
      setBusy(form, true);

      try {
        await post(form.action, new FormData(form));
        Swal.fire({ icon: 'success', title: form.dataset.successTitle, text: form.dataset.successText });
        form.reset();
        closeModal(form);
      } catch (err) {
        Swal.fire({ icon: 'error', title: 'Submission Failed', text: err.message });
      } finally {
        setBusy(form, false);
      }
    });
  });

  const donateForm = document.getElementById('donateForm');
  if (!donateForm) return;

  donateForm.addEventListener('submit', async function (e) {
    e.preventDefault();
    setBusy(donateForm, true, 'Starting secure payment...');

    try {
      const res = await post(donateForm.action, Object.fromEntries(new FormData(donateForm)));
      const d = res.data;

      LencoPay.getPaid({
        key: d.public_key,
        reference: d.reference,
        email: d.donation.email,
        amount: Number(d.donation.amount),
        currency: d.donation.currency,
        channels: d.channels,
        customer: splitName(d.donation.name),
        onSuccess: function (response) {
          // Server confirms the status with Lenco; fire-and-forget here.
          post(donateForm.dataset.verifyUrl, { reference: response.reference || d.reference }).catch(function () {});
          closeModal(donateForm);
          donateForm.reset();
          Swal.fire({
            icon: 'success',
            title: 'Support Received ❤️',
            text: 'We are confirming your support. You will be notified shortly.',
            confirmButtonText: 'Okay',
          });
        },
        onClose: function () {
          Swal.fire({ icon: 'info', title: 'Payment Cancelled', text: 'You closed the payment window.', confirmButtonText: 'Close' });
        },
        onConfirmationPending: function () {
          Swal.fire({ icon: 'info', title: 'Pending', text: 'Your payment is pending confirmation.', confirmButtonText: 'Close' });
        },
      });
    } catch (err) {
      console.error(err);
      Swal.fire({ icon: 'error', title: 'Oops!', text: err.message || 'Donation initiation failed. Please try again.', confirmButtonText: 'Close' });
    } finally {
      setBusy(donateForm, false);
    }
  });
})();
