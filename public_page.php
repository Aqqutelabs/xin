<?php
if (!function_exists('e')) {
	function e($value): string {
		return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
	}
}

if (!function_exists('public_initials')) {
	function public_initials($value): string {
		$clean = preg_replace('/[^A-Za-z0-9\s]/', '', (string)$value);
		$parts = preg_split('/\s+/', trim($clean), -1, PREG_SPLIT_NO_EMPTY);
		if (!$parts) {
			return 'P';
		}
		if (count($parts) === 1) {
			return strtoupper(substr($parts[0], 0, 2));
		}
		return strtoupper(substr($parts[0], 0, 1) . substr($parts[count($parts) - 1], 0, 1));
	}
}

if (!function_exists('public_social_icon')) {
	function public_social_icon($platform): string {
		$platform = strtolower((string)($platform ?: 'link'));
		$icons = [
			'twitter' => 'fa-brands fa-x-twitter',
			'x' => 'fa-brands fa-x-twitter',
			'instagram' => 'fa-brands fa-instagram',
			'linkedin' => 'fa-brands fa-linkedin-in',
			'youtube' => 'fa-brands fa-youtube',
			'tiktok' => 'fa-brands fa-tiktok',
			'github' => 'fa-brands fa-github',
			'whatsapp' => 'fa-brands fa-whatsapp',
			'email' => 'fa-regular fa-envelope',
			'dribbble' => 'fa-brands fa-dribbble',
			'behance' => 'fa-brands fa-behance',
			'website' => 'fa-solid fa-globe',
			'link' => 'fa-solid fa-link',
		];
		return '<i class="' . ($icons[$platform] ?? $icons['link']) . '"></i>';
	}
}

if (!function_exists('public_block_icon')) {
  function public_block_icon($type): string {
    $icons = [
      'link' => 'fa-solid fa-link',
      'social' => 'fa-solid fa-share-nodes',
      'image' => 'fa-regular fa-image',
      'video' => 'fa-solid fa-play',
      'youtube' => 'fa-brands fa-youtube',
      'music' => 'fa-solid fa-music',
      'shop' => 'fa-solid fa-bag-shopping',
      'subscribe' => 'fa-regular fa-envelope',
      'contact' => 'fa-regular fa-envelope',
      'booking' => 'fa-regular fa-calendar',
      'qr' => 'fa-solid fa-qrcode',
      'short_link' => 'fa-solid fa-link',
      'text' => 'fa-regular fa-file-lines',
    ];
    return '<i class="' . ($icons[$type] ?? 'fa-solid fa-arrow-up-right-from-square') . '" aria-hidden="true"></i>';
  }
}

$page = is_array($page ?? null) ? $page : [];
$blocks = is_array($blocks ?? null) ? $blocks : [];
if (empty($pdo) && file_exists(__DIR__ . '/config.php')) {
	require_once __DIR__ . '/config.php';
	$pdo = get_db_connection();
}
if (empty($page['id']) && !empty($pdo) && $pdo instanceof PDO) {
	$slug = trim((string)($_GET['slug'] ?? ($_GET['back_half'] ?? '')));
	if ($slug !== '') {
		$stmt = $pdo->prepare('SELECT * FROM pages WHERE slug = ? AND is_published = 1 LIMIT 1');
		$stmt->execute([$slug]);
		$page = $stmt->fetch() ?: [];
	}
}

if (empty($page['id'])) {
	http_response_code(404);
	echo 'Not found';
	exit;
}
if (!empty($pdo) && $pdo instanceof PDO) {
	try {
		xinng_record_page_view($pdo, (int)$page['id']);
	} catch (Throwable $e) {
		error_log($e->getMessage());
	}
}
if (empty($blocks) && !empty($pdo) && $pdo instanceof PDO) {
  $stmt = $pdo->prepare('SELECT id, title, description, type, destination_url, image_path, is_active FROM page_blocks WHERE page_id = ? AND deleted_at IS NULL ORDER BY position ASC, id ASC');
	$stmt->execute([(int)$page['id']]);
	$blocks = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
}
$blocks = array_values(array_filter($blocks, static fn($block) => ($block['is_active'] ?? true) && trim((string)($block['title'] ?? '')) !== ''));

$socials = [];
if (!empty($pdo) && $pdo instanceof PDO) {
  $stmt = $pdo->prepare('SELECT platform, url, is_active FROM page_socials WHERE page_id = ? AND deleted_at IS NULL ORDER BY position ASC, id ASC');
  $stmt->execute([(int)$page['id']]);
  $socials = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
}
$socials = array_values(array_filter($socials, static fn($social) => ($social['is_active'] ?? true) && trim((string)($social['url'] ?? '')) !== ''));

$title = $page['title'] ?? $page['slug'] ?? '';
$description = $page['description'] ?? ($page['bio'] ?? '');
$pageType = ($page['page_type'] ?? 'creator') === 'corporate' ? 'corporate' : 'creator';
$corporateMode = $pageType === 'corporate';
$profile = $page['profile_image_path'] ?? ($page['profile_image_url'] ?? '');

$corporate = xinng_load_corporate_page_data($pdo, (int)$page['id'], $page);
$corpContact = is_array($corporate['contact'] ?? null) ? $corporate['contact'] : [];
$corpEvent = is_array($corporate['event'] ?? null) ? $corporate['event'] : [];
$corpTeam = is_array($corporate['team'] ?? null) ? $corporate['team'] : [];
$corporateTitle = trim((string)($corporate['company_name'] ?? '')) ?: $title;
$corporateDescription = trim((string)($corporate['description'] ?? '')) ?: $description;
$corpLogo = trim((string)($corporate['logo'] ?? '')) ?: $profile;
$corpHeaderPhoto = trim((string)($corporate['header_photo'] ?? ''));
$corpSpecialties = array_values(array_filter(array_map('strval', (array)($corporate['specialties'] ?? []))));
$corpLocations = array_values(array_filter(array_map('strval', (array)($corporate['locations'] ?? []))));
$corpLinks = array_values(array_filter((array)($corporate['links'] ?? []), static fn($item) => is_array($item) && (!empty($item['label']) || !empty($item['url']))));
$companyWebsite = trim((string)($corporate['company_website'] ?? ''));
if ($companyWebsite !== '') {
	$corpLinks = array_values(array_filter($corpLinks, static fn($item) => !is_array($item) || !in_array(strtolower(trim((string)($item['label'] ?? ''))), ['website', 'company website', 'company website url'], true)));
	array_unshift($corpLinks, ['label' => 'Website', 'url' => $companyWebsite]);
}
$corpSocials = array_values(array_filter((array)($corporate['socials'] ?? []), static fn($item) => is_array($item) && !empty($item['platform']) && !empty($item['url'])));
$corpCards = array_values(array_filter((array)($corporate['cards'] ?? []), static fn($item) => is_array($item) && !empty($item['title'])));
$corpButtons = array_values(array_filter((array)($corporate['buttons'] ?? []), static fn($item) => is_array($item) && !empty($item['label']) && !empty($item['url'])));
$corpMembers = array_values(array_filter((array)($corpTeam['members'] ?? []), static fn($item) => is_array($item) && (!empty($item['name']) || !empty($item['title']))));

