/** Independent responsive bottom navigation and conservative theme bar suppression. */
(function () {
  'use strict';
  function start() {
    const nav = document.getElementById('ps-studio-bottom-nav');
    if (!nav || !document.body || !window.matchMedia) return;
    const mode = nav.dataset.override || 'guide';
    // Auto-hide dock in the scroll direction, independent of selected-button animation.
    // Uses CSS individual `translate` so WoodMart/tablet centering transform is preserved.
    const autoHide = nav.dataset.scrollBehavior === 'hide-down';
    let lastScrollY = Math.max(0, window.scrollY || 0);
    let scrollDistance = 0;
    let scrollDirection = 0;
    let scrollQueued = false;
    function showDock() { nav.classList.remove('psn-scroll-hidden'); }
    function maybeUpdateDock() {
      scrollQueued = false;
      if (!autoHide) return;
      const now = Math.max(0, window.scrollY || document.documentElement.scrollTop || 0);
      const delta = now - lastScrollY;
      lastScrollY = now;
      if (now < 70 || document.activeElement && nav.contains(document.activeElement) ||
          (nav.querySelector('#psn-search-form:not([hidden])'))) {
        scrollDistance = 0;
        showDock();
        return;
      }
      if (Math.abs(delta) < 2) return;
      const dir = delta > 0 ? 1 : -1;
      scrollDistance = dir === scrollDirection ? scrollDistance + Math.abs(delta) : Math.abs(delta);
      scrollDirection = dir;
      if (scrollDistance >= 18) {
        nav.classList.toggle('psn-scroll-hidden', dir > 0);
        scrollDistance = 0;
      }
    }
    if (autoHide) {
      window.addEventListener('scroll', function () {
        if (scrollQueued) return;
        scrollQueued = true;
        window.requestAnimationFrame(maybeUpdateDock);
      }, { passive: true });
      nav.addEventListener('focusin', showDock);
      nav.addEventListener('pointerenter', showDock);
      window.addEventListener('pageshow', function () { lastScrollY = window.scrollY || 0; showDock(); });
    }

    // Exclusive selection: the same WordPress page can be linked by multiple
    // buttons (e.g. Shop, Account and WhatsApp all pointing to Sample Page).
    // PHP picks only one active item; a tab-scoped selection remembers which
    // of those identical links was actually clicked during navigation.
    const entries = Array.from(nav.querySelectorAll('.psn-item'));
    const selectionKey = 'ps-studio-nav-selected-v181';
    function canonical(href) {
      try {
        const url = new URL(href, window.location.href);
        if (!/^https?:$/.test(url.protocol)) return '';
        return url.origin + (url.pathname.replace(/\/+$/, '') || '/') + url.search;
      } catch (error) { return ''; }
    }
    function markSelected(entry, isCurrentPage) {
      entries.forEach(function (candidate) {
        const selected = candidate === entry;
        candidate.classList.toggle('is-active', selected);
        // aria-current is only appropriate for a link to the current document.
        // Search buttons and external links are not the current page.
        if (selected && isCurrentPage && candidate.tagName === 'A') candidate.setAttribute('aria-current', 'page');
        else candidate.removeAttribute('aria-current');
      });
    }
    const initialActive = entries.find(entry => entry.classList.contains('is-active')) || null;
    // The current URL must match the stored link; stale and unrelated click
    // history must never cause highlighting on another page.
    try {
      const stored = JSON.parse(window.sessionStorage.getItem(selectionKey) || 'null');
      if (stored && Number.isInteger(stored.index) && stored.index >= 0 &&
          stored.index < entries.length && stored.href === canonical(window.location.href) &&
          Date.now() - stored.at >= 0 && Date.now() - stored.at < 20 * 60 * 1000 &&
          canonical(entries[stored.index].getAttribute('href')) === stored.href) {
        markSelected(entries[stored.index], true);
      } else if (stored) {
        window.sessionStorage.removeItem(selectionKey);
      }
    } catch (error) { /* Private browsing or storage restrictions. */ }
    // Also collapse bad/duplicated server-side states from old cached pages.
    const current = entries.filter(function (entry) { return entry.classList.contains('is-active'); });
    if (current.length > 1) markSelected(current[0], true);
    entries.forEach(function (entry, index) {
      entry.addEventListener('click', function (event) {
        if (event.defaultPrevented || event.button !== 0 || event.ctrlKey ||
            event.metaKey || event.altKey || event.shiftKey) return;
        const href = entry.getAttribute('href');
        const newTab = entry.getAttribute('target') === '_blank';
        const target = href ? canonical(href) : '';
        // Keep the old tab's current-page state when a link opens in a new tab.
        if (!newTab) markSelected(entry, target && target === canonical(window.location.href));
        if (href && !newTab && target) {
          try {
            window.sessionStorage.setItem(selectionKey, JSON.stringify({
              index: index, href: target, at: Date.now()
            }));
          } catch (error) { /* Storage is optional; selection still works. */ }
        }
      });
    });
    const searchToggle = nav.querySelector('[data-psn-search-toggle]');
    const searchPanel = nav.querySelector('#psn-search-form');
    if (searchToggle && searchPanel) {
      function closeSearch() {
        if (searchPanel.hidden) return;
        searchPanel.hidden=true; searchToggle.setAttribute('aria-expanded','false');
        if (!window.location.search.match(/[?&]s=/)) markSelected(initialActive, Boolean(initialActive && initialActive.tagName === 'A'));
      }
      searchToggle.addEventListener('click', function () {
        const opening=searchPanel.hidden;
        if (!opening) { closeSearch(); return; }
        searchPanel.hidden=false; searchToggle.setAttribute('aria-expanded','true'); showDock();
        markSelected(searchToggle, false);
        const input=searchPanel.querySelector('input[name=s]'); if(input)input.focus();
      });
      document.addEventListener('keydown',function (event) { if(event.key==='Escape' && !searchPanel.hidden){closeSearch(); searchToggle.focus();} });
      document.addEventListener('pointerdown',function(event) {if(!nav.contains(event.target))closeSearch();});
    }
    const sizes = {
      mobile: window.matchMedia('(max-width:767px)'),
      tablet: window.matchMedia('(min-width:768px) and (max-width:1024px)'),
      both: window.matchMedia('(max-width:1024px)')
    };
    const mq = sizes[(nav.className.match(/psn-device--(mobile|tablet|both)/) || [])[1] || 'both'];
    const stored = new Map();
    const defaults = [
      '#mobile-bottom-navigation', '.mobile-bottom-navigation', '.mobile-bottom-nav',
      '.mobile-bottom-bar', '.mobile-bottom-menu', '.bottom-mobile-nav',
      '.wd-toolbar', '.woodmart-toolbar'
    ];
    const manual = (nav.dataset.selectors || '').split(',').map(s => s.trim()).filter(s => /^[#.][A-Za-z_-][A-Za-z0-9_-]*$/.test(s));
    const selectors = (mode === 'auto' ? defaults.concat(manual) : mode === 'manual' ? manual : []);
    let queued = false;
    let observer = null;
    const oldPadding = document.body.style.getPropertyValue('padding-bottom');
    const oldPaddingPriority = document.body.style.getPropertyPriority('padding-bottom');
    function restore() {
      stored.forEach((style, el) => {
        if (el.isConnected) {
          el.style.setProperty('display', style.value, style.priority);
          el.removeAttribute('data-psn-suppressed');
        }
      });
      stored.clear();
    }
    function sync() {
      queued = false;
      if (!mq.matches || nav.getClientRects().length === 0) {
        document.body.style.setProperty('padding-bottom', oldPadding, oldPaddingPriority);
        nav.dataset.appliedPadding = '0';
        restore();
        return;
      }
      const bar = nav.getBoundingClientRect();
      // Preserve existing footer spacing; the new navigation must not cover content.
      const existing = parseFloat(window.getComputedStyle(document.body).paddingBottom) || 0;
      const own = parseFloat(nav.dataset.appliedPadding || '0');
      const base = Math.max(0, existing - own);
      const height = Math.ceil(window.innerHeight - bar.top + 8);
      document.body.style.setProperty('padding-bottom', Math.max(base, height) + 'px');
      nav.dataset.appliedPadding = String(Math.max(base, height) - base);
      if (!selectors.length) return;
      const candidates = new Set();
      selectors.forEach(s => { try { document.querySelectorAll(s).forEach(el => candidates.add(el)); } catch (e) { /* Invalid theme selector. */ } });
      candidates.forEach(el => {
        if (el === nav || el.contains(nav) || nav.contains(el) || stored.has(el)) return;
        const r = el.getBoundingClientRect();
        const css = window.getComputedStyle(el);
        // Only a fixed/sticky horizontal bar anchored at the viewport bottom.
        if (!['fixed', 'sticky'].includes(css.position) || css.display === 'none' || r.width < 120 || r.height < 25 || r.height > 160 || Math.abs(window.innerHeight - r.bottom) > 42) return;
        stored.set(el, { value: el.style.getPropertyValue('display'), priority: el.style.getPropertyPriority('display') });
        el.style.setProperty('display', 'none', 'important');
        el.setAttribute('data-psn-suppressed', '1');
      });
    }
    function schedule() { if (!queued) { queued = true; window.requestAnimationFrame(sync); } }
    schedule();
    window.addEventListener('resize', schedule, { passive: true });
    window.addEventListener('orientationchange', schedule, { passive: true });
    if (selectors.length && typeof MutationObserver !== 'undefined') {
      observer = new MutationObserver(schedule);
      observer.observe(document.body, { childList: true, subtree: true });
    }
    window.addEventListener('pagehide', function () { restore(); if (observer) observer.disconnect(); document.body.style.setProperty('padding-bottom', oldPadding, oldPaddingPriority); }, { once: true });
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start, { once: true });
  else start();
})();
