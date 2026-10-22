# Google Analytics

Xinng loads GA4 from `includes/google-analytics.php` on every user-facing HTML page.
The default measurement ID is `G-FC783XQGWT`. Override it per deployment with:

```dotenv
GOOGLE_ANALYTICS_ID=G-FC783XQGWT
```

The shared tag records normal GA4 page views. Query strings and fragments are
removed from the reported page location so password-reset tokens, ticket
references, and other URL parameters are not sent to Google Analytics.

## Custom events

Track a declarative click without adding page-specific JavaScript:

```html
<button data-analytics-event="create_page"
        data-analytics-component="primary_button"
        data-analytics-location="pages">
  Create page
</button>
```

Or track an event from JavaScript:

```js
window.XinngAnalytics.track('preview_open', {
  component: 'page_card',
  location: 'pages'
});
```

Decoupled components may dispatch the same event through the document:

```js
document.dispatchEvent(new CustomEvent('xinng:analytics', {
  detail: {
    event: 'preview_open',
    parameters: { component: 'qr_card', location: 'qr_codes' }
  }
}));
```

Event names and parameter names must use lowercase letters, numbers, and
underscores. The bridge rejects parameter names associated with passwords,
tokens, sessions, contact details, names, or addresses. Never send form values,
credentials, session identifiers, or attendee data to analytics.
