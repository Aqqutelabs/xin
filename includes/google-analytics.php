<?php
declare(strict_types=1);

/**
 * Shared Google Analytics 4 tag for every rendered Xinng page.
 *
 * The environment variable makes the property replaceable per deployment while
 * keeping the requested production property as the default. Including this file
 * more than once is safe: a request emits the tag only once.
 */
if (defined('XINNG_GOOGLE_ANALYTICS_RENDERED')) {
    return;
}
define('XINNG_GOOGLE_ANALYTICS_RENDERED', true);

$xinngGaId = trim((string) (getenv('GOOGLE_ANALYTICS_ID') ?: 'G-FC783XQGWT'));
if (!preg_match('/^G-[A-Z0-9]+$/', $xinngGaId)) {
    return;
}
$xinngGaIdEscaped = htmlspecialchars($xinngGaId, ENT_QUOTES, 'UTF-8');
?>
<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=<?= $xinngGaIdEscaped ?>"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  window.gtag = window.gtag || gtag;
  gtag('js', new Date());
  gtag('config', <?= json_encode($xinngGaId, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>, {
    // Do not send reset tokens, ticket references, or other query parameters.
    page_location: window.location.origin + window.location.pathname,
    transport_type: 'beacon'
  });

  // Safe custom-event bridge. It accepts explicitly supplied metadata only and
  // rejects parameter names that could contain credentials or personal data.
  window.XinngAnalytics = window.XinngAnalytics || Object.freeze({
    track: function (eventName, parameters) {
      if (typeof eventName !== 'string' || !/^[a-z][a-z0-9_]{1,39}$/.test(eventName)) return;
      var blocked = /(?:password|passwd|secret|token|authorization|cookie|session|email|phone|name|address)/i;
      var clean = {};
      Object.entries(parameters || {}).slice(0, 20).forEach(function (entry) {
        var key = String(entry[0]);
        var value = entry[1];
        if (!/^[a-z][a-z0-9_]{0,39}$/.test(key) || blocked.test(key)) return;
        if (typeof value === 'string') clean[key] = value.slice(0, 100);
        else if (typeof value === 'number' || typeof value === 'boolean') clean[key] = value;
      });
      gtag('event', eventName, clean);
    }
  });

  document.addEventListener('click', function (event) {
    var target = event.target instanceof Element
      ? event.target.closest('[data-analytics-event]')
      : null;
    if (!target) return;
    window.XinngAnalytics.track(target.dataset.analyticsEvent, {
      component: target.dataset.analyticsComponent || target.tagName.toLowerCase(),
      location: target.dataset.analyticsLocation || 'page'
    });
  });

  document.addEventListener('xinng:analytics', function (event) {
    var detail = event.detail || {};
    window.XinngAnalytics.track(detail.event, detail.parameters || {});
  });
</script>
