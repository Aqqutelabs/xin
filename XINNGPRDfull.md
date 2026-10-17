# Xinng Master Product Requirements Document

**Version:** 2.1  
**Status:** Consolidated working specification  
**Date:** 16 October 2026  
**Platform:** xin.ng  
**Primary stack:** PHP, MySQL/MariaDB, HTML, CSS, JavaScript, responsive web and PWA-compatible services

---

## 1. Product definition

Xinng is a modular growth operating system for creators, businesses, publishers, organizers and professional teams.

It connects four jobs that are usually fragmented across separate products:

1. **Establish a presence** through Pages, short links and QR experiences.
2. **Build an audience** through contacts, lead capture and segmentation.
3. **Communicate and distribute** through campaigns, automations, social publishing and PR.
4. **Measure and improve** through analytics, intelligence and controlled AI assistance.

Events and creator tools are specialist products built on these shared services. They must reuse the same identity, organization, contact, payment, notification, media, link and analytics infrastructure rather than create parallel platforms.

### Product promise

> Create, distribute and measure every important connection from one Xinng workspace.

### Product principles

- Start with independently useful tools and connect them through shared records.
- Keep each product train independently licensable.
- Use one account and organization model across the platform.
- Allow anonymous entry where it improves acquisition, including QR creation and creator calculators.
- Distinguish verified facts, user-entered data, forecasts and AI-generated estimates.
- Keep sponsorship, sales, subscriptions, credits, procurement and financing financially distinct.
- Require human approval for material financial, publishing or account actions performed with AI assistance.
- Store configuration and third-party rules as versioned data rather than permanent hardcoded assumptions.
- Prefer provider adapters to direct dependencies on one vendor.
- Treat analytics, permissions, auditability and accessibility as release requirements.

### Non-goals

The initial platform will not:

- replace every specialist CRM, ERP or event hardware product;
- manufacture access-control hardware;
- guarantee creator revenue, press coverage or marketing outcomes;
- automate deceptive engagement, artificial impressions or spam;
- let AI independently approve payments, financing or irreversible external actions;
- expose future modules in navigation before they are usable.

---

## 2. Users and workspace model

### Primary users

- Creators and independent professionals
- Small and medium businesses
- Marketing, communications and PR teams
- Publishers, bloggers and media operators
- Event organizers and conference teams
- Sponsors, exhibitors and suppliers
- Institutional funding and finance teams
- Platform administrators and support operators

A user may belong to multiple organizations and hold different roles in each organization or project. All permission checks must be server-side and scoped to the relevant organization, event, campaign, contact, transaction or document.

Standard roles include owner, administrator, manager, editor/operator, analyst, finance approver, contributor, viewer and external collaborator. Specialist products may add event manager, check-in staff, publisher, sponsor, vendor or institutional funder.

---

## 3. Product trains

| Train | Outcome | Initial priority |
| --- | --- | --- |
| Platform and workspace | Secure shared foundation, navigation and administration | P0 |
| Presence | Pages, short links and QR experiences | P0 |
| Audience | Contacts, companies, lists, forms and lead capture | P0 |
| Communications | Email, SMS, messaging and inbox | P0 |
| Flow | Triggered automation and journey orchestration | P1 |
| Campaigns and distribution | Campaign planning, content distribution and measurement | P1 |
| PR and publisher network | PR workflow, publishers, paid add-ons and reporting | P1 |
| Social and content | Publishing, scheduling, media and performance analytics | P2 |
| Intelligence | Monitoring, narrative, SEO, journalist and LLM visibility | P2 |
| Events | Planning, commerce and professional event operations | P1–P3 |
| Creator tools | Creator qualification, revenue planning and multi-platform insight | P0–P2 |
| Commerce and finance | Credits, subscriptions, checkout, payouts and controlled financing | P0–P3 |
| Xinng Nano | Missions, influencer commerce, verified work and lightweight gamification | P1–P4 |

---

# Train A — Platform and workspace

## 4. Identity, organizations and permissions

