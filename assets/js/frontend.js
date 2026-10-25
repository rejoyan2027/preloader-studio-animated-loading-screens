/** Front-end controller. No external dependencies; overlays never stay stuck on a failed load. */
(function () {
  'use strict';
  // With JS disabled, the CSS leaves the overlay hidden and the site usable.
  document.documentElement.classList.add('ps-script-ready');

  function start() {
    const overlay = document.getElementById('ps-studio-overlay');
    if (!overlay) return;
    const data = overlay.dataset;
    const debug = data.debug === '1';
    const report = { mode: data.themeMode || 'auto', candidates: 0, suppressed: 0, skipped: '', started: Date.now() };
    const debugLog = () => {
      if (!debug) return;
      const panel = document.createElement('div');
      panel.id = 'ps-studio-diagnostics';
      panel.setAttribute('role', 'status');
      panel.style.cssText = 'position:fixed;bottom:12px;left:12px;right:12px;max-width:530px;z-index:2147483646;background:#121b2d;color:#f8fafc;font:13px/1.65 system-ui,sans-serif;padding:16px;border:1px solid #6b7e9d;border-radius:12px;box-shadow:0 12px 30px #0005';
      const headline = document.createElement('strong');
      headline.textContent = 'Preloader Studio - browser diagnostics';
      const body = document.createElement('div');
      body.textContent = 'Mode: ' + report.mode + ' | Fullscreen candidates: ' + report.candidates + ' | Suppressed: ' + report.suppressed + (report.skipped ? ' | Skip: ' + report.skipped : ' | Finished in: ' + (Date.now() - report.started) + ' ms');
      const close = document.createElement('button');
      close.type = 'button'; close.textContent = 'Close';
      close.style.cssText = 'display:block;margin-top:9px;padding:5px 15px;cursor:pointer';
      close.addEventListener('click', () => panel.remove());
      panel.append(headline, body, close);
      document.body.appendChild(panel);
    };
    const read = (key, fallback) => Number.isFinite(Number(data[key])) ? Number(data[key]) : fallback;
    const minimum = Math.max(0, read('min', 250));
    const maximum = Math.max(1500, read('max', 4500));
    const fade = Math.max(0, read('fade', 250));
    const reduced = typeof window.matchMedia === 'function' && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const mobile = typeof window.matchMedia === 'function' && window.matchMedia('(max-width: 767px)').matches;
    let stored = false;
    try { stored = window.sessionStorage.getItem('ps-studio-seen') === 'yes'; } catch (e) { /* Storage may be restricted. */ }
    if (reduced || (data.devices === 'mobile' && !mobile) || (data.devices === 'desktop' && mobile) || (data.showOn === 'session' && stored)) {
      report.skipped = reduced ? 'reduced-motion' : 'frequency or device rule';
      overlay.remove();
      debugLog();
      return;
    }
    // Skip only completed, measured quick navigations. Balanced skips very fast
    // pages (<=150ms), Fast uses the configured threshold, Always disables skip.
    if (data.performance !== 'always' && document.readyState === 'complete' && window.performance && typeof window.performance.getEntriesByType === 'function') {
      const entries = window.performance.getEntriesByType('navigation');
      const navigation = entries && entries[0];
      const limit = data.performance === 'fast' ? Math.max(100, Math.min(1200, read('fastSkip', 400))) : 150;
      if (navigation && navigation.loadEventEnd > 0 && navigation.loadEventEnd <= limit) {
        report.skipped = 'fast navigation (' + Math.round(navigation.loadEventEnd) + 'ms)';
        overlay.remove();
        debugLog();
        return;
      }
    }
    if (data.showOn === 'session') {
      try { window.sessionStorage.setItem('ps-studio-seen', 'yes'); } catch (e) { /* Private mode, etc. */ }
    }

    // Conservative suppression: no theme option writes, no wildcard CSS hiding.
    // Auto handles known IDs/classes only when the matching element itself covers
    // most of the viewport. Manual mode only considers admin-entered selectors.
    // Suppression is runtime-only and stops checking shortly after window load.
    const standard = '#preloader,#site-preloader,#page-preloader,.site-preloader,.theme-preloader,.page-preloader,.loader-wrapper,.loading-overlay';
    const seenCandidates = new WeakSet();
    const suppressed = new WeakSet();
    function preventDuplicateLoaders() {
      const mode = data.themeMode || (data.theme === '1' ? 'auto' : 'off');
      if (mode === 'off') return;
      const manual = (data.selectors || '').split(',').map(s => s.trim()).filter(s => /^[#.][A-Za-z_-][A-Za-z0-9_-]*$/.test(s));
      const selectors = (mode === 'auto' ? standard.split(',') : []).concat(manual);
      const targets = new Set();
      selectors.forEach(function (selector) {
        try { document.querySelectorAll(selector).forEach(node => targets.add(node)); } catch (e) { /* invalid theme DOM; ignore */ }
      });
      targets.forEach(function (node) {
        if (node === overlay || overlay.contains(node) || node.contains(overlay)) return;
        if (!seenCandidates.has(node)) { seenCandidates.add(node); report.candidates += 1; }
        if (suppressed.has(node)) return;
        const r = node.getBoundingClientRect();
        const style = window.getComputedStyle(node);
        if (style.display === 'none' || style.visibility === 'hidden' || style.opacity === '0') return;
        const fullscreen = (style.position === 'fixed' || style.position === 'absolute') &&
          r.width >= window.innerWidth * 0.75 && r.height >= window.innerHeight * 0.75;
        if (fullscreen) {
          node.style.setProperty('display', 'none', 'important');
          node.setAttribute('data-ps-theme-suppressed', '1');
          suppressed.add(node);
          report.suppressed += 1;
        }
      });
    }
    preventDuplicateLoaders();
    // Some themes insert overlays late; observe briefly, never indefinitely.
    let observer = null;
    if (document.readyState !== 'complete' && typeof MutationObserver === 'function' && (data.themeMode !== 'off')) {
      observer = new MutationObserver(function () { preventDuplicateLoaders(); });
      observer.observe(document.body, { childList: true, subtree: true });
    }
    function endThemeScan() {
      preventDuplicateLoaders();
      if (observer) observer.disconnect();
      observer = null;
    }
    window.addEventListener('load', endThemeScan, { once: true });
    const startedAt = Date.now();
    let finished = false;
    let safeTimer;
    let interval;
    let loadEventReceived = document.readyState === 'complete';
    const percentageElements = Array.from(overlay.querySelectorAll('.ps-loading-percent,.ps-visual-counter'));
    function setPercent(value) {
      percentageElements.forEach((element) => { element.textContent = Math.max(0, Math.min(100, value)) + '%'; });
    }
    if (percentageElements.length) {
      interval = window.setInterval(function () {
        if (finished) return;
        // This is a display estimate, capped at 90 until the page load event.
        const elapsed = Date.now() - startedAt;
        setPercent(Math.min(90, Math.floor((elapsed / maximum) * 100)));
      }, 110);
    }
    function done() {
      if (finished) return;
      finished = true;
      window.clearTimeout(safeTimer);
      window.clearInterval(interval);
      if (observer) observer.disconnect();
      observer = null;
      setPercent(100);
      overlay.classList.add('ps-is-hiding');
      window.setTimeout(function () { overlay.remove(); debugLog(); }, reduced ? 0 : fade + 40);
    }
    function onLoaded() {
      if (loadEventReceived && finished) return;
      loadEventReceived = true;
      const remaining = Math.max(0, minimum - (Date.now() - startedAt));
      window.setTimeout(done, remaining);
    }
    safeTimer = window.setTimeout(done, maximum);
    if (loadEventReceived) onLoaded();
    else window.addEventListener('load', onLoaded, { once: true });
    window.addEventListener('pagehide', done, { once: true });
    // Back-forward cache restoration should never restore a blocking overlay.
    window.addEventListener('pageshow', function (event) { if (event.persisted) done(); });
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start, { once: true });
  else start();
})();
