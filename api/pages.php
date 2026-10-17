<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json; charset=utf-8');

$pdo = get_db_connection();
if (!$pdo) {
	http_response_code(500);
	echo json_encode(['ok' => false, 'error' => 'db']);
	exit;
}
xinng_ensure_page_builder_tables($pdo);
xinng_ensure_credit_tables($pdo);
xinng_ensure_qr_code_tables($pdo);

function pages_payload(): array {
	$raw = file_get_contents('php://input');
	$json = json_decode($raw, true);
	return is_array($json) ? $json : $_POST;
}

function page_row(PDO $pdo, array $page): array {
	$stmt = $pdo->prepare('SELECT * FROM page_blocks WHERE page_id = ? AND deleted_at IS NULL ORDER BY position ASC, id ASC');
	$stmt->execute([(int)$page['id']]);
	$blocks = array_map(static function($block) {
		$block['metadata'] = !empty($block['metadata']) ? json_decode($block['metadata'], true) : [];
		$block['is_active'] = (bool)$block['is_active'];
		return $block;
	}, $stmt->fetchAll());

	$stmt = $pdo->prepare('SELECT * FROM page_socials WHERE page_id = ? AND deleted_at IS NULL ORDER BY position ASC, id ASC');
	$stmt->execute([(int)$page['id']]);
	$socials = array_map(static function($social) {
		$social['is_active'] = (bool)$social['is_active'];
		return $social;
	}, $stmt->fetchAll());

	return ['page' => $page, 'blocks' => $blocks, 'socials' => $socials, 'corporate' => xinng_load_corporate_page_data($pdo, (int)$page['id'], $page), 'event' => xinng_load_event_page_data($pdo, (int)$page['id'], $page)];
}

function load_user_page(PDO $pdo, int $id, int $user_id): ?array {
	$stmt = $pdo->prepare('SELECT * FROM pages WHERE id = ? AND user_id = ? AND deleted_at IS NULL LIMIT 1');
	$stmt->execute([$id, $user_id]);
	return $stmt->fetch() ?: null;
}

function page_analytics_summary(PDO $pdo, int $page_id): array {
	$stmt = $pdo->prepare('SELECT COUNT(*) AS cnt FROM page_views WHERE page_id = ?');
	$stmt->execute([$page_id]);
	$pageViews = (int)$stmt->fetchColumn();

	$stmt = $pdo->prepare('SELECT COUNT(DISTINCT COALESCE(visitor_id, CONCAT("row:", id))) AS cnt FROM page_views WHERE page_id = ?');
	$stmt->execute([$page_id]);
	$uniqueVisitors = (int)$stmt->fetchColumn();

	$stmt = $pdo->prepare('SELECT COUNT(*) AS cnt FROM link_clicks WHERE page_id = ?');
	$stmt->execute([$page_id]);
	$linkClicks = (int)$stmt->fetchColumn();

	$stmt = $pdo->prepare('SELECT COUNT(*) AS cnt FROM qr_scans WHERE page_id = ?');
	$stmt->execute([$page_id]);
	$qrScans = (int)$stmt->fetchColumn();

	$stmt = $pdo->prepare('SELECT DATE(viewed_at) AS day, COUNT(*) AS cnt FROM page_views WHERE page_id = ? AND viewed_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) GROUP BY day ORDER BY day ASC');
	$stmt->execute([$page_id]);
	$trendRows = $stmt->fetchAll();

	$trend = [];
	for ($i = 6; $i >= 0; $i--) {
		$day = date('Y-m-d', strtotime("-{$i} days"));
		$trend[$day] = 0;
	}
	foreach ($trendRows as $row) {
		$trend[$row['day']] = (int)$row['cnt'];
	}
	$trendList = [];
	foreach ($trend as $day => $count) {
		$trendList[] = ['date' => $day, 'count' => $count];
	}

	$stmt = $pdo->prepare('SELECT pb.id AS block_id, pb.title, pb.destination_url, pb.type, COUNT(lc.id) AS clicks FROM link_clicks lc JOIN page_blocks pb ON pb.id = lc.block_id WHERE lc.page_id = ? GROUP BY pb.id, pb.title, pb.destination_url, pb.type ORDER BY clicks DESC LIMIT 5');
	$stmt->execute([$page_id]);
	$topBlocks = array_map(static function ($row) {
		return [
			'block_id' => (int)$row['block_id'],
			'title' => $row['title'] ?? 'Untitled',
			'destination_url' => $row['destination_url'] ?? '',
			'type' => $row['type'] ?? 'link',
			'clicks' => (int)$row['clicks'],
		];
	}, $stmt->fetchAll());

	$ctr = $pageViews > 0 ? round(($linkClicks + $qrScans) / $pageViews * 100, 1) : 0;
	return [
		'page_views' => $pageViews,
		'unique_visitors' => $uniqueVisitors,
		'link_clicks' => $linkClicks,
		'qr_scans' => $qrScans,
		'ctr_percent' => $ctr,
		'page_views_7_day_trend' => $trendList,
		'top_blocks' => $topBlocks,
	];
}

