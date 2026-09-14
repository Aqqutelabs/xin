import {parseScript,timing,clock,PROFILES,Playback} from './core.mjs';
import {setupImports} from './imports.mjs';
const $=id=>document.getElementById(id), now=()=>performance.now();
const base=document.body.dataset.base;
const defaults={mode:'pace',wpm:135,minutes:2,seconds:0,'font-size':48,theme:'dark',countdown:3,mirror:false,flip:false,'font-family':'sans-serif','line-height':1.6,'text-width':75,margins:5,'guide-position':35,alignment:'left','text-color':'#f5f4f0','bg-color':'#151918',guide:true,dim:false,loop:false,manual:false};
let settings={...defaults}, scripts=[], currentId='', db, saving=Promise.resolve(), saveTimer, history=[], redo=[], model, engine=new Playback(), presenting=false, countdownTimer, countdownLeft=0, needsCountdown=true, positions=[], lines=[], wordNodes=[], lastHighlight=-1, raf=0, wakeLock, idleTimer, resizeFrame, sessionScriptId=null, sessionText='';
const sample=`[Take a breath. Look at the camera.]

Every great idea starts with a conversation.

Sometimes, all it takes is a little space to find the right words. To slow down, look up, and share something that matters.

That’s what this moment is for.

Whether you’re telling your story, teaching something new, or introducing your next big idea, you don’t need to be perfect. You just need to be present.

[Smile.]

So take a breath. Find your rhythm. And let’s begin.

**Your words have somewhere to go.**`;
function notify(message){$('notice').textContent=message;$('notice').hidden=false;clearTimeout(notify.timer);notify.timer=setTimeout(()=>$('notice').hidden=true,6500);}
function storageError(){ $('save-status').textContent='Could not save — export a copy'; }
function openDB(){return new Promise((resolve,reject)=>{const r=indexedDB.open('xinng-teleprompter',1);r.onupgradeneeded=()=>r.result.createObjectStore('workspace');r.onsuccess=()=>resolve(r.result);r.onerror=()=>reject(r.error);r.onblocked=()=>reject(new Error('Storage blocked'));});}
function readDB(){return new Promise((resolve,reject)=>{const r=db.transaction('workspace').objectStore('workspace').get('state');r.onsuccess=()=>resolve(r.result);r.onerror=()=>reject(r.error);});}
function commitDB(state){return new Promise((resolve,reject)=>{const tx=db.transaction('workspace','readwrite');tx.objectStore('workspace').put(state,'state');tx.oncomplete=resolve;tx.onerror=()=>reject(tx.error);tx.onabort=()=>reject(tx.error);});}
function collect(){const s=scripts.find(s=>s.id===currentId);if(s){s.title=$('title').value.trim()||'Untitled script';s.text=$('script').value;s.updated=Date.now();}}
function save(){clearTimeout(saveTimer);collect();if(!db){storageError();return;}const state=structuredClone({scripts,currentId,settings,position:engine.position,active:engine.active,presenting,sessionScriptId,sessionText});$('save-status').textContent='Saving…';saving=saving.then(()=>commitDB(state)).then(()=>$('save-status').textContent='Saved on this device').catch(storageError);}
function autosave(){clearTimeout(saveTimer);saveTimer=setTimeout(save,350);}
function recent(){const selected=currentId;$('recent').replaceChildren(...scripts.map(s=>new Option(s.title,s.id)));$('recent').value=selected;}
function chooseScript(id){collect();currentId=id;const s=scripts.find(s=>s.id===id);$('title').value=s.title;$('script').value=s.text;history=[s.text];redo=[];engine.reset(0,135);sessionScriptId=null;sessionText='';recent();refresh();save();}
function addScript(text='',title='Untitled script'){collect();const s={id:crypto.randomUUID(),text,title,updated:Date.now()};scripts.unshift(s);chooseScript(s.id);}
function edit(text,selection){if(text===$('script').value)return;$('script').value=text;history.push(text);if(history.length>100)history.shift();redo=[];refresh();autosave();if(selection){$('script').focus();$('script').setSelectionRange(...selection);}}
function getTiming(){
  const result=timing(model.count,settings.mode,settings.wpm,Number(settings.minutes)*60+Number(settings.seconds));
  if(settings.mode==='pace'&&!Number.isInteger(settings.wpm))result.valid=false;
  if(settings.mode==='duration'&&(!Number.isInteger(settings.minutes)||settings.minutes<0||!Number.isFinite(settings.seconds)||settings.seconds<0||settings.seconds>=60))result.valid=false;
  return result;
}
function refresh(){model=parseScript($('script').value);const t=getTiming();$('word-count').textContent=model.count.toLocaleString();$('editor-time').textContent=clock(t.duration);$('estimate').textContent=clock(t.duration);$('required-wpm').textContent=`${Number.isFinite(t.wpm)?t.wpm.toFixed(1):'—'} words per minute required`;$('timing-error').hidden=t.valid||!model.count;$('timing-error').textContent=model.count>20000?'This script exceeds 20,000 spoken words. Split it into shorter scripts.':`This needs ${Number.isFinite(t.wpm)?t.wpm.toFixed(1):'an invalid'} WPM. Choose a duration between ${t.min.toFixed(3)} and ${t.max.toFixed(3)} seconds (90–220 WPM), or edit your script.`;$('start').disabled=!t.valid;$('resume-session').disabled=!t.valid;$('mode-pace').classList.toggle('selected',settings.mode==='pace');$('mode-duration').classList.toggle('selected',settings.mode==='duration');$('mode-pace').setAttribute('aria-pressed',settings.mode==='pace');$('mode-duration').setAttribute('aria-pressed',settings.mode==='duration');$('pace-inputs').hidden=settings.mode!=='pace';$('duration-inputs').hidden=settings.mode!=='duration';$('undo').disabled=history.length<2;$('redo').disabled=!redo.length;$('resume-session').hidden=!(sessionScriptId===currentId&&sessionText===$('script').value&&engine.count>0&&engine.position<engine.count);}
function applySettings(){for(const [id,value] of Object.entries(settings)){const el=$(id);if(!el)continue;if(el.type==='checkbox')el.checked=value;else el.value=value;}$('pace-range').value=settings.wpm;$('profile').value=PROFILES.find(p=>p[2]===Number(settings.wpm))?.[0]||'custom';$('size-output').textContent=settings['font-size']+' px';const player=$('player');const props={'--reader-bg':settings['bg-color'],'--reader-color':settings['text-color'],'--font-size':settings['font-size']+'px','--font':settings['font-family'],'--leading':settings['line-height'],'--width':settings['text-width']+'%','--margin':settings.margins+'%','--guide':settings['guide-position']+'%','--align':settings.alignment,'--mirror':settings.mirror?-1:1,'--flip':settings.flip?-1:1};for(const [k,v]of Object.entries(props))player.style.setProperty(k,v);$('reading-guide').hidden=!settings.guide;$('reading-text').classList.toggle('dimmed',settings.dim);const preview=$('mini-preview');preview.style.background=settings['bg-color'];preview.style.color=settings['text-color'];$('preview-text').style.fontFamily=settings['font-family'];$('preview-text').style.fontSize=(Number(settings['font-size'])/2.4)+'px';$('preview-text').style.lineHeight=settings['line-height'];$('preview-text').style.transform=`scale(${settings.mirror?-1:1},${settings.flip?-1:1})`;if(presenting){measure();paint();}refresh();}
function changeSetting(id,value){if(presenting)pause();settings[id]=value;applySettings();autosave();}
function renderScript(){const fragment=document.createDocumentFragment();for(const paragraph of model.paragraphs){const p=document.createElement('p');p.dataset.start=paragraph.start;p.dataset.paragraph=paragraph.id;for(const part of paragraph.parts){const el=document.createElement(part.bold?'strong':part.italic?'em':'span');if(part.cue)el.className='cue';for(const token of part.tokens){if(token.word!==undefined){const span=document.createElement('span');span.dataset.word=token.word;span.textContent=token.text;el.append(span);}else el.append(document.createTextNode(token.text));}p.append(el);}fragment.append(p);}$('reading-text').replaceChildren(fragment);wordNodes=[...$('reading-text').querySelectorAll('[data-word]')];lastHighlight=-1;measure();}
function measure(){
  positions=wordNodes.map(el=>el.offsetTop+el.offsetHeight/2);
  lines=[];
  positions.forEach((y,index)=>{const line=lines.at(-1);if(!line||Math.abs(line.y-y)>2)lines.push({start:index,end:index+1,y});else line.end=index+1;});
}

