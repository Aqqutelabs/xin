<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
$userId = filter_var($_SESSION['user_id'] ?? null, FILTER_VALIDATE_INT);
if (!$userId) { header('Location: signin.php', true, 303); exit; }
$pdo = get_db_connection();
if (!$pdo) { http_response_code(503); exit('Administration is temporarily unavailable.'); }
xinng_ensure_workspace_tables($pdo);
xinng_ensure_admin_tables($pdo);
xinng_ensure_auth_security_tables($pdo);
xinng_ensure_credit_tables($pdo);
$admin = xinng_admin_user($pdo, (int)$userId);
if (!$admin) { http_response_code(403); require __DIR__ . '/includes/admin-forbidden.php'; exit; }

if (isset($_GET['receipt'])) {
    $receiptId = filter_var($_GET['receipt'], FILTER_VALIDATE_INT);
    $receiptStatement = $pdo->prepare('SELECT receipt_path, original_filename FROM bank_transfer_receipts WHERE id = ? LIMIT 1');
    $receiptStatement->execute([$receiptId ?: 0]);
    $receipt = $receiptStatement->fetch();
    $relativePath = (string) ($receipt['receipt_path'] ?? '');
    if (!$receipt || strpos($relativePath, 'uploads/bank-receipts/') !== 0 || basename($relativePath) !== substr($relativePath, strlen('uploads/bank-receipts/'))) {
        http_response_code(404);
        exit('Receipt not found.');
    }
    $receiptPath = __DIR__ . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
    if (!is_file($receiptPath) || !is_readable($receiptPath)) {
        http_response_code(404);
        exit('Receipt file not found.');
    }
    $extension = strtolower(pathinfo($receiptPath, PATHINFO_EXTENSION));
    $mimeTypes = ['jpg' => 'image/jpeg', 'png' => 'image/png', 'pdf' => 'application/pdf'];
    if (!isset($mimeTypes[$extension])) {
        http_response_code(404);
        exit('Receipt file type is not supported.');
    }
    header('Content-Type: ' . $mimeTypes[$extension]);
    header('Content-Length: ' . (string) filesize($receiptPath));
    header('Content-Disposition: attachment; filename="payment-receipt.' . $extension . '"');
    header('X-Content-Type-Options: nosniff');
    header('Cache-Control: private, no-store');
    readfile($receiptPath);
    exit;
}