function unique_page_slug(PDO $pdo, string $base, ?int $current_page_id = null): string {
	$base = xinng_normalize_back_half($base) ?: 'page';
	$slug = $base;
	$i = 1;
	while (true) {
		$check = xinng_validate_page_slug($pdo, $slug, $current_page_id);
		if ($check['ok']) return $check['slug'];
		$i++;
		$slug = $base . '-' . $i;
	}
}

function normalize_page_type($value): string {
	$v = strtolower(trim((string)$value));
	$event_aliases = ['event', 'event page', 'event_page', 'eventpage'];
	if (in_array($v, $event_aliases, true)) return 'event';
	$corporate_aliases = ['corporate', 'company', 'company page', 'company_page', 'companypage', 'corp'];
	if (in_array($v, $corporate_aliases, true)) return 'corporate';
	return 'creator';
}

function page_type_defaults(string $pageType): array {
	if ($pageType === 'event') {
		return ['title'=>'New Event','description'=>'Everything attendees need, from registration to check-in.','event'=>['format'=>'physical','attendance_type'=>'open','start_at'=>'','end_at'=>'','venue'=>'','city'=>'','country'=>'','virtual_url'=>'','cover_image'=>'','organizer_name'=>'','contact_email'=>'','currency'=>'NGN','capacity'=>1,'sales_start_at'=>'','sales_end_at'=>'','tickets'=>[]],'theme'=>'warm','layout'=>'event','header_color'=>'#201A19','background_color'=>'#FFF9F7','block_color'=>'#E83B32','block_text_color'=>'#FFFFFF','blocks'=>[]];
	}
	if (in_array($pageType, ['corporate', 'event'], true)) {
		$isEvent = $pageType === 'event';
		$corporate = [
			'header_photo' => '',
			'logo' => '',
			'company_name' => '',
			'description' => '',
			'cards_title' => '',
			'cards_lede' => '',
			'actions_title' => '',
			'actions_lede' => '',
			'event_register_title' => '',
			'hero_primary_cta_label' => '',
			'hero_primary_cta_url' => '',
			'quote_title' => '',
			'quote_description' => '',
			'quote_button_label' => '',
			'contact' => ['meeting_link' => '', 'brochure_link' => '', 'phone' => '', 'email' => '', 'whatsapp' => ''],
			'specialties' => [],
			'locations' => [],
			'links' => [],
			'socials' => [],
			'event' => ['title' => $isEvent ? 'Your event' : '', 'description' => $isEvent ? 'Add the essential details attendees need to register.' : '', 'start_at' => '', 'end_at' => '', 'location' => '', 'city' => '', 'countdown' => $isEvent, 'book_link' => '', 'brochure_link' => '', 'register' => $isEvent, 'card_color' => $isEvent ? '#201A19' : '#062947', 'button_label' => $isEvent ? 'Register now' : ''],
			'cards' => [],
			'buttons' => [],
			'team' => ['title' => '', 'description' => '', 'members' => []],
		];
		return [
			'title' => $isEvent ? 'Event Page' : 'Company Page',
			'description' => $isEvent ? 'Event details, ticket options, registration, and attendee updates.' : 'Company profile, documents, meetings, and business inquiries.',
			'corporate' => $corporate,
			'theme' => $isEvent ? 'warm' : 'navy',
			'layout' => 'compact',
			'header_color' => $isEvent ? '#201A19' : '#102A43',
			'background_color' => $isEvent ? '#FFF9F7' : '#F6F8FB',
			'block_color' => $isEvent ? '#E83B32' : '#102A43',
			'block_text_color' => '#FFFFFF',
			'blocks' => [],
		];
	}
	return [
		'title' => 'Personal Page',
		'description' => 'Share your links, content, socials, bookings, and products.',
		'theme' => 'default',
		'layout' => 'header-image',
		'header_color' => '#26282C',
		'background_color' => '#FFFAF6',
		'block_color' => '#0A9994',
		'block_text_color' => '#FFFAF6',
		'blocks' => [
			['type' => 'link', 'title' => 'Follow me', 'description' => '', 'destination_url' => 'https://example.com'],
			['type' => 'booking', 'title' => 'Book me', 'description' => 'Reserve a session or appointment.', 'destination_url' => 'https://example.com/book'],
			['type' => 'subscribe', 'title' => 'Subscribe', 'description' => 'Get my latest updates.', 'destination_url' => 'https://example.com/subscribe'],
		],
	];
}