- Account creation, sign-in, sign-out and password reset
- Optional email verification and new-login security alerts
- Organization creation, membership and invitations
- Server-side role-based access control
- Soft deletion and recovery rules where appropriate
- Session security, CSRF protection and rate limiting
- Audit records for security-sensitive and financially material actions

Acceptance requires organization isolation, hashed and expiring reset tokens, and traceable privileged actions.

## 5. Navigation and workspace shell

The primary navigation contains:

- Dashboard
- Presence: Pages, Short Links and QR Codes
- Audience: Contacts, Lists, Segments and Forms
- Campaigns
- Inbox
- Flow
- PR
- Social
- Events
- Analytics
- Finance
- Nano
- Tools
- Settings

Only enabled and authorized modules appear. Future products must not look available before release.

The top bar provides an organization switcher, global search, quick-create action, notifications, help and account actions. Mobile uses a compact bottom navigation and a “More” surface. Desktop-only visual builders are replaced on mobile by safe review and control actions.

## 6. Dashboard home

- Workspace health and selected date range
- Traffic, contacts, conversions and spend/revenue indicators
- Primary actions based on enabled products
- Global search
- Recent activity
- Tasks, alerts and reviews
- Contextual onboarding

Metrics link to their underlying records or analytics views.

## 7. Shared services

Every train should reuse:

- Authentication, organizations and permissions
- Contacts and consent
- Files and media
- Short URLs and QR resolution
- Notifications and transactional email
- Payments, credits and subscriptions
- Analytics events
- Activity and audit logs
- Background jobs and schedules
- Provider integrations and secrets
- Search

---

# Train B — Presence

## 8. Pages

Users can create mobile-first public pages for personal, creator, professional and organization use cases.

- Slug and public URL
- Title, bio, profile media and branding
- Ordered links, social links and content blocks
- Draft and published states
- Live preview
- Theme and layout settings
- Page visits and link-click analytics
- QR code linked to the canonical public page

## 9. Short links

- Create, edit, pause and soft-delete links
- Unique back-half validation
- Destination validation and safe redirects
- Privacy-aware click tracking
- Campaign parameters and attribution
- Status and expiration controls
- Organization ownership and permissions
- Exportable analytics

## 10. QR codes

- URL and page-based QR codes
- Live preview, colors, size and error correction
- Optional logo and brand treatment
- PNG and SVG downloads; PDF for print layouts
- Scan analytics and campaign attribution
- Editable destinations for supported dynamic QR codes

### Anonymous QR flow

1. A visitor enters a destination on the landing page.
2. Xinng creates a temporary QR result.
3. The result page shows the QR and destination.
4. Download requires a valid email address.
5. The visitor may download or create an account to retain the QR.

Temporary data expires. Anonymous identifiers are unguessable. IP data is minimized or irreversibly hashed when needed for abuse prevention.

---

# Train C — Audience

## 11. Contacts and companies

The contact record is the identity anchor for communication and engagement.

- Names, email addresses, phone numbers and location
- Company, job title and acquisition source
- Owners, lifecycle stage and status
- Tags, custom fields and notes
- Consent and communication preferences by channel
- List and segment membership
- Unified activity timeline (“Moments”)
- Related campaigns, conversations, forms and transactions

Companies group contacts and store domain, industry, size, location and account ownership.

## 12. Lists, segments and audiences

- Static lists
- Dynamic property and behavior segments
- AND/OR filter groups
- Saved reusable conditions
- Audience estimates
- Exclusion and suppression lists
- Real-time audience streams for supported events

## 13. Import, export and data quality

- CSV import with field mapping, preview and validation
- Partial-success reporting
- Duplicate detection and merge suggestions
- Email and phone normalization
- Authorized, audited export jobs
- Bulk tagging and field updates
- Background processing for large operations

The configurable MVP import target is 5,000 contacts per file.

## 14. Forms and lead capture

