(function () {
  'use strict';

  var page = document.querySelector('.customer-qris-page[data-payment-status-url]');
  if (!page) return;

  var checkPending = false;

  function checkPaymentStatus() {
    if (document.hidden || checkPending) return;
    checkPending = true;

    fetch(page.dataset.paymentStatusUrl, {
      credentials: 'same-origin',
      headers: { Accept: 'application/json' },
      cache: 'no-store'
    }).then(function (response) {
      if (!response.ok) throw new Error('Status pembayaran gagal diperiksa: HTTP ' + response.status);
      return response.json();
    }).then(function (status) {
      var bookingId = status.estimateBookingId == null ? '' : String(status.estimateBookingId);
      var updatedAt = status.estimateUpdatedAt == null ? '' : String(status.estimateUpdatedAt);
      if (bookingId !== page.dataset.bookingId || updatedAt !== page.dataset.bookingUpdatedAt) {
        window.location.reload();
      }
    }).catch(function (error) {
      console.error(error);
    }).then(function () {
      checkPending = false;
    });
  }

  window.setInterval(checkPaymentStatus, 10000);
  document.addEventListener('visibilitychange', function () {
    if (!document.hidden) checkPaymentStatus();
  });
})();
