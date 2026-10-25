/**
 * Preloader Studio: accessible visibility controls for custom account passwords.
 * No build system or JavaScript dependencies are required.
 */
(function () {
  'use strict';

  function initializePasswordToggles() {
    var buttons = document.querySelectorAll('.psx-account .psx-eye');

    buttons.forEach(function (button) {
      button.addEventListener('click', function () {
        var wrapper = button.closest('.psx-password-wrap');
        if (!wrapper) return;

        var input = wrapper.querySelector('input');
        if (!input) return;

        var show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        button.setAttribute('aria-pressed', show ? 'true' : 'false');
        button.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
        button.textContent = show ? '◌' : '◉';
      });
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializePasswordToggles);
  } else {
    initializePasswordToggles();
  }
})();