- Native form builder
- Public embed and hosted form URLs
- Validation, consent and spam protection
- Source and campaign attribution
- Contact creation or update with deduplication
- List, tag, owner and Flow actions
- Submission viewer and export

Later prospecting, enrichment, browser capture and verification features require documented permission, provenance, retention and deletion behavior. They must not bypass external platform controls.

---

# Train D — Communications

## 15. Campaigns

A campaign groups messages, audiences, schedules, content, spend and results.

- Name, objective, owner and status
- Channels and child messages
- Audience inclusion and exclusion
- Draft, scheduled, active, paused, completed and archived states
- Budget or credit estimate
- Approval workflow where required
- Performance summary and activity history
- Duplication and templates

## 16. Email, SMS and messaging

Email supports sender identity, subject, preview text, rich and plain content, test sends, scheduling, unsubscribe enforcement and provider delivery events.

SMS supports provider-compliant sender configuration, segment count, cost estimate, opt-out handling, delivery receipts and replies where available.

WhatsApp is a later adapter-based channel requiring approved business access and templates where applicable.

## 17. Unified inbox

- Contact-centered conversations
- Channel and status filters
- Assignment and ownership
- Internal notes
- Search
- Read/unread and open/closed states
- Response-time metrics
- Contact context beside the conversation

## 18. Transactional communications

Initial events include account creation, onboarding, password reset, new login, subscription changes, payment/credit confirmation, weekly analytics and product-specific operational updates.

Every email send stores its event type, recipient, status, provider identifier, error and timestamps. Failed sends are observable and safely retryable. In-app notifications support action URLs and single/all read controls.

Transactional and marketing messages remain operationally separate.

---

# Train E — Flow automation

## 19. Flow builder

### Phase 1 triggers

- Contact created
- List or segment entry
- Tag added
- Form submitted
- Manual enrollment

### Phase 1 actions

- Send email
- Send SMS
- Delay
- If/else branch
- Update contact
- Add/remove tag or list membership
- Goal/end

### Runtime requirements

- Draft, published, paused and archived states
- Versioned publication and validation
- Re-enrollment and frequency limits
- Test-contact simulation
- Per-contact execution state
- Idempotent nodes and retries with backoff
- Dead-letter handling
- Rate and cost controls
- Searchable run logs and errors

The visual builder is desktop-first. Mobile supports monitoring, pausing and safe approvals rather than full graph editing.

---

# Train F — PR and publisher network

## 20. PR workspace

- PR dashboard and performance summary
- Create a release from a campaign or standalone brief
- Draft, development, review, approval, distribution and reporting states
- Market, outlet and package selection
- Content upload and structured order details
- Payment and invoice linkage
- Status timeline and notifications
- Distribution evidence and exportable reports

## 21. Publisher marketplace

Publisher profiles contain publication identity, markets, categories, a core offer, verified metrics, reviews, turnaround expectations, content rules and optional paid add-ons.

Add-ons remain optional, individually priced and visibly separate from the base offer. Cart totals update before checkout.

## 22. Publisher operations

- Receive and review requests
- Accept, reject or request changes
- Publish and submit evidence
- Track performance
- View earnings and payouts
- Receive operational notifications

A WordPress plugin may later support request intake, publishing assistance and analytics; it is not required for the first web release.

## 23. PR intelligence expansion

Later releases may add journalist and outlet graphs, earned-media outreach, pitch assistance, monitoring, sentiment, crisis signals, custom reporting, SEO opportunities and LLM visibility.

Competitor copy and screenshots are research references only, not Xinng requirements or approved copy.

---

# Train G — Social, content and task distribution

## 24. Social publishing

- Authorized connected accounts
- Draft, preview, schedule and publish
- Calendar and queue
- Media library
- Platform-specific validation
- Failure recovery
- Engagement and performance analytics

Only networks with authorized production access appear.

## 25. Content engine

AI may assist with press releases, journalist pitches, LinkedIn posts, X threads, SEO articles, newsletter copy and crisis-response drafts. Generated content requires review before publication, with inspectable sources where factual accuracy matters.

