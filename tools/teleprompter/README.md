# Xinng teleprompter

Plain JavaScript, CSS and PHP. No Node packages, build step, React, Next.js, document libraries, database or account dependency.

Open `/tools/teleprompter/`. Apache also redirects `/teleprompter` to this folder. The app works at a domain root or beneath a subfolder such as `/xing/`.

## Current scope

- Paste/type plain text or import a UTF-8/UTF-16 TXT file (20 MB, 20,000 spoken words maximum). Imports are reviewed and create a new script. PDF, DOC, DOCX and OCR are deferred by the owner.
- Local script library with autosave, duplicate, rename, delete, undo/redo, find/replace, line repair and TXT export.
- `**bold**`, `*italic*`, and `[non-spoken cues]` in the plain-text editor. These markers render in the prompter.
- Speaking pace (90–220 WPM), the six PRD profiles, or exact target duration. Active prompting time excludes pauses and countdown.
- Word-based positioning, hold to pause, persistent pause, safe focus-loss pause, paragraph navigation, seeking, restart, loop and manual mode.
- Display customization, independent mirror/flip, guide, fullscreen fallback, optional wake lock and saved reading-position recovery.
- Device-local IndexedDB storage. A service worker caches only this app and its assets for offline reload after a successful initial load over HTTPS or localhost.

## Deployment

Upload this folder and `assets/css/teleprompter.css`, `assets/js/teleprompter/`, plus the existing shared CSS and logo assets. Use the repository `.htaccess` for the short route. There is no install command. PHP serves the page only; playback and TXT reading are entirely local. Serve `.mjs` as JavaScript (the included folder `.htaccess` adds the MIME type on Apache).

Do not place secrets in browser assets. No analytics, remote script uploads, microphone or camera requests are implemented.

When changing cached files, bump the cache version in `sw.js`. Other Xinng pages are not cached. Local browser data is not cloud backup; users should export important scripts.

## Validation

Open `tests.html` for deterministic browser timing/state tests, including the 675-word/300-second case, 20-second hold, exact pace limits, cancelled holds, seeks and final-word timing. No test runner or packages are required. These tests simulate time; real device rendering and touchscreen behavior still need testing on intended hardware.

Manual checks: start/pause/restart, settings return without losing position, refresh recovery, TXT review/cancel, export, offline reload, and mobile layout. Safari/iOS fullscreen and wake-lock availability vary; the presentation view works without either API. No claim of native resting-finger trackpad detection is made.
