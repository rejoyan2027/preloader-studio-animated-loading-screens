/** Preloader Studio 1.4.1: truthful previews, valid links and one-click setup. */
(function () {
  'use strict';
  const form = document.getElementById('ps-nav-settings-form');
  const initial = window.prelstan_navigation_data || {};
  if (!form) return;
  const opts = initial.settings || {};
  const styles = initial.styles || {};
  const icons = initial.icons || {};
  // Curated palettes are opt-in: selecting a new design applies its suggested
  // colors to the draft; previously saved designs and palettes stay unchanged.
  const studioPalettes = {
    'prism-arc':['#a997ff','#10162b','#c1c9e9','#ffffff'],
    'citrus-pop':['#f18b18','#fff9dc','#765329','#ffffff'],
    'ocean-tide':['#30d6db','#06394c','#b5e7ee','#082d3d'],
    'bento-dock':['#e75b6b','#f6f4f1','#596072','#ffffff'],
    'cyber-circuit':['#53f6d2','#090e19','#a3b9c7','#04131b'],
    'clay-bubble':['#e98474','#fae6d9','#977568','#ffffff'],
    'sakura-petal':['#db5f97','#fff0f5','#87566d','#ffffff'],
    'emerald-capsule':['#b2f07c','#093d33','#c0e4d8','#113b30'],
    'retro-pixel':['#363244','#fff6d5','#494452','#ffffff'],
    'royal-velvet':['#e8bf6b','#221630','#e8d9c9','#201228'],
    'orbit-halo':['#85a8ff','#111c42','#b6cafa','#ffffff'],
    'mono-editorial':['#1a1a1a','#ffffff','#575757','#ffffff']
  };
  let styleGroup='all';
  const filterTabs=element('div','psn-style-filters');
  [['all','All 24'],['new','New 12'],['classic','Original 12']].forEach(([key,label])=>{
    const tab=element('button','psn-style-filter',label); tab.type='button';tab.dataset.group=key;
    tab.addEventListener('click',()=>{styleGroup=key;drawStyles();});filterTabs.appendChild(tab);
  });
  function applyPalette(key) {
    const colors=studioPalettes[key]; if(!colors)return;
    ['accent','background','text_color','active_color'].forEach((name,i)=>{
      const field=document.getElementById('psn-'+name);if(field){field.value=colors[i];}
    });
  }

  const pages = initial.pages || [];
  const area = document.getElementById('psn-menu-items');
  const gallery = document.getElementById('psn-style-gallery');
  gallery.before(filterTabs);
  const preview = document.getElementById('psn-live-nav');
  const phone = document.getElementById('psn-phone');
  const styleField = document.getElementById('psn-style');
  const state = { items: Array.isArray(opts.items) ? opts.items.map(item => ({ ...item })) : [], style: opts.style || 'soft-pill' };
  const maxItems = 6;
  const iconKeys = Object.keys(icons);
  const iconGroups = ['All','Popular','Shopping','Communication','Business','Media','Navigation','Content','Community','Security','Classic'];
  const sizes = {
    menu_height: ['--psn-menu-height','px',72], menu_width: ['--psn-menu-width','%',100],
    icon_size: ['--psn-icon-size','px',24], label_size: ['--psn-label-size','px',11],
    item_gap: ['--psn-item-gap','px',5], menu_radius: ['--psn-menu-radius','px',22],
    item_radius: ['--psn-item-radius','px',14], bottom_offset: ['--psn-bottom-offset','px',0]
  };
  const responsiveKeys = ['menu_height','menu_width','icon_size','label_size','bottom_offset'];
  let previewDevice = 'mobile';
  let dragged = null;
  let previewActive = 0;
  const readyNotice = document.getElementById('psn-preview-notice');
  const status = document.getElementById('psn-status');
  const enabledField = form.elements.namedItem('ps_nav[enabled]');
  function working(item) {
    if (!item || !String(item.label || '').trim()) return false;
    if (item.type === 'home' || item.type === 'search') return true;
    if (item.type === 'page') return pages.some(p => Number(p.id) === Number(item.page_id));
    if (item.type === 'url') return /^(https?:\/\/[^\s]+|\/[^\/\s][^\s]*)$/i.test(String(item.url || '').trim());
    return false;
  }
  function visibleItems() { return state.items.filter(working); }
  function duplicateDestinations(items) {
    const seen = new Set();
    let duplicates = 0;
    items.forEach(item => {
      let target = '';
      if (item.type === 'home') target = 'page:home';
      else if (item.type === 'search') target = 'action:search';
      else if (item.type === 'page') target = 'page:' + Number(item.page_id);
      else if (item.type === 'url') {
        try {
          const url = new URL(String(item.url).trim(), initial.siteUrl || window.location.href);
          target = 'url:' + url.origin.toLowerCase() + url.pathname.replace(/\/+$/, '') + url.search;
        } catch (e) { target = 'url:' + String(item.url || '').trim(); }
      }
      if (!target) return;
      if (seen.has(target)) duplicates++;
      else seen.add(target);
    });
    return duplicates;
  }
  function updateStatus() {
    const items = visibleItems();
    const ready = items.length;
    const missing = state.items.filter(item => !working(item)).length;
    const repeated = duplicateDestinations(items);
    const enabled = Boolean(enabledField && enabledField.checked);
    const duplicateHelp = repeated ? ' ' + repeated + ' shared destination' + (repeated===1?'':'s') + ': choose distinct pages if the buttons should open different content.' : '';
    status.textContent = ready < 2 ? 'Not ready: only ' + ready + ' working link' + (ready===1?'':'s') + '. Add a destination or click Quick Setup; the website will not show this menu yet.' : ready + ' working menu links' + (missing ? ', ' + missing + ' incomplete (not shown)' : '') + '.' + duplicateHelp + ' ' + (enabled ? 'Click Save Bottom Menu to publish.' : 'Enable Bottom Navigation and Save to publish.');
    status.classList.toggle('psn-status-error', ready < 2);
    if (readyNotice) readyNotice.textContent = ready < 2 ? 'Preview: not enough valid destinations to display on your website.' : 'Previewing ' + ready + ' working links. Tap an icon to see its selected design.';
    return ready;
  }
  function svg(icon) {
    // Only local trusted icon data is read; no arbitrary uploaded SVG/HTML is used.
    const info = icons[icon] || icons.home;
    const el = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
    el.classList.add('psn-icon'); el.setAttribute('aria-hidden','true');
    el.setAttribute('focusable','false');
    if (info && typeof info === 'object' && typeof info.d === 'string') {
      el.classList.add('psn-fa-icon');
      el.setAttribute('viewBox',info.viewBox || '0 0 512 512');
      el.setAttribute('fill','currentColor'); el.setAttribute('stroke','none');
      const path = document.createElementNS('http://www.w3.org/2000/svg','path');
      path.setAttribute('d',info.d); el.appendChild(path);
    } else {
      el.setAttribute('viewBox','0 0 24 24'); el.setAttribute('stroke','currentColor');
      el.setAttribute('stroke-width','1.8');el.setAttribute('stroke-linecap','round');
      el.setAttribute('stroke-linejoin','round');el.setAttribute('fill','none');
      const template = document.createElement('template');
      template.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg">' + (typeof info==='string'?info:'') + '</svg>';
      const nodes = template.content.firstElementChild;
      if (nodes) Array.from(nodes.children).forEach(child => el.appendChild(document.importNode(child,true)));
    }
    return el;
  }
  function element(tag, className, label) {
    const node = document.createElement(tag);
    if (className) node.className = className;
    if (label !== undefined) node.textContent = label;
    return node;
  }
  function input(name, value, type, required) {
    const el = document.createElement('input');
    el.type = type || 'text'; el.name = name; el.value = value == null ? '' : String(value);
    if (required) el.required = true;
    return el;
  }
  function select(name, choices, selected) {
    const el = document.createElement('select'); el.name = name;
    choices.forEach(pair => {
      const option = document.createElement('option'); option.value = String(pair[0]); option.textContent = pair[1];
      if (String(pair[0]) === String(selected)) option.selected = true;
      el.appendChild(option);
    });
    return el;
  }
  function field(title, control, wide) {
    const label = element('label', 'ps-field' + (wide ? ' psn-wide' : ''));
    label.append(element('span', '', title), control);
    return label;
  }
  function drawStyles() {
    gallery.replaceChildren();
    const styleEntries=Object.entries(styles);
    filterTabs.querySelectorAll('button').forEach(tab=>{
      const selected=tab.dataset.group===styleGroup;
      tab.classList.toggle('is-active',selected);tab.setAttribute('aria-pressed',String(selected));
    });
    styleEntries.forEach(([key,label],index)=>{
      const isNew=index>=12;
      if((styleGroup==='new'&&!isNew)||(styleGroup==='classic'&&isNew))return;
      const button=element('button','psn-style-option'+(state.style===key?' is-selected':'')+(isNew?' psn-new-style':''));
      button.type='button';button.dataset.style=key;
      button.setAttribute('aria-pressed',String(state.style===key));
      button.setAttribute('aria-label',label+' navigation style'+(isNew?', new design':''));
      const sample=element('span','psn-style-sample');
      const miniBar=element('span','psn-mini-bar');
      ['home','grid','user'].forEach((icon,i)=>{
        const mini=element('span','psn-mini-item'+(i===0?' is-active':''));
        mini.appendChild(svg(icon));miniBar.appendChild(mini);
      });
      sample.appendChild(miniBar);
      const title=element('span','psn-style-title',String(index+1).padStart(2,'0')+' · '+label);
      button.append(sample,title);
      if(isNew)button.appendChild(element('span','psn-style-new-badge','NEW'));
      button.addEventListener('click',()=>{
        state.style=key;styleField.value=key;applyPalette(key);drawStyles();drawPreview();
      });
      gallery.appendChild(button);
    });
  }
  function itemKey(i,key) { return 'ps_nav[items][' + i + '][' + key + ']'; }
  function drawItems() {
    area.replaceChildren();
    state.items.forEach((item,i) => {
      const row = element('div','psn-item-editor'); row.dataset.index = String(i); row.draggable = true;
      const head = element('div','psn-item-head');
      const grip = element('span','dashicons dashicons-move psn-item-grip'); grip.title = 'Drag to reorder';
      const title = element('strong','psn-item-num','Item ' + (i+1));
      const makeAction = (label,cb,title) => { const b=element('button','button',label); b.type='button'; b.title=title; b.addEventListener('click',cb); return b; };
      head.append(grip,title,makeAction('↑',()=>move(i,-1),'Move up'),makeAction('↓',()=>move(i,1),'Move down'),makeAction('×',()=>{ state.items.splice(i,1); drawItems(); drawPreview(); },'Remove item'));
      const controls = element('div','psn-item-fields');
      const name = input(itemKey(i,'label'),item.label,'text',true); name.maxLength=22; name.placeholder='Menu label';
      name.addEventListener('input',()=>{ item.label=name.value; drawPreview(); });
      controls.appendChild(field('Menu label',name));
      const iconField = input(itemKey(i,'icon'),item.icon,'hidden');
      const iconArea=element('div','psn-wide');
      const iconControl=element('div','psn-icon-control');
      const selectedIcon=element('span','psn-icon-selected'); selectedIcon.appendChild(svg(item.icon));
      const chosen=icons[item.icon];
      const chooseIcon=element('button','button psn-choose-icon','Browse icons · '+(chosen && chosen.label ? chosen.label : item.icon));
      chooseIcon.type='button'; chooseIcon.setAttribute('aria-expanded','false');
      const badge=element('div','psn-icon-browser'); badge.hidden=true;
      const toolbar=element('div','psn-icon-toolbar');
      const query=element('input','psn-icon-search');query.type='search';query.placeholder='Search 248 icons…';
      query.setAttribute('aria-label','Search available icons');
      const category=element('select','psn-icon-category'); category.setAttribute('aria-label','Icon category');
      iconGroups.forEach(group=>{const option=element('option','',group);option.value=group;category.appendChild(option);});
      const count=element('span','psn-icon-count','');
      const iconGrid=element('div','psn-icon-picker');
      const selectIcon=(key)=>{
        item.icon=key;iconField.value=key;selectedIcon.replaceChildren(svg(key));
        const info=icons[key];chooseIcon.textContent='Browse icons · '+(info && info.label?info.label:key);
        badge.hidden=true;chooseIcon.setAttribute('aria-expanded','false');drawPreview();
      };
      function filterIcons() {
        const search=query.value.toLowerCase().trim();
        const group=category.value;
        const matching=iconKeys.filter(key=>{
          const info=icons[key];const label=info && info.label?info.label:key;
          const belonging=info && info.group?info.group:'Classic';
          return (group==='All'||group===belonging) && (!search||(key+' '+label+' '+belonging).toLowerCase().includes(search));
        });
        iconGrid.replaceChildren();
        matching.forEach(key=>{
          const info=icons[key];
          const b=element('button','psn-icon-choice');b.type='button';
          b.title=info && info.label?info.label:key;
          b.setAttribute('aria-label','Choose '+b.title+' icon');
          b.setAttribute('aria-pressed',String(item.icon===key));
          b.appendChild(svg(key));
          b.addEventListener('click',()=>selectIcon(key));
          iconGrid.appendChild(b);
        });
        count.textContent=matching.length+' icons';
      }
      query.addEventListener('input',filterIcons);category.addEventListener('change',filterIcons);
      toolbar.append(query,category,count);
      badge.append(toolbar,iconGrid);
      chooseIcon.addEventListener('click',()=>{
        // Keep one searchable icon drawer open at a time.
        document.querySelectorAll('.psn-icon-browser').forEach(other=>{if(other!==badge)other.hidden=true;});
        document.querySelectorAll('.psn-choose-icon').forEach(other=>{if(other!==chooseIcon)other.setAttribute('aria-expanded','false');});
        badge.hidden=!badge.hidden;chooseIcon.setAttribute('aria-expanded',String(!badge.hidden));
        if(!badge.hidden){filterIcons();query.focus();}
      });
      iconControl.append(selectedIcon,chooseIcon,iconField);
      iconArea.append(iconControl,badge);controls.appendChild(iconArea);
      const type = select(itemKey(i,'type'),[['home','Home page'],['search','Search (opens search box)'],['page','WordPress page'],['url','Custom link']],item.type);
      controls.appendChild(field('Destination type',type));
      const page = select(itemKey(i,'page_id'),[['0','Select a page…'],...pages.map(p=>[p.id,p.title])],item.page_id || 0);
      const pageBox = field('Destination page',page);
      const url = input(itemKey(i,'url'),item.url || '','url'); url.placeholder='https://example.com/page';
      const urlBox = field('Custom URL (https:// or /path)',url,true);
      const tab = input(itemKey(i,'new_tab'),'1','checkbox'); tab.checked = Boolean(Number(item.new_tab));
      const tabBox = element('label','psn-item-checkbox'); tabBox.append(tab,element('span','','Open custom link in new tab'));
      // URL can be site-relative, so type=text (browser type=url rejects relative URLs).
      url.type='text';
      controls.append(pageBox,urlBox,tabBox);
      function updateDestination() {
        pageBox.hidden = type.value !== 'page'; urlBox.hidden = type.value !== 'url'; tabBox.hidden = type.value !== 'url';
        item.type=type.value; drawPreview();
      }
      type.addEventListener('change',updateDestination);
      page.addEventListener('change',()=>{ item.page_id=Number(page.value); drawPreview(); });
      url.addEventListener('input',()=>{ item.url=url.value; drawPreview(); });
      tab.addEventListener('change',()=>{ item.new_tab=tab.checked?1:0; });
      const warning = element('p','psn-item-warning','');
      function markValidity() {
        const valid = working(item);
        row.classList.toggle('is-incomplete', !valid);
        warning.textContent = valid ? '' : item.type === 'page' ? 'Choose a published page for this item to appear on the live website.' : item.type === 'url' ? 'Enter a valid https:// link or /site-path.' : 'Add a menu label.';
        warning.hidden = valid;
      }
      row.append(head,controls,warning); area.appendChild(row); updateDestination(); markValidity();
      row.addEventListener('input',markValidity); row.addEventListener('change',markValidity);
      row.addEventListener('dragstart',event=>{dragged=row; row.classList.add('is-dragging'); if(event.dataTransfer){event.dataTransfer.effectAllowed='move';event.dataTransfer.setData('text/plain',String(i));}});
      row.addEventListener('dragover',event=>{if(dragged && dragged!==row){event.preventDefault(); row.classList.add('is-target');}});
      row.addEventListener('dragleave',()=>row.classList.remove('is-target'));
      row.addEventListener('drop',event=>{event.preventDefault(); if(!dragged || dragged===row)return; const from=Number(dragged.dataset.index); const to=i; const moved=state.items.splice(from,1)[0]; state.items.splice(to,0,moved); dragged=null; drawItems();drawPreview();});
      row.addEventListener('dragend',()=>{dragged=null;area.querySelectorAll('.is-target,.is-dragging').forEach(el=>el.classList.remove('is-dragging','is-target'));});
    });
    document.getElementById('psn-add-item').disabled=state.items.length>=maxItems;
    updateStatus();
  }
  function move(i,offset) {
    const j=i+offset; if(j<0||j>=state.items.length)return;
    [state.items[i],state.items[j]]=[state.items[j],state.items[i]];
    drawItems();drawPreview();
  }
  function drawPreview() {
    const current=state.style;
    preview.className='psn-nav psn-style--'+current+' psn-device--both'+(form.elements.namedItem('ps_nav[labels]').checked?'':' psn-no-label')+
      (document.getElementById('psn-mobile-custom').checked?' psn-tuned-mobile':'')+
      (document.getElementById('psn-tablet-custom').checked?' psn-tuned-tablet':'')+
      ' psn-preview-device--'+previewDevice+' psn-motion--'+document.getElementById('psn-motion').value+
      ' psn-indicator--'+document.getElementById('psn-indicator').value;
    const settings=['accent','background','text_color','active_color'];
    settings.forEach(key => preview.style.setProperty('--psn-'+(key==='background'?'bg':key==='text_color'?'text':key==='active_color'?'active':'accent'),document.getElementById('psn-'+key).value));
    Object.keys(sizes).forEach(key=>{
      const node=document.getElementById('psn-'+key); if(!node)return;
      const [cssname,unit]=sizes[key];
      preview.style.setProperty(cssname,node.value+unit);
      const output=document.getElementById('psn-value-'+key);
      if(output)output.textContent=node.value+unit;
    });
    ['mobile','tablet'].forEach(device=>{
      responsiveKeys.forEach(key=>{
        const control=document.getElementById('psn-'+device+'_'+key);
        const suffix=key==='menu_width'?'%':'px';
        const cssname='--psn-'+device+'-'+key.replace(/_/g,'-');
        if(control)preview.style.setProperty(cssname,control.value+suffix);
        const output=document.getElementById('psn-value-'+device+'_'+key);
        if(output&&control)output.textContent=control.value+suffix;
      });
      const card=document.querySelector('[data-psn-profile="'+device+'"]');
      if(card)card.classList.toggle('is-disabled',!document.getElementById('psn-'+device+'-custom').checked);
    });
    const ms=document.getElementById('psn-motion-ms');
    if(ms) { preview.style.setProperty('--psn-motion-ms',ms.value+'ms');
      const o=document.getElementById('psn-value-motion_ms');if(o)o.textContent=ms.value+'ms'; }
    preview.replaceChildren();
    const items = visibleItems();
    if (previewActive >= items.length) previewActive=0;
    items.forEach((item,i)=>{
      const a=element('button','psn-item'+(i===previewActive?' is-active':''));
      a.type='button'; a.setAttribute('aria-label','Preview '+item.label);
      a.setAttribute('aria-pressed',String(i===previewActive));
      const shell=element('span','psn-icon-shell'); shell.appendChild(svg(item.icon));
      a.append(shell,element('span','psn-label',item.label));
      a.addEventListener('click',()=>{ previewActive=i; drawPreview(); });
      preview.appendChild(a);
    });
    updateStatus();
  }
  const resetSizing = document.getElementById('psn-reset-sizing');
  if (resetSizing) resetSizing.addEventListener('click',()=>{
    Object.keys(sizes).forEach(key=>{const field=document.getElementById('psn-'+key);if(field)field.value=sizes[key][2];});
    drawPreview();
  });
  styleField.addEventListener('change',()=>{state.style=styleField.value;drawStyles();drawPreview();});
  form.addEventListener('input',event=>{if(event.target.closest('.psn-item-editor'))return;drawPreview();});
  form.addEventListener('change',event=>{if(event.target.closest('.psn-item-editor'))return;drawPreview();});
  const quick = document.getElementById('psn-quick-setup');
  if (quick) quick.addEventListener('click',()=>{
    if (state.items.length && !window.confirm('Replace the current unsaved menu items with Home, Search and available published pages?')) return;
    const fresh = [
      {label:'Home',icon:'home',type:'home',page_id:0,url:'',new_tab:0},
      {label:'Search',icon:'search',type:'search',page_id:0,url:'',new_tab:0}
    ];
    pages.slice(0,maxItems-2).forEach(pg=>{
      const label=pg.title.slice(0,22);
      const low=label.toLowerCase();
      const icon=low.includes('shop')?'shopping-bag':low.includes('account')?'user':low.includes('contact')?'mail':low.includes('about')?'globe':'grid';
      fresh.push({label,icon,type:'page',page_id:Number(pg.id),url:'',new_tab:0});
    });
    state.items=fresh; previewActive=0; drawItems(); drawPreview();
    if(enabledField) enabledField.checked=true;
    updateStatus();
  });
  form.addEventListener('submit',event=>{
    if (enabledField && enabledField.checked && updateStatus() < 2) {
      event.preventDefault();
      status.scrollIntoView({behavior:'smooth',block:'center'});
      status.focus();
    }
  });
  document.getElementById('psn-add-item').addEventListener('click',()=>{
    if(state.items.length>=maxItems)return;
    state.items.push({label:'New item',icon:'star',type:'page',page_id:0,url:'',new_tab:0}); drawItems();drawPreview();
  });
  const navStage=document.getElementById('psn-device-stage');
  const devicePreview=window.PSStudioDevicePreview && navStage
    ? window.PSStudioDevicePreview.create(navStage,phone,document.getElementById('psn-device-meta'),'navigation')
    : null;
  document.querySelectorAll('[data-psn-preview]').forEach(button=>button.addEventListener('click',()=>{
    const device=button.dataset.psnPreview==='tablet'?'tablet':'mobile';
    previewDevice=device;
    if(devicePreview)devicePreview.setDevice(device);
    else phone.classList.toggle('psn-tablet',device==='tablet');
    drawPreview();
    document.querySelectorAll('[data-psn-preview]').forEach(b=>{ b.classList.toggle('is-active',b===button); b.setAttribute('aria-pressed',String(b===button)); });
  }));
  drawStyles();drawItems();drawPreview();
  // Keep the settings header quick navigation visible with a clear active section.
  const links=Array.from(document.querySelectorAll('.ps-header-navigation a[href^="#"]'));
  links.forEach(link=>link.addEventListener('click',()=>{links.forEach(a=>a.classList.toggle('is-active',a===link));}));
  if ('IntersectionObserver' in window) {
    const sections=links.map(link=>document.querySelector(link.getAttribute('href'))).filter(Boolean);
    const observer=new IntersectionObserver(entries=>{
      entries.filter(entry=>entry.isIntersecting).forEach(entry=>links.forEach(a=>a.classList.toggle('is-active',a.getAttribute('href')==='#'+entry.target.id)));
    },{rootMargin:'-130px 0px -72% 0px',threshold:0});
    sections.forEach(section=>observer.observe(section));
  }
})();