## 26. Nano-tasker

- Campaign objective, instructions and eligibility
- Supported platforms and task types
- Budget, reward, geography and participant limits
- Application or assignment
- Evidence submission
- Review, approval, rejection and disputes
- Spend and completion analytics
- Authorized payout records

The system prohibits fake reviews, deceptive engagement, artificial impressions and platform-policy violations.

---

# Train H — Intelligence

## 27. Analytics foundation

Every module uses a shared event envelope with event name/version, timestamp, actor, organization, source, campaign, object identifiers and privacy-safe session context.

Reporting distinguishes verified transactions, observed activity, attributed outcomes, forecasts and AI-generated estimates.

## 28. Narrative and monitoring

Planned capabilities include daily narrative snapshots, explainable narrative scores, opportunity discovery, media/social monitoring, spike and crisis alerts, competitor comparison, SEO opportunity indices and LLM visibility.

Insights retain source provenance, collection time, confidence and usage rights.

## 29. Controlled AI architecture

- Provider-neutral adapters
- Prompt and model versioning
- Cost, latency and quality telemetry
- Fallback policy
- Validated structured output
- Human confirmation for extracted or material data
- User-scoped action permissions
- AI action logs
- No autonomous financial authorization

RAG and vector search enforce organization and document permissions during retrieval.

---

# Train I — Events

## 30. Product definition

Xinng Events is a modular event operating system for planning, commerce, conference operations, sponsorship, procurement, financing, attendee experience and access.

Every module references one canonical event record. Event Tracker works independently of ticketing; Conference OS can operate with imported external registration data.

## 31. Delivery milestones

| Milestone | Scope |
| --- | --- |
| E0 | Event workspace, tasks, milestones, readiness and event-day operations |
| E1 | Event pages, registration, tickets, verified payments and check-in |
| E2 | Marketplace, print QR, walk-in sales and offline validation |
| E3 | Programmes, speakers, rooms, exhibitors and SponsorMatch |
| E4 | Vendors, budgets, RFQs, contracts, purchase orders and delivery |
| E5 | Attendee app, networking, passes, seating and venue commerce |
| E6 | Institutional Event Fund and controlled revenue participation |
| E7 | Native and approved external advertising |
| E8 | OSINT, sponsor intelligence and personal event management |
| E9 | Organizer and attendee AI assistance with controlled actions |
| E10 | High-speed QR, NFC, RFID and professional access integrations |

## 32. Events financial rules

Forecast revenue, proposed/contracted/invoiced/paid sponsorship, sponsorship receivables, in-kind value, funding offers/commitments/disbursements, vendor commitments/payments, cleared receipts and distributions must remain distinct.

No production financing release occurs before legal classification, eligible counterparties, approved contracts, KYC/AML controls where applicable, authorized settlement, disputes, reconciliation and risk allocation are defined.

The first commercial release is Event Tracker plus a professional-conference pilot. Financing and access hardware require separate legal or physical pilots. The detailed Sprint 0–20 backlog belongs in the Events tracker instead of being repeated here.

---

# Train J — Creator tools

## 33. Creator Revenue Calculator

The first creator tool explains what is missing and whether current pace can qualify; qualified creators receive an estimated earnings range.

### MVP

- One continuous calculator
- Optional X handle and complete manual mode
- Followers, verified followers, impressions and posts per week
- Optional actual 90-day impressions and country
- Separate qualified-impression and original-content assumptions
- Versioned platform rules
- Qualification gaps and sustainable velocity analysis
- Rolling-window timeline logic
- Reverse posting-frequency and reach calculations
- Live scenario controls
- Range-based earnings with disclaimer
- 1200×675 Xinng share cards
- Random-token public results exposing selected data only
- Anonymous use and analytics

### Expansion

- Multi-screenshot upload
- Provider-neutral vision extraction
- Confidence per value and editable confirmation
- Instagram and TikTok adapters
- Unified cross-platform revenue snapshot