$errors = [];
$notice = (string)($_SESSION['admin_notice'] ?? '');
unset($_SESSION['admin_notice']);

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Your session expired. Refresh the page and try again.';
    } else {
        try {
            $action = (string)($_POST['action'] ?? '');
            if ($action === 'review_bank_transfer') {
                $receiptId = filter_var($_POST['receipt_id'] ?? null, FILTER_VALIDATE_INT);
                $decision = (string) ($_POST['decision'] ?? '');
                if (!$receiptId || !in_array($decision, ['approve', 'reject'], true)) {
                    throw new InvalidArgumentException('Choose a valid payment and review decision.');
                }
                $reviewNote = trim((string) ($_POST['review_note'] ?? ''));
                if (function_exists('mb_substr')) {
                    $reviewNote = mb_substr($reviewNote, 0, 255);
                } else {
                    $reviewNote = substr($reviewNote, 0, 255);
                }

                $pdo->beginTransaction();
                $reviewStatement = $pdo->prepare("SELECT r.id, r.user_id, r.credit_transaction_id, r.expected_amount, r.currency, r.status, t.amount AS credit_amount, t.status AS transaction_status FROM bank_transfer_receipts r JOIN credit_transactions t ON t.id = r.credit_transaction_id WHERE r.id = ? AND t.payment_gateway = 'bank_transfer' LIMIT 1 FOR UPDATE");
                $reviewStatement->execute([(int) $receiptId]);
                $payment = $reviewStatement->fetch();
                if (!$payment || $payment['status'] !== 'pending' || $payment['transaction_status'] !== 'pending') {
                    throw new RuntimeException('That bank transfer is no longer awaiting review.');
                }

                if ($decision === 'approve') {
                    $userStatement = $pdo->prepare('SELECT credit_balance, credits_purchased_total FROM users WHERE id = ? AND deleted_at IS NULL LIMIT 1 FOR UPDATE');
                    $userStatement->execute([(int) $payment['user_id']]);
                    $paymentUser = $userStatement->fetch();
                    if (!$paymentUser) {
                        throw new RuntimeException('The payment user no longer exists.');
                    }
                    $creditAmount = (int) $payment['credit_amount'];
                    $pdo->prepare('UPDATE users SET credit_balance = credit_balance + ?, credits_purchased_total = credits_purchased_total + ?, updated_at = NOW() WHERE id = ?')
                        ->execute([$creditAmount, $creditAmount, (int) $payment['user_id']]);
                    $pdo->prepare("UPDATE credit_transactions SET status = 'completed', reason = 'Bank transfer approved' WHERE id = ? AND status = 'pending'")
                        ->execute([(int) $payment['credit_transaction_id']]);
                    $pdo->prepare("UPDATE bank_transfer_receipts SET status = 'approved', review_note = ?, updated_at = NOW() WHERE id = ? AND status = 'pending'")
                        ->execute([$reviewNote !== '' ? $reviewNote : null, (int) $receiptId]);
                    xinng_record_admin_activity($pdo, (int) $userId, 'payment.bank_transfer_approved', 'bank_transfer_receipt', (int) $receiptId, [
                        'user_id' => (int) $payment['user_id'],
                        'credits' => $creditAmount,
                        'amount' => (int) $payment['expected_amount'],
                        'currency' => (string) $payment['currency'],
                    ]);
                    $decisionNotice = 'Bank transfer approved and credits released.';
                } else {
                    $pdo->prepare("UPDATE credit_transactions SET status = 'failed', reason = 'Bank transfer rejected' WHERE id = ? AND status = 'pending'")
                        ->execute([(int) $payment['credit_transaction_id']]);
                    $pdo->prepare("UPDATE bank_transfer_receipts SET status = 'rejected', review_note = ?, updated_at = NOW() WHERE id = ? AND status = 'pending'")
                        ->execute([$reviewNote !== '' ? $reviewNote : null, (int) $receiptId]);
                    xinng_record_admin_activity($pdo, (int) $userId, 'payment.bank_transfer_rejected', 'bank_transfer_receipt', (int) $receiptId, [
                        'user_id' => (int) $payment['user_id'],
                        'reason' => $reviewNote,
                    ]);
                    $decisionNotice = 'Bank transfer rejected.';
                }
                $pdo->commit();
                $_SESSION['admin_notice'] = $decisionNotice;
                header('Location: admin.php#payments', true, 303);
                exit;
            } elseif ($action === 'update_access') {
                $targetId = filter_var($_POST['user_id'] ?? null, FILTER_VALIDATE_INT);
                if (!$targetId) throw new InvalidArgumentException('Choose a valid user.');
                if ((int)$targetId === (int)$userId) throw new RuntimeException('Use another administrator account to change your own access.');
                $stmt = $pdo->prepare('SELECT id, role, status FROM users WHERE id = ? AND deleted_at IS NULL LIMIT 1');
                $stmt->execute([(int)$targetId]);
                $target = $stmt->fetch();
                if (!$target) throw new RuntimeException('That user no longer exists.');
                $role = (string)($_POST['role'] ?? 'user');
                $status = (string)($_POST['status'] ?? 'active');
                if (!in_array($role, ['user', 'admin'], true) || !in_array($status, ['active', 'suspended'], true)) throw new InvalidArgumentException('Choose valid access settings.');
                if ($target['role'] === 'admin' && ($role !== 'admin' || $status !== 'active')) {
                    $activeAdmins = (int)$pdo->query('SELECT COUNT(*) FROM users WHERE role = "admin" AND status = "active" AND deleted_at IS NULL')->fetchColumn();
                    if ($activeAdmins <= 1) throw new RuntimeException('Xinng must retain at least one active administrator.');
                }
                $pdo->prepare('UPDATE users SET role = ?, status = ?, updated_at = NOW() WHERE id = ?')->execute([$role, $status, (int)$targetId]);
                xinng_record_admin_activity($pdo, (int)$userId, 'user.access_updated', 'user', (int)$targetId, ['role' => $role, 'status' => $status]);
                $_SESSION['admin_notice'] = 'User access updated.';
                header('Location: admin.php', true, 303); exit;
            } else {
                throw new InvalidArgumentException('Unknown administration action.');
            }
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $errors[] = $e instanceof InvalidArgumentException || $e instanceof RuntimeException ? $e->getMessage() : 'The administration action failed.';
            if (!($e instanceof InvalidArgumentException) && !($e instanceof RuntimeException)) error_log('Admin action failed: ' . $e->getMessage());
        }
    }
}

