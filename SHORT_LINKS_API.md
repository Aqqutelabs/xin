# Short Links API Integration

Use this guide to connect another application to the Xinng short-link API.

## Connection details

- Base URL: `https://your-xinng-domain.example`
- Endpoint: `/api/short-links.php`
- Authentication: `Authorization: Bearer YOUR_API_TOKEN`
- Send and accept JSON (`Content-Type: application/json`, `Accept: application/json`).
- Use HTTPS. Keep the API token on your server; do not embed it in browser JavaScript or a mobile app bundle.
- The endpoint does not send CORS headers. For a separate frontend, call it through your own backend or configure a same-origin proxy.

API tokens are issued to an Xinng account and shown once. Use the token for that account; each request only reads and changes that account's short links. Requests with a bearer token do not need a CSRF token.

Set `XINNG_API_BASE_URL` and `XINNG_API_TOKEN` in the integrating app's server-side environment. Do not hardcode either value in client code.

## List links

```http
GET /api/short-links.php
Authorization: Bearer YOUR_API_TOKEN
Accept: application/json
```

Successful response (`200`):

```json
{
  "ok": true,
  "short_links": [
    {
      "id": 123,
      "title": "Product page",
      "destination_url": "https://example.com/product",
      "back_half": "product",
      "full_short_url": "https://your-xinng-domain.example/product",
      "status": "active",
      "click_count": 0,
      "created_at": "2026-09-29 12:00:00",
      "updated_at": "2026-09-29 12:00:00"
    }
  ]
}
```

## Create a link

```http
POST /api/short-links.php
Authorization: Bearer YOUR_API_TOKEN
Content-Type: application/json

{
  "title": "Product page",
  "destination_url": "https://example.com/product",
  "back_half": "product"
}
```

`title` is optional; if omitted or blank, the back-half is used. A destination without a scheme is treated as HTTPS. The back-half is normalized to lowercase and allows letters, numbers, underscores, and hyphens; it must be 3-64 characters and unique across links and profile slugs.

Successful response (`200`):

```json
{
  "ok": true,
  "short_link": {
    "id": 123,
    "title": "Product page",
    "destination_url": "https://example.com/product",
    "back_half": "product",
    "full_short_url": "https://your-xinng-domain.example/product",
    "status": "active",
    "click_count": 0,
    "created_at": "2026-09-29 12:00:00",
    "updated_at": "2026-09-29 12:00:00"
  }
}
```

Creating a link costs 1 credit. A user with no credits receives `402` with `{"ok":false,"error":"insufficient_credits"}`.

## Use Xinng links for Nonagon QR codes

Use Xinng as the redirect and link-analytics layer; generate the QR image in Nonagon. This API creates short links, not QR images. The QR image must encode the returned `short_link.full_short_url`, which redirects to the Nonagon resource page.

Persist the returned Xinng link ID and URL on the associated Nonagon resource (for example, `xinng_short_link_id` and `xinng_short_url`). Treat these as integration fields; the names can follow Nonagon's existing schema conventions. The ID supports later edits, and the URL is the value to encode in the QR.

Create the Xinng link only when a user explicitly generates or enables a QR for that resource. Do not create a link during page rendering, QR preview, or routine resource reads. On later visits, reuse the saved URL and render the QR from it; this must not call `POST` or spend another credit. If no saved link exists, show the explicit generate/enable action rather than creating one implicitly.

Use a stable, publicly accessible Nonagon destination URL for the resource, such as its canonical request or certificate page URL. Prefer keeping that destination stable: changing it requires confirmation and creates a separate Xinng link for 1 credit, while the existing link and its analytics remain. After a confirmed change succeeds, save the newly returned link ID and URL for future QR renders. Do not replace the saved values if the create/update request fails.

Recommended lifecycle:

1. On the explicit QR action, check the resource for its saved Xinng link ID and URL. If both exist, reuse the URL and generate the QR image locally in Nonagon.
2. If no link is saved, have Nonagon's server call `POST /api/short-links.php` with a stable destination and an available back-half. Save the returned `id` and `full_short_url` against the resource, then generate the QR image using that URL.
3. For an ordinary page view or subsequent QR render, use the saved URL without calling the Xinng create endpoint.
4. If the destination genuinely needs to change, use the saved ID with `PATCH`. Handle the initial `409` as a confirmation requirement; only after user confirmation retry with `confirm_create_new: true` and a new back-half. On success, replace the resource's saved link ID and URL with those from the new response.

Xinng has no idempotency key or resource-reference field on this endpoint, so Nonagon should prevent concurrent duplicate submissions for the same resource and persist a successful response promptly. Do not retry a timed-out create blindly: first reconcile against the account's links with `GET`, matching the intended destination and back-half, before deciding whether another `POST` is needed.

