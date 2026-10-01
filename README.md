# xin_ng — Production checklist

This project includes password reset and SMTP support. Follow these steps before deploying to production (cPanel or any host).

1. Environment
- Create a `.env` file in the project root (do NOT commit it).
- Set at minimum:
  - `APP_ENV=production`
  - `APP_URL=https://yourdomain.com`
  - `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`
  - `GMAIL_USER`, `GMAIL_PASS`, `GMAIL_FROM` (or SMTP_* settings)
  - `NOWPAYMENTS_API_KEY`, `NOWPAYMENTS_IPN_SECRET`
  - `NOWPAYMENTS_PRICE_CURRENCY=NGN` (use a currency supported by your NOWPayments account)
  - `GEMINI_API_KEY` and optional `GEMINI_MODEL` for AI page drafts
  - `DEEPSEEK_API_KEY` and optional `DEEPSEEK_MODEL` for AI page drafts
  - `AI_DEFAULT_PROVIDER=gemini`, `AI_MAX_PROMPT_LENGTH=3000`, and `AI_PAGE_GENERATION_COST=5`

2. Composer dependencies
- On the server (or locally before upload) run:
```
composer install --no-dev --prefer-dist
```
- If Composer is not available on cPanel, run it locally and upload the `vendor/` directory.

3. PHP extensions / server setup
- Enable `zip` and `curl` extensions in `php.ini`.
- Ensure `openssl` is enabled for TLS.
- Use PHP 7.4+ or newer when possible.

4. File permissions
- Ensure `logs/` is writable by the web server (created automatically).
- Keep `.env` and other secrets out of version control.

5. SSL / HTTPS
- Serve the site over HTTPS. Set `APP_URL` to `https://yourdomain.com`.
- The app uses the `APP_URL` / `PUBLIC_URL` to build absolute links.
- In NOWPayments, configure the IPN callback URL as `https://yourdomain.com/nowpayments_ipn.php` and use the same IPN secret as `NOWPAYMENTS_IPN_SECRET`.

6. Mail
- Use an app password for Gmail (2FA required) or configure a dedicated SMTP/mail provider.
- Do not use Mailgun sandbox for production — configure a verified domain.

7. Security
- Keep `APP_ENV=production` in production to disable display_errors.
- Enforce rate-limiting for sensitive endpoints (password reset, signin).

8. Database migrations
- The app will create needed tables on demand via helper functions. Review schema in `config.php`.

9. API authentication
- Each new account receives an API token during signup. It is shown once on the first page after signup; the database stores only its SHA-256 hash.
- Create an account with `POST /api/signup.php` using JSON fields `name`, `email`, and `password`; `slug` is optional. A successful response is `201` and includes the one-time `api_token`.
- Send it to `POST /api/short-links.php` or `POST /api/qr-codes.php` in the `Authorization: Bearer YOUR_TOKEN` header over HTTPS. API-token requests do not need a CSRF token.
- If the token is lost, sign in and rotate it with `POST /api/token.php` using your session cookie and CSRF token. Rotating immediately invalidates the previous token.
- `GET /api/token.php` reports whether a token exists. `DELETE /api/token.php` revokes it; both require a signed-in session and CSRF token for deletion.
- Keep the token private.

10. Test
- Use the included `test_mail.php` to verify SMTP after config.

If you want, I can prepare a `deploy.md` with step-by-step cPanel instructions.
