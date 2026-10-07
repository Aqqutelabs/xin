<?php
declare(strict_types=1);
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../services/x/XAccountStore.php';
require_once __DIR__ . '/../../services/x/TwitterApiIoService.php';

use Xinng\X\TwitterApiIoService;
use Xinng\X\XAccountStore;
use Xinng\X\XApiException;

session_start();
$userId = filter_var($_SESSION['user_id'] ?? null, FILTER_VALIDATE_INT);
if ($userId === false || $userId <= 0) {
    unset($_SESSION['user_id']);
    header('Location: ../../signin.php');
    exit;
}
$requestedUserId = filter_var($_GET['user_id'] ?? $_POST['user_id'] ?? null, FILTER_VALIDATE_INT);
if ($requestedUserId !== false && $requestedUserId !== null && $requestedUserId !== (int)$userId) {
    http_response_code(403);
    exit('This connection request does not match the signed-in account.');
}
if ($_SERVER['REQUEST_METHOD'] !== 'GET' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: GET, POST');
    http_response_code(405);
    exit('Use GET or POST.');
}

$errorMessage = '';
$requestHost = strtolower((string)parse_url('http://' . ($_SERVER['HTTP_HOST'] ?? ''), PHP_URL_HOST));
$remoteAddress = (string)($_SERVER['REMOTE_ADDR'] ?? '');
$isLocalHost = in_array($requestHost, ['localhost', '127.0.0.1', '::1'], true)
    && in_array($remoteAddress, ['127.0.0.1', '::1'], true);
$isHttps = (!empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off') || (int)($_SERVER['SERVER_PORT'] ?? 0) === 443;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ((int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 16384) {
        $errorMessage = 'The submitted connection details are too large.';
        http_response_code(413);
    } elseif (!verify_csrf_token(is_string($_POST['_csrf'] ?? null) ? $_POST['_csrf'] : null)) {
        $errorMessage = 'The form expired. Reload this page and try again.';
        http_response_code(403);
    } elseif (!$isHttps && !$isLocalHost) {
        $errorMessage = 'Use a secure HTTPS connection before submitting X account credentials.';
        http_response_code(400);
    } else {
        $username = trim((string)($_POST['username'] ?? ''));
        $email = trim((string)($_POST['email'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
        $totpSecret = trim((string)($_POST['totp_secret'] ?? ''));
        $proxy = trim((string)($_POST['proxy'] ?? ''));
        if ($username === '' || $email === '' || $password === '' || $proxy === '') {
            $errorMessage = 'Username, email, password, and proxy are required.';
            http_response_code(422);
        } elseif (TWITTERAPI_IO_API_KEY === '' || X_TOKEN_ENCRYPTION_KEY === '') {
            $errorMessage = 'TwitterAPI.io is not configured on this server.';
            http_response_code(503);
        } else {
            try {
                $pdo = get_db_connection();
                if (!$pdo) throw new XApiException(503, 'The database is unavailable.');
                xinng_ensure_x_account_tables($pdo);
                $service = new TwitterApiIoService(TWITTERAPI_IO_API_KEY);
                $session = $service->login($username, $email, $password, $totpSecret, $proxy);
                $account = $service->userInfo($username);
                (new XAccountStore($pdo, X_TOKEN_ENCRYPTION_KEY))->save(
                    (int)$userId,
                    $account,
                    $session['login_cookie'],
                    $proxy
                );
                header('Location: ../../dashboard.php?x_connected=1#x-account-connection', true, 302);
                exit;
            } catch (XApiException $error) {
                http_response_code($error->status);
                $errorMessage = $error->getMessage();
            } catch (Throwable $error) {
                error_log('TwitterAPI.io account connection failed: ' . $error->getMessage());
                http_response_code(503);
                $errorMessage = 'The X account could not be connected.';
            }
        }
    }
}

$configured = TWITTERAPI_IO_API_KEY !== '' && X_TOKEN_ENCRYPTION_KEY !== '';
if (!$configured && http_response_code() < 400) http_response_code(503);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Connect X account | Xinng</title>
    <style>
        body { margin: 0; padding: 32px 16px; color: #202622; background: #f2f5f1; font: 16px/1.5 system-ui, sans-serif; }
        main { box-sizing: border-box; max-width: 560px; margin: 0 auto; padding: 28px; background: #fff; border: 1px solid #dce3dd; border-radius: 8px; }
        h1 { margin: 0 0 8px; font-size: 1.6rem; }
        p { margin: 0 0 18px; }
        label { display: block; margin: 14px 0 5px; font-weight: 600; }
        input { box-sizing: border-box; width: 100%; padding: 10px 12px; border: 1px solid #aebbb0; border-radius: 4px; font: inherit; }
        button { margin-top: 20px; padding: 11px 16px; color: #fff; background: #176b4a; border: 0; border-radius: 4px; font: inherit; font-weight: 700; cursor: pointer; }
        .notice { margin: 16px 0; padding: 12px; background: #f4f7f4; border-left: 3px solid #176b4a; }
        .error { color: #9c2929; }
        a { color: #176b4a; }
    </style>
</head>
<body>
<main>
    <h1>Connect your X account</h1>
    <p>TwitterAPI.io uses your X account credentials to create a posting session. Requests are sent from this server using its configured API key.</p>
    <?php if ($errorMessage !== ''): ?><p class="error" role="alert"><?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
    <?php if (!$configured): ?>
        <p class="error">TwitterAPI.io is not configured on this server. Set <code>TWITTERAPI_IO_API_KEY</code> and <code>X_TOKEN_ENCRYPTION_KEY</code>.</p>
    <?php else: ?>
        <div class="notice">Your X password and 2FA seed are sent to TwitterAPI.io for login and are not stored by Xinng. A residential proxy is required by the provider. Only the returned login cookie and proxy are stored encrypted.</div>
        <form method="post" action="connect.php?user_id=<?= (int)$userId ?>" autocomplete="off">
            <input type="hidden" name="user_id" value="<?= (int)$userId ?>">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
            <label for="username">X username</label>
            <input id="username" name="username" type="text" required maxlength="100" autocomplete="off">
            <label for="email">X account email</label>
            <input id="email" name="email" type="email" required maxlength="254" autocomplete="off">
            <label for="password">X password</label>
            <input id="password" name="password" type="password" required maxlength="1024" autocomplete="new-password">
            <label for="totp_secret">2FA secret seed (recommended)</label>
            <input id="totp_secret" name="totp_secret" type="password" maxlength="256" autocomplete="off">
            <label for="proxy">Residential proxy URL</label>
            <input id="proxy" name="proxy" type="password" required maxlength="2048" placeholder="http://username:password@host:port" autocomplete="off">
            <button type="submit">Connect X account</button>
        </form>
    <?php endif; ?>
    <p><a href="../../dashboard.php#x-account-connection">Return to dashboard</a></p>
</main>
</body>
</html>
