# Creator calculator - Sprints 1 through 5

Open `/tools/creator-calculator/x/` (or `/xing/tools/creator-calculator/x/` under the local XAMPP subdirectory). `/tool/x-revenue-calculator/` redirects to it.

Requires PHP 8+ and the existing web server. Calculations require no database, credentials, package installation, or login. Sharing requires writable persistent storage as described below. The same PHP endpoint accepts an HTML form POST or returns JSON when `Accept: application/json` is sent. Ordinary calculations are not persisted. Explicit share creation saves only the selected public snapshot.

Sprint 1 includes manual inputs, validation, configurable qualification rules, explicit estimated inputs, both threshold states, gaps, and limiting-requirement progress. Actual gross 90-day impressions override the activity projection. Original-content share remains a separate input. The unknown verified-follower fallback is 40%, a labelled Xinng assumption chosen for this first version; edit it in the rules configuration as needed.

Official rules and modelling defaults: `services/creator-calculator/rules/x.php`. Rules were checked against the linked X help page on 2026-09-17. Configuration includes source, review date, and version. The engine accepts a supplied rules array so a database-backed provider can be introduced later. `CreatorPlatformCalculator` defines the implemented qualification capability; the timeline capability is included, alongside earnings and scenario capabilities.

Out of scope: screenshot extraction and additional platforms (next stage). Revenue assumptions are explicitly illustrative; qualification dates are not guaranteed.

Run engine checks from the repository root:

```sh
php services/creator-calculator/tests/run.php
```

Manual checks: enter 10,000 followers, 650 verified followers, 1,000 average impressions, and 7 posts/week. Expect 36,000 estimated qualifying impressions and 7% displayed progress. Enter 1,250,000 actual gross impressions in advanced settings to meet the reach threshold. Test unknown verified followers, zero values, edited averages, invalid verified counts, narrow screens, and native HTML submission without JavaScript.

## Sprint 2: velocity, reverse targets and rolling timeline

`QualificationProgressEngine` consumes validated inputs and the configured rules. Its results are returned under `progress`; `ProgressPresenter` supplies consistent copy to native HTML and JSON responses. The adapter also exposes `calculateTimeline()`.

- Recent qualification uses actual gross impressions when supplied. Future pace always uses average reach ? posts/week ? qualified share. A current pass can therefore coexist with an unsustainable future pace.
- Required posts/week and gross/qualified reach per post are separate mathematical targets. Original-content percentage is not applied again. Zero denominators yield null targets and explanatory text. UI targets round upwards and can exceed the form?s posting limit without being clamped into misleading advice.
- Below-target sustainable pace never gets a future impression date. If the impression threshold is already met, the UI reports that and separately warns about low pace.
- With a supplied recent total below the target and sufficient future pace, `d` is new qualifying impressions/day, `C` is the current qualifying total, `T` is the target, and `W` is the configured window. Earliest bound: `ceil((T-C)/d)` (old impressions retained). Latest bound: `ceil(T/d)` (old impressions expire immediately). Uniform-history estimate: `ceil((T-C)/(d-C/W))`. The sustainable-pace gate keeps the calculation within one window; this avoids accumulating old impressions forever. These are modelling bounds, not statistical confidence intervals.
- Impression timelines are distinct from overall qualification. Missing or estimated verified followers prevent overall qualification-day fields from being populated. No follower-growth rate or X application/payout date is invented.

Checks include the PRD example, exact thresholds, zero activity/share, actual-history precedence, old impressions expiring, follower blockers, changing rules, and currently-qualified accounts with insufficient future pace.

## Sprint 3: earnings and live scenarios

`RevenueEstimator` uses versioned configuration in `rules/revenue-x.php`. Rates are illustrative, uncalibrated Xinng planning assumptions, not observed or official X payouts. They must not be presented as statistically validated estimates. The methodology accordion discloses all four rates. Qualified accounts use projected qualifying activity times original-content share, converted to a 30-day month. Annual outputs equal monthly outputs times 12. Below-threshold accounts receive a clearly hypothetical minimum-threshold example. Zero original content or zero future activity returns zero revenue for an otherwise qualified account.

Each calculation returns `revenue_model_version`; selected public results are persisted by Sprint 4. No database or external account connection is required. `calculateScenarios` models a new full window while leaving supplied actual analytics intact in the main qualification result.

After the first calculation, changing account fields or any of the four scenario sliders recalculates after a 200 ms debounce. Requests are aborted and stale responses ignored on further edits. Automatic updates do not move keyboard focus. Without JavaScript, the native form still calculates and scenario sliders stay disabled.

