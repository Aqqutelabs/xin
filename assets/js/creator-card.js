(() => {
 'use strict';
 const draw = (card, dragon) => {
  const surface = document.createElement('canvas'); surface.width = 1080; surface.height = 1350;
  const ctx = surface.getContext('2d');
  const background = ctx.createLinearGradient(0, 0, 1080, 1350);
  background.addColorStop(0, '#e74738'); background.addColorStop(1, '#861a20');
  ctx.fillStyle = background; ctx.fillRect(0, 0, 1080, 1350);
  const size = [120, 135, 180][Math.floor(Math.random() * 3)];
  for (let y = 0; y < 1350; y += size) for (let x = 0; x < 1080; x += size) {
   if (Math.random() > .45) continue;
   ctx.fillStyle = Math.random() > .5 ? '#ffffff12' : '#380b141e';
   ctx.beginPath();
   if (Math.random() > .5) ctx.arc(x + size / 2, y + size / 2, size / 2, 0, Math.PI * 2);
   else ctx.rect(x, y, size, size);
   ctx.fill();
  }
  ctx.shadowColor = '#29060970'; ctx.shadowBlur = 65; ctx.shadowOffsetY = 25;
  ctx.fillStyle = '#101011'; ctx.beginPath(); ctx.roundRect(86, 110, 908, 1130, 44); ctx.fill();
  ctx.shadowBlur = 0; ctx.shadowOffsetY = 0;
  // Keep the whole composition inside the rounded black inset.
  ctx.save(); ctx.beginPath(); ctx.roundRect(86, 110, 908, 1130, 44); ctx.clip();
  const glow = ctx.createRadialGradient(870, 170, 0, 870, 170, 610);
  glow.addColorStop(0, '#d63b2a25'); glow.addColorStop(1, '#d63b2a00');
  ctx.fillStyle = glow; ctx.fillRect(86, 110, 908, 1130);
  ctx.textAlign = 'center';
  ctx.fillStyle = '#e8b8af'; ctx.font = '600 25px Arial';
  ctx.fillText('X ORIGINAL CONTENT', 540, 213);
  ctx.fillStyle = '#a89996'; ctx.font = '500 23px Arial';
  ctx.fillText('REWARDS PROGRAM', 540, 250);

  ctx.font = 'bold 31px Arial';
  const handle = card.handle || '';
  const pillWidth = Math.min(720, ctx.measureText(handle).width + 64);
  if (handle) {
   ctx.fillStyle = '#ffffff0d'; ctx.beginPath(); ctx.roundRect(540 - pillWidth / 2, 302, pillWidth, 64, 32); ctx.fill();
   ctx.fillStyle = '#fff2ed'; ctx.fillText(handle, 540, 344);
  }

  const monthly = card.value.endsWith(' / month');
  ctx.fillStyle = '#fff6f0'; ctx.font = 'bold 78px Arial';
  ctx.fillText(monthly ? 'My earning' : 'My next', 540, 484);
  ctx.fillText(monthly ? 'potential.' : 'milestone.', 540, 571);
  ctx.fillStyle = '#bfaeaa'; ctx.font = '400 28px Arial';
  ctx.fillText(monthly ? 'Estimated monthly earnings' : 'Estimated time to qualify', 540, 639);

  const dayMatch = card.value.match(/^([\d,]+) (days?)$/);
  const value = monthly ? card.value.slice(0, -8) : dayMatch ? dayMatch[1] : card.value;
  let sizeText = dayMatch ? 178 : 108; ctx.font = `bold ${sizeText}px Arial`;
  while (ctx.measureText(value).width > 744 && sizeText > 32) ctx.font = `bold ${--sizeText}px Arial`;
  const ink = ctx.createLinearGradient(160, 700, 900, 850);
  ink.addColorStop(0, '#ffd0bd'); ink.addColorStop(1, '#ff6858');
  ctx.fillStyle = ink; ctx.fillText(value, 540, 824);
  if (monthly || dayMatch) {
   ctx.fillStyle = '#edc2b7'; ctx.font = '500 32px Arial';
   ctx.fillText(monthly ? 'per month' : dayMatch[2], 540, 881);
  }
  ctx.drawImage(dragon, 463, 963, 154, 154);
  ctx.fillStyle = '#fff6f0'; ctx.font = 'bold 34px Arial'; ctx.fillText('xin.ng', 540, 1162);
  ctx.restore();
  return surface;
 };
 window.CreatorCard = {draw};
})();
