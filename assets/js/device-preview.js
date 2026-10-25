/** v1.6.0 - true CSS pixel-sized viewports displayed at a fit-to-panel scale. */
(function () {
  'use strict';
  const sizes = Object.freeze({
    mobile: Object.freeze({ width: 390, height: 844, name: 'Mobile' }),
    tablet: Object.freeze({ width: 820, height: 1180, name: 'Tablet' }),
    desktop: Object.freeze({ width: 1440, height: 900, name: 'Desktop' })
  });
  function create(stage, shell, caption, mode) {
    if (!stage || !shell) return null;
    let device = mode === 'navigation' ? 'mobile' : 'desktop';
    let orientation = 'portrait';
    let queued = false;
    let lastWidth = 0;
    function fit() {
      queued = false;
      const base = sizes[device];
      const rotated = orientation === 'landscape' && device !== 'desktop';
      const spec = { width: rotated ? base.height : base.width, height: rotated ? base.width : base.height,
        name: base.name };
      const outerWidth = Math.max(180, stage.getBoundingClientRect().width);
      const padding = outerWidth < 420 ? 16 : (stage.classList.contains('is-expanded') ? 70 : 22);
      const expanded = stage.classList.contains('is-expanded');
      const maxHeight = expanded ? Math.max(300, window.innerHeight - 100) : (mode === 'navigation' ? 510 : 480);
      const scale = Math.min(1, (outerWidth - padding) / spec.width, maxHeight / spec.height);
      const left = Math.max(0, (outerWidth - spec.width * scale) / 2);
      shell.style.setProperty('--ps-device-width', spec.width + 'px');
      shell.style.setProperty('--ps-device-height', spec.height + 'px');
      shell.style.setProperty('--ps-device-scale', String(scale));
      shell.style.left = left + 'px';
      shell.style.top = expanded ? Math.max(56, (window.innerHeight - spec.height * scale) / 2) + 'px' : '0px';
      stage.style.height = expanded ? '' : Math.ceil(spec.height * scale + 5) + 'px';
      if (typeof rotateControl !== 'undefined' && rotateControl) { rotateControl.disabled = device === 'desktop'; rotateControl.setAttribute('aria-pressed', String(rotated)); }
      stage.dataset.device = device;
      stage.dataset.orientation = rotated ? 'landscape' : 'portrait';
      shell.dataset.orientation = rotated ? 'landscape' : 'portrait';
      shell.classList.remove('ps-device-shell--mobile', 'ps-device-shell--tablet', 'ps-device-shell--desktop');
      shell.classList.add('ps-device-shell--' + device);
      if (caption) caption.textContent = spec.name + ' ' + (rotated ? 'Landscape' : 'Portrait') + ' viewport: ' + spec.width + ' \u00d7 ' + spec.height + ' CSS px  |  Preview: ' + Math.round(scale * 100) + '% scale';
      lastWidth = outerWidth;
    }
    function requestFit() {
      if (queued) return;
      queued = true;
      window.requestAnimationFrame(fit);
    }
    function setDevice(next) {
      if (!Object.prototype.hasOwnProperty.call(sizes, next)) return;
      if (mode === 'navigation' && next === 'desktop') return;
      device = next;
      if (next === 'desktop') orientation = 'portrait';
      if (typeof onDeviceChange === 'function') onDeviceChange(device);
      fit();
    }
    let onDeviceChange = null;
    const rotateButton = stage.parentElement && stage.parentElement.querySelector('[data-ps-rotate]');
    // Buttons may be outside stage's direct parent (preloader preview sidebar).
    const rotateControl = rotateButton || document.querySelector(mode === 'navigation' ? '.psn-rotate-button' : '.ps-device-rotate');
    if (rotateControl) rotateControl.addEventListener('click', () => {
      if (device === 'desktop') return;
      orientation = orientation === 'portrait' ? 'landscape' : 'portrait';
      rotateControl.setAttribute('aria-pressed', String(orientation === 'landscape'));
      fit();
    });
    const expandButton = document.getElementById(mode === 'navigation' ? 'psn-open-device' : 'ps-open-device');
    function toggleExpanded(expand) {
      stage.classList.toggle('is-expanded', !!expand);
      if (expandButton) expandButton.setAttribute('aria-expanded', String(!!expand));
      fit();
      if (expand) {
        const close = stage.querySelector('[data-ps-device-close]');
        if (close) close.focus();
      } else if (expandButton) expandButton.focus();
    }
    if (expandButton) expandButton.addEventListener('click', () => toggleExpanded(true));
    const close = stage.querySelector('[data-ps-device-close]');
    if (close) close.addEventListener('click', () => toggleExpanded(false));
    document.addEventListener('keydown', event => {
      if (event.key === 'Escape' && stage.classList.contains('is-expanded')) toggleExpanded(false);
    });
    // The surrounding admin sidebar can resize independently of the browser window.
    if (typeof ResizeObserver !== 'undefined') {
      const observer = new ResizeObserver(() => {
        const width = stage.getBoundingClientRect().width;
        if (Math.abs(width - lastWidth) > 0.5) requestFit();
      });
      observer.observe(stage);
    }
    window.addEventListener('resize', requestFit, { passive: true });
    fit();
    return { setDevice: setDevice, refresh: requestFit, getDevice: () => device, getOrientation: () => orientation, onDeviceChange: fn => { onDeviceChange=fn; } };
  }
  window.PSStudioDevicePreview = Object.freeze({ create: create, sizes: sizes });
})();