Verification: 60 engine assertions plus JSON/native HTML response checks and PHP/JavaScript syntax checks. Sprint 3 browser interaction verification was blocked by automatic approval review due to the usage limit.

## Sprint 4: share cards and persistent public results

Create share card stores only selected display rows, the handle only if selected, a timestamp, schema/rules version, and the revenue model version only if earnings are selected. Raw inputs, country, unchecked metrics and unselected handles are excluded. The server recalculates and validates the submitted inputs instead of trusting client-supplied result text. Tokens use 24 cryptographically random bytes (48 hex characters). Public results load snapshots without recalculating against changed rules. Invalid tokens return 404. Public pages are marked noindex and have a no-referrer policy.

Routes:
- `POST /tools/creator-calculator/x/share.php`: JSON inputs/options/optional handle; session CSRF header required. Maximum 16 KB body and 30 create attempts per session per hour.
- `/tools/creator-calculator/x/r/{token}`: persistent public page, routed by the root Apache rewrite rule.

Storage:
- On a loopback server address, development uses PDO SQLite at `services/creator-calculator/storage/shared-results.sqlite`, because the configured local application MySQL database is absent. The file is ignored by Git and its directory is protected by `Require all denied`. Apache must honour that .htaccess. Back up the file to retain local links.
- Other deployments use the existing configured MySQL connection. The first explicit save creates `creator_shared_results` if absent, following the app's existing table-initialization approach. The database user needs CREATE and INSERT/SELECT privileges, or an administrator can provision the table ahead of time using the schema in `ShareRepository`.
- Development and production stores are separate; deploying code does not migrate local snapshots. There is no automatic fallback between stores. A storage failure returns 503 while ordinary calculations keep working.

The browser renders a 1200 x 675 PNG on an off-screen canvas using the existing logo and dragon assets. No GD/Imagick service or uploaded raster data is required. The preview, download and public-page image share the same renderer. The public page also has readable HTML for the selected metrics and works without JavaScript. PNG download requires JavaScript/canvas. X sharing opens an intent draft containing the public link; attaching the downloaded PNG remains a user action. It never posts automatically.

Changes to inputs or selection invalidate the current preview/download/link controls. Old published links are immutable and retain their original selections. Share creation is explicit; sliders never persist data. No external analytics or account lookup is added.

Run `php services/creator-calculator/tests/share.php` for selection/privacy/handle validation and persistence tests using an in-memory SQLite database. HTTP checks also cover CSRF, public routes, invalid links, methods, and blocked direct database access. Browser PNG appearance/download/copy verification remains pending because automatic approval review hit the usage limit.

## Sprint 5: optional X handle lookup

The handle form sits before manual inputs. `XHandleLookup` uses the official `GET https://api.x.com/2/users/by/username/{username}` endpoint with `public_metrics`, `verified_followers_count`, and `protected`. It returns only handle, display name, total followers, a verified count when available, source, and retrieval time. Missing verified counts stay null rather than becoming a fabricated actual count. Posts/week, reach, and private 90-day analytics remain manual; no follower-list crawl, profile scraping, or paid-account provisioning is performed.

Configure `X_API_BEARER_TOKEN` in the server environment or existing untracked root `.env` before live lookup can work. Use your X developer application's bearer token with access to user lookup. Never place it in JavaScript or commit it. The current local environment has no token, so the endpoint returns a friendly 503 and manual entry remains available. API access and usage costs are managed through the user's X developer account. Reference: https://docs.x.com/x-api/users/get-user-by-username

`POST lookup.php` requires the calculator's session CSRF token. It accepts up to 1 KB of JSON and limits lookup attempts to 15 per session per hour. HTTPS transport uses the fixed official host, no redirects, timeouts, and a capped response size. User-facing errors do not contain bearer tokens or raw upstream responses. Lookups are not persisted or published. The user is told the handle is sent to X. Local analytics hooks emit event names only; no handle or metric payload is sent to an analytics service.

Results are previewed and only applied on Use these numbers. Applying resets average impressions to the default and clears old 90-day impressions to avoid mixing historical data from another account. A missing verified count activates the labelled 40% assumption. Every value remains editable. A sharing handle is offered without checking Include handle. Typing another handle or choosing manual entry cancels pending lookup and clears the previous preview.

Verification: `php services/creator-calculator/tests/lookup.php` covers mocked successful/partial/error responses, zero counts, protected or mismatched profiles, invalid handles, missing credentials and invalid JSON. All 164 combined assertions pass. HTTP tests verify CSRF, bad handles, missing-token responses, and continued manual access. Successful live X integration needs the missing credential; browser verification remains pending after the earlier automatic-review usage-limit block.