$corpCardsTitle = trim((string)($corporate['cards_title'] ?? ''));
$corpCardsLede = trim((string)($corporate['cards_lede'] ?? ''));
$corpActionsTitle = trim((string)($corporate['actions_title'] ?? ''));
$corpActionsLede = trim((string)($corporate['actions_lede'] ?? ''));
$corpEventRegisterTitle = trim((string)($corporate['event_register_title'] ?? ''));
$heroPrimaryCtaLabel = trim((string)($corporate['hero_primary_cta_label'] ?? ''));
$heroPrimaryCtaUrl = trim((string)($corporate['hero_primary_cta_url'] ?? ''));
$quoteTitle = trim((string)($corporate['quote_title'] ?? ''));
$quoteDescription = trim((string)($corporate['quote_description'] ?? ''));
$quoteButtonLabel = trim((string)($corporate['quote_button_label'] ?? ''));
$eventButtonLabel = trim((string)($corpEvent['button_label'] ?? ''));

$corpHeroButtons = [];
if (!empty($heroPrimaryCtaUrl)) {
	$corpHeroButtons[] = [
		'label' => $heroPrimaryCtaLabel ?: 'Learn more',
		'url' => $heroPrimaryCtaUrl,
		'button_color' => '#1979BF',
		'text_color' => '#FFFFFF',
	];
}
foreach ($corpButtons as $button) {
	$corpHeroButtons[] = $button;
}