function pages_clean_text($value, int $max): string {
	return mb_substr(trim((string)$value), 0, $max, 'UTF-8');
}

function pages_clean_url($value): string {
	return mb_substr(trim((string)$value), 0, 1200, 'UTF-8');
}

function pages_clean_image_url($value): string {
	return mb_substr(trim((string)$value), 0, 250000, 'UTF-8');
}

function pages_clean_bool($value): bool {
	return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? !empty($value);
}

function pages_clean_hex($value, string $fallback): string {
	return xinng_validate_hex_color((string)$value, $fallback);
}

function sanitize_event_metadata($raw): array {
	$raw=is_array($raw)?$raw:[]; $format=in_array($raw['format'] ?? '',['physical','virtual','hybrid'],true)?$raw['format']:'physical';
	$attendanceType=in_array($raw['attendance_type'] ?? '',['open','ticketed'],true)?$raw['attendance_type']:'open';
	$currency=strtoupper(pages_clean_text($raw['currency'] ?? 'NGN',3)); if(!preg_match('/^[A-Z]{3}$/',$currency)) $currency='NGN';
	$clean=['format'=>$format,'attendance_type'=>$attendanceType,'start_at'=>pages_clean_text($raw['start_at'] ?? '',40),'end_at'=>pages_clean_text($raw['end_at'] ?? '',40),'venue'=>pages_clean_text($raw['venue'] ?? '',180),'city'=>pages_clean_text($raw['city'] ?? '',100),'country'=>pages_clean_text($raw['country'] ?? '',100),'virtual_url'=>pages_clean_url($raw['virtual_url'] ?? ''),'cover_image'=>pages_clean_image_url($raw['cover_image'] ?? ''),'organizer_name'=>pages_clean_text($raw['organizer_name'] ?? '',120),'contact_email'=>pages_clean_text($raw['contact_email'] ?? '',120),'currency'=>$currency,'capacity'=>max(0,(int)($raw['capacity'] ?? 0)),'sales_start_at'=>pages_clean_text($raw['sales_start_at'] ?? '',40),'sales_end_at'=>pages_clean_text($raw['sales_end_at'] ?? '',40),'tickets'=>[]];
	foreach(array_slice((array)($raw['tickets'] ?? []),0,20) as $ticket){if(!is_array($ticket))continue;$name=pages_clean_text($ticket['name'] ?? '',100);if($name==='')continue;$clean['tickets'][]=['id'=>max(0,(int)($ticket['id'] ?? 0)),'name'=>$name,'description'=>pages_clean_text($ticket['description'] ?? '',220),'price_minor'=>max(0,(int)($ticket['price_minor'] ?? 0)),'quantity_total'=>max(0,(int)($ticket['quantity_total'] ?? 0)),'min_per_order'=>max(1,(int)($ticket['min_per_order'] ?? 1)),'max_per_order'=>max(1,min(100,(int)($ticket['max_per_order'] ?? 10))),'is_active'=>!empty($ticket['is_active'])];}
	return $clean;
}

function validate_event_metadata(array $event): ?string {
	if ($event['start_at'] === '' || $event['end_at'] === '' || $event['venue'] === '' || $event['country'] === '') return 'event_required_fields';
	if ((int)$event['capacity'] < 1) return 'event_capacity_required';
	if ($event['attendance_type'] !== 'ticketed') return null;
	if (count($event['tickets']) < 1) return 'event_ticket_required';
	$totalQuantity = 0;
	foreach ($event['tickets'] as $ticket) {
		if ((int)$ticket['price_minor'] < 1) return 'event_ticket_price_required';
		if ((int)$ticket['quantity_total'] < 1) return 'event_ticket_quantity_required';
		$totalQuantity += (int)$ticket['quantity_total'];
	}
	if ($totalQuantity > (int)$event['capacity']) return 'event_ticket_quantity_exceeds_capacity';
	return null;
}