function paint(){const index=Math.min(engine.count-1,Math.floor(engine.position));if(index>=0&&positions.length){const lineIndex=lines.findIndex(line=>index>=line.start&&index<line.end);const line=lines[lineIndex];const nextLine=lines[lineIndex+1]||line;const fraction=(engine.position-line.start)/(line.end-line.start);const y=line.y+(nextLine.y-line.y)*Math.min(1,fraction);const height=$('reading-surface').clientHeight;const guide=height*Number(settings['guide-position'])/100;const logicalGuide=settings.flip?height-guide:guide;$('reading-text').style.transform=`translateY(${logicalGuide-y}px)`;if(index!==lastHighlight){wordNodes[lastHighlight]?.classList.remove('current');wordNodes[index]?.classList.add('current');lastHighlight=index;}}$('active-time').textContent=clock(Math.floor(engine.active));$('remaining-time').textContent=clock(engine.remaining);$('percent').textContent=Math.floor(engine.position/Math.max(1,engine.count)*100)+'%';$('progress').value=engine.position/Math.max(1,engine.count)*1000;$('player-pace').textContent=engine.wpm.toFixed(1)+' WPM';$('play').textContent=engine.playing?'Ⅱ Pause':'▶ Play';$('play-state').textContent=countdownTimer?'Get ready':engine.position>=engine.count?'Complete':engine.holds.size?'Holding':engine.playing?'Presenting':'Paused';}
function tick(time){const wasPlaying=engine.playing;engine.advance(time);paint();if(wasPlaying&&!engine.playing){save();releaseWake();if(settings.loop){restart();startPlayback();}else{$('player-hint').textContent='That’s a wrap. Restart to rehearse again.';showControls();}}if(presenting)raf=requestAnimationFrame(tick);}
async function requestWake(){try{if(!navigator.wakeLock){$('player-hint').textContent='Keep your screen awake in device settings. Hold Space or the reading surface to pause.';return;}if(!wakeLock){const lock=await navigator.wakeLock.request('screen');if(!presenting||!engine.playing){await lock.release();return;}wakeLock=lock;lock.addEventListener('release',()=>{if(wakeLock===lock)wakeLock=null;});}}catch{$('player-hint').textContent='Screen-awake permission unavailable. Adjust your device’s auto-lock settings.';}}
function releaseWake(){if(wakeLock){wakeLock.release().catch(()=>{});wakeLock=null;}}
function clearCountdown(){clearInterval(countdownTimer);countdownTimer=null;$('preroll').hidden=true;}
function pause(){clearCountdown();engine.cancel(now());releaseWake();showControls();paint();save();}
function startPlayback(){if(settings.manual){notify('Manual mode: use paragraph buttons, arrow keys or the progress slider.');return;}if(engine.position>=engine.count)return;if(needsCountdown&&Number(settings.countdown)>0){clearCountdown();countdownLeft=Number(settings.countdown);$('preroll').hidden=false;$('preroll').textContent=countdownLeft;countdownTimer=setInterval(()=>{countdownLeft--;if(countdownLeft<=0){clearCountdown();needsCountdown=false;engine.play(now());requestWake();showControls();}else $('preroll').textContent=countdownLeft;},1000);paint();}else{needsCountdown=false;engine.play(now());requestWake();showControls();paint();}}
function toggle(){if(engine.playing||engine.holds.size||countdownTimer)pause();else startPlayback();}
function showControls(){document.body.classList.remove('tp-idle');clearTimeout(idleTimer);if(engine.playing)idleTimer=setTimeout(()=>{if(engine.playing)document.body.classList.add('tp-idle');},3000);}
function start(){refresh();const t=getTiming();if(!t.valid)return;presenting=true;engine.reset(model.count,t.wpm);sessionScriptId=currentId;sessionText=$('script').value;needsCountdown=true;$('player').hidden=false;$('workspace').inert=true;document.querySelector('header').inert=true;document.body.classList.add('tp-presenting');$('player-title').textContent=$('title').value;renderScript();applySettings();$('reading-surface').focus();cancelAnimationFrame(raf);raf=requestAnimationFrame(tick);startPlayback();}
function exit(){pause();presenting=false;cancelAnimationFrame(raf);$('player').hidden=true;$('workspace').inert=false;document.querySelector('header').inert=false;document.body.classList.remove('tp-presenting','tp-idle');if(document.fullscreenElement)document.exitFullscreen().catch(()=>{});refresh();$('start').focus();save();}
function restart(){pause();engine.reset(model.count,getTiming().wpm);needsCountdown=true;paint();save();}
function seek(position){pause();needsCountdown=false;engine.seek(position,now());paint();$('player-hint').textContent=`Position changed. ${clock(engine.remaining)} remaining; session estimate ${clock(engine.active+engine.remaining)} active time.`;save();}
function paragraph(direction){const starts=[...new Set(model.paragraphs.filter(p=>p.end>p.start).map(p=>p.start))];const target=direction>0?starts.find(s=>s>engine.position+.01):starts.slice().reverse().find(s=>s<engine.position-.01);seek(target??(direction>0?engine.count:0));}
function speed(delta){pause();if(settings.mode==='duration'&&!confirm('Switch to speaking pace mode? Your target duration will become an estimate.'))return;settings.mode='pace';settings.wpm=Math.max(90,Math.min(220,Math.round(engine.wpm)+delta));engine.wpm=settings.wpm;applySettings();$('player-hint').textContent=`Pace changed. New remaining estimate: ${clock(engine.remaining)}. Resume when ready.`;save();}
function hold(key){if(!presenting)return;if(countdownTimer){pause();return;}engine.hold(key,now());showControls();paint();}
function release(key){engine.release(key,now());if(engine.playing){requestWake();showControls();}paint();}
$('profile').replaceChildren(...PROFILES.map(p=>new Option(`${p[1]} · ${p[2]} WPM`,p[0])),new Option('Custom pace','custom'));
$('script').addEventListener('input',()=>{history.push($('script').value);if(history.length>100)history.shift();redo=[];refresh();autosave();});
$('title').addEventListener('input',()=>{collect();recent();autosave();});
$('recent').onchange=()=>chooseScript($('recent').value);
$('new-script').onclick=()=>addScript();
$('duplicate').onclick=()=>addScript($('script').value,($('title').value||'Untitled script')+' (copy)');
$('delete-script').onclick=()=>{if(!confirm('Delete this script from this browser? Export a copy first if you need it.'))return;scripts=scripts.filter(s=>s.id!==currentId);if(scripts.length)chooseScript(scripts[0].id);else addScript();};
$('export').onclick=()=>{const blob=new Blob([$('script').value],{type:'text/plain;charset=utf-8'}),a=document.createElement('a');a.href=URL.createObjectURL(blob);a.download=($('title').value||'script').replace(/[<>:"/\\|?*]/g,'_')+'.txt';a.click();setTimeout(()=>URL.revokeObjectURL(a.href),1000);};
$('clear-data').onclick=()=>{if(!confirm('Delete ALL teleprompter scripts and settings saved in this browser? This cannot be undone.'))return;clearTimeout(saveTimer);scripts=[];currentId='';settings={...defaults};addScript();applySettings();notify('Local scripts and settings cleared. A new empty workspace is ready.');};
$('undo').onclick=()=>{if(history.length<2)return;redo.push(history.pop());$('script').value=history.at(-1);refresh();autosave();};
$('redo').onclick=()=>{if(!redo.length)return;const value=redo.pop();history.push(value);$('script').value=value;refresh();autosave();};
document.querySelectorAll('[data-wrap]').forEach(button=>button.onclick=()=>{const editor=$('script'),a=editor.selectionStart,b=editor.selectionEnd,value=editor.value,wrap=button.dataset.wrap;const left=wrap==='cue'?'[':wrap,right=wrap==='cue'?']':wrap;edit(value.slice(0,a)+left+value.slice(a,b)+right+value.slice(b),[a+left.length,b+left.length]);});
$('find-toggle').onclick=()=>{$('find-bar').hidden=!$('find-bar').hidden;if(!$('find-bar').hidden)$('find-text').focus();};
$('replace-all').onclick=()=>{const find=$('find-text').value;if(!find)return;const occurrences=$('script').value.split(find).length-1;edit($('script').value.split(find).join($('replace-text').value));notify(`${occurrences} replacements made.`);};
$('repair').onclick=()=>{if(confirm('Join single line breaks within paragraphs? Blank lines will remain. You can undo this.'))edit($('script').value.replace(/([^\n])\n(?!\n)/g,'$1 '));};
$('headers').onclick=()=>{const lines=$('script').value.split('\n'),counts=new Map();for(const line of lines)if(line.trim())counts.set(line.trim(),(counts.get(line.trim())||0)+1);const repeated=[...counts].filter(([,count])=>count>1).map(([line])=>line);if(!repeated.length)return notify('No repeated lines found.');const selected=prompt('Enter the exact repeated line to remove (all occurrences). Undo is available.\n\n'+repeated.slice(0,10).join('\n'),repeated[0]);if(selected&&repeated.includes(selected.trim()))edit(lines.filter(l=>l.trim()!==selected.trim()).join('\n'));};
for(const mode of ['pace','duration'])$('mode-'+mode).onclick=()=>changeSetting('mode',mode);
for(const [id,value]of Object.entries(defaults)){if(id==='mode')continue;const el=$(id);if(!el)continue;el.addEventListener('input',()=>{let val=el.type==='checkbox'?el.checked:typeof value==='number'?Number(el.value):el.value;if(id==='wpm'&&(!Number.isInteger(val)||val<90||val>220)){changeSetting(id,val);return;}if(id==='seconds'&&(val<0||val>=60||!Number.isFinite(val))){settings.seconds=val;el.setCustomValidity('Use seconds from 0 up to (but not including) 60.');refresh();return;}el.setCustomValidity('');if(id==='theme'){const colors={dark:['#f5f4f0','#151918'],light:['#20251e','#ffffff'],warm:['#3c352b','#f3eddf']};[settings['text-color'],settings['bg-color']]=colors[val];}changeSetting(id,val);});}
$('profile').onchange=()=>{const p=PROFILES.find(p=>p[0]===$('profile').value);if(p)changeSetting('wpm',p[2]);};
$('pace-range').oninput=()=>changeSetting('wpm',Number($('pace-range').value));
$('adjust-time').onclick=()=>{const t=getTiming(),seconds=Math.min(t.max,Math.max(t.min,Number(settings.minutes)*60+Number(settings.seconds)));settings.minutes=Math.floor(seconds/60);settings.seconds=seconds%60;applySettings();save();};
$('reset-display').onclick=()=>{for(const key of Object.keys(defaults))if(!['mode','wpm','minutes','seconds'].includes(key))settings[key]=defaults[key];applySettings();save();};
$('start').onclick=start;$('play').onclick=toggle;$('exit').onclick=exit;$('restart').onclick=restart;$('previous').onclick=()=>paragraph(-1);$('next').onclick=()=>paragraph(1);$('slower').onclick=()=>speed(-1);$('faster').onclick=()=>speed(1);
$('progress').oninput=()=>seek(Number($('progress').value)/1000*engine.count);
$('player-settings').onclick=()=>{
  pause();presenting=false;cancelAnimationFrame(raf);$('player').hidden=true;$('workspace').inert=false;document.querySelector('header').inert=false;
  document.body.classList.remove('tp-presenting','tp-idle');
  if(document.fullscreenElement)document.exitFullscreen().catch(()=>{});
  refresh();$('font-size').focus();notify('Session paused. Adjust settings, then return to your paused session.');
};
$('resume-session').onclick=()=>{
  if(!getTiming().valid)return;
  engine.wpm=getTiming().wpm;presenting=true;needsCountdown=false;
  $('player').hidden=false;$('workspace').inert=true;document.querySelector('header').inert=true;document.body.classList.add('tp-presenting');
  $('player-title').textContent=$('title').value;renderScript();applySettings();
  $('reading-surface').focus();paint();cancelAnimationFrame(raf);raf=requestAnimationFrame(tick);
  $('player-hint').textContent='Your position is restored. Press Play when ready.';save();
};
$('fullscreen').onclick=async()=>{try{if(document.fullscreenElement)await document.exitFullscreen();else if($('player').requestFullscreen)await $('player').requestFullscreen();else notify('Fullscreen is unavailable. The distraction-free view is already active.');}catch{notify('Fullscreen is unavailable. You can continue in this presentation view.');}};
const surface=$('reading-surface');surface.oncontextmenu=e=>e.preventDefault();surface.addEventListener('pointerdown',e=>{if(e.button!==0)return;e.preventDefault();surface.focus();try{surface.setPointerCapture(e.pointerId);}catch{}hold('pointer:'+e.pointerId);});surface.addEventListener('pointerup',e=>release('pointer:'+e.pointerId));surface.addEventListener('pointercancel',pause);surface.addEventListener('lostpointercapture',e=>{if(engine.holds.has('pointer:'+e.pointerId))pause();});
document.addEventListener('keydown',e=>{if(!presenting||$('player').hidden||e.target.closest('input,textarea,select,[contenteditable],dialog')||e.ctrlKey||e.metaKey||e.altKey)return;if(e.code==='Space'){if(e.target!==surface)return;e.preventDefault();if(!e.repeat)hold('space');return;}if(e.repeat)return;switch(e.key.toLowerCase()){case'p':e.preventDefault();toggle();break;case'r':restart();break;case'arrowleft':e.preventDefault();paragraph(-1);break;case'arrowright':e.preventDefault();paragraph(1);break;case'escape':exit();break;}});
document.addEventListener('keyup',e=>{if(e.code==='Space'&&engine.holds.has('space')){e.preventDefault();release('space');}});
window.addEventListener('blur',()=>{if(presenting)pause();});document.addEventListener('visibilitychange',()=>{if(document.hidden){if(presenting)pause();save();}});window.addEventListener('pagehide',save);setInterval(()=>{if(presenting&&engine.playing)save();},2000);
new ResizeObserver(()=>{cancelAnimationFrame(resizeFrame);resizeFrame=requestAnimationFrame(()=>{if(presenting){measure();paint();}});}).observe(surface);
$('player').addEventListener('pointermove',showControls);$('player').addEventListener('focusin',showControls);
$('help').onclick=()=>$('help-dialog').showModal();$('close-help').onclick=()=>$('help-dialog').close();
setupImports({base,accept:(text,title)=>addScript(text,title),notify});
try{db=await openDB();const saved=await readDB();if(saved?.scripts?.length){scripts=saved.scripts;settings={...defaults,...saved.settings};currentId=scripts.some(s=>s.id===saved.currentId)?saved.currentId:scripts[0].id;const s=scripts.find(s=>s.id===currentId);$('title').value=s.title;$('script').value=s.text;history=[s.text];recent();applySettings();if(saved.sessionScriptId===currentId&&saved.sessionText===s.text&&saved.position>=0){const t=getTiming();engine.reset(model.count,t.wpm);engine.position=Math.min(model.count,saved.position);engine.active=saved.active||0;sessionScriptId=currentId;sessionText=s.text;notify('Your script and reading position were recovered. Return to your paused session when ready.');}save();}else addScript(sample,'A little introduction');}catch{addScript(sample,'A little introduction');storageError();}
applySettings();
if('serviceWorker'in navigator)navigator.serviceWorker.register(`${base}/tools/teleprompter/sw.js`,{scope:`${base}/tools/teleprompter/`}).then(()=>navigator.serviceWorker.ready).catch(()=>notify('Offline reload is unavailable here. Export your script before closing this tab.'));