`XingPRD.md` remains the detailed calculator specification; this document defines its platform relationship.

---

# Train K — Commerce and finance

## 34. Credits, subscriptions and checkout

- Products, plans and credit packages
- Transparent price and currency
- Provider-neutral checkout
- Verified server-side payment results
- Idempotent webhooks
- Credit ledger rather than balance-only accounting
- Receipts and transaction history
- Refund and reversal records
- Subscription lifecycle and verified entitlements

Provider-specific payments map to one internal transaction model.

## 35. Financial controls

- Immutable external references
- Separate pending, verified, failed, reversed and refunded states
- Reconciliation reports
- Approval/execution role separation
- Audited manual adjustments
- Currency on every amount
- Integer minor units or documented decimals
- No trust in client-supplied totals or success flags

---

# Train L — Administration and operations

## 36. Admin console

Authorized administrators manage users, organizations, plans, credits, campaign health, PR jobs, payment exceptions, providers, templates, abuse reports, failed jobs and feature flags.

Impersonation, data export and financial adjustment require strong authorization and audit logs.

## 37. Reliability and observability

- Structured application logs
- Background-job state and retries
- Provider request identifiers
- Error aggregation and health checks
- Queue depth and failure alerts
- Delivery and webhook monitoring
- Analytics freshness
- Backup and restore verification

---

## 38. Shared data architecture

### Platform

`users`, `organizations`, `organization_members`, roles, permissions, `audit_logs`, files, notifications and email logs.

### Presence

Pages, blocks, links, short links, QR codes, campaigns, views, clicks and scans.

### Audience and communications

Contacts, contact methods, companies, lists, segments, memberships, forms, submissions, campaigns, messages, conversations, templates and suppressions.

### Automation

Flows, versions, nodes, connections, enrollments, runs and logs.

### Commerce

Products, configurable plans and entitlements, subscriptions, separate subscription and purchased-credit ledgers, usage events, rate cards, reservations, payments, invoices, refunds, payout obligations and reconciliation.

### Specialist domains

PR/publisher records, social posts, intelligence sources, canonical events, creator rules/calculations, Nano profiles, missions, claims, evidence, disputes, contracts, deliverables, reward obligations, XP events, guilds and seasons.

Physical names may differ, but ownership, foreign keys, timestamps, statuses and audit expectations stay consistent.

---

## 39. API and integration standards

- Version public APIs.
- Authenticate and authorize every non-public operation.
- Use idempotency keys for payments, webhooks and repeatable external actions.
- Validate and normalize input server-side.
- Use signed callbacks where supported.
- Keep provider credentials outside source control.
- Use adapters for email, SMS, payments, social, AI and storage.
- Record provider identifiers and failures without exposing secrets.
- Apply user, organization, risk and provider rate limits.
- Document retention, deletion and replay behavior.

---

## 40. Experience standards

- Mobile-first responsive behavior
- Keyboard access and visible focus
- Semantic labels and screen-reader support
- Clear empty, loading, error, success and denied states
- Confirmation before destructive or externally visible actions
- Plain-language financial and AI disclosures
- Consistent Xinng design system
- Autosave and draft recovery where data loss is likely
- No critical workflow dependent on hover alone

---

## 41. Security, privacy and compliance

- HTTPS in production
- Secure session cookies and current password hashing
- CSRF protection and output escaping
- File validation and malware controls
- Least-privilege data/provider access
- Appropriate encryption in transit and at rest
- Consent, suppression and unsubscribe enforcement
- Data export and deletion workflows
- Configurable retention
- Privileged-action audit trails
- Incident response
- Legal and KYC/AML review before regulated financing
- Opt-in networking and privacy-aware analytics

---

## 42. Delivery sequence

### Release 0 — Stabilize the existing foundation

- Authentication and workspace shell
- Pages, short links and QR
- Credits and current payments
- Dashboard and analytics baseline
- Transactional communications
- Migration discipline and critical tests

### Release 1 — Audience and engagement core

