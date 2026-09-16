<?php
require_once __DIR__ . "/../../includes/brand.php";
// Deliberately independent of config.php: guests and offline use need no database.
$script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/tools/teleprompter/index.php');
$base = preg_replace('~/tools/teleprompter/(?:index\.php)?$~', '', $script);
if ($base === $script) $base = rtrim(dirname($script), '/.');
$logoUrl = htmlspecialchars(xinng_brand_logo_url($base), ENT_QUOTES, 'UTF-8');
$base = htmlspecialchars($base, ENT_QUOTES, 'UTF-8');
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <meta name="theme-color" content="#fbfaf7">
  <title>Teleprompter — xin.ng</title>
  <meta name="description" content="Find your flow. A free, private teleprompter for your next video, presentation or big idea. No account needed.">
  <link rel="icon" href="<?= $base ?>/assets/images/logo-icon.svg">
  <link rel="stylesheet" href="<?= $base ?>/assets/css/style.css">
  <link rel="stylesheet" href="<?= $base ?>/assets/css/teleprompter.css">
</head>
<body class="tp-app" data-base="<?= $base ?>">
  <a class="tp-skip" href="#script">Skip to script</a>
  <header class="landing-nav"><div class="landing-nav__inner">
    <a class="xinng-brand" href="<?= $base ?>/index.php" aria-label="Xinng home"><img class="xinng-brand__image" src="<?= $logoUrl ?>" alt="Xinng" width="1736" height="906"></a>
    <nav class="landing-nav__links" aria-label="Primary navigation"><a href="<?= $base ?>/index.php#features">Solutions</a><a href="<?= $base ?>/pricing.php">Pricing</a><a href="<?= $base ?>/signin.php">Login</a><a class="landing-btn landing-btn--primary" href="<?= $base ?>/signup.php">Sign up</a></nav>
  </div></header>
  <main class="tp-shell" id="workspace">
    <div class="tp-heading"><div><div class="tp-eyebrow">XINNG TOOLS <span>/</span> TELEPROMPTER</div><h1>Your words. <em>Your rhythm.</em></h1><p>Stay present. Speak naturally. Let your script follow your lead.</p></div><span class="tp-guest"><i></i> Free to use · No account needed</span></div>
    <div class="tp-layout">
      <section class="tp-editor-card" aria-label="Script workspace">
        <div class="tp-card-heading"><div><span class="tp-step">01</span><h2>Your script</h2></div><span id="save-status" role="status">Saving on this device…</span></div>
        <div class="tp-script-actions"><label class="tp-sr" for="recent">Recent scripts</label><select id="recent" aria-label="Recent scripts"></select><button id="new-script">+ New</button><button id="duplicate" title="Duplicate script">Duplicate</button><button id="export" title="Export as TXT">Export ↓</button><button id="delete-script" class="tp-subtle" title="Delete current script">Delete</button></div>
        <label class="tp-sr" for="title">Script title</label><input id="title" class="tp-title" maxlength="120" placeholder="Untitled script" value="A little introduction">
        <div class="tp-editor-tools" aria-label="Editor tools"><button data-wrap="**" title="Bold selection"><b>B</b></button><button data-wrap="*" title="Italic selection"><i>I</i></button><button data-wrap="cue" title="Mark selection as a non-spoken cue">[ Cue ]</button><span></span><button id="undo" title="Undo">↶</button><button id="redo" title="Redo">↷</button><button id="find-toggle">Find & replace</button><button id="repair">Repair line breaks</button><button id="headers">Remove repeated lines</button></div>
        <div id="find-bar" class="tp-find" hidden><input id="find-text" aria-label="Find text" placeholder="Find text"><input id="replace-text" aria-label="Replacement text" placeholder="Replace with"><button id="replace-all">Replace all</button></div>
        <label class="tp-sr" for="script">Script text. Use brackets for non-spoken cues, double asterisks for bold, single asterisks for italic.</label><textarea id="script" spellcheck="true" placeholder="Paste your script here, or start with a thought…"></textarea>
        <div class="tp-editor-foot"><span><strong id="word-count">0</strong> spoken words <span class="tp-dot">·</span> <span id="editor-time">0:00</span> estimated</span><span>[brackets] = silent cues</span></div>
        <label class="tp-upload" id="drop-zone" for="upload"><span class="tp-upload-icon">↥</span><span><strong>Bring your own words</strong><small>Drop a file here or <u>browse files</u> · Plain text (.txt) · up to 20 MB</small></span><input id="upload" type="file" accept=".txt,text/plain" class="tp-sr"></label>
        <div class="tp-tip"><span>✦</span><p><strong>A little room to breathe.</strong> Add cues like [pause] or [look at camera]. They stay on screen without changing your speaking time.</p></div>
      </section>
      <aside class="tp-setup" aria-label="Prompting settings">
        <section class="tp-settings-card"><div class="tp-card-heading"><div><span class="tp-step">02</span><h2>Find your pace</h2></div><span class="tp-tag">YOUR RHYTHM</span></div>
          <div class="tp-segments" role="group" aria-label="Timing mode"><button id="mode-pace" class="selected" aria-pressed="true">Speaking pace</button><button id="mode-duration" aria-pressed="false">Target duration</button></div>
          <div id="pace-inputs"><label for="profile">Speaking style</label><select id="profile"></select><div class="tp-pace-number"><input id="wpm" type="number" min="90" max="220" step="1" value="135" aria-label="Words per minute"><span>words / minute</span></div><input id="pace-range" aria-label="Speaking pace" type="range" min="90" max="220" value="135"><div class="tp-range-labels"><span>90 · Deliberate</span><span>220 · Fast</span></div></div>
          <div id="duration-inputs" hidden><label>How long do you have?</label><div class="tp-duration"><label><input id="minutes" type="number" min="0" max="999" value="2">minutes</label><span>:</span><label><input id="seconds" type="number" min="0" max="59.999999" step="any" value="0">seconds</label></div><p id="required-wpm"></p><button id="adjust-time">Use nearest valid duration</button></div>
          <div class="tp-estimate"><span>Your script, comfortably delivered</span><strong id="estimate">0:00</strong></div><p id="timing-error" class="tp-error" role="alert" hidden></p>
        </section>
        <section class="tp-settings-card"><div class="tp-card-heading"><div><span class="tp-step">03</span><h2>Make it yours</h2></div><button id="reset-display" class="tp-subtle">Reset</button></div>
          <div class="tp-control-row"><label for="font-size">Text size</label><output id="size-output">48 px</output></div><input id="font-size" type="range" min="24" max="96" value="48">
          <div class="tp-two"><label>Theme<select id="theme"><option value="dark">Midnight</option><option value="light">Daylight</option><option value="warm">Warm paper</option></select></label><label>Countdown<select id="countdown"><option value="0">Off</option><option value="3" selected>3 seconds</option><option value="5">5 seconds</option><option value="10">10 seconds</option></select></label></div>
          <div class="tp-checks"><label><input id="mirror" type="checkbox"> Mirror horizontally</label><label><input id="flip" type="checkbox"> Flip vertically</label></div>
          <details><summary>More display settings <span>+</span></summary><div class="tp-advanced"><label>Font family<select id="font-family"><option value="sans-serif">Sans serif</option><option value="serif">Serif</option><option value="monospace">Monospace</option></select></label><label>Line spacing<input id="line-height" type="range" min="1.2" max="2.4" step="0.1" value="1.6"></label><label>Text width<input id="text-width" type="range" min="30" max="95" value="75"></label><label>Side margins<input id="margins" type="range" min="0" max="15" value="5"></label><label>Reading guide position<input id="guide-position" type="range" min="15" max="65" value="35"></label><label>Alignment<select id="alignment"><option value="left">Left</option><option value="center">Center</option><option value="right">Right</option></select></label><div class="tp-two"><label>Text color<input id="text-color" type="color" value="#f5f4f0"></label><label>Background<input id="bg-color" type="color" value="#151918"></label></div><label><input id="guide" type="checkbox" checked> Show reading guide</label><label><input id="dim" type="checkbox"> Dim surrounding words</label><label><input id="loop" type="checkbox"> Loop rehearsal</label><label><input id="manual" type="checkbox"> Manual mode (no automatic scrolling)</label></div></details>
          <div class="tp-mini-preview" id="mini-preview" aria-label="Live display preview"><div id="preview-text">Take a breath.<br><strong>You’ve got this.</strong></div><span>LIVE PREVIEW</span></div>
        </section>
        <button class="tp-start" id="start">Start prompting <span>↗</span></button><button id="resume-session" hidden>Return to paused session</button><p class="tp-under-start">Just you, your script, and a little confidence.</p>
      </aside>
    </div>
    <footer class="tp-footer"><a class="xinng-brand xinng-brand--small" href="<?= $base ?>/index.php" aria-label="Xinng home"><img class="xinng-brand__image" src="<?= $logoUrl ?>" alt="Xinng" width="1736" height="906"></a><span>Private by default. Scripts stay in this browser, on this device.</span><button id="clear-data">Clear local data</button><button id="help">Shortcuts & help</button></footer>
    <p id="notice" class="tp-notice" role="status" hidden></p>
  </main>
  <section id="player" hidden aria-label="Teleprompter presentation">
    <div class="tp-player-top"><button id="exit">← Back to script</button><span id="player-title"></span><button id="fullscreen">⛶ Fullscreen</button></div>
    <div id="reading-surface" tabindex="0" aria-label="Reading surface. Hold Space or press and hold to pause. Press P to toggle playback."><div id="reading-guide"></div><div id="mirror-layer"><div id="reading-text"></div></div><div id="preroll" hidden></div></div>
    <div class="tp-player-bottom"><div class="tp-session"><span id="play-state" role="status">Ready</span><span>Active <b id="active-time">0:00</b></span><span>Remaining <b id="remaining-time">0:00</b></span><span id="player-pace">135 WPM</span><span id="percent">0%</span></div><input id="progress" aria-label="Seek through script (pauses playback)" type="range" min="0" max="1000" value="0"><div class="tp-player-controls"><button id="restart" title="Restart">↶ Restart</button><button id="previous" title="Previous paragraph">← Paragraph</button><button id="play" class="tp-play">▶ Play</button><button id="next" title="Next paragraph">Paragraph →</button><button id="slower" aria-label="Slower by one word per minute">−</button><button id="faster" aria-label="Faster by one word per minute">+</button><button id="player-settings">Aa Settings</button></div><p id="player-hint">Hold Space or the reading surface to pause · P to play/pause · Esc to exit</p></div>
  </section>
  <dialog id="import-dialog"><div class="tp-dialog-heading"><h2 id="import-title">Import script</h2><button id="cancel-import">Cancel</button></div><p id="import-status" role="status">Reading your file…</p><progress id="import-progress" max="1"></progress><p id="import-notes"></p><textarea id="import-review" aria-label="Review and edit extracted text" hidden></textarea><button id="accept-import" class="tp-start" hidden>Use reviewed text</button></dialog>
  <dialog id="help-dialog"><div class="tp-dialog-heading"><h2>A smoother delivery</h2><button id="close-help">Close</button></div><p><kbd>P</kbd> toggles play/pause. Hold <kbd>Space</kbd> or the reading surface for a momentary pause. Arrow keys move between paragraphs; <kbd>R</kbd> restarts, <kbd>Esc</kbd> exits.</p><p>Pauses freeze active time. Switching apps or tabs pauses safely; resume explicitly. Keep the prompter visible while your separate camera records.</p><p>Use **bold**, *italic* and [non-spoken cues]. Contractions, hyphenated words and written numbers count as one word each. Punctuation and cues do not count. Timing is designed for English; it does not measure your voice.</p><p>Scripts autosave only in this browser, not to your Xinng account. Export important scripts. Clearing browser data removes local scripts. Once loaded and cached, this app and your saved scripts work offline.</p><p>Paste your script or import a plain-text (.txt) file. Imports stay on this device and never replace your current script. Review the text before adding it to your library.</p></dialog>
  <noscript>This teleprompter needs JavaScript for editing, local saving and playback.</noscript>
  <script type="module" src="<?= $base ?>/assets/js/teleprompter/app.mjs"></script>
</body>
</html>
