import {parseScript} from './core.mjs';

export function decodePlainText(bytes) {
  if(bytes.byteLength>20*1024*1024)throw new Error('This file is larger than 20 MB. Choose a smaller TXT file.');
  let encoding='utf-8';
  if(bytes[0]===0xff&&bytes[1]===0xfe)encoding='utf-16le';
  if(bytes[0]===0xfe&&bytes[1]===0xff)encoding='utf-16be';
  let text;
  try{text=new TextDecoder(encoding,{fatal:true}).decode(bytes);}catch{throw new Error('This text encoding is not supported. Save the file as UTF-8 TXT and try again.');}
  if(/[\x00-\x08\x0e-\x1f]/.test(text))throw new Error('This looks like a binary file. Save the actual text as UTF-8 TXT; changing its extension is not enough.');
  if(!text.trim())throw new Error('This file is empty. Choose a TXT file containing your script.');
  if(parseScript(text).count>20000)throw new Error('This file exceeds 20,000 spoken words. Split it into smaller scripts before importing.');
  return text;
}

// Native browser APIs only. Reading a TXT file never sends it to the server.
export function setupImports({accept,notify}) {
  const $=id=>document.getElementById(id);
  let generation=0, title='Imported script';
  const cancel=()=>{generation++;$('import-dialog').close();$('upload').value='';};
  $('cancel-import').onclick=cancel;
  $('import-dialog').addEventListener('cancel',()=>{generation++;$('upload').value='';});
  async function read(file) {
    if(!file)return;
    if(!/\.txt$/i.test(file.name)){notify('This version accepts plain-text (.txt) files. Paste your text or save it as TXT first.');return;}
    if(file.size>20*1024*1024){notify('This file is larger than 20 MB. Choose a smaller TXT file.');return;}
    const token=++generation;
    title=file.name.replace(/\.txt$/i,'');
    $('import-review').hidden=true;$('accept-import').hidden=true;
    $('import-title').textContent='Review your text';$('import-status').textContent='Reading on this device…';
    $('import-notes').textContent='Your current script stays safe. Accepting this preview creates a new script.';
    $('import-progress').hidden=false;$('import-progress').removeAttribute('value');
    $('import-dialog').showModal();
    try {
      const bytes=new Uint8Array(await file.arrayBuffer());
      const text=decodePlainText(bytes);
      if(token!==generation)return;
      const count=parseScript(text).count;
      $('import-review').value=text;$('import-review').hidden=false;$('accept-import').hidden=false;
      $('import-status').textContent=`${count.toLocaleString()} spoken words. Review and edit before adding your script.`;
    } catch(error) {
      if(token===generation)$('import-status').textContent=error.message||'Could not read this file. Try a UTF-8 TXT file.';
    } finally {if(token===generation)$('import-progress').hidden=true;}
  }
  $('upload').onchange=()=>read($('upload').files[0]);
  const zone=$('drop-zone');
  for(const name of ['dragenter','dragover'])zone.addEventListener(name,e=>{e.preventDefault();zone.classList.add('dragging');});
  for(const name of ['dragleave','drop'])zone.addEventListener(name,e=>{e.preventDefault();zone.classList.remove('dragging');});
  zone.addEventListener('drop',e=>{if(e.dataTransfer.files.length!==1)return notify('Import one TXT file at a time.');read(e.dataTransfer.files[0]);});
  $('accept-import').onclick=()=>{const text=$('import-review').value,count=parseScript(text).count;if(!text.trim()||count>20000){$('import-status').textContent='Enter text with no more than 20,000 spoken words.';return;}accept(text,title);cancel();notify('Your reviewed text was added as a new script.');};
}