## Edit a link

Send `PATCH` with the link `id` and any fields to change. The ID can be in the JSON body or as `?id=123`.

```http
PATCH /api/short-links.php
Authorization: Bearer YOUR_API_TOKEN
Content-Type: application/json

{
  "id": 123,
  "title": "Updated title",
  "back_half": "product-sale"
}
```

Changing only the title or back-half updates the existing link and does not use a credit. Changing `destination_url` returns `409` with `requires_confirmation: true` by default, because preserving analytics requires creating a new link. After user confirmation, repeat the request with `confirm_create_new: true` and a new available `back_half`; this creates a new link, costs 1 credit, and returns `created_new: true`.

```json
{
  "id": 123,
  "destination_url": "https://example.com/new-product",
  "back_half": "new-product",
  "confirm_create_new": true
}
```

## Delete (archive) a link

Deletion is a soft delete: the link is archived and omitted from future list results.

```http
DELETE /api/short-links.php
Authorization: Bearer YOUR_API_TOKEN
Content-Type: application/json

{"id":123}
```

Successful response (`200`): `{"ok":true}`.

## Errors to handle

| HTTP status | `error` / response field | Meaning |
| --- | --- | --- |
| `401` | `auth` | Missing, invalid, or revoked API token |
| `402` | `insufficient_credits` | Creating a link requires 1 credit |
| `403` | `csrf` | Session-authenticated write is missing a CSRF token; bearer-token requests do not need one |
| `404` | `not_found` | Link does not exist for this account or is already archived |
| `409` | `requires_confirmation` | Destination change needs confirmation; other conflicts may include a message |
| `422` | Validation message in `error` | Invalid destination or unavailable/invalid back-half |
| `500` | `db` | Server/database error |
| `405` | `method_not_allowed` | HTTP method is unsupported |

Error response bodies are JSON. Check both the HTTP status and response body; do not assume every error uses the same `error` string.

## Minimal server-side JavaScript example

```js
const baseUrl = process.env.XINNG_API_BASE_URL;
const token = process.env.XINNG_API_TOKEN;

async function createShortLink({ title, destinationUrl, backHalf }) {
  const response = await fetch(`${baseUrl}/api/short-links.php`, {
    method: "POST",
    headers: {
      Authorization: `Bearer ${token}`,
      "Content-Type": "application/json",
      Accept: "application/json",
    },
    body: JSON.stringify({
      title,
      destination_url: destinationUrl,
      back_half: backHalf,
    }),
  });

  const result = await response.json();
  if (!response.ok || !result.ok) {
    throw new Error(result.error || `Xinng API returned HTTP ${response.status}`);
  }
  return result.short_link;
}
```

## Copilot prompt template

```text
Integrate the Xinng Short Links API into Nonagon. First inspect the existing
architecture and follow its current conventions for configuration, server-side API
calls, persistence, error handling, UI, and tests. Do not expose the API token to
browser code.

Use Xinng as the redirect and link-analytics layer, and generate QR images in Nonagon.
Encode the returned full_short_url in the QR. Persist each resource's returned Xinng
link ID and short URL. Create a link only after the user explicitly generates or
enables that resource's QR; never create a link during page rendering or a routine
resource read. Reuse the saved link on later visits so those visits spend no Xinng
credits. Prefer stable request/certificate destination URLs. If a destination must
change, require explicit user confirmation for the API's 409 flow, then save the new
link ID and URL only after the confirmed request succeeds.

API base URL: configure with XINNG_API_BASE_URL.
API token: configure with XINNG_API_TOKEN and send as Authorization: Bearer <token>.
Endpoint: /api/short-links.php. Use JSON and HTTPS.

Supported operations:
- GET endpoint: list this token owner's links; response is {ok:true, short_links:[...]}.
- POST endpoint: create with {title?, destination_url, back_half}; costs 1 credit;
  response is {ok:true, short_link:{id,title,destination_url,back_half,
  full_short_url,status,click_count,created_at,updated_at}}.
- PATCH endpoint: update with {id, title?, destination_url?, back_half?}. A destination
  change first returns HTTP 409 and requires_confirmation:true. Ask the user to confirm,
  then retry with confirm_create_new:true and a new back_half. This creates a separate
  link and costs 1 credit, preserving the old link's analytics.
- DELETE endpoint: archive with {id}; response is {ok:true}.

Handle JSON errors and HTTP statuses, especially 401 (auth), 402 (insufficient credits),
404 (not found), 409 (confirmation/conflict), and 422 (validation). Do not invent API
routes or fields. Implement the integration in the appropriate server-side layer, add or
update focused tests, and report any required environment variables and setup steps.
```