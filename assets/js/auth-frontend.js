/* Only anchor targets; never intercepts, modifies or reads authentication fields. */
(function () {
  'use strict';
  function setup() {
    if (!document.body.classList.contains('psa-wc-active')) return;
    var root = document.querySelector('#customer_login');
    if (!root) return;
    var login = root.querySelector('.u-column1, .col-1, form.woocommerce-form-login');
    var registration = root.querySelector('.u-column2, .col-2, form.woocommerce-form-register');
    if (login && !document.getElementById('psa-account-login')) login.id = 'psa-account-login';
    if (registration && !document.getElementById('psa-account-register')) registration.id = 'psa-account-register';
    var nav = document.querySelector('.psa-account-jump');
    if (!nav) return;
    nav.querySelectorAll('a[href^="#psa-account-"]').forEach(function (a) {
      if (!document.querySelector(a.getAttribute('href'))) a.hidden = true;
    });
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', setup);
  else setup();
}());
