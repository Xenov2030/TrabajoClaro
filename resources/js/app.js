const container = document.getElementById('quote-items');
if (container) {
 let index = Math.max(...Array.from(container.querySelectorAll('input')).map(el => Number(el.name.match(/\[(\d+)\]/)?.[1] || 0))) + 1;
 const syncControls=()=>{container.querySelectorAll('.remove-item').forEach(el=>el.disabled=container.children.length<=1);document.getElementById('add-item').disabled=container.children.length>=30;};
 syncControls();
 document.getElementById('add-item').addEventListener('click', () => {
  if(container.children.length >= 30) return;
  const copy = container.querySelector('fieldset').cloneNode(true);
  copy.querySelector('legend').textContent = `Ítem ${index + 1}`;
  copy.querySelectorAll('.error').forEach(el=>el.remove());
  copy.querySelectorAll('input, select').forEach(el => {
   const oldId = el.id;
   el.name = el.name.replace(/\[\d+\]/, `[${index}]`);
   el.id = el.id.replace(/item-\d+-/, `item-${index}-`);
   copy.querySelector(`label[for="${oldId}"]`)?.setAttribute('for',el.id);
   el.value = el.name.includes('[quantity]') ? '1' : '';
  });
  container.append(copy); syncControls(); copy.querySelector('input').focus(); index++;
 });
 container.addEventListener('click',e=>{
  if(e.target.classList.contains('remove-item') && container.children.length > 1){e.target.closest('fieldset').remove();syncControls();}
 });
}
document.querySelectorAll('.print-button').forEach(el=>el.addEventListener('click',()=>window.print()));
const message=document.getElementById('whatsapp-message'), link=document.getElementById('whatsapp-link');
if(message && link){const update=()=>{link.href=`https://wa.me/${link.dataset.phone}?text=${encodeURIComponent(message.value)}`;};message.addEventListener('input',update);update();}

const resetPending=()=>document.querySelectorAll('form[data-pending]').forEach(form=>{delete form.dataset.pending;form.removeAttribute('aria-busy');form.querySelectorAll('[data-pending-original]').forEach(button=>{button.disabled=false;button.textContent=button.dataset.pendingOriginal;delete button.dataset.pendingOriginal;});form.querySelector('.form-progress')?.remove();});
window.addEventListener('pageshow',resetPending);
document.addEventListener('submit',event=>{
 const form=event.target;if(!(form instanceof HTMLFormElement))return;
 if(form.dataset.pending){event.preventDefault();return;}
 if(!form.checkValidity())return;
 // La primera solicitud mantiene los datos del botón; no alterar controles del formulario.
 form.dataset.pending='true';form.setAttribute('aria-busy','true');
 const status=document.createElement('p');status.className='form-progress';status.setAttribute('role','status');status.textContent='Procesando. Esperá un momento…';form.append(status);
 if(event.submitter?.name){const hidden=document.createElement('input');hidden.type='hidden';hidden.name=event.submitter.name;hidden.value=event.submitter.value;form.append(hidden);}
 form.querySelectorAll('button:not([type="button"])').forEach(button=>{button.dataset.pendingOriginal=button.textContent;button.disabled=true;button.textContent=button.dataset.pendingLabel||'Procesando…';});
});
