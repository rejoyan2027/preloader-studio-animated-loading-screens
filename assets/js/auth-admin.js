(function(){'use strict';
 const root=document.getElementById('psa-studio');if(!root)return;
 const presets=window.psStudioAuth&&window.psStudioAuth.presets||{};
 const canvas=root.querySelector('.psa-canvas');const presetField=root.querySelector('#psa-preset');
 const accent=root.querySelector('#psa-accent'), bg=root.querySelector('#psa-background');
 const txt=root.querySelector('#psa-headline'),sub=root.querySelector('#psa-subtitle');
 const previewHeading=root.querySelector('#psa-heading-preview'),previewSubtitle=root.querySelector('#psa-subtitle-preview');
 const device=root.querySelector('#psa-device');
 const updateColors=()=>{canvas.style.setProperty('--psa-accent',accent.value);canvas.style.setProperty('--psa-background',bg.value);};
 root.querySelectorAll('[data-style]').forEach(button=>button.addEventListener('click',()=>{
   const slug=button.dataset.style; if(!presets[slug]) return;
   presetField.value=slug;canvas.dataset.preset=slug;
   canvas.className='psa-canvas psa-design--'+slug;
   accent.value=presets[slug][2];bg.value=presets[slug][3];
   canvas.style.setProperty('--psa-panel',presets[slug][4]);canvas.style.setProperty('--psa-ink',presets[slug][5]);updateColors();
   root.querySelectorAll('[data-style]').forEach(b=>{const selected=b===button;b.classList.toggle('is-selected',selected);b.setAttribute('aria-pressed',String(selected));});
 }));
 root.querySelectorAll('[data-psa-filter]').forEach(button=>button.addEventListener('click',()=>{
   const type=button.dataset.psaFilter;
   root.querySelectorAll('[data-psa-filter]').forEach(b=>b.classList.toggle('is-selected',b===button));
   root.querySelectorAll('[data-style]').forEach(b=>{b.hidden=(type!=='all'&&b.dataset.category!==type);});
 }));
 accent.addEventListener('input',updateColors);bg.addEventListener('input',updateColors);
 txt.addEventListener('input',()=>previewHeading.textContent=txt.value||'Welcome back');
 sub.addEventListener('input',()=>previewSubtitle.textContent=sub.value||'Sign in to continue.');
 root.querySelectorAll('[data-device]').forEach(button=>button.addEventListener('click',()=>{
  root.querySelectorAll('[data-device]').forEach(b=>b.classList.toggle('is-selected',b===button));
  device.className='psa-device psa-device-'+button.dataset.device;
 }));
 const pwd=root.querySelector('.psa-password-field'),remember=root.querySelector('.psa-form-row');
 const cta=root.querySelector('#psa-preview-button'),footer=root.querySelector('#psa-preview-footer');
 root.querySelectorAll('[data-view]').forEach(button=>button.addEventListener('click',()=>{
  root.querySelectorAll('[data-view]').forEach(b=>b.classList.toggle('is-selected',b===button));
  const mode=button.dataset.view;
  pwd.hidden=mode==='reset';remember.hidden=mode!=='login';
  cta.textContent=mode==='login'?'Sign in securely →':mode==='register'?'Create your account →':'Send reset link →';
  footer.textContent=mode==='login'?'New here? Create account':mode==='register'?'Already a member? Sign in':'Remembered your password? Sign in';
 }));
 const logoField=root.querySelector('#psa-logo-id'),logoPreview=root.querySelector('#psa-preview-logo'),status=root.querySelector('#psa-logo-indicator');
 root.querySelector('#psa-pick-logo').addEventListener('click',()=>{
  if(!window.wp||!window.wp.media){return;}
  const frame=wp.media({title:'Choose account logo',library:{type:'image'},multiple:false,button:{text:'Use logo'}});
  frame.on('select',()=>{
   const obj=frame.state().get('selection').first().toJSON();logoField.value=obj.id;
   const url=obj.sizes&&obj.sizes.thumbnail?obj.sizes.thumbnail.url:obj.url;
   const img=document.createElement('img');img.alt='';img.src=url;logoPreview.replaceChildren(img);status.textContent='Logo selected';
  });frame.open();
 });
 root.querySelector('#psa-remove-logo').addEventListener('click',()=>{
  logoField.value='0';const url=window.psStudioAuth&&window.psStudioAuth.logoUrl;
  // Selection is reverted to Site Logo on the next request; avoid falsely displaying the uploaded logo.
  const symbol=document.createElement('span');symbol.className='psa-brand-symbol';symbol.textContent='✳';logoPreview.replaceChildren(symbol);status.textContent='Using Site Logo on publish (if configured)';
 });
})();