function pages_card_link_allowed(string $type, string $url): bool {
	if ($url === '') return false;
	if ($type === 'video') return (bool)preg_match('/^(https?:\/\/)?(www\.)?(youtube\.com|youtu\.be|vimeo\.com)\//i', $url);
	if ($type === 'pdf') return (bool)preg_match('/\.pdf($|\?)/i', $url);
	return true;
}

function sanitize_corporate_metadata($raw): array {
	$raw = is_array($raw) ? $raw : [];
	$contact = is_array($raw['contact'] ?? null) ? $raw['contact'] : [];
	$event = is_array($raw['event'] ?? null) ? $raw['event'] : [];
	$team = is_array($raw['team'] ?? null) ? $raw['team'] : [];
	$clean = [
		'header_photo' => '',
		'logo' => '',
		'company_name' => pages_clean_text($raw['company_name'] ?? '', 100),
		'description' => pages_clean_text($raw['description'] ?? '', 200),
		'cards_title' => pages_clean_text($raw['cards_title'] ?? '', 80),
		'cards_lede' => pages_clean_text($raw['cards_lede'] ?? '', 200),
		'actions_title' => pages_clean_text($raw['actions_title'] ?? '', 80),
		'actions_lede' => pages_clean_text($raw['actions_lede'] ?? '', 200),
		'event_register_title' => pages_clean_text($raw['event_register_title'] ?? '', 80),
		'hero_primary_cta_label' => pages_clean_text($raw['hero_primary_cta_label'] ?? '', 60),
		'hero_primary_cta_url' => pages_clean_url($raw['hero_primary_cta_url'] ?? ''),
		'quote_title' => pages_clean_text($raw['quote_title'] ?? '', 80),
		'quote_description' => pages_clean_text($raw['quote_description'] ?? '', 180),
		'quote_button_label' => pages_clean_text($raw['quote_button_label'] ?? '', 40),
		'contact' => [
			'meeting_link' => '',
			'brochure_link' => '',
			'phone' => pages_clean_text($contact['phone'] ?? '', 40),
			'email' => pages_clean_text($contact['email'] ?? '', 120),
			'whatsapp' => pages_clean_text($contact['whatsapp'] ?? '', 40),
		],
		'specialties' => [],
		'locations' => [],
		'links' => [],
		'socials' => [],
		'event' => [
			'title' => pages_clean_text($event['title'] ?? '', 80),
			'description' => pages_clean_text($event['description'] ?? '', 150),
			'start_at' => pages_clean_text($event['start_at'] ?? '', 40),
			'end_at' => pages_clean_text($event['end_at'] ?? '', 40),
			'location' => pages_clean_text($event['location'] ?? '', 120),
			'city' => pages_clean_text($event['city'] ?? '', 80),
			'countdown' => pages_clean_bool($event['countdown'] ?? false),
			'book_link' => '',
			'brochure_link' => '',
			'register' => pages_clean_bool($event['register'] ?? false),
			'card_color' => xinng_validate_hex_color($event['card_color'] ?? '#062947', '#062947'),
			'button_label' => pages_clean_text($event['button_label'] ?? '', 40),
		],
		'cards' => [],
		'buttons' => [],
		'team' => [
			'title' => pages_clean_text($team['title'] ?? 'Team', 30) ?: 'Team',
			'description' => pages_clean_text($team['description'] ?? '', 150),
			'members' => [],
		],
	];
	$clean['header_photo'] = pages_clean_image_url($raw['header_photo'] ?? '');
	$clean['logo'] = pages_clean_image_url($raw['logo'] ?? '');
	$meetingLink = pages_clean_url($contact['meeting_link'] ?? '');
	if ($meetingLink !== '') {
		$validated = xinng_validate_destination_url($meetingLink);
		if ($validated['ok']) $clean['contact']['meeting_link'] = $validated['url'];
	}
	$brochureLink = pages_clean_url($contact['brochure_link'] ?? '');
	if ($brochureLink !== '') {
		$validated = xinng_validate_destination_url($brochureLink);
		if ($validated['ok']) $clean['contact']['brochure_link'] = $validated['url'];
	}
	$eventBookLink = pages_clean_url($event['book_link'] ?? '');
	if ($eventBookLink !== '') {
		$validated = xinng_validate_destination_url($eventBookLink);
		if ($validated['ok']) $clean['event']['book_link'] = $validated['url'];
	}
	$clean['event']['button_label'] = pages_clean_text($event['button_label'] ?? '', 40);
	$eventBrochureLink = pages_clean_url($event['brochure_link'] ?? '');
	if ($eventBrochureLink !== '') {
		$validated = xinng_validate_destination_url($eventBrochureLink);
		if ($validated['ok']) $clean['event']['brochure_link'] = $validated['url'];
	}
	foreach (array_slice((array)($raw['specialties'] ?? []), 0, 6) as $item) {
		$value = pages_clean_text(is_array($item) ? ($item['label'] ?? '') : $item, 60);
		if ($value !== '') $clean['specialties'][] = $value;
	}
	foreach (array_slice((array)($raw['locations'] ?? []), 0, 3) as $item) {
		$value = pages_clean_text(is_array($item) ? ($item['label'] ?? '') : $item, 80);
		if ($value !== '') $clean['locations'][] = $value;
	}
	foreach (array_slice((array)($raw['links'] ?? []), 0, 3) as $item) {
		if (!is_array($item)) continue;
		$label = pages_clean_text($item['label'] ?? '', 60);
		$url = pages_clean_url($item['url'] ?? '');
		if ($url !== '') {
			$validated = xinng_validate_destination_url($url);
			if ($validated['ok']) $url = $validated['url'];
			else $url = '';
		}
		if ($label !== '' || $url !== '') $clean['links'][] = ['label' => $label, 'url' => $url];
	}
	foreach (array_slice((array)($raw['socials'] ?? []), 0, 6) as $item) {
		if (!is_array($item)) continue;
		$platform = pages_clean_text($item['platform'] ?? '', 40);
		$url = pages_clean_url($item['url'] ?? '');
		if ($url !== '') {
			$validated = xinng_validate_destination_url($url);
			if ($validated['ok']) $url = $validated['url'];
			else $url = '';
		}
		if ($platform !== '' || $url !== '') $clean['socials'][] = ['platform' => $platform, 'url' => $url];
	}
	foreach (array_slice((array)($raw['cards'] ?? []), 0, 12) as $item) {
		if (!is_array($item)) continue;
		$type = in_array(($item['type'] ?? 'text'), ['text', 'video', 'pdf'], true) ? $item['type'] : 'text';
		$fillType = in_array(($item['fill_type'] ?? 'color'), ['color', 'gradient', 'photo'], true) ? $item['fill_type'] : 'color';
		$weight = max(0, min(5, (int)($item['outline_weight'] ?? 0)));
		$title = pages_clean_text($item['title'] ?? '', 100);
		if ($title === '') continue;
		$link = pages_clean_url($item['link'] ?? '');
		if ($link !== '') {
			$validated = xinng_validate_destination_url($link);
			if ($validated['ok']) $link = $validated['url'];
			else $link = '';
		}
		if ($link !== '' && !pages_card_link_allowed($type, $link)) $link = '';
		$clean['cards'][] = [
			'title' => $title,
			'type' => $type,
			'description' => pages_clean_text($item['description'] ?? '', 220),
			'cta_label' => pages_clean_text($item['cta_label'] ?? '', 30),
			'link' => $link,
			'fill_type' => $fillType,
			'fill_color' => xinng_validate_hex_color($item['fill_color'] ?? '#06111E', '#06111E'),
			'gradient_start' => xinng_validate_hex_color($item['gradient_start'] ?? '#06111E', '#06111E'),
			'gradient_end' => xinng_validate_hex_color($item['gradient_end'] ?? '#0A9994', '#0A9994'),
			'photo' => pages_clean_url($item['photo'] ?? ''),
			'outline_color' => xinng_validate_hex_color($item['outline_color'] ?? '#0A9994', '#0A9994'),
			'outline_weight' => $weight,
		];
	}
	foreach (array_slice((array)($raw['buttons'] ?? []), 0, 8) as $item) {
		if (!is_array($item)) continue;
		$label = pages_clean_text($item['label'] ?? '', 30);
		$url = pages_clean_url($item['url'] ?? '');
		if ($url !== '') {
			$validated = xinng_validate_destination_url($url);
			if ($validated['ok']) $url = $validated['url'];
			else $url = '';
		}
		if ($label !== '' && $url !== '') {
			$clean['buttons'][] = [
				'label' => $label,
				'url' => $url,
				'button_color' => xinng_validate_hex_color($item['button_color'] ?? '#1979BF', '#1979BF'),
				'text_color' => xinng_validate_hex_color($item['text_color'] ?? '#FFFFFF', '#FFFFFF'),
			];
		}
	}
	foreach (array_slice((array)($team['members'] ?? []), 0, 12) as $item) {
		if (!is_array($item)) continue;
		$name = pages_clean_text($item['name'] ?? '', 120);
		$role = pages_clean_text($item['title'] ?? '', 30);
		if ($name === '' && $role === '') continue;
		$linkedin = pages_clean_url($item['linkedin'] ?? '');
		if ($linkedin !== '') {
			$validated = xinng_validate_destination_url($linkedin);
			if ($validated['ok']) $linkedin = $validated['url'];
			else $linkedin = '';
		}
		$clean['team']['members'][] = [
			'photo' => pages_clean_url($item['photo'] ?? ''),
			'name' => $name,
			'title' => $role,
			'phone' => pages_clean_text($item['phone'] ?? '', 40),
			'email' => pages_clean_text($item['email'] ?? '', 120),
			'linkedin' => $linkedin,
		];
	}
	return $clean;
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$payload = pages_payload();
$user_id = xinng_public_api_user_id($payload);
if ($user_id <= 0) {
	http_response_code(422);
	echo json_encode(['ok' => false, 'error' => 'missing_user_id']);
	exit;
}
$requestedPageType = $payload['page_type'] ?? $payload['pageType'] ?? $payload['type'] ?? 'creator';
if ($method === 'POST' && !empty($payload['_method'])) $method = strtoupper((string)$payload['_method']);

try {
	if ($method === 'GET') {
		if (!empty($_GET['analytics'])) {
			$id = (int)($_GET['id'] ?? 0);
			if ($id <= 0) { http_response_code(400); echo json_encode(['ok' => false, 'error' => 'missing_page_id']); exit; }
			$page = load_user_page($pdo, $id, $user_id);
			if (!$page) { http_response_code(404); echo json_encode(['ok' => false, 'error' => 'not_found']); exit; }
			echo json_encode(['ok' => true, 'analytics' => page_analytics_summary($pdo, $id)]);
			exit;
		}
		$id = (int)($_GET['id'] ?? 0);
		if ($id > 0) {
			$page = load_user_page($pdo, $id, $user_id);
			if (!$page) { http_response_code(404); echo json_encode(['ok' => false, 'error' => 'not_found']); exit; }
			echo json_encode(['ok' => true] + page_row($pdo, $page));
			exit;
		}
		$stmt = $pdo->prepare('
			SELECT p.*,
				(SELECT COUNT(*) FROM page_views pv WHERE pv.page_id = p.id) AS views
			FROM pages p
			WHERE p.user_id = ? AND p.deleted_at IS NULL
			ORDER BY p.updated_at DESC, p.id DESC
		');
		$stmt->execute([$user_id]);
		echo json_encode(['ok' => true, 'pages' => $stmt->fetchAll()]);
		exit;
	}

	if ($method === 'POST') {
		$pageType = normalize_page_type($requestedPageType);
		$defaults = page_type_defaults($pageType);
		$title = trim((string)($payload['title'] ?? $defaults['title']));
		if ($title === '') $title = $defaults['title'];
		$slug = unique_page_slug($pdo, $payload['slug'] ?? ($title ?: 'page'));
		$stmt = $pdo->prepare('INSERT INTO pages (user_id, page_type, corporate_metadata, slug, title, description, bio, is_published, status, theme, layout, font, title_color, description_color, header_mode, header_color, background_mode, background_color, block_shape, block_shadow, block_color, block_text_color, published_at, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, 1, "published", ?, ?, "system", "#26282C", "#26282C", "color", ?, "color", ?, "rounded", "soft", ?, ?, NOW(), NOW(), NOW())');
		$stmt->execute([$user_id, $pageType, json_encode($defaults['corporate'] ?? [], JSON_UNESCAPED_SLASHES), $slug, $title, $defaults['description'], $defaults['description'], $defaults['theme'], $defaults['layout'], $defaults['header_color'], $defaults['background_color'], $defaults['block_color'], $defaults['block_text_color']]);
		$id = (int)$pdo->lastInsertId();
		if ($pageType === 'corporate') {
			xinng_save_corporate_page_data($pdo, $id, $user_id, sanitize_corporate_metadata($defaults['corporate'] ?? []));
		}
		if ($pageType === 'event') xinng_save_event_page_data($pdo,$id,$user_id,sanitize_event_metadata($defaults['event'] ?? []));
		$stmt = $pdo->prepare('INSERT INTO page_blocks (page_id, user_id, type, title, description, destination_url, position, is_active, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, 1, NOW(), NOW())');
		foreach ($defaults['blocks'] as $position => $block) {
			$stmt->execute([$id, $user_id, $block['type'], $block['title'], $block['description'], $block['destination_url'], $position]);
		}
		xinng_ensure_page_qr_code($pdo, $user_id, $id, $title, xinng_short_url($slug));
		echo json_encode(['ok' => true, 'id' => $id, 'slug' => $slug, 'edit_url' => 'page_builder.php?id=' . $id]);
		exit;
	}

	if ($method === 'PATCH') {
		$id = (int)($payload['id'] ?? 0);
		$page = $id > 0 ? load_user_page($pdo, $id, $user_id) : null;
		if (!$page) { http_response_code(404); echo json_encode(['ok' => false, 'error' => 'not_found']); exit; }
		$state = $payload['state'] ?? [];
		if (!is_array($state)) $state = [];
		$slugCheck = xinng_validate_page_slug($pdo, (string)($state['slug'] ?? $page['slug']), $id);
		if (!$slugCheck['ok']) { http_response_code(422); echo json_encode(['ok' => false, 'error' => $slugCheck['error']]); exit; }
		$pageType = normalize_page_type($state['page_type'] ?? ($page['page_type'] ?? 'creator'));

		$header = is_array($state['header'] ?? null) ? $state['header'] : [];
		$background = is_array($state['background'] ?? null) ? $state['background'] : [];
		$blockStyle = is_array($state['block_style'] ?? null) ? $state['block_style'] : [];
		$branding = is_array($state['branding'] ?? null) ? $state['branding'] : [];
		$canRemoveBranding = xinng_user_has_paid_access($pdo, $user_id);
		$corporateMetadata = sanitize_corporate_metadata($state['corporate'] ?? []);
		$eventMetadata = sanitize_event_metadata($state['event'] ?? []);
		if ($pageType === 'event' && ($eventError = validate_event_metadata($eventMetadata))) {
			http_response_code(422);
			echo json_encode(['ok' => false, 'error' => $eventError]);
			exit;
		}
		if ($pageType === 'corporate') {
			if ($corporateMetadata['company_name'] === '') $corporateMetadata['company_name'] = substr((string)($state['title'] ?? $page['title'] ?? 'Company Page'), 0, 100);
			if ($corporateMetadata['description'] === '') $corporateMetadata['description'] = substr((string)($state['description'] ?? $page['description'] ?? ''), 0, 200);
		}

		$pdo->beginTransaction();
		$stmt = $pdo->prepare('UPDATE pages SET page_type=?, corporate_metadata=?, slug=?, title=?, description=?, bio=?, profile_image_path=?, profile_image_url=?, theme=?, layout=?, font=?, title_color=?, description_color=?, header_mode=?, header_color=?, header_gradient_start=?, header_gradient_end=?, header_image_path=?, header_fit=?, background_mode=?, background_color=?, background_gradient_start=?, background_gradient_end=?, background_image_path=?, social_icon_style=?, social_placement=?, block_shape=?, block_shadow=?, block_color=?, block_text_color=?, hide_xinng_logo=?, status="published", is_published=1, published_at=NOW(), updated_at=NOW() WHERE id=? AND user_id=?');
		$stmt->execute([
			$pageType,
			json_encode($corporateMetadata, JSON_UNESCAPED_SLASHES),
			$slugCheck['slug'],
			substr((string)($state['title'] ?? ''), 0, 150),
			mb_substr((string)($state['description'] ?? ''), 0, 255, 'UTF-8'),
			mb_substr((string)($state['description'] ?? ''), 0, 255, 'UTF-8'),
			$state['profile_image'] ?? null,
			$state['profile_image'] ?? null,
			$state['theme'] ?? 'default',
			$state['layout'] ?? 'simple',
			$state['font'] ?? 'system',
			xinng_validate_hex_color($state['text_color'] ?? '#26282C', '#26282C'),
			xinng_validate_hex_color($state['description_color'] ?? '#26282C', '#26282C'),
			$header['mode'] ?? 'color',
			xinng_validate_hex_color($header['color'] ?? '#26282C', '#26282C'),
			xinng_validate_hex_color($header['gradient_start'] ?? '#26282C', '#26282C'),
			xinng_validate_hex_color($header['gradient_end'] ?? '#0A9994', '#0A9994'),
			$header['image'] ?? null,
			$header['fit'] ?? 'cover',
			$background['mode'] ?? 'color',
			xinng_validate_hex_color($background['color'] ?? '#FFFAF6', '#FFFAF6'),
			xinng_validate_hex_color($background['gradient_start'] ?? '#FFFAF6', '#FFFAF6'),
			xinng_validate_hex_color($background['gradient_end'] ?? '#FFFFFF', '#FFFFFF'),
			$background['image'] ?? null,
			$state['social_style'] ?? 'original',
			$state['social_placement'] ?? 'top',
			$blockStyle['shape'] ?? 'rounded',
			$blockStyle['shadow'] ?? 'soft',
			xinng_validate_hex_color($blockStyle['block_color'] ?? '#0A9994', '#0A9994'),
			xinng_validate_hex_color($blockStyle['block_text_color'] ?? '#FFFAF6', '#FFFAF6'),
			$canRemoveBranding && !empty($branding['hide_xinng_logo']) ? 1 : 0,
			$id,
			$user_id
		]);
		if ($pageType === 'corporate') {
			xinng_save_corporate_page_data($pdo, $id, $user_id, $corporateMetadata);
		}
		if ($pageType === 'event') xinng_save_event_page_data($pdo,$id,$user_id,$eventMetadata);

		$pdo->prepare('UPDATE page_blocks SET deleted_at = NOW() WHERE page_id = ? AND user_id = ?')->execute([$id, $user_id]);
		foreach (($state['blocks'] ?? []) as $pos => $block) {
			if (!is_array($block)) continue;
			$stmt = $pdo->prepare('INSERT INTO page_blocks (page_id, user_id, type, title, description, destination_url, image_path, metadata, position, is_active, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())');
			$stmt->execute([$id, $user_id, $block['type'] ?? 'link', $block['title'] ?? null, $block['description'] ?? null, $block['destination_url'] ?? null, $block['image_path'] ?? null, json_encode($block['metadata'] ?? [], JSON_UNESCAPED_SLASHES), (int)$pos, !empty($block['is_active']) ? 1 : 0]);
		}

		$pdo->prepare('UPDATE page_socials SET deleted_at = NOW() WHERE page_id = ? AND user_id = ?')->execute([$id, $user_id]);
		foreach (($state['socials'] ?? []) as $pos => $social) {
			if (!is_array($social) || empty($social['platform']) || empty($social['url'])) continue;
			$stmt = $pdo->prepare('INSERT INTO page_socials (page_id, user_id, platform, label, url, icon, position, is_active, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())');
			$stmt->execute([$id, $user_id, $social['platform'], $social['label'] ?? $social['platform'], $social['url'], $social['icon'] ?? null, (int)$pos, !empty($social['is_active']) ? 1 : 0]);
		}
		xinng_ensure_page_qr_code($pdo, $user_id, $id, (string)($state['title'] ?? $page['title'] ?? 'Page'), xinng_short_url($slugCheck['slug']));
		$pdo->commit();
		echo json_encode([
			'ok' => true,
			'slug' => $slugCheck['slug'],
			'public_url' => xinng_short_url($slugCheck['slug']),
			'event' => $pageType === 'event' ? xinng_load_event_page_data($pdo, $id, $page) : null,
		]);
		exit;
	}

	if ($method === 'DELETE') {
		$id = (int)($payload['id'] ?? 0);
		$stmt = $pdo->prepare('UPDATE pages SET status = "archived", is_published = 0, deleted_at = NOW(), updated_at = NOW() WHERE id = ? AND user_id = ?');
		$stmt->execute([$id, $user_id]);
		echo json_encode(['ok' => true]);
		exit;
	}

	http_response_code(405);
	echo json_encode(['ok' => false, 'error' => 'method_not_allowed']);
} catch (Throwable $e) {
	if ($pdo->inTransaction()) $pdo->rollBack();
	error_log($e->getMessage());
	http_response_code(500);
	echo json_encode(['ok' => false, 'error' => 'server']);
}
