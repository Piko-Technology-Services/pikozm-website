/**
 * PikoPay – create a ClientPayment, open the Lenco widget, then ask the server to verify.
 */
(function () {
  'use strict';

  const { post, setBusy, closeModal, splitName } = window.PikoForms;
  const form = document.getElementById('clientPaymentForm');
  if (!form) return;

  form.addEventListener('submit', async function (e) {
    e.preventDefault();
    setBusy(form, true, 'Starting secure payment...');

    try {
      const res = await post(form.action, Object.fromEntries(new FormData(form)));
      const d = res.data;

      LencoPay.getPaid({
        key: d.public_key,
        reference: d.reference,
        email: d.payment.email,
        amount: Number(d.payment.amount),
        currency: d.payment.currency,
        channels: d.channels,
        customer: splitName(d.payment.full_name),
        onSuccess: function (response) {
          post(form.dataset.verifyUrl, { reference: response.reference || d.reference }).catch(function () {});
          closeModal(form);
          form.reset();
          Swal.fire({
            icon: 'success',
            title: 'Payment Initiated',
            text: 'We are confirming your Payment. You will be notified shortly via Email.',
            confirmButtonText: 'Okay',
          });
        },
        onClose: function () {
          Swal.fire('Payment Cancelled', '', 'info');
        },
        onConfirmationPending: function () {
          Swal.fire('Pending Confirmation', 'Your payment is awaiting confirmation.', 'info');
        },
      });
    } catch (err) {
      console.error(err);
      Swal.fire('Payment Failed', err.message || 'Unable to initiate payment. Please try again.', 'error');
    } finally {
      setBusy(form, false);
    }
  });
})();