if ($corporateMode):
$corpUrl = static function(array $blocks, array $needles): string {
	foreach ($blocks as $block) {
		$text = strtolower(trim(($block['title'] ?? '') . ' ' . ($block['description'] ?? '') . ' ' . ($block['type'] ?? '')));
		foreach ($needles as $needle) {
			if (strpos($text, strtolower($needle)) !== false && !empty($block['destination_url'])) return $block['destination_url'];
		}
	}
	return '';
};
$meetingUrl = $corpUrl($blocks, ['meeting', 'book']);
$profileUrl = $corpUrl($blocks, ['profile', 'document']);
$catalogUrl = $corpUrl($blocks, ['catalog', 'solution', 'product']);
$pageHostRaw = parse_url((string)($page['domain'] ?? $page['slug']), PHP_URL_HOST) ?: '';
$pageHostClean = preg_replace('/[^A-Za-z0-9.\-]/', '', (string)$pageHostRaw) ?: 'example.com';
$contactUrl = $corpUrl($blocks, ['contact', 'business']);
$meetingUrl = trim((string)($corpContact['meeting_link'] ?? '')) ?: $meetingUrl;
$profileUrl = trim((string)($corpContact['brochure_link'] ?? '')) ?: $profileUrl;
$phoneUrl = !empty($corpContact['phone']) ? 'tel:' . preg_replace('/\s+/', '', (string)$corpContact['phone']) : '';
$emailUrl = !empty($corpContact['email']) ? 'mailto:' . trim((string)$corpContact['email']) : '';
$whatsappDigits = preg_replace('/\D+/', '', (string)($corpContact['whatsapp'] ?? ''));
$whatsappUrl = $whatsappDigits !== '' ? 'https://wa.me/' . $whatsappDigits : '';
$eventStart = !empty($corpEvent['start_at']) ? strtotime((string)$corpEvent['start_at']) : false;
$seconds = $eventStart ? max(0, $eventStart - time()) : 0;
$countdownDays = (int)floor($seconds / 86400);
$countdownHours = (int)floor(($seconds % 86400) / 3600);
$countdownMinutes = (int)floor(($seconds % 3600) / 60);
$eventLine = trim((string)($corpEvent['description'] ?? ''));
if ($eventLine === '') {
	$eventLine = trim(($corpEvent['city'] ?? '') . (($corpEvent['city'] ?? '') && ($corpEvent['location'] ?? '') ? ', ' : '') . ($corpEvent['location'] ?? ''));
}
$actionCards = [];
if (!empty($heroPrimaryCtaUrl)) {
	$actionCards[] = ['icon' => 'fa-solid fa-arrow-up-right-from-square', 'title' => $heroPrimaryCtaLabel ?: ($corpEvent['title'] ?: 'Learn more'), 'description' => $corpEvent['description'] ?: '', 'cta' => $heroPrimaryCtaLabel ?: 'View', 'url' => $heroPrimaryCtaUrl];
}
foreach ($corpButtons as $button) {
	$actionCards[] = ['icon' => 'fa-solid fa-arrow-up-right-from-square', 'title' => $button['label'] ?? '', 'description' => '', 'cta' => 'Open', 'url' => $button['url'] ?? ''];
}
foreach ($blocks as $block) {
	if (!empty($block['title']) && !empty($block['destination_url'])) {
		$actionCards[] = ['icon' => 'fa-solid fa-arrow-right', 'title' => $block['title'], 'description' => $block['description'] ?? '', 'cta' => 'Open', 'url' => $block['destination_url']];
	}
}
$existingActionUrls = array_column($actionCards, 'url');
if (!empty($meetingUrl) && !in_array($meetingUrl, $existingActionUrls, true)) {
	$actionCards[] = ['icon' => 'fa-solid fa-calendar-days', 'title' => $corpEvent['title'] ?: 'Book a meeting', 'description' => $corpEvent['description'] ?: '', 'cta' => $eventButtonLabel ?: 'Book meeting', 'url' => $meetingUrl];
}
$actionCards = array_values(array_filter($actionCards, static fn($action) => !empty($action['title']) && !empty($action['url'])));
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($corporateTitle) ?></title>
  <meta name="description" content="<?= e($corporateDescription) ?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <style>
    * { box-sizing: border-box; }
    html { font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; color: #eef2ff; background: #061424; }
    body { margin: 0; min-height: 100vh; background: radial-gradient(circle at top, rgba(59, 130, 246, .12), transparent 32%), #061424; color: #eef2ff; line-height: 1.65; }
    a { text-decoration: none; color: inherit; }
    img { max-width: 100%; display: block; }

    .corp-page { min-height: 100vh; }
    .corp-wrap { width: min(1120px, calc(100% - 48px)); margin: 0 auto; }

    .corp-banner { min-height: 140px; background-size: cover; background-position: center; border-radius: 28px; overflow: hidden; position: relative; margin-bottom: -60px; box-shadow: 0 38px 110px rgba(0, 0, 0, .32); }
    .corp-banner::after { content: ''; position: absolute; inset: 0; background: linear-gradient(180deg, rgba(6, 20, 36, .28), rgba(6, 20, 36, .88)); }
    .corp-hero { position: relative; z-index: 2; background: rgba(5, 18, 38, .96); color: #fff; padding: 48px 0 64px; border-radius: 28px; overflow: hidden; }
    .corp-hero-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 24px; flex-wrap: wrap; }
    .corp-logo-row { display: flex; align-items: center; gap: 18px; }
    .corp-logo-card { width: 72px; height: 72px; flex: none; display: grid; place-items: center; border-radius: 18px; background: #fff; overflow: hidden; box-shadow: 0 16px 40px rgba(0, 0, 0, .24); }
    .corp-logo-card img { width: 100%; height: 100%; object-fit: contain; padding: 10px; }
    .corp-logo-mark { font-size: 22px; font-weight: 900; color: #0f172a; }
    .corp-hero-actions { display: flex; gap: 12px; flex-wrap: wrap; }
    .corp-icon-btn { width: 46px; height: 46px; display: grid; place-items: center; border-radius: 14px; background: rgba(255, 255, 255, .08); color: #fff; font-size: 16px; transition: transform .2s ease, background .2s ease; }
    .corp-icon-btn:hover { transform: translateY(-1px); background: rgba(255, 255, 255, .16); }
    .corp-title-block { margin-top: 28px; }
    .corp-title-row { display: flex; align-items: center; gap: 14px; flex-wrap: wrap; }
    .corp-title-row h1 { margin: 0; font-size: 3rem; line-height: 1.05; font-weight: 900; letter-spacing: -.03em; color: #fff; }
    .corp-verified { width: 24px; height: 24px; display: grid; place-items: center; border: 2px solid #34d399; border-radius: 50%; color: #34d399; font-size: 12px; }
    .corp-eyebrow { margin: 10px 0 0; color: #94a3b8; font-size: 14px; letter-spacing: .08em; text-transform: uppercase; }
    .corp-summary { max-width: 760px; margin: 18px 0 0; color: #dbeafe; font-size: 1rem; line-height: 1.85; }
    .corp-tag-row { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; margin-top: 18px; }
    .corp-tag-icon { width: 22px; color: #94a3b8; font-size: 12px; }
    .corp-pill { display: inline-flex; align-items: center; min-height: 32px; padding: 0 14px; border-radius: 999px; background: rgba(59, 130, 246, .16); color: #bfdbfe; font-size: .95rem; font-weight: 700; }
    .corp-cta-row { display: flex; flex-wrap: wrap; gap: 14px; margin-top: 24px; }
    .corp-social-row { display: flex; flex-wrap: wrap; gap: 12px; }
    .corp-primary { display: inline-flex; align-items: center; gap: .6em; min-height: 46px; padding: 0 22px; border: 0; border-radius: 999px; background: #3b82f6; color: #fff; opacity: 1; visibility: visible; font-size: .98rem; font-weight: 700; box-shadow: 0 18px 40px rgba(59, 130, 246, .24); transition: transform .2s ease, box-shadow .2s ease; }
    .corp-primary:hover { transform: translateY(-1px); box-shadow: 0 22px 45px rgba(59, 130, 246, .32); }

    .corp-event { color: #fff; }
    .corp-event-inner { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 20px; padding: 26px 0; }
    .corp-event-main { display: flex; align-items: center; gap: 18px; min-width: 0; }
    .corp-event-icon { width: 46px; height: 46px; display: grid; place-items: center; border-radius: 14px; background: rgba(255, 255, 255, .12); font-size: 16px; }
    .corp-event-text h2 { margin: 0 0 6px; font-size: 1.05rem; font-weight: 700; }
    .corp-event-text p { margin: 0; color: #cbd5e1; font-size: .95rem; line-height: 1.7; }
    .corp-event-actions { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 10px; }
    .corp-event-btn { height: 42px; display: inline-flex; align-items: center; gap: .6em; padding: 0 18px; border-radius: 999px; background: #fff; color: #0f172a; font-size: .95rem; font-weight: 700; }
    .corp-countdown { display: flex; gap: 10px; }
    .corp-countdown div { text-align: center; }
    .corp-countdown span { display: grid; place-items: center; width: 52px; height: 52px; border-radius: 14px; background: rgba(255, 255, 255, .14); font-size: 1rem; font-weight: 800; }
    .corp-countdown small { display: block; margin-top: 6px; color: #94a3b8; font-size: 11px; letter-spacing: .08em; }

    .corp-section { padding: 64px 0; }
    .corp-section.light { background: #071423; }
    .corp-section.white { background: #f8fafc; color: #0f172a; }
    .corp-section.white h2, .corp-section.white p, .corp-section.white .corp-pill { color: #0f172a; }
    .corp-section h2 { margin: 0 0 14px; font-size: 2rem; font-weight: 800; letter-spacing: -.03em; }
    .corp-section-lede { max-width: 720px; margin: 0 0 36px; color: rgba(226, 232, 240, .92); font-size: 1rem; line-height: 1.85; }

    .corp-card-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 24px; }
    .corp-card { position: relative; min-height: 260px; display: flex; align-items: flex-end; overflow: hidden; border-radius: 24px; padding: 28px; color: #fff; background-size: cover; background-position: center; box-shadow: 0 24px 64px rgba(0, 0, 0, .22); transition: transform .2s ease, box-shadow .2s ease; }
    .corp-card:hover { transform: translateY(-6px); box-shadow: 0 28px 70px rgba(0, 0, 0, .28); }
    .corp-card:before { content: ''; position: absolute; inset: 0; background: linear-gradient(180deg, rgba(4, 17, 34, .64), rgba(4, 17, 34, .18)); }
    .corp-card.corp-card--light { color: var(--corp-ink); }
    .corp-card.corp-card--light:before { background: rgba(255, 255, 255, 0); }
    .corp-card-content { position: relative; z-index: 1; }
    .corp-card-content strong { display: block; margin-bottom: 12px; font-size: 1.1rem; font-weight: 800; line-height: 1.25; }
    .corp-card-content p { margin: 0 0 18px; color: rgba(255, 255, 255, .92); font-size: .98rem; line-height: 1.8; }
    .corp-card-btn { display: inline-flex; align-items: center; gap: .5em; height: 38px; padding: 0 14px; border-radius: 999px; background: rgba(255, 255, 255, .16); color: #fff; font-size: .95rem; font-weight: 700; }
    .corp-card--light .corp-card-btn { background: #1979BF; color: #fff; }

    .corp-actions-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 18px; }
    .corp-action-card { min-height: 180px; padding: 26px; border-radius: 24px; background: #0f243b; border: 1px solid rgba(255, 255, 255, .08); box-shadow: 0 18px 48px rgba(0, 0, 0, .18); transition: transform .2s ease, box-shadow .2s ease; }
    .corp-action-card:hover { transform: translateY(-4px); box-shadow: 0 22px 58px rgba(0, 0, 0, .2); }
    .corp-action-icon { width: 48px; height: 48px; display: grid; place-items: center; border-radius: 16px; background: rgba(59, 130, 246, .12); color: #60a5fa; font-size: 16px; }
    .corp-action-card strong { display: block; margin: 16px 0 12px; font-size: 1.05rem; font-weight: 800; line-height: 1.3; }
    .corp-action-card p { margin: 0 0 18px; color: rgba(226, 232, 240, .88); font-size: .98rem; line-height: 1.8; }
    .corp-action-card small { color: #7dd3fc; font-weight: 700; font-size: .93rem; }

    .corp-quote-form { margin-top: 36px; padding: 30px; border-radius: 18px; background: rgba(255, 255, 255, .08); border: 1px solid rgba(255, 255, 255, .12); }
    .corp-quote-form h3 { margin: 0 0 10px; font-size: 1.15rem; color: #fff; }
    .corp-quote-form p { margin: 0 0 18px; color: #dbeafe; font-size: .98rem; line-height: 1.75; }
    .corp-form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
    .corp-quote-form input, .corp-quote-form textarea { width: 100%; min-height: 48px; border-radius: 14px; border: 1px solid rgba(226, 232, 240, .18); padding: 14px 16px; font: inherit; background: rgba(255, 255, 255, .08); color: #f8fafc; }
    .corp-quote-form textarea { min-height: 120px; resize: vertical; }
    .corp-quote-form button { margin-top: 8px; padding: 0 24px; height: 50px; border-radius: 999px; border: 0; background: #3b82f6; color: #fff; font-size: 1rem; font-weight: 800; cursor: pointer; transition: transform .2s ease, background .2s ease; }
    .corp-quote-form button:hover { transform: translateY(-1px); background: #2563eb; }

    .corp-team-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 18px; }
    .corp-contact-card { display: flex; gap: 18px; align-items: center; padding: 22px; border-radius: 18px; background: rgba(255, 255, 255, .08); border: 1px solid rgba(255, 255, 255, .12); }
    .corp-contact-avatar { width: 58px; height: 58px; flex: none; display: grid; place-items: center; border-radius: 16px; background: rgba(255, 255, 255, .12); color: #fff; font-weight: 800; font-size: 18px; overflow: hidden; }
    .corp-contact-avatar img { width: 100%; height: 100%; object-fit: cover; }
    .corp-contact-card strong { display: block; font-size: 1rem; margin-bottom: 6px; color: #fff; }
    .corp-contact-card small { color: #cbd5e1; font-size: .96rem; }
    .corp-contact-actions { display: flex; gap: 10px; margin-top: 8px; }
    .corp-contact-actions a { width: 34px; height: 34px; display: grid; place-items: center; border-radius: 12px; background: rgba(255, 255, 255, .08); color: #fff; font-size: 12px; }

    .corp-brand { padding: 28px 0 12px; color: #a8b8d7; font-size: 12px; text-align: center; letter-spacing: .12em; text-transform: uppercase; }

    @media (max-width: 980px) {
      .corp-card-grid, .corp-actions-grid, .corp-team-grid { grid-template-columns: 1fr 1fr; }
      .corp-form-grid { grid-template-columns: 1fr; }
    }
    @media (max-width: 700px) {
      .corp-wrap { width: min(100% - 32px, 1000px); }
      .corp-title-row h1 { font-size: 2.4rem; }
      .corp-hero { padding: 36px 0 52px; }
      .corp-banner { min-height: 100px; margin-bottom: -40px; }
      .corp-event-inner { flex-direction: column; align-items: flex-start; }
    }
    @media (max-width: 560px) {
      .corp-wrap { width: min(100% - 24px, 100%); }
      .corp-title-row h1 { font-size: 2rem; }
      .corp-primary, .corp-event-btn { width: 100%; justify-content: center; }
      .corp-action-card, .corp-card, .corp-contact-card { min-height: auto; }
      .corp-form-grid, .corp-card-grid, .corp-actions-grid, .corp-team-grid { grid-template-columns: 1fr; }
      .corp-section { padding: 44px 0; }
    }
  </style>
  <link rel="stylesheet" href="<?= e(xinng_public_base_url()) ?>/assets/css/brand.css">
</head>
<body>
  <main class="corp-page">
    <?php if ($corpHeaderPhoto): ?><div class="corp-banner" style="background-image:url('<?= e($corpHeaderPhoto) ?>')"></div><?php endif; ?>
    <section class="corp-hero">
      <div class="corp-wrap">
        <div class="corp-hero-top">
          <div class="corp-logo-row">
            <div class="corp-logo-card">
              <?php if ($corpLogo): ?><img src="<?= e($corpLogo) ?>" alt="<?= e($corporateTitle) ?>"><?php else: ?><span class="corp-logo-mark"><?= e(public_initials($corporateTitle)) ?></span><?php endif; ?>
            </div>
          </div>
          <?php if (!empty($whatsappUrl) || !empty($phoneUrl) || !empty($corpContact['email']) || !empty($corpHeroButtons) || (!empty($meetingUrl) && empty($heroPrimaryCtaUrl) && empty($corpHeroButtons))): ?>
          <div class="corp-hero-actions">
            <?php if (!empty($whatsappUrl)): ?><a class="corp-icon-btn" href="<?= e($whatsappUrl) ?>" target="_blank" rel="noopener" aria-label="WhatsApp"><i class="fa-brands fa-whatsapp"></i></a><?php endif; ?>
            <?php if (!empty($phoneUrl)): ?><a class="corp-icon-btn" href="<?= e($phoneUrl) ?>" aria-label="Call"><i class="fa-solid fa-phone"></i></a><?php endif; ?>
            <?php if (!empty($corpContact['email'])): ?><a class="corp-icon-btn" href="<?= e($emailUrl) ?>" aria-label="Email"><i class="fa-regular fa-envelope"></i></a><?php endif; ?>
            <?php foreach ($corpHeroButtons as $button): $btnBg = e($button['button_color'] ?? '#1979BF'); $btnFg = e($button['text_color'] ?? '#FFFFFF'); ?>
              <a class="corp-primary" href="<?= e($button['url']) ?>" style="background:<?= $btnBg ?>;color:<?= $btnFg ?>" target="_blank" rel="noopener"><?= e($button['label']) ?> <i class="fa-solid fa-arrow-right"></i></a>
            <?php endforeach; ?>
            <?php if (empty($heroPrimaryCtaUrl) && !empty($meetingUrl) && empty($corpHeroButtons)): ?>
              <a class="corp-primary" href="<?= e($meetingUrl) ?>"><?= e($corpEvent['title'] ?: ($eventButtonLabel ?: 'Book meeting')) ?> <i class="fa-solid fa-arrow-right"></i></a>
            <?php endif; ?>
          </div>
          <?php endif; ?>
        </div>

        <div class="corp-title-block">
          <div class="corp-title-row">
            <h1><?= e($corporateTitle) ?></h1>
            <span class="corp-verified"><i class="fa-solid fa-check"></i></span>
          </div>
          <?php if (!empty($page['slug'])): ?><p class="corp-eyebrow">@<?= e($page['slug']) ?></p><?php endif; ?>
          <?php if (!empty($corporateDescription)): ?><p class="corp-summary"><?= e($corporateDescription) ?></p><?php endif; ?>

          <?php if ($corpSpecialties): ?>
          <div class="corp-tag-row"><span class="corp-tag-icon"><i class="fa-solid fa-toolbox"></i></span><?php foreach ($corpSpecialties as $item): ?><span class="corp-pill"><?= e($item) ?></span><?php endforeach; ?></div>
          <?php endif; ?>

          <?php if ($corpLocations): ?>
          <div class="corp-tag-row"><span class="corp-tag-icon"><i class="fa-solid fa-location-dot"></i></span><?php foreach ($corpLocations as $item): ?><span class="corp-pill"><?= e($item) ?></span><?php endforeach; ?></div>
          <?php endif; ?>

          <?php if ($corpLinks): ?>
          <div class="corp-tag-row"><span class="corp-tag-icon"><i class="fa-solid fa-globe"></i></span><?php foreach ($corpLinks as $item): ?>
            <?php if (!empty($item['url'])): ?>
              <a class="corp-pill" href="<?= e($item['url']) ?>" target="_blank" rel="noopener"><?= e($item['label'] ?: $item['url']) ?></a>
            <?php else: ?>
              <span class="corp-pill"><?= e($item['label']) ?></span>
            <?php endif; ?>
          <?php endforeach; ?></div>
          <?php endif; ?>

          <?php if ($corpLocations || $corpSpecialties): ?>
          <div class="corp-tag-row">
            <span class="corp-tag-icon"><i class="fa-solid fa-location-dot"></i></span>
            <?php if ($companyWebsite !== ''): ?><a class="corp-pill" href="<?= e($companyWebsite) ?>" target="_blank" rel="noopener">Website</a><?php endif; ?>
            <?php foreach ($corpLocations as $item): ?><span class="corp-pill"><?= e($item) ?></span><?php endforeach; ?>
            <?php foreach ($corpSpecialties as $item): ?><span class="corp-pill"><?= e($item) ?></span><?php endforeach; ?>
          </div>
          <?php endif; ?>

          <div class="corp-cta-row">
            <?php if ($corpSocials): ?>
            <div class="corp-social-row">
              <?php foreach ($corpSocials as $item): ?><a class="corp-icon-btn" href="<?= e($item['url'] ?: '#') ?>" aria-label="<?= e($item['platform'] ?: 'Social') ?>" target="_blank" rel="noopener"><?= public_social_icon($item['platform'] ?: 'Link') ?></a><?php endforeach; ?>
            </div>
            <?php endif; ?>
            <?php foreach ($corpButtons as $button): $btnBg = e($button['button_color'] ?? '#1979BF'); $btnFg = e($button['text_color'] ?? '#FFFFFF'); ?>
              <a class="corp-primary" href="<?= e($button['url']) ?>" style="background:<?= $btnBg ?>;color:<?= $btnFg ?>" target="_blank" rel="noopener"><?= e($button['label']) ?> <i class="fa-solid fa-arrow-right"></i></a>
            <?php endforeach; ?>
            <?php if (empty($heroPrimaryCtaUrl) && !empty($meetingUrl) && !$corpButtons): ?>
              <a class="corp-primary" href="<?= e($meetingUrl) ?>"><?= e($corpEvent['title'] ?: $eventButtonLabel) ?> <i class="fa-solid fa-arrow-right"></i></a>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </section>

    <?php if (!empty($corpEvent['title']) || !empty($corpEvent['book_link']) || !empty($corpEvent['description'])): ?>
    <section class="corp-event" style="background:<?= e($corpEvent['card_color'] ?? '#062947') ?>">
      <div class="corp-wrap corp-event-inner">
        <div class="corp-event-main">
          <div class="corp-event-icon"><i class="fa-solid fa-calendar-days"></i></div>
          <div class="corp-event-text">
            <h2><?= e($corpEvent['title']) ?></h2>
            <?php if ($eventLine !== ''): ?><p><?= e($eventLine) ?></p><?php endif; ?>
          </div>
        </div>
        <div class="corp-event-actions">
          <?php if (!empty($corpEvent['book_link'])): ?><a class="corp-event-btn" href="<?= e($corpEvent['book_link']) ?>"><?= e($eventButtonLabel) ?> <i class="fa-solid fa-arrow-right"></i></a><?php endif; ?>
          <?php if (!empty($corpEvent['countdown']) && $eventStart): ?>
          <div class="corp-countdown">
            <div><span><?= e(str_pad((string)$countdownDays, 2, '0', STR_PAD_LEFT)) ?></span><small>DAYS</small></div>
            <div><span><?= e(str_pad((string)$countdownHours, 2, '0', STR_PAD_LEFT)) ?></span><small>HOURS</small></div>
            <div><span><?= e(str_pad((string)$countdownMinutes, 2, '0', STR_PAD_LEFT)) ?></span><small>MINS</small></div>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </section>
    <?php endif; ?>

    <?php if ($corpCards): ?>
      <section class="corp-section white">
      <div class="corp-wrap">
          <?php if ($corpCardsTitle): ?><h2><?= e($corpCardsTitle) ?></h2><?php endif; ?>
          <?php if ($corpCardsLede): ?><p class="corp-section-lede"><?= e($corpCardsLede) ?></p><?php endif; ?>
        <div class="corp-card-grid">
          <?php foreach ($corpCards as $card):
            $fill = $card['fill_color'] ?? '#06111E';
            $style = 'background:' . $fill;
            if (($card['fill_type'] ?? '') === 'gradient') $style = 'background:linear-gradient(135deg,' . ($card['gradient_start'] ?? '#06111E') . ',' . ($card['gradient_end'] ?? '#0A9994') . ')';
            if (($card['fill_type'] ?? '') === 'photo' && !empty($card['photo'])) $style = "background-image:url('" . e($card['photo']) . "')";
            $outline = !empty($card['outline_weight']) ? 'border:' . (int)$card['outline_weight'] . 'px solid ' . e($card['outline_color'] ?? '#0A9994') : '';
            $isLight = in_array(strtoupper((string)$fill), ['#E0E4E8', '#FFFFFF'], true);
          ?>
          <a class="corp-card <?= $isLight ? 'corp-card--light' : '' ?>" href="<?= e($card['link'] ?? '#') ?>" style="<?= e($style) ?>;<?= $outline ?>" target="_blank" rel="noopener">
            <div class="corp-card-content">
              <strong><?= e($card['title']) ?></strong>
              <?php if (!empty($card['description'])): ?><p><?= e($card['description']) ?></p><?php endif; ?>
              <span class="corp-card-btn"><?= e(($card['cta_label'] ?? '') ?: 'View Capability') ?> <i class="fa-solid fa-arrow-right"></i></span>
            </div>
          </a>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
    <?php endif; ?>

    <?php if ($actionCards || $quoteTitle || $quoteDescription || $quoteButtonLabel): ?>
    <section class="corp-section light">
      <div class="corp-wrap">
        <?php if ($corpActionsTitle): ?><h2><?= e($corpActionsTitle) ?></h2><?php endif; ?>
        <?php if ($corpActionsLede): ?><p class="corp-section-lede"><?= e($corpActionsLede) ?></p><?php endif; ?>
        <?php if ($actionCards): ?>
        <div class="corp-actions-grid">
          <?php foreach ($actionCards as $action): ?>
            <a class="corp-action-card" <?= !empty($action['url']) && strpos((string)$action['url'], 'meeting') !== false ? 'id="schedule-meeting"' : '' ?> href="<?= e($action['url']) ?>">
              <div class="corp-action-icon"><i class="<?= e($action['icon']) ?>"></i></div>
              <strong><?= e($action['title']) ?></strong>
              <p><?= e($action['description']) ?></p>
              <small><?= e($action['cta']) ?> <i class="fa-solid fa-arrow-right"></i></small>
            </a>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if ($quoteTitle || $quoteDescription || $quoteButtonLabel): ?>
        <form class="corp-quote-form" id="request-quote" method="post">
          <input type="hidden" name="corporate_form" value="quote">
          <?php if ($quoteTitle): ?><h3><?= e($quoteTitle) ?></h3><?php endif; ?>
          <?php if ($quoteDescription): ?><p style="margin:0;color:#64748b;font-size:13px;line-height:1.6;"><?= e($quoteDescription) ?></p><?php endif; ?>
          <div class="corp-form-grid">
            <input name="company" placeholder="Company">
            <textarea name="request_text" placeholder="What do you need?"></textarea>
          </div>
          <button class="corp-primary" type="submit"><?= e($quoteButtonLabel) ?></button>
        </form>
        <?php endif; ?>
      </div>
    </section>
    <?php endif; ?>

    <?php if (!empty($corpEvent['register'])): ?>
    <section class="corp-section white">
      <div class="corp-wrap">
        <h2><?= e($corpEventRegisterTitle) ?></h2>
        <form class="corp-quote-form" method="post">
          <input type="hidden" name="corporate_form" value="event_register">
          <?php if ($quoteButtonLabel): ?><button class="corp-primary" type="submit"><?= e($quoteButtonLabel) ?></button><?php endif; ?>
        </form>
      </div>
    </section>
    <?php endif; ?>

    <?php if ($corpMembers): ?>
    <section class="corp-section light">
      <div class="corp-wrap">
        <h2><?= e(($corpTeam['title'] ?? '') ?: 'Team') ?></h2>
        <?php if (!empty($corpTeam['description'])): ?><p class="corp-section-lede"><?= e($corpTeam['description']) ?></p><?php endif; ?>
        <div class="corp-team-grid">
          <?php foreach ($corpMembers as $member): ?>
          <div class="corp-contact-card">
            <div class="corp-contact-avatar">
              <?php if (!empty($member['photo'])): ?><img src="<?= e($member['photo']) ?>" alt="<?= e($member['name'] ?? '') ?>"><?php else: ?><?= e(public_initials($member['name'] ?? 'TM')) ?><?php endif; ?>
            </div>
            <div>
              <strong><?= e($member['name'] ?? '') ?></strong>
              <?php if (!empty($member['title'])): ?><small><?= e($member['title']) ?></small><?php endif; ?>
              <div class="corp-contact-actions">
                <?php if (!empty($member['email'])): ?><a href="mailto:<?= e($member['email']) ?>"><i class="fa-regular fa-envelope"></i></a><?php endif; ?>
                <?php if (!empty($member['phone'])): ?><a href="tel:<?= e(preg_replace('/\s+/', '', (string)$member['phone'])) ?>"><i class="fa-solid fa-phone"></i></a><?php endif; ?>
                <?php if (!empty($member['linkedin'])): ?><a href="<?= e($member['linkedin']) ?>" target="_blank" rel="noopener"><i class="fa-brands fa-linkedin-in"></i></a><?php endif; ?>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
    <?php endif; ?>

    <?php if (empty($page['hide_xinng_logo'])): ?><div class="corp-brand xinng-powered"><span>Powered by</span><a class="xinng-brand xinng-brand--small" href="<?= e(xinng_public_base_url()) ?>/index.php" aria-label="Xinng home"><img class="xinng-brand__image" src="<?= e(xinng_brand_logo_url(xinng_public_base_url())) ?>" alt="Xinng" width="1736" height="906"></a></div><?php endif; ?>
  </main>
</body>
</html>
<?php else: ?>
<?php
$appBase = rtrim((string)xinng_public_base_url(), '/');
$allowedUrl = static function ($value): string {
	$value = trim((string)$value);
	return preg_match('~^(https?://|mailto:|tel:)~i', $value) ? $value : '#';
};
$safeAsset = static function ($value): string {
	$value = trim((string)$value);
  if (preg_match('~^https?://[A-Za-z0-9.-]+(?::[0-9]{1,5})?(?:/[A-Za-z0-9_./%?=&-]*)?(?:#[A-Za-z0-9_-]*)?$~i', $value)) return $value;
	return preg_match('~^(?!//)[A-Za-z0-9_./%?=&-]+$~', $value) ? $value : '';
};
$safeColor = static function ($value, string $fallback): string {
	$value = trim((string)$value);
  return preg_match('/^#(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{4}|[0-9a-fA-F]{6}|[0-9a-fA-F]{8})$/', $value) ? $value : $fallback;
};
$luminance = static function (string $color): float {
	$hex = ltrim($color, '#');
	if (strlen($hex) === 3) $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
	if (strlen($hex) < 6 || !ctype_xdigit(substr($hex, 0, 6))) return 0.2;
	$channels = array_map(static fn($part) => hexdec($part) / 255, str_split(substr($hex, 0, 6), 2));
	$channels = array_map(static fn($channel) => $channel <= 0.04045 ? $channel / 12.92 : (($channel + 0.055) / 1.055) ** 2.4, $channels);
	return 0.2126 * $channels[0] + 0.7152 * $channels[1] + 0.0722 * $channels[2];
};
$headerMode = in_array(($page['header_mode'] ?? 'color'), ['color', 'gradient', 'image'], true) ? $page['header_mode'] : 'color';
$headerColor = $safeColor($page['header_color'] ?? '', '#263238');
$headerStart = $safeColor($page['header_gradient_start'] ?? '', $headerColor);
$headerEnd = $safeColor($page['header_gradient_end'] ?? '', '#0A9994');
$headerImage = $safeAsset($page['header_image_path'] ?? '');
$headerFit = in_array(($page['header_fit'] ?? 'cover'), ['cover', 'contain', 'repeat'], true) ? $page['header_fit'] : 'cover';
$pageBg = $safeColor($page['background_color'] ?? '', '#F4F1EC');
$bgMode = in_array(($page['background_mode'] ?? 'color'), ['color', 'gradient', 'image'], true) ? $page['background_mode'] : 'color';
$bgStart = $safeColor($page['background_gradient_start'] ?? '', $pageBg);
$bgEnd = $safeColor($page['background_gradient_end'] ?? '', '#FFFFFF');
$bgImage = $safeAsset($page['background_image_path'] ?? '');
$accent = $safeColor($page['block_color'] ?? '', '#A44F52');
$blockFg = $safeColor($page['block_text_color'] ?? '', '#FFFFFF');
$titleColor = $safeColor($page['title_color'] ?? '', '#292827');
$descriptionColor = $safeColor($page['description_color'] ?? '', '#716F6B');
$shape = in_array(($page['block_shape'] ?? 'rounded'), ['rounded', 'pill', 'square'], true) ? $page['block_shape'] : 'rounded';
$shadow = in_array(($page['block_shadow'] ?? 'soft'), ['none', 'soft', 'strong'], true) ? $page['block_shadow'] : 'soft';
$font = trim((string)($page['font'] ?? 'system'));
$font = preg_match('/^[A-Za-z0-9 -]{1,60}$/', $font) ? $font : 'system';
$fontValue = $font === 'system' ? "'Inter', system-ui, sans-serif" : "'{$font}', 'Inter', system-ui, sans-serif";
$headerLuma = $headerMode === 'gradient' ? ($luminance($headerStart) + $luminance($headerEnd)) / 2 : $luminance($headerColor);
$heroTextColor = $headerMode === 'image' ? '#FFFFFF' : ($headerLuma > 0.48 ? '#202124' : '#FFFFFF');
$pageLuma = $bgMode === 'gradient' ? ($luminance($bgStart) + $luminance($bgEnd)) / 2 : $luminance($pageBg);
$theme = $pageLuma > 0.52 ? 'light' : 'dark';
$headerStyle = 'background:' . $headerColor . ';';
if ($headerMode === 'gradient') $headerStyle = 'background:linear-gradient(135deg,' . $headerStart . ',' . $headerEnd . ');';
if ($headerMode === 'image' && $headerImage !== '') {
  $headerStyle = 'background-color:' . $headerColor . ';background-image:url(' . $headerImage . ');background-size:' . ($headerFit === 'repeat' ? 'auto' : $headerFit) . ';background-repeat:' . ($headerFit === 'repeat' ? 'repeat' : 'no-repeat') . ';background-position:center;';
}
$backgroundStyle = 'background:' . $pageBg . ';';
if ($bgMode === 'gradient') $backgroundStyle = 'background:linear-gradient(180deg,' . $bgStart . ',' . $bgEnd . ');';
if ($bgMode === 'image' && $bgImage !== '') $backgroundStyle = 'background-color:' . $pageBg . ';background-image:url(' . $bgImage . ');background-size:cover;background-position:center;';
$requestPath = strtok((string)($_SERVER['REQUEST_URI'] ?? ''), '?') ?: '';
$requestHost = (string)($_SERVER['HTTP_HOST'] ?? '');
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['SERVER_PORT'] ?? '') === '443');
$publicUrl = preg_match('/^[A-Za-z0-9.\-:\[\]]+$/', $requestHost) && str_starts_with($requestPath, '/')
	? (($isHttps ? 'https://' : 'http://') . $requestHost . $requestPath)
	: ($appBase . '/' . rawurlencode((string)($page['slug'] ?? '')));
$publicUrl = $allowedUrl($publicUrl);
$homeUrl = $allowedUrl($appBase . '/index.php');
$signupUrl = $allowedUrl($appBase . '/signup.php');
$profileImage = $safeAsset($profile);
$hasSubscribe = (bool)array_filter($blocks, static fn($block) => ($block['type'] ?? '') === 'subscribe');
$socialPlacement = ($page['social_placement'] ?? 'top') === 'bottom' ? 'bottom' : 'top';
$socialStyle = preg_replace('/[^A-Za-z0-9_-]/', '', (string)($page['social_icon_style'] ?? 'original')) ?: 'original';
$youtubeId = static function ($url): string {
	$parts = parse_url((string)$url);
	if (!is_array($parts)) return '';
	$host = strtolower((string)($parts['host'] ?? ''));
	$path = trim((string)($parts['path'] ?? ''), '/');
	$id = '';
  if ($host === 'youtu.be' || $host === 'www.youtu.be') $id = explode('/', $path)[0] ?? '';
  elseif (in_array($host, ['youtube.com', 'www.youtube.com', 'm.youtube.com'], true)) {
		parse_str((string)($parts['query'] ?? ''), $query);
		if (!empty($query['v'])) $id = (string)$query['v'];
		elseif (preg_match('~^(?:shorts|embed)/([^/]+)~', $path, $match)) $id = $match[1];
	}
	return preg_match('/^[A-Za-z0-9_-]{11}$/', $id) ? $id : '';
};
$qrImage = static function (string $url): string {
	return 'https://api.qrserver.com/v1/create-qr-code/?size=240x240&margin=8&data=' . rawurlencode($url);
};
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="<?= e($pageBg) ?>">
  <title><?= e($title) ?></title>
  <meta name="description" content="<?= e($description) ?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <link rel="stylesheet" href="<?= e($appBase) ?>/assets/css/public-page.css">
  <link rel="stylesheet" href="<?= e($appBase) ?>/assets/css/brand.css">
  <script src="<?= e($appBase) ?>/assets/js/public-page.js" defer></script>
</head>
<body class="pb-public-body" style="--wall-bg:<?= e($pageBg) ?>;<?= e($backgroundStyle) ?>">
  <main class="pb-public-page" data-theme="<?= e($theme) ?>" data-page-title="<?= e($title) ?>" data-page-url="<?= e($publicUrl) ?>" style="--accent:<?= e($accent) ?>;--block-bg:<?= e($accent) ?>;--block-fg:<?= e($blockFg) ?>;--radius:<?= $shape === 'square' ? '6px' : '18px' ?>;--page-bg:<?= e($pageBg) ?>;--font:<?= e($fontValue) ?>;--title-color:<?= e($titleColor) ?>;--description-color:<?= e($descriptionColor) ?>;--hero-fg:<?= e($heroTextColor) ?>;<?= e($backgroundStyle) ?>">
    <header class="pb-hero" style="<?= e($headerStyle) ?>">
      <div class="pb-topbar">
        <?php if (empty($page['hide_xinng_logo'])): ?><a class="pb-glass pb-mark" href="<?= e($homeUrl) ?>" aria-label="Xinng home"><span>xin</span></a><?php else: ?><span></span><?php endif; ?>
        <div class="pb-top-actions">
          <?php if ($hasSubscribe): ?><button class="pb-glass pb-subscribe" type="button" data-scroll-subscribe><i class="fa-regular fa-bell" aria-hidden="true"></i><span>Subscribe</span></button><?php endif; ?>
          <button class="pb-glass pb-share" type="button" data-share-page aria-label="Share this page" title="Share"><i class="fa-solid fa-arrow-up-from-bracket" aria-hidden="true"></i></button>
        </div>
      </div>
      <div class="pb-hero-fade" aria-hidden="true"></div>
    </header>

    <section class="pb-content" aria-label="<?= e($title ?: 'Profile') ?>">
      <div class="pb-identity">
        <div class="pb-avatar" aria-hidden="true" style="--avatar-fallback:linear-gradient(135deg,<?= e($accent) ?>,<?= e($headerEnd) ?>)">
          <?php if ($profileImage !== ''): ?><img src="<?= e($profileImage) ?>" alt=""><?php else: ?><span><?= e(public_initials($title)) ?></span><?php endif; ?>
        </div>
        <div class="pb-name-row"><h1><?= e($title ?: 'Page title') ?></h1><?php if (!empty($page['is_verified'])): ?><span class="pb-verified" aria-label="Verified"><i class="fa-solid fa-check" aria-hidden="true"></i></span><?php endif; ?></div>
        <?php if (!empty($page['slug'])): ?><p class="pb-handle">@<?= e($page['slug']) ?></p><?php endif; ?>
        <?php if (trim((string)$description) !== ''): ?><div class="pb-bio-wrap"><p class="pb-bio" data-bio><?= e($description) ?></p><button class="pb-more" type="button" data-bio-toggle aria-expanded="false" hidden>more</button></div><?php endif; ?>
      </div>

      <?php if ($socialPlacement === 'top' && $socials): ?><ul class="pb-socials style-<?= e($socialStyle) ?>" aria-label="Social links">
        <?php foreach ($socials as $social): $socialUrl = $allowedUrl($social['url'] ?? ''); $socialExternal = preg_match('~^https?://~i', $socialUrl); ?>
          <li><a href="<?= e($socialUrl) ?>" aria-label="<?= e(ucfirst((string)($social['platform'] ?? 'Social'))) ?>"<?= $socialExternal ? ' target="_blank" rel="noopener noreferrer"' : '' ?>><?= public_social_icon($social['platform'] ?? 'link') ?></a></li>
        <?php endforeach; ?>
      </ul><?php endif; ?>

      <ul class="pb-blocks" aria-label="Page links">
        <?php $delay = 0; foreach ($blocks as $index => $block):
          $type = (string)($block['type'] ?? 'link');
          $blockTitle = trim((string)($block['title'] ?? '')) ?: 'Link';
          $blockDescription = trim((string)($block['description'] ?? ''));
          $blockUrl = $allowedUrl($block['destination_url'] ?? '');
          $external = preg_match('~^https?://~i', $blockUrl);
          $image = $safeAsset($block['image_path'] ?? '');
          $videoId = in_array($type, ['video', 'youtube'], true) ? $youtubeId($blockUrl) : '';
          $headingMatch = [];
          $isSection = $type === 'text' && (preg_match('/^##\s+(.+)$/', $blockTitle, $headingMatch) || str_ends_with($blockTitle, ':'));
          if ($isSection) {
            $sectionTitle = isset($headingMatch[1]) ? trim($headingMatch[1]) : rtrim($blockTitle, ':');
        ?>
          <li class="pb-section-label" style="--item-delay:<?= (int)$delay++ * 40 ?>ms"><h2><?= e($sectionTitle) ?></h2></li>
        <?php continue; } ?>
          <li class="pb-item" data-block-type="<?= e($type) ?>"<?= $type === 'subscribe' ? ' id="pb-subscribe"' : '' ?> style="--item-delay:<?= (int)$delay++ * 40 ?>ms">
            <article class="pb-card pb-card--<?= e(preg_match('/^[a-z_]+$/', $type) ? $type : 'link') ?> shape-<?= e($shape) ?> shadow-<?= e($shadow) ?>">
              <?php if (in_array($type, ['video', 'youtube'], true)): ?>
                <div class="pb-media pb-video" data-video-container>
                  <?php if ($videoId !== ''): ?><button class="pb-video-launch" type="button" data-youtube-id="<?= e($videoId) ?>" aria-label="Play <?= e($blockTitle) ?>">
                    <img src="https://i.ytimg.com/vi/<?= e($videoId) ?>/hqdefault.jpg" alt="" loading="lazy"><span class="pb-play"><i class="fa-solid fa-play" aria-hidden="true"></i></span>
                    <span class="pb-media-caption"><strong><?= e($blockTitle) ?></strong><small><i class="fa-brands fa-youtube" aria-hidden="true"></i> YouTube</small></span>
                  </button><?php else: ?><a class="pb-video-launch" href="<?= e($blockUrl) ?>"<?= $external ? ' target="_blank" rel="noopener noreferrer"' : '' ?> aria-label="Open <?= e($blockTitle) ?>">
                    <?php if ($image !== ''): ?><img src="<?= e($image) ?>" alt="" loading="lazy"><?php else: ?><span class="pb-video-placeholder"></span><?php endif; ?><span class="pb-play"><i class="fa-solid fa-play" aria-hidden="true"></i></span><span class="pb-media-caption"><strong><?= e($blockTitle) ?></strong><small>Video</small></span>
                  </a><?php endif; ?>
                </div>
              <?php elseif ($type === 'image'): ?>
                <div class="pb-image-card">
                  <?php if ($image !== ''): ?><img class="pb-image" src="<?= e($image) ?>" alt="<?= e($blockTitle) ?>" loading="lazy"><?php endif; ?>
                  <div class="pb-card-title"><a href="<?= e($blockUrl) ?>"<?= $external ? ' target="_blank" rel="noopener noreferrer"' : '' ?>><?= e($blockTitle) ?></a><?php if ($blockDescription !== ''): ?><small><?= e($blockDescription) ?></small><?php endif; ?></div>
                </div>
              <?php elseif ($type === 'text'): ?>
                <div class="pb-text-card"><span><?= public_block_icon($type) ?></span><div><?php if ($blockTitle !== ''): ?><strong><?= e($blockTitle) ?></strong><?php endif; ?><?php if ($blockDescription !== ''): ?><p><?= e($blockDescription) ?></p><?php endif; ?></div></div>
              <?php elseif ($type === 'qr'): ?>
                <a class="pb-qr-card" href="<?= e($blockUrl) ?>"<?= $external ? ' target="_blank" rel="noopener noreferrer"' : '' ?>><img src="<?= e($qrImage($blockUrl !== '#' ? $blockUrl : $publicUrl)) ?>" alt="QR code for <?= e($blockTitle) ?>" loading="lazy"><strong><?= e($blockTitle) ?></strong><?php if ($blockDescription !== ''): ?><small><?= e($blockDescription) ?></small><?php endif; ?></a>
              <?php else: ?>
                <a class="pb-link-card" href="<?= e($blockUrl) ?>"<?= $external ? ' target="_blank" rel="noopener noreferrer"' : '' ?> aria-label="<?= e($blockTitle) ?>">
                  <span class="pb-link-icon"><?= public_block_icon($type) ?></span><span class="pb-link-copy"><strong><?= e($blockTitle) ?></strong><?php if ($blockDescription !== ''): ?><small><?= e($blockDescription) ?></small><?php endif; ?></span>
                </a>
              <?php endif; ?>
              <div class="pb-kebab-wrap"><button class="pb-kebab" type="button" aria-label="More options for <?= e($blockTitle) ?>" aria-expanded="false" aria-haspopup="true" data-kebab><span aria-hidden="true">⋮</span></button><div class="pb-popover" data-popover hidden><button type="button" data-copy-url="<?= e($blockUrl) ?>">Copy link</button><button type="button" data-share-url="<?= e($blockUrl) ?>" data-share-title="<?= e($blockTitle) ?>">Share</button></div></div>
            </article>
          </li>
        <?php endforeach; ?>
        <?php if (!$blocks): ?><li class="pb-empty">Nothing here yet</li><?php endif; ?>
      </ul>

      <?php if ($socialPlacement === 'bottom' && $socials): ?><ul class="pb-socials pb-socials--bottom style-<?= e($socialStyle) ?>" aria-label="Social links">
        <?php foreach ($socials as $social): $socialUrl = $allowedUrl($social['url'] ?? ''); $socialExternal = preg_match('~^https?://~i', $socialUrl); ?>
          <li><a href="<?= e($socialUrl) ?>" aria-label="<?= e(ucfirst((string)($social['platform'] ?? 'Social'))) ?>"<?= $socialExternal ? ' target="_blank" rel="noopener noreferrer"' : '' ?>><?= public_social_icon($social['platform'] ?? 'link') ?></a></li>
        <?php endforeach; ?>
      </ul><?php endif; ?>

      <footer class="pb-footer">
        <nav class="pb-footer-links" aria-label="Footer"><a href="#">Report</a><a href="#">Privacy</a><a href="<?= e($homeUrl) ?>">Explore</a></nav>
        <?php if (empty($page['hide_xinng_logo'])): ?><div class="pb-brand xinng-powered"><span>Powered by</span><a class="xinng-brand xinng-brand--small" href="<?= e($homeUrl) ?>" aria-label="Xinng home"><img class="xinng-brand__image" src="<?= e(xinng_brand_logo_url($appBase)) ?>" alt="Xinng" width="1736" height="906"></a></div><?php endif; ?>
      </footer>
    </section>
    <?php if (empty($page['hide_xinng_logo'])): ?><div class="pb-mobile-promo" data-promo><a href="<?= e($signupUrl) ?>">xin.ng/<?= e($page['slug'] ?? '') ?> <span>Create your own page</span></a><button type="button" data-dismiss-promo aria-label="Dismiss">×</button></div><?php endif; ?>
    <aside class="pb-desktop-qr" aria-label="View this page on mobile"><img src="<?= e($qrImage($publicUrl)) ?>" alt="QR code to view this page on mobile"><span>View on mobile</span></aside>
    <div class="pb-toast" role="status" aria-live="polite" data-toast></div>
  </main>
</body>
</html>
<?php endif; ?>
