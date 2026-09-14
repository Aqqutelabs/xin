// One rule for editor and playback: Unicode letters/numbers; internal apostrophes
// and hyphens join tokens. Bracketed cues and emphasis markers are not spoken.
export const WORD = /[\p{L}\p{N}]+(?:['’\-][\p{L}\p{N}]+)*/gu;
export const PROFILES = [
  ['snail','Very Slow',90], ['slow','Slow',110],
  ['conversational','Normal Conversation',135], ['narration','Professional Narration',155],
  ['fast','Fast / Excited',180], ['maximum','Extreme Maximum',220]
];
export function parseScript(text) {
  let count = 0;
  const paragraphs = text.replace(/\r\n?/g,'\n').split(/\n+/).filter(s=>s.trim()).map((line, id) => {
    const start = count;
    const parts = line.split(/(\[[^\]]*\]|\*\*[^*]+\*\*|\*[^*]+\*)/g).filter(Boolean).map(part => {
      const cue = part.startsWith('[') && part.endsWith(']');
      const bold = part.startsWith('**') && part.endsWith('**');
      const italic = !bold && part.startsWith('*') && part.endsWith('*');
      const value = bold ? part.slice(2,-2) : italic ? part.slice(1,-1) : part;
      const tokens = []; let end = 0;
      if (!cue) for (const m of value.matchAll(WORD)) {
        if (m.index > end) tokens.push({text:value.slice(end,m.index)});
        tokens.push({text:m[0],word:count++}); end = m.index + m[0].length;
      }
      tokens.push({text:value.slice(end)});
      return {cue,bold,italic,tokens};
    });
    return {id,start,end:count,parts};
  });
  return {paragraphs,count};
}
export function timing(count, mode, pace, seconds) {
  const wpm = mode === 'duration' ? count * 60 / seconds : Number(pace);
  const duration = mode === 'duration' ? Number(seconds) : count * 60 / wpm;
  return {wpm,duration,min:count*60/220,max:count*60/90,
    valid:count>0 && count<=20000 && Number.isFinite(wpm) && wpm>=90 && wpm<=220 && duration>0};
}
export function clock(seconds) {
  seconds = Number.isFinite(seconds) ? Math.max(0, Math.ceil(seconds)) : 0;
  return `${Math.floor(seconds/60)}:${String(seconds%60).padStart(2,'0')}`;
}
export class Playback {
  constructor(count=0,wpm=135) { this.reset(count,wpm); }
  reset(count=this.count,wpm=this.wpm) {
    Object.assign(this,{count,wpm,position:0,active:0,playing:false,holds:new Set(),resumeAfterHold:false,last:null});
  }
  advance(now) {
    if (this.playing && this.last !== null) {
      const elapsed = Math.max(0,(now-this.last)/1000);
      const used = Math.min(elapsed,this.remaining);
      this.active += used; this.position = Math.min(this.count,this.position+used*this.wpm/60);
      if (this.position >= this.count-1e-9) { this.position=this.count; this.playing=false; }
    }
    this.last=now;
  }
  get remaining() { return Math.max(0,(this.count-this.position)*60/this.wpm); }
  play(now) { if(this.position<this.count && !this.holds.size) {this.playing=true;this.last=now;} }
  pause(now) { this.advance(now);this.playing=false;this.resumeAfterHold=false; }
  hold(key,now) {
    if(this.holds.has(key)) return;
    if(!this.holds.size) {this.advance(now);this.resumeAfterHold=this.playing;this.playing=false;}
    this.holds.add(key);
  }
  release(key,now) {
    if(!this.holds.delete(key)) return;
    if(!this.holds.size && this.resumeAfterHold) {this.resumeAfterHold=false;this.play(now);}
  }
  cancel(now) {this.pause(now);this.holds.clear();}
  seek(position,now) {this.cancel(now);this.position=Math.max(0,Math.min(this.count,position));}
}
