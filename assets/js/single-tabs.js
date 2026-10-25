/** Lightweight, accessible product tabs. No jQuery or 3rd-party library. */
(function () {
  'use strict';
  function setup(root) {
    var buttons = Array.prototype.slice.call(root.querySelectorAll('[role="tab"]'));
    if (!buttons.length) return;
    function activate(button, focus) {
      buttons.forEach(function (item) {
        var selected = item === button;
        item.classList.toggle('is-active', selected);
        item.setAttribute('aria-selected', selected ? 'true' : 'false');
        item.tabIndex = selected ? 0 : -1;
        var panel = document.getElementById(item.getAttribute('aria-controls'));
        if (panel) {
          panel.hidden = !selected;
          panel.classList.toggle('is-active', selected);
        }
      });
      if (focus) button.focus();
    }
    buttons.forEach(function (button, i) {
      button.addEventListener('click', function () { activate(button, false); });
      button.addEventListener('keydown', function (event) {
        var next = -1;
        if (event.key === 'ArrowRight') next = (i + 1) % buttons.length;
        if (event.key === 'ArrowLeft') next = (i - 1 + buttons.length) % buttons.length;
        if (event.key === 'Home') next = 0;
        if (event.key === 'End') next = buttons.length - 1;
        if (next < 0) return;
        event.preventDefault();
        activate(buttons[next], true);
        buttons[next].scrollIntoView({block:'nearest',inline:'nearest',behavior:'instant'});
      });
    });
    function selectHash(hash) {
      if (!hash) return false;
      if (hash === '#reviews' || hash === '#tab-reviews' || hash === '#comments') {
        var review = buttons.filter(function(b){return /-reviews$/.test(b.id);})[0];
        if (review) {activate(review,false);return true;}
      }
      for (var i = 0; i < buttons.length; i++) {
        var panel = document.getElementById(buttons[i].getAttribute('aria-controls'));
        if (hash === '#' + buttons[i].id || (panel && hash === '#' + panel.id)) {
          activate(buttons[i],false);return true;
        }
      }
      return false;
    }
    // Panels are all visible until JavaScript activates the interactive tab interface.
    // This preserves product details/reviews when JavaScript is unavailable.
    activate(buttons[0], false);
    selectHash(window.location.hash);
    window.addEventListener('hashchange', function () { selectHash(window.location.hash); }, {passive:true});
    document.addEventListener('click', function (event) {
      var link = event.target.closest && event.target.closest('a[href]');
      if (!link) return;
      var hash = link.hash;
      if (!hash || !link.href.startsWith(window.location.origin + window.location.pathname)) return;
      if (selectHash(hash)) {
        var nav = root.querySelector('[role="tablist"]');
        if (nav) nav.scrollIntoView({block:'start',behavior:'smooth'});
      }
    });
  }
  function init() {
    document.querySelectorAll('.psp-detail-tabs').forEach(setup);
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded',init,{once:true});
  else init();
})();