- Contacts MVP
- Import/export and deduplication
- Lists and forms
- Campaign records
- Email campaigns and reporting
- Inbox foundation
- Flow Phase 1

### Release 2 — Growth products

- PR and publisher marketplace MVP
- Social publishing foundation
- Nano-tasker pilot
- Creator Calculator Stage 0
- Event Tracker and conference pilot

### Release 3 — Intelligence and expansion

- Media and social monitoring
- Journalist and SEO intelligence
- Creator screenshot extraction and platform adapters
- Advanced Events modules
- AI assistance with approval controls

### Release 4 — Enterprise and regulated operations

- Enterprise roles, reporting and integrations
- Event funding after legal approval
- High-volume venue-access pilots
- Advanced narrative and LLM visibility

Release labels express dependency order, not fixed calendar promises.

---

## 43. Definition of done

A feature is complete only when it has:

- Working backend and responsive interface
- Required migration or configuration
- Server-side permission enforcement
- Validation and error handling
- Audit and analytics events
- Accessibility review
- Critical-path automated tests
- Operational logging
- Documentation
- Deployment verification
- Demonstrable end-to-end journey

Financial, messaging, publishing and AI features also require applicable compliance and provider checks.

---

## 44. Portfolio success measures

### Activation

- Account-to-first-value conversion
- Anonymous-tool-to-account conversion
- Time to first Page, QR, contact, campaign, Flow, PR job or event workspace

### Engagement

- Active organizations and repeat use by train
- Contacts added and engaged
- Campaign and Flow completion
- Return visits to creator and event tools

### Outcomes

- Visits, clicks and QR scans
- Qualified leads and attributed conversions
- Message delivery and response
- PR distribution and measurable coverage
- Event readiness and attendance
- Verified commerce volume

### Quality

- Error and provider-failure rates
- Delivery and webhook latency
- Duplicate transaction rate
- Accessibility defects
- Support volume by workflow
- Abuse, complaint and unsubscribe rates

---

## 45. Xinng Nano ecosystem

Xinng Nano is the trusted-work and creator-commerce train spanning paid digital and physical missions, social/content creation, event assignments, and professional or business tasks. A mission defines its campaign, brief, eligibility, geography, capacity, deadline, evidence rules, review policy, funded reward and dispute path. Claims must be concurrency-safe. Unsafe, deceptive, illegal or platform-policy-violating missions are prohibited.

Creator profiles combine portfolios, connected social accounts, audience and performance evidence, eligibility and rate cards. Brands can discover and match creators, issue briefs, agree contracts, review content and deliverables, measure outcomes and settle earnings. Mega-influencer management extends this model to celebrities, agencies, talent rosters, private negotiations, ambassador contracts, appearances, licensing, exclusivity, manager commissions and enterprise reporting.

One canonical campaign can coordinate mega-influencer contracts, influencer collaborations and Nano missions. It reuses Campaigns, Analytics, Contacts, Pages, Events, Advertising and Finance, and prevents double counting of spend, commitments, deliverables, reach, conversions or payouts.

Nano World adds optional player profiles, avatars, mission districts, XP, levels, achievements, reputation, guilds, team missions, leaderboards and seasonal challenges. Its first experience is lightweight, responsive and 2D. Core work remains usable without the game layer. XP and reputation are non-withdrawable progression records.

A possible fixed-supply XING token may later support optional Nano utility, cross-product benefits, third-party merchant acceptance, external wallets and approved stablecoin conversion. Treasury, vesting, distribution, smart-contract security, regulation and a controlled pilot require separate approval. Xinng must work without a native token. This PRD does not authorize issuance, and guaranteed rewards cannot be replaced by speculative tokens. `nano.md` is the detailed source of truth.

## 46. Subscription, credit and payment economy

Xinng uses subscriptions for feature access and credits for metered usage. Exhausting credits does not deactivate an active subscription; entitled non-metered features remain available.