$q = trim((string)($_GET['q'] ?? ''));
$where = 'WHERE deleted_at IS NULL'; $params = [];
if ($q !== '') { $where .= ' AND (name LIKE ? OR email LIKE ?)'; $params = ['%'.$q.'%', '%'.$q.'%']; }
$stmt = $pdo->prepare("SELECT id,name,email,role,status,credit_balance,created_at,last_login_at FROM users {$where} ORDER BY created_at DESC LIMIT 100");
$stmt->execute($params); $users = $stmt->fetchAll();
$metrics = [
 'users'=>(int)$pdo->query('SELECT COUNT(*) FROM users WHERE deleted_at IS NULL')->fetchColumn(),
 'admins'=>(int)$pdo->query('SELECT COUNT(*) FROM users WHERE role="admin" AND status="active" AND deleted_at IS NULL')->fetchColumn(),
 'organizations'=>(int)$pdo->query('SELECT COUNT(*) FROM organizations WHERE status="active" AND deleted_at IS NULL')->fetchColumn(),
 'pages'=>(int)$pdo->query('SELECT COUNT(*) FROM pages WHERE deleted_at IS NULL')->fetchColumn(),
];
$organizations=$pdo->query('SELECT o.id,o.name,o.slug,o.created_at,u.email AS owner_email,COUNT(om.id) AS member_count FROM organizations o JOIN users u ON u.id=o.created_by LEFT JOIN organization_members om ON om.organization_id=o.id AND om.status="active" WHERE o.deleted_at IS NULL GROUP BY o.id,o.name,o.slug,o.created_at,u.email ORDER BY o.created_at DESC LIMIT 30')->fetchAll();
$adminActivity=$pdo->query('SELECT aa.event_type,aa.created_at,u.name AS actor_name FROM admin_activity aa JOIN users u ON u.id=aa.actor_user_id ORDER BY aa.created_at DESC LIMIT 20')->fetchAll();
$authActivity=$pdo->query('SELECT event_type,outcome,created_at FROM auth_events ORDER BY created_at DESC LIMIT 20')->fetchAll();
$bankTransfers=$pdo->query("SELECT r.id,r.user_id,r.credit_transaction_id,r.package_id,r.expected_amount,r.currency,r.receipt_path,r.original_filename,r.status,r.review_note,r.created_at,t.amount AS credit_amount,t.reference,u.name AS user_name,u.email AS user_email FROM bank_transfer_receipts r JOIN credit_transactions t ON t.id=r.credit_transaction_id JOIN users u ON u.id=r.user_id ORDER BY r.created_at DESC LIMIT 100")->fetchAll();
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Administration — <?= e($APP_NAME) ?></title><link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=DM+Mono:wght@400;500&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet"><link rel="stylesheet" href="assets/css/brand.css"><link rel="stylesheet" href="assets/css/admin.css?v=<?= filemtime(__DIR__.'/assets/css/admin.css') ?>"><?php require __DIR__ . '/includes/google-analytics.php'; ?></head><body>
<aside class="admin-sidebar"><a class="xinng-brand" href="dashboard.php"><img class="xinng-brand__image" src="<?= e(xinng_brand_logo_url(xinng_public_base_url())) ?>" alt="Xinng" width="1736" height="906"></a><span class="admin-label">ADMINISTRATION</span><nav><a class="active" href="#overview">Overview</a><a href="#users">Users</a><a href="#payments">Payments</a><a href="#organizations">Organizations</a><a href="#activity">Activity</a></nav><div class="admin-account"><strong><?= e($admin['name']) ?></strong><small><?= e($admin['email']) ?></small><a href="dashboard.php">Return to workspace</a></div></aside>
<main class="admin-main"><header><div><span class="eyebrow">PLATFORM CONTROL</span><h1>Administration</h1><p>Manage access and monitor the Xinng platform.</p></div><span class="environment"><?= e(strtoupper($APP_ENV)) ?></span></header>
<?php foreach($errors as $error):?><div class="message error" role="alert"><?=e($error)?></div><?php endforeach;?><?php if($notice):?><div class="message notice" role="status"><?=e($notice)?></div><?php endif;?>
<section id="overview" class="metric-grid"><?php foreach([['Users',$metrics['users']],['Active admins',$metrics['admins']],['Organizations',$metrics['organizations']],['Pages',$metrics['pages']]] as [$label,$value]):?><article><span><?=e($label)?></span><strong><?=number_format($value)?></strong></article><?php endforeach;?></section>
<section id="users" class="admin-panel"><div class="panel-heading"><div><span class="eyebrow">ACCESS CONTROL</span><h2>Users</h2></div><form method="get" class="search"><label for="admin-search">Search users</label><div><input id="admin-search" name="q" type="search" value="<?=e($q)?>" placeholder="Name or email"><button>Search</button></div></form></div><div class="table-wrap"><table><thead><tr><th>User</th><th>Credits</th><th>Created</th><th>Last login</th><th>Access</th></tr></thead><tbody><?php foreach($users as $user):?><tr><td><strong><?=e($user['name'])?></strong><small><?=e($user['email'])?></small></td><td><?=number_format((int)$user['credit_balance'])?></td><td><?=e(date('M j, Y',strtotime($user['created_at'])))?></td><td><?=$user['last_login_at']?e(date('M j, Y',strtotime($user['last_login_at']))):'Never'?></td><td><?php if((int)$user['id']===(int)$userId):?><span class="self-access"><?=e(ucfirst($user['role']))?> · <?=e(ucfirst($user['status']))?></span><?php else:?><form method="post" class="access-form"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="update_access"><input type="hidden" name="user_id" value="<?=(int)$user['id']?>"><select name="role" aria-label="Role for <?=e($user['name'])?>"><option value="user"<?=$user['role']==='user'?' selected':''?>>User</option><option value="admin"<?=$user['role']==='admin'?' selected':''?>>Admin</option></select><select name="status" aria-label="Status for <?=e($user['name'])?>"><option value="active"<?=$user['status']==='active'?' selected':''?>>Active</option><option value="suspended"<?=$user['status']==='suspended'?' selected':''?>>Suspended</option></select><button>Save</button></form><?php endif;?></td></tr><?php endforeach;?></tbody></table></div></section>
<section id="payments" class="admin-panel"><div class="panel-heading"><div><span class="eyebrow">PAYMENT VERIFICATION</span><h2>Bank transfers</h2><p>Approve verified transfers to release the requested wallet credits.</p></div></div><?php if(!$bankTransfers):?><p class="payment-empty">No bank transfers have been submitted.</p><?php else:?><div class="table-wrap"><table class="payment-table"><thead><tr><th>User</th><th>Transfer</th><th>Requested action</th><th>Receipt</th><th>Status / review</th></tr></thead><tbody><?php foreach($bankTransfers as $transfer):?><tr><td><strong><?=e($transfer['user_name'])?></strong><small><?=e($transfer['user_email'])?></small></td><td><strong><?=e($transfer['currency'])?> <?=number_format((int)$transfer['expected_amount'])?></strong><small>Ref: <?=e($transfer['reference']??'—')?> · <?=e(date('M j, Y g:i a',strtotime($transfer['created_at'])))?></small></td><td><strong><?=number_format((int)$transfer['credit_amount'])?> credits</strong><small><?=e(ucfirst((string)$transfer['package_id']))?> package</small></td><td><a class="receipt-link" href="admin.php?receipt=<?=(int)$transfer['id']?>"><?=e($transfer['original_filename'])?></a></td><td><span class="payment-status <?=e((string)$transfer['status'])?>"><?=e(ucfirst((string)$transfer['status']))?></span><?php if($transfer['review_note']):?><small><?=e($transfer['review_note'])?></small><?php endif;?><?php if($transfer['status']==='pending'):?><form method="post" class="payment-review-form"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="review_bank_transfer"><input type="hidden" name="receipt_id" value="<?=(int)$transfer['id']?>"><label class="sr-only" for="review-note-<?=(int)$transfer['id']?>">Review note for <?=e($transfer['user_name'])?></label><input id="review-note-<?=(int)$transfer['id']?>" name="review_note" maxlength="255" placeholder="Optional review note"><button name="decision" value="approve">Approve &amp; add credits</button><button class="reject" name="decision" value="reject">Reject</button></form><?php endif;?></td></tr><?php endforeach;?></tbody></table></div><?php endif;?></section>
<section id="organizations" class="admin-panel"><div class="panel-heading"><div><span class="eyebrow">WORKSPACES</span><h2>Organizations</h2></div></div><div class="organization-grid"><?php foreach($organizations as $organization):?><article><span><?=e($organization['slug'])?></span><h3><?=e($organization['name'])?></h3><p><?=e($organization['owner_email'])?></p><strong><?=(int)$organization['member_count']?> member<?=(int)$organization['member_count']===1?'':'s'?></strong></article><?php endforeach;?></div></section>
<section id="activity" class="activity-grid"><article class="admin-panel"><span class="eyebrow">PRIVILEGED ACTIONS</span><h2>Admin activity</h2><div class="activity-list"><?php if(!$adminActivity):?><p>No administrative changes yet.</p><?php else:foreach($adminActivity as $event):?><div><strong><?=e(str_replace('.',' ',$event['event_type']))?></strong><span><?=e($event['actor_name'])?> · <?=e(date('M j, g:i a',strtotime($event['created_at'])))?></span></div><?php endforeach;endif;?></div></article><article class="admin-panel"><span class="eyebrow">SECURITY</span><h2>Authentication activity</h2><div class="activity-list"><?php foreach($authActivity as $event):?><div><strong><?=e(str_replace('_',' ',$event['event_type']))?></strong><span><?=e(ucfirst($event['outcome']))?> · <?=e(date('M j, g:i a',strtotime($event['created_at'])))?></span></div><?php endforeach;?></div></article></section>
</main></body></html>
