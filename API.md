# API quick guide

Most endpoints under `/api/` are callable without signing in, an API token, or a CSRF token. The X account connection form requires a signed-in browser session, and dashboard publish/disconnect requests require its CSRF token. Account-scoped endpoints use a caller-supplied `user_id` (or `account_id`) to select the account. Any caller able to use an unprotected account-scoped endpoint can act on an account ID they know or guess, so never expose this API to untrusted clients.

## Create an account

`POST /api/signup.php` creates an account. It is public and does **not** require an API token, login session, or CSRF token.

Send JSON with `Content-Type: application/json`:

```http
POST /api/signup.php
Content-Type: application/json
Accept: application/json

{
  "name": "Alex Example",
  "email": "alex@example.com",
  "password": "choose-a-password",
  "slug": "alex"
}
```

`slug` is optional. If omitted, the endpoint creates an available slug based on the name. A successful request returns `201 Created`:

```json
{
  "ok": true,
  "user": {
    "id": 123,
    "name": "Alex Example",
    "email": "alex@example.com",
    "page_id": 456,
    "page_slug": "alex",
    "credit_balance": 1000
  },
  "api_token": "xin_...",
  "token_message": "Store this token securely. It is only returned during signup."
}
```

Save `api_token` securely when the response is received; it is only returned at signup. The API signup endpoint does not create a browser login session. The HTML form at `/signup.php` is a separate flow: it uses form fields and redirects to the dashboard after signup.

### Signup errors

| Status | Error | Meaning |
| --- | --- | --- |
| `400` | `invalid_json` | Request body was not valid JSON |
| `405` | `method_not_allowed` | Use `POST` |
| `409` | `email_already_exists` | An account already uses that email |
| `422` | `missing_fields`, `field_too_long`, `invalid_email`, or `invalid_slug` | Correct the submitted fields |
| `500` | `db` or `signup_failed` | Server or database error |

Signup is not account-scoped and does not need `user_id`.

## Account-scoped requests

Supply the account's numeric Xin `user_id` as a query parameter for GET requests or in the JSON body for writes. `account_id` is also accepted. For example, list an account's pages:

```http
GET /api/pages.php?user_id=123
Accept: application/json
```

Example write:

```http
POST /api/pages.php
Content-Type: application/json

{"user_id":123,"title":"My page"}
```

Short-link and QR-code API requests also accept a frontend/customer ID as a string `user_id` (for example, a UUID). Xin maps that external ID to an internal account and reuses the mapping on later requests; customers do not need to sign up on Xin for these endpoints. Numeric IDs continue to refer to existing internal Xin users. This mapping is not authentication: because the API does not verify who sent the external ID, anyone who knows or guesses it can act as that account.

If no `user_id` is supplied, a browser session may be used when one exists; otherwise account-scoped endpoints return `422` with `missing_user_id`.

## Create a short link for an equipment page

Send a `POST` request to `https://your-xin-domain.com/api/short-links.php` with `Content-Type: application/json`. Use the same customer ID from your frontend on every request for that customer. It can be a UUID/string ID; numeric IDs refer to existing Xin users. Choose an unused `back_half`:

   ```json
   {
     "user_id": "customer-uuid-from-your-system",
     "title": "Equipment name",
     "destination_url": "https://frontend.example.com/equipment/equipment-id",
     "back_half": "equipment-name"
   }
   ```

Replace `https://frontend.example.com/equipment/equipment-id` with the complete equipment page URL currently used by your frontend. Include the domain and full route. The backend stores and redirects to that exact URL; it does not assume a fixed Nonangon domain. If the frontend domain changes, send the new complete URL when creating the link.

When testing locally with XAMPP, the endpoint may be `http://localhost/xinngqr/api/short-links.php`.

On success, the response includes `short_link.full_short_url`, the Xin URL to share:

```json
{
  "ok": true,
  "short_link": {
    "id": 123,
    "title": "Equipment name",
    "destination_url": "https://frontend.example.com/equipment/equipment-id",
    "back_half": "equipment-name",
    "full_short_url": "https://your-xin-domain.com/equipment-name",
    "status": "active",
    "click_count": 0
  }
}
```

Opening `full_short_url` redirects to the saved `destination_url`. The link must be active and not archived. Each visit is recorded in its click count.

To list the customer's short links and check click counts, send:

```http
GET https://your-xin-domain.com/api/short-links.php?user_id=customer-uuid-from-your-system
Accept: application/json
```

Find the link in the `short_links` array. Its `click_count` increases after visits to the short URL. A missing account ID returns `422` with `missing_user_id`.

## Create a QR code that uses the short link

To have scans pass through Xin's short-link redirect (and track short-link clicks), create the QR code with the same `destination_url` and an unused `back_half`:

```http
POST https://your-xin-domain.com/api/qr-codes.php
Content-Type: application/json
Accept: application/json
```

```json
{
  "user_id": "customer-uuid-from-your-system",
  "title": "Equipment name",
  "type": "website",
  "destination_url": "https://frontend.example.com/equipment/equipment-id",
  "back_half": "equipment-name"
}
```

Use the full, current equipment page URL for `destination_url`, just as for short-link creation. When `back_half` is supplied, the QR image encodes the Xin short URL (`https://your-xin-domain.com/equipment-name`), not the frontend destination. Scanning it opens Xin's resolver, which redirects to the saved destination URL. The response's `qr_code.full_short_url` gives the short URL and `qr_code.qr_image_url` gives the QR image URL.

If you omit `back_half`, the QR code goes directly to `destination_url` and does not use the short-link redirect.

## API endpoints

| Endpoint | Purpose | Account selector |
| --- | --- | --- |
| `/api/signup.php` | Create an account | Not required |
| `/api/pages.php` | List, view, create, update, archive pages | `user_id` in query/body |
| `/api/ai-page.php` | Generate an AI page draft | `user_id` in JSON body |
| `/api/qr-codes.php` | List, create, edit, archive QR codes | `user_id` in query/body |
| `/api/short-links.php` | List, create, edit, archive short links | `user_id` in query/body |
| `/api/token.php` | Inspect, issue, or revoke an API token | `user_id` in query/body |
| `/api/x/account.php` | Check X connection | `user_id` in query |
| `/api/x/connect.php` | Connect an X account through TwitterAPI.io | Signed-in browser session |
| `/api/x/disconnect.php` | Disconnect X account | `user_id` in query/body |
| `/api/x/analytics.php` | Read connected X account analytics | `user_id` in query |
| `/api/x/publish.php` | Publish a post to X | `user_id` in JSON body |

The X account connection uses TwitterAPI.io, not X's OAuth authorization page. Configure `TWITTERAPI_IO_API_KEY` and `X_TOKEN_ENCRYPTION_KEY` on the server. The signed-in dashboard connection form sends the X username, email, password, optional 2FA seed, and required residential proxy to TwitterAPI.io; Xinng does not store the password or 2FA seed. The returned login cookie and proxy are stored encrypted and used for provider-backed posting. Never submit X credentials on an untrusted or non-HTTPS deployment.

The API does not configure CORS headers. A cross-origin browser frontend may need a same-origin backend/proxy or an explicit CORS configuration. Check the Network tab for the exact URL, HTTP status, and JSON response; `401` from an external provider can still indicate an expired X authorization, rather than Xinng API login.