| Plan | Monthly price | Credit policy |
| --- | ---: | --- |
| Nigeria Starter | ₦500 | Configurable, very limited; verified Nigerian billing eligibility required |
| Basic | $5 | Configurable small allowance |
| Plus | $15 | Configurable moderate allowance |
| Pro | $50 | Configurable high allowance |
| Enterprise | $750 | Configurable shared pool and five included seats |

Additional Enterprise seats cost $50/month each and add user access only. They do not automatically add another 10× Pro allowance. Enterprise allowances, per-user limits and contractual caps remain configurable. Final allocations require provider-cost and target-margin validation.

Subscription credits are granted per billing cycle and reset or expire under disclosed terms. Purchased credits are tracked separately. Consumption uses subscription credits first, then purchased credits. Plans, prices, allowances, rate cards, thresholds and entitlements are administered as versioned configuration rather than hardcoded values.

Metered operations include AI generation and document analysis, image generation, agents and tool calls, OSINT, extraction, large research, premium automation and variable-cost bulk services. Ordinary management or viewing of existing Pages, QR codes, event dashboards and completed reports does not automatically consume credits.

Every metered operation has an identifiable usage event. Expensive work supports an estimate and atomic reservation; completion finalizes the actual debit and releases unused reservation; qualifying failure restores credits. Deductions are idempotent and concurrency-safe. Rate cards use actual provider costs, currency conversion, infrastructure overhead and target margin. A credit has no assumed one-to-one naira or dollar value.

Paystack and NOWPayments remain integrated. Pandascrow remains planned until production verification. Provider-neutral adapters support subscriptions, top-ups, one-time purchases, approved stablecoins, creator/Nano payouts, event tickets, vendor payments, approved escrow, refunds and reconciliation.

The system keeps subscription credits, purchased credits, XP, real-money earnings and payout obligations, stablecoin records and any future XING token holdings separate. Credits and XP are not withdrawable money. Creator earnings and mission compensation are real financial obligations.

## 47. Revised delivery sequence and measures

- Release 0 audits existing payment and credit behavior, preserves Paystack and NOWPayments, and establishes configurable plans, entitlements and ledgers.
- Release 1 adds rate cards, usage events, reservations, renewal allocation, top-ups, notifications and reconciliation.
- Release 2 delivers Nano N0–N4: policy, identity, funded missions, execution, evidence, disputes and payouts.
- Release 3 delivers Nano N5–N9: gamification, influencer and mega-influencer management, unified campaigns and the lightweight 2D Nano World.
- Release 4 may deliver stablecoin support and token assessment; a controlled token pilot remains conditional on legal, security, technical and commercial approval.

Success measures add funded mission completion, evidence acceptance, dispute rate, payout timeliness, creator/brand repeat use, subscription retention, credit-estimate accuracy, provider cost margin, reservation recovery and reconciliation exceptions. Fiat volume, internal credits, XP, stablecoins and tokens are reported independently.

## 48. Document governance

This file is the portfolio-level source of truth.

- Detailed module PRDs may define calculations, screen copy or specialist workflows without repeating the platform architecture.
- If historical specifications conflict, this structure and the newest explicitly approved module PRD take precedence.
- Competitor examples, copied website text and screenshots are research only.
- Embedded base64 images do not belong in this Markdown file. Approved assets belong in the project asset directory and should be referenced by path.
- Duplicate feature descriptions belong under the owning product train.
- New modules must declare shared-service dependencies, permissions, analytics, commercial model and release boundary.

### Supporting specifications

- `XingPRD.md` — Creator Revenue Calculator
- `event.md` — Xinng Events detailed roadmap
- `nano.md` — Xinng Nano, influencer, gamification and token proposal
- `comms.md` — transactional email and in-app notifications
- `rebuild_xinng_db.sql` — current database rebuild reference

---

## Final product definition

**Xinng is a modular growth operating system that connects presence, audience, communications, distribution, commerce and intelligence through one shared workspace.**

Its advantage should come from connected execution data and reusable infrastructure—not from presenting a long list of disconnected features.
