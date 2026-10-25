/** Preloader Studio v1.4 - independent admin pages and shared live preview. */
(function () {
  'use strict';
  const form = document.getElementById('ps-settings-form');
  if (!form) return;
  const gallery = document.getElementById('ps-gallery');
  const cards = gallery ? Array.from(gallery.querySelectorAll('.ps-design')) : [];
  const chosen = document.getElementById('ps-preset');
  const fallbackVisual = document.querySelector('#ps-current-visual .ps-visual');
  const preview = document.getElementById('ps-preview-screen');
  const center = document.getElementById('ps-preview-center');
  const title = document.getElementById('ps-current-preset');
  const search = document.getElementById('ps-search');
  const filters = Array.from(document.querySelectorAll('.ps-filter'));
  const fullscreen = document.getElementById('ps-fullscreen');
  const layerList = document.getElementById('ps-layer-list');
  const layerOrderField = document.getElementById('ps-layer-order');
  const deviceButtons = Array.from(document.querySelectorAll('.ps-device'));
  const savedList = document.getElementById('ps-saved-designs');
  const designNotice = document.getElementById('ps-design-message');
  const designName = document.getElementById('ps-design-name');
  const locale = window.prelstan_admin_data || {};
  let designs = Array.isArray(locale.designs) ? locale.designs : [];
  let category = 'All';
  let dragItem = null;

  const field = key => form.elements.namedItem('ps_settings[' + key + ']');
  const value = key => field(key) ? field(key).value : '';
  const checked = key => Boolean(field(key) && (field(key).type === 'hidden' ? field(key).value === '1' : field(key).checked));
  function number(key, min, max, fallback) {
    const n = Number(value(key));
    return Number.isFinite(n) ? Math.min(max, Math.max(min, n)) : fallback;
  }
  function order() {
    return layerList ? Array.from(layerList.querySelectorAll('.ps-layer-row')).map(row => row.dataset.layer) : String(value('layer_order') || 'visual,text,percent').split(',');
  }
  function updateOrder() {
    if (layerOrderField) layerOrderField.value = order().join(',');
    refresh();
  }
  function refresh() {
    const selected = cards.find(card => card.dataset.preset === chosen.value) || cards[0];
    const currentVisual = selected ? selected.querySelector('.ps-visual') : fallbackVisual;
    if (!currentVisual) return;
    const color = value('color');
    const background = value('background');
    const ink = value('text_color');
    const size = number('size', 32, 180, 72);
    const speed = number('speed', 50, 200, 100);
    const gradient = value('gradient_color');
    const glow = number('glow', 0, 100, 25);
    const bgStyle = value('background_style');
    const effect = value('exit_effect');
    if(preview){
      for(const device of ['mobile','tablet']) {
        const tuned=checked(device+'_custom');
        preview.classList.toggle('ps-tuned-'+device,tuned);
        preview.style.setProperty('--ps-device-'+device+'-size', number(device+'_size',32,180,size)+'px');
        preview.style.setProperty('--ps-device-'+device+'-speed',String(100/number(device+'_speed',50,200,speed)));
      }
    }
    [preview, fullscreen].forEach(element => {
      if (!element) return;
      element.style.setProperty('--ps-color', color);
      element.style.setProperty('--ps-bg', background);
      element.style.setProperty('--ps-gradient', gradient);
      element.style.setProperty('--ps-text', ink);
      element.style.setProperty('--ps-size', size + 'px');
      element.style.setProperty('--ps-speed', String(100 / speed));
      element.style.setProperty('--ps-glow', String(glow));
      element.style.setProperty('--ps-font-size', number('font_size', 10, 28, 14) + 'px');
      element.style.setProperty('--ps-font-weight', value('font_weight'));
      element.style.setProperty('--ps-letter-spacing', number('letter_spacing', 0, 10, 1) + 'px');
      element.style.setProperty('--ps-fade', number('fade_ms', 0, 1500, 250) + 'ms');
      element.classList.remove('ps-bg--solid', 'ps-bg--gradient', 'ps-bg--aurora', 'ps-bg--glass');
      element.classList.add('ps-bg--' + bgStyle);
    });
    if (fullscreen) fullscreen.classList.remove('ps-exit--fade', 'ps-exit--zoom-out', 'ps-exit--zoom-in', 'ps-exit--slide-up', 'ps-exit--slide-down', 'ps-exit--slide-left', 'ps-exit--slide-right', 'ps-exit--blur', 'ps-exit--rotate', 'ps-exit--curtain', 'ps-exit--split', 'ps-exit--shrink', 'ps-exit--wipe');
    if (fullscreen) fullscreen.classList.add('ps-exit--' + effect);

    const content = {};
    const visualLayer = document.createElement('div');
    visualLayer.className = 'ps-layer ps-layer--visual';
    const source = value('source');
    let imgURL = '';
    if (source === 'site_logo') imgURL = locale.siteLogoUrl || '';
    if (source === 'logo' || source === 'custom_image') {
      const box = document.querySelector('[data-upload="' + (source === 'logo' ? 'logo_id' : 'custom_image_id') + '"]');
      const image = box && box.querySelector('.ps-upload-preview img');
      if (image) imgURL = image.dataset.fullSrc || image.src;
      if (!imgURL) imgURL = source === 'logo' ? (locale.logoUrl || '') : (locale.customImageUrl || '');
    }
    if (imgURL) {
      const span = document.createElement('span');
      span.className = 'ps-custom-image ps-image--' + value('image_effect');
      const image = document.createElement('img');
      image.src = imgURL;
      image.alt = '';
      span.appendChild(image);
      visualLayer.appendChild(span);
      if (title) title.textContent = source === 'logo' ? 'Your Logo' : source === 'site_logo' ? 'Website Logo' : 'Custom Image / GIF';
    } else {
      const clone = currentVisual.cloneNode(true);
      const word = clone.querySelector('.ps-visual-word');
      if (word) word.textContent = value('loading_text').substring(0, 14) || 'BRAND';
      visualLayer.appendChild(clone);
      if (title) title.textContent = selected ? selected.querySelector('.ps-design-name').textContent : (locale.presetName || 'Preloader');
    }
    content.visual = visualLayer;
    if (checked('show_text')) {
      const row = document.createElement('div');
      row.className = 'ps-layer ps-layer--text';
      const label = document.createElement('span');
      label.className = 'ps-preview-caption';
      label.textContent = value('loading_text') || 'Loading';
      row.appendChild(label);
      content.text = row;
    }
    if (checked('show_percent')) {
      const row = document.createElement('div');
      row.className = 'ps-layer ps-layer--percent';
      const label = document.createElement('span');
      label.className = 'ps-preview-percent';
      label.textContent = '75% (estimate)';
      row.appendChild(label);
      content.percent = row;
    }
    center.replaceChildren(...order().filter(layer => content[layer]).map(layer => content[layer]));
    cards.forEach(card => card.querySelector('.ps-design-stage').style.setProperty('--ps-color', color));
    cards.forEach(card => {
      const active = card.dataset.preset === chosen.value;
      card.classList.toggle('is-selected', active);
      card.setAttribute('aria-pressed', String(active));
    });
  }

  cards.forEach(card => card.addEventListener('click', function () {
    chosen.value = card.dataset.preset;
    if (field('source')) field('source').value = 'preset';
    refresh();
  }));
  function filterGallery() {
    const term = search.value.trim().toLowerCase();
    let visible = 0;
    cards.forEach(card => {
      const matches = (category === 'All' || card.dataset.category === category) && card.dataset.name.includes(term);
      card.hidden = !matches;
      if (matches) visible++;
    });
    const empty = document.querySelector('.ps-empty');
    if (empty) empty.hidden = visible !== 0;
  }
  filters.forEach(button => button.addEventListener('click', function () {
    category = button.dataset.filter;
    filters.forEach(item => item.classList.toggle('is-active', item === button));
    filterGallery();
  }));
  if (search) search.addEventListener('input', filterGallery);
  form.addEventListener('input', function (event) {
    if (event.target.closest('#ps-library-panel')) return;
    refresh();
  });
  form.addEventListener('change', refresh);

  // HTML Drag & Drop, with keyboard/mobile-friendly up/down controls.
  if (layerList) {
  layerList.addEventListener('click', function (event) {
    const button = event.target.closest('.ps-layer-up,.ps-layer-down');
    if (!button) return;
    const row = button.closest('.ps-layer-row');
    if (button.classList.contains('ps-layer-up') && row.previousElementSibling) layerList.insertBefore(row, row.previousElementSibling);
    if (button.classList.contains('ps-layer-down') && row.nextElementSibling) layerList.insertBefore(row.nextElementSibling, row);
    updateOrder();
  });
  layerList.addEventListener('dragstart', event => {
    const row = event.target.closest('.ps-layer-row');
    if (!row) return;
    dragItem = row;
    row.classList.add('is-dragging');
    if (event.dataTransfer) { event.dataTransfer.effectAllowed = 'move'; event.dataTransfer.setData('text/plain', row.dataset.layer); }
  });
  layerList.addEventListener('dragover', event => {
    const row = event.target.closest('.ps-layer-row');
    if (dragItem && row && row !== dragItem) { event.preventDefault(); row.classList.add('is-target'); }
  });
  layerList.addEventListener('dragleave', event => {
    const row = event.target.closest('.ps-layer-row');
    if (row) row.classList.remove('is-target');
  });
  layerList.addEventListener('drop', event => {
    const row = event.target.closest('.ps-layer-row');
    if (!dragItem || !row || dragItem === row) return;
    event.preventDefault();
    const positions = Array.from(layerList.children);
    if (positions.indexOf(dragItem) < positions.indexOf(row)) layerList.insertBefore(dragItem, row.nextSibling);
    else layerList.insertBefore(dragItem, row);
    updateOrder();
  });
  layerList.addEventListener('dragend', () => {
    layerList.querySelectorAll('.ps-layer-row').forEach(row => row.classList.remove('is-target', 'is-dragging'));
    dragItem = null;
  });

  } // end builder drag handlers

  document.querySelectorAll('.ps-upload').forEach(box => {
    const input = box.querySelector('input[type=hidden]');
    const view = box.querySelector('.ps-upload-preview');
    box.querySelector('.ps-pick-image').addEventListener('click', function () {
      if (!window.wp || !wp.media) return;
      const picker = wp.media({ title: locale.mediaTitle, button: { text: locale.mediaButton }, multiple: false, library: { type: 'image' } });
      picker.on('select', function () {
        const attachment = picker.state().get('selection').first().toJSON();
        if (attachment.type !== 'image') return;
        input.value = attachment.id;
        const img = document.createElement('img');
        img.src = attachment.url;
        img.dataset.fullSrc = attachment.url;
        img.alt = '';
        view.replaceChildren(img);
        refresh();
      });
      picker.open();
    });
    box.querySelector('.ps-remove-image').addEventListener('click', function () {
      input.value = '0';
      const icon = document.createElement('span');
      icon.className = 'dashicons dashicons-format-image';
      view.replaceChildren(icon);
      refresh();
    });
  });

  // v1.6: render at the actual reference CSS viewport, then scale the complete frame.
  const deviceStage = document.getElementById('ps-device-stage');
  const devicePreview = window.PSStudioDevicePreview && deviceStage
    ? window.PSStudioDevicePreview.create(deviceStage, document.getElementById('ps-device-shell'), document.getElementById('ps-device-meta'), 'preloader')
    : null;
  deviceButtons.forEach(button => button.addEventListener('click', function () {
    deviceButtons.forEach(item => {
      const active = item === button;
      item.classList.toggle('is-active', active);
      item.setAttribute('aria-pressed', String(active));
    });
    if (devicePreview) devicePreview.setDevice(button.dataset.device);
    else {
      preview.classList.remove('ps-screen--desktop', 'ps-screen--tablet', 'ps-screen--mobile');
      preview.classList.add('ps-screen--' + button.dataset.device);
    }
  }));

  function showNotice(message, error) {
    if (!designNotice) return;
    designNotice.textContent = message;
    designNotice.style.color = error ? '#a8324e' : '#5c4cc6';
  }
  function libraryRender() {
    if (!savedList) return;
    savedList.replaceChildren();
    if (!designs.length) {
      const empty = document.createElement('div');
      empty.className = 'ps-library-empty';
      empty.textContent = 'No saved designs yet. Make a design and save it here.';
      savedList.appendChild(empty);
      return;
    }
    designs.forEach(item => {
      const row = document.createElement('div');
      row.className = 'ps-library-item';
      const name = document.createElement('span');
      name.className = 'ps-library-item-name';
      name.textContent = item.name;
      const load = document.createElement('button');
      load.type = 'button'; load.className = 'button'; load.textContent = 'Load';
      load.textContent = 'Load in Builder';
      load.addEventListener('click', () => { window.location.href = (locale.builderUrl || 'admin.php?page=preloader-studio-builder') + '&load_design=' + encodeURIComponent(item.id); });
      const remove = document.createElement('button');
      remove.type = 'button'; remove.className = 'button-link-delete'; remove.textContent = 'Delete';
      remove.addEventListener('click', () => manage('delete', item.id));
      row.append(name, load, remove);
      savedList.appendChild(row);
    });
  }
  async function manage(operation, id) {
    const button = document.getElementById('ps-save-design');
    const payload = operation === 'save' ? new FormData(form) : new FormData();
    payload.set('action', 'ps_studio_design');
    payload.set('nonce', locale.designNonce);
    payload.set('operation', operation);
    if (operation === 'save') payload.set('design_name', designName.value.trim());
    if (operation === 'delete') payload.set('design_id', id);
    button.disabled = true;
    try {
      const response = await fetch(locale.ajaxUrl, { method: 'POST', body: payload, credentials: 'same-origin' });
      const result = await response.json();
      if (!response.ok || !result.success || !Array.isArray(result.data.designs)) {
        throw new Error(result.data && result.data.message ? result.data.message : locale.designError);
      }
      designs = result.data.designs;
      libraryRender();
      if (operation === 'save') designName.value = '';
      showNotice(operation === 'save' ? locale.designSaved : locale.designDeleted, false);
    } catch (error) {
      showNotice(error.message || locale.designError || 'Could not complete the request.', true);
    } finally {
      button.disabled = false;
    }
  }
  if (document.getElementById('ps-save-design')) document.getElementById('ps-save-design').addEventListener('click', () => manage('save')); 

  const open = document.getElementById('ps-open-preview');
  const close = document.getElementById('ps-close-preview');
  const testExit = document.getElementById('ps-test-exit');
  function hideFullscreen() { fullscreen.hidden = true; fullscreen.classList.remove('ps-is-hiding'); open.focus(); }
  if (open && close && testExit && fullscreen) {
  open.addEventListener('click', function () {
    refresh();
    fullscreen.classList.remove('ps-is-hiding');
    fullscreen.querySelector('#ps-fullscreen-content').replaceChildren(...Array.from(center.children).map(child => child.cloneNode(true)));
    fullscreen.hidden = false;
    close.focus();
  });
  close.addEventListener('click', hideFullscreen);
  testExit.addEventListener('click', function () {
    fullscreen.classList.add('ps-is-hiding');
    const delay = number('fade_ms', 0, 1500, 250);
    window.setTimeout(() => fullscreen.classList.remove('ps-is-hiding'), delay + 500);
  });
  fullscreen.addEventListener('click', event => { if (event.target === fullscreen) hideFullscreen(); });
  document.addEventListener('keydown', event => { if (event.key === 'Escape' && !fullscreen.hidden) hideFullscreen(); });
  } // end fullscreen events
  libraryRender();
  refresh();
})();
