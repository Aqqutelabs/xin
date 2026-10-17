# **XING EVENTS**

## **Product Requirements Document (PRD)**

### **Version 3.0 — Sprint 0 to Sprint 20**

**Status:** Final working product specification  
**Date:** 16 October 2026  
**Product Owner:** Xing  
**Platform:** xin.ng  
**Core Architecture:** PHP, MySQL/MariaDB-compatible database, HTML5, JavaScript, responsive web, PWA  
**Primary Integrations:** Pandascrow, approved payment providers, authorized advertising platforms and external event software

---

# **PART 1 — PRODUCT DEFINITION**

## **1\. Executive Summary**

Xing Events is an event management, commerce, intelligence and financing ecosystem.

The platform enables organizers to plan events, register participants, coordinate suppliers, manage exhibitors, attract sponsors, secure eligible institutional financing, promote events, execute physical operations and analyze results.

The system serves seven primary groups:

1. **Event Organizers:** Plan, execute and measure events.  
2. **Attendees:** Discover, register, attend and network.  
3. **Exhibitors:** Participate in trade shows and professional exhibitions.  
4. **Event Suppliers:** Supply goods and services required to execute events.  
5. **Sponsors:** Discover suitable events, purchase commercial sponsorship opportunities and measure outcomes.  
6. **Institutional Funders:** Finance approved event expenditure under negotiated revenue participation or other legally permitted contracts.  
7. **Event Staff and Administrators:** Coordinate operations, control access, process authorized transactions and oversee delivery.

The product must work for organizers running a small event and for professional associations managing large multi-day conferences.

### **1.1 Product Positioning**

**Xing Events: One operating system for everything behind an event.**

Its five interconnected business functions are:

* **Plan:** Event tracking, programmes, budgets and logistics.  
* **Commercialize:** Tickets, exhibitors, sponsorship, advertising and onsite sales.  
* **Finance:** Institutional event funding and controlled supplier payments.  
* **Operate:** Vendors, passes, venues, attendees, speakers and networking.  
* **Understand:** Analytics, OSINT, financial reporting and AI assistance.

### **1.2 Strategic Priorities**

1. Produce a usable organizer product from Sprint 0\.  
2. Generate revenue before completing the full roadmap.  
3. Support professional conferences without mandatory ticketing migration.  
4. Keep sponsorship distinct from financing.  
5. Finance identifiable event expenditure rather than unrestricted organizer spending.  
6. Use verified financial transactions wherever monetary allocations depend on them.  
7. Build reusable Xing services instead of parallel systems.  
8. Minimize dependence on unapproved external APIs.  
9. Integrate commercial hardware rather than manufacture devices prematurely.  
10. Make all major modules independently licensable.

---

# **PART 2 — PRODUCT ARCHITECTURE**

## **2\. Core Products**

| Product | Function |
| ----- | ----- |
| Xing Event Tracker | Planning, assignments, timelines, budgets and execution tracking |
| Xing Events | Event pages, tickets, registration and check-in |
| Xing Discover | Public event discovery marketplace |
| Xing Conference OS | Programmes, speakers, exhibitors, networking and venue operations |
| Xing SponsorMatch | Sponsor discovery, matching, proposals, contracts and sponsorship reporting |
| Xing Event Procurement | Vendor sourcing, quotations, POs and delivery control |
| Xing Event Fund | Private institutional funding and revenue participation administration |
| Xing Ads | Advertising through Xing and approved external networks |
| Xing Intelligence | Event OSINT, analytics, AI and commercial intelligence |
| Xing Access | Digital passes, QR, NFC, RFID and venue admission |

## **3\. Product Principles**

### **3.1 Modular by Design**

A customer must be able to purchase Conference OS without ticketing, SponsorMatch without financing, or Event Tracker without publishing a public event.

### **3.2 Single Event Record**

All modules reference a canonical event record.

The same event should not become different disconnected records in Tracker, Tickets, Sponsors, Vendors and Funding.

### **3.3 Reusable Xing Infrastructure**

Use existing:

* Xing authentication and account management.  
* Xing Pages.  
* QR and short-link infrastructure.  
* Organization profiles.  
* Email and in-app notifications.  
* Media storage.  
* Existing payment integrations where appropriate.

### **3.4 Financial Separation**

Ticket revenue, sponsorship revenue, exhibitor payments, vendor sales, funding commitments and actual financing distributions are different financial concepts.

All must remain separately identifiable.

### **3.5 Permission-Based Operations**

Every user receives only the permissions needed for their assigned responsibilities.

Financial authorization, vendor delivery approval and payment execution should support separation of duties.

---

# **PART 3 — USER TYPES**

## **4\. Primary Roles**

| Role | Responsibilities |
| ----- | ----- |
| Organizer Owner | Owns event and appoints personnel |
| Event Manager | Coordinates planning and execution |
| Programme Manager | Manages speakers, sessions and agendas |
| Exhibitor Manager | Handles exhibitors and booth allocations |
| Sponsorship Manager | Manages proposals, contracts and sponsor benefits |
| Procurement Manager | Manages vendors, quotations and POs |
| Finance Manager | Controls authorized budgets, payments and reconciliation |
| Check-In Staff | Verifies tickets and passes |
| Attendee | Registers, attends and networks |
| Speaker | Manages assigned programme obligations |
| Vendor | Submits offers and delivers contracted services |
| Exhibitor | Manages exhibition participation |
| Sponsor | Manages commercial sponsorship commitments |
| Institutional Funder | Reviews authorized financing opportunities and agreements |
| Platform Administrator | Oversees system governance and authorized administrative functions |

A user may hold multiple roles.

Permissions must be scoped to the correct event, organization, transaction or document.

---

# 

# **PART 4 — DETAILED SPRINT REQUIREMENTS**

# **SPRINT 0 — EVENT TRACKER**

**Priority:** Immediate  
**Product:** Xing Event Tracker  
**Purpose:** Deliver the first independently usable event planning and execution tool.

### **0.1 Event Workspace**

Organizers can create an event workspace without creating tickets or publishing a public event page.

Fields:

* Event name.  
* Organizer.  
* Description.  
* Event type.  
* Start and end dates.  
* Venue or proposed location.  
* Event objectives.  
* Expected attendance.  
* Responsible event manager.  
* Planning status.

### **0.2 Workstreams**

Organize tasks into workstreams such as:

* Programme and speakers.  
* Sponsorship.  
* Exhibitions.  
* Vendors and procurement.  
* Finance.  
* Marketing.  
* Logistics.  
* Ticketing and registration.  
* Technical production.  
* Security and safety.  
* Venue preparation.  
* Event-day execution.  
* Post-event closure.

Organizers can create custom workstreams.

### **0.3 Task Management**

Every task contains:

* Task title.  
* Description.  
* Assigned user.  
* Priority.  
* Start date.  
* Due date.  
* Status.  
* Dependencies.  
* Attachments.  
* Comments.  
* Completion evidence.  
* Activity history.

**Task statuses:** To Do, In Progress, Blocked, Under Review, Done, Cancelled.

### **0.4 Tracker Views**

* List.  
* Kanban.  
* Calendar.  
* Timeline.  
* Milestones.  
* Assigned-to-me.  
* Overdue tasks.

### **0.5 Event Readiness Dashboard**

Display:

* Overall task completion.  
* Tasks due soon.  
* Overdue tasks.  
* Blocked tasks.  
* Milestones.  
* Workstream readiness.  
* Open issues.  
* Days to event.  
* Recent team activity.

Overall completion must be calculated from actual task states, with configurable weighting if required.

### **0.6 Milestone Tracking**

Examples:

* Venue secured.  
* Programme approved.  
* Speakers confirmed.  
* Sponsorship closed.  
* Major vendors contracted.  
* Exhibitors finalized.  
* Registration ready.  
* Venue installation complete.  
* Event ready.  
* Event concluded.

### **0.7 Event-Day Operations**

* Operational checklists.  
* Incident reporting.  
* Assigned responsibilities.  
* Time-stamped operational updates.  
* Urgent blockers.  
* Resolution tracking.  
* Shift handover notes.

### **0.8 Collaboration**

* Invite team members.  
* Assign workstream permissions.  
* Comment on tasks.  
* Attach documents.  
* Send reminders.  
* View activity history.  
* Import/export tasks using CSV.

### **Acceptance Criteria**

* An organizer can create an event workspace.  
* Team members can be assigned tasks.  
* Dependencies, deadlines and statuses are enforced.  
* The dashboard accurately summarizes work.  
* Changes are traceable.  
* The tracker works independently of ticketing.  
* Future Xing modules can associate records with tracker tasks.

**Deliverable:** A functioning Event Tracker that can immediately be used to manage a real conference.

---

# **SPRINT 1 — EVENT CREATION, TICKETING AND ANALYTICS**

**Objective:** Complete the basic event transaction lifecycle.

### **Features**

**Event creation**

* Native Xing Event Page.  
* Title, description and cover.  
* Physical, virtual and hybrid events.  
* Event dates, times and timezone.  
* Venue address.  
* Event categories.  
* Publish, edit, cancel and duplicate.

**Ticketing**

* Multiple ticket categories.  
* Free and paid tickets.  
* Ticket quantity limits.  
* Registration form.  
* Ticket selection.  
* Attendee details.  
* Registration confirmation.  
* Unique digital ticket.  
* Secure ticket QR.

**Payments**

* Pandascrow checkout.  
* Payment reference.  
* Payment verification.  
* Payment status.  
* Payment failure recovery.  
* Verified paid registration.  
* Transaction reconciliation.

**Check-In**

* Mobile camera scanner.  
* Validity check.  
* Duplicate prevention.  
* Ticket revocation.  
* Scan history.

**Analytics**

* Event visits.  
* Ticket selections.  
* Registration starts.  
* Completed registrations.  
* Ticket sales.  
* Payment conversion.  
* Revenue.  
* Check-ins.  
* Attendance rates.

### **Integration with Tracker**

Creating an event can automatically create planning tasks using optional event templates.

The event workspace remains available from the organizer dashboard.

### **Acceptance Criteria**

* Events can be published and shared.  
* Paid registration requires a verified successful payment.  
* Tickets can be issued, accessed and scanned.  
* Transactions are not duplicated by webhook retries.  
* Organizers have accurate registration and revenue dashboards.

**Deliverable:** Basic event commerce application.

---

# 

# 

# 

# **SPRINT 2 — PUBLIC LANDER, EVENTS MARKETPLACE AND PHYGITAL REGISTRATION**

**Objective:** Establish public acquisition and basic physical-venue commerce.

### **Public Landing Page**

`xin.ng/event`

Contents:

* Create Event CTA.  
* Event product overview.  
* Ticketing and check-in explanation.  
* Organizer dashboard preview.  
* Conference OS capabilities.  
* SponsorMatch preview.  
* Event Fund preview.  
* Enterprise event enquiries.

### **Events Marketplace**

`xin.ng/events`

Features:

* Browse published events.  
* Search.  
* City filtering.  
* Category filtering.  
* Date filtering.  
* Free/paid filtering.  
* Featured events.  
* Organizer profiles.  
* Event cards.  
* Ticket purchase links.

### **Large Printable Event QR**

Provide A4, A3, A2, A1 and custom-size event QR templates.

Outputs:

* PDF.  
* SVG.  
* High-resolution image.

Configurable contents:

* Event title.  
* Event date.  
* Venue.  
* Organizer logo.  
* Registration CTA.  
* Large QR.  
* Short URL.

Support distinct QR campaign identifiers for attribution.

### **Event Confirmation**

**Organizer**

* Publication confirmation.  
* Event URL.  
* Event reference.  
* QR download.  
* Share actions.

**Attendee**

* Registration confirmation.  
* Payment confirmation where applicable.  
* Digital ticket.  
* QR credential.  
* Email.  
* Calendar entry.

### **Phygital Ticket Sales**

* Walk-in registration.  
* Staff-assisted purchases.  
* Dynamic payment QR.  
* Approved card/POS integrations.  
* Bank transfer verification.  
* Authorized cash recording.  
* Transaction receipts.  
* Immediate ticket issuance.  
* Sales by staff and channel.  
* Daily reconciliation.

### 

### **Offline Check-In**

Support pre-synchronized ticket records and controlled offline validation.

Offline scans must be queued for synchronization and conflicting admissions flagged.

### **Acceptance Criteria**

* Events appear publicly.  
* Users can discover events by location.  
* Organizers can print event registration QR codes.  
* A walk-in customer can purchase and receive a ticket.  
* Onsite electronic payments are verified.  
* A configured scanner can validate cached tickets offline.

**Deliverable:** Xing public event presence, marketplace and venue registration.

---

# 

# 

# 

# 

# 

# 

# 

# **SPRINT 3 — CONFERENCE COMMAND CENTRE AND PROGRAMME MANAGEMENT**

**Objective:** Support professional conferences independently of Xing ticketing.

### **Conference Command Centre**

* Event readiness summary.  
* Open tasks from Event Tracker.  
* Programme status.  
* Speaker confirmations.  
* Upcoming milestones.  
* Schedule conflicts.  
* Operational alerts.

### **Programme Builder**

* Multiple days.  
* Conference tracks.  
* Rooms.  
* Sessions.  
* Session categories.  
* Start and end times.  
* Moderators.  
* Speakers.  
* Workshops.  
* Breaks.  
* Agenda version control.

### **Speaker Management**

* Speaker profile.  
* Bio and photo.  
* Organization and title.  
* Session assignment.  
* Confirmation status.  
* Presentation submission.  
* Speaker requirements.  
* Speaker itinerary.  
* Automated reminders.

### **Schedule Validation**

Detect:

* Speaker double-booking.  
* Room conflicts.  
* Overlapping resource assignments.  
* Missing speakers.  
* Missing session information.  
* Unconfirmed sessions.

### **Publishing**

* Draft agenda.  
* Approved agenda.  
* Public agenda.  
* Change log.  
* Affected-speaker notifications.  
* Attendee schedule updates.

### **External Integration**

Support importing:

* Attendees.  
* Speakers.  
* Programmes.  
* Sessions.

CSV is mandatory; provider-specific APIs are optional integrations.

### **Acceptance Criteria**

* A multi-day event programme can be managed.  
* Programme changes are traceable.  
* Critical conflicts are identified.  
* Authorized updates reach relevant participants.  
* The module functions without Xing ticketing.

**Deliverable:** Conference programme and speaker-management system.

---

# 

# 

# **SPRINT 4 — EXHIBITOR MANAGEMENT AND XING SPONSORMATCH**

**Objective:** Manage exhibitions and build a sponsorship marketplace.

## **Module A — Exhibitor Management**

### **Exhibitor Profiles**

* Company registration.  
* Company logo.  
* Business description.  
* Industry.  
* Products and services.  
* Staff details.  
* Exhibitor documents.  
* Approval status.

### **Booth Management**

* Exhibition halls.  
* Booth numbers.  
* Booth categories.  
* Booth dimensions.  
* Floor plan upload.  
* Allocation.  
* Availability.  
* Exhibitor fees.  
* Booth readiness checklist.

### **Exhibitor Operations**

* Required documents.  
* Branding requirements.  
* Electricity/internet requirements.  
* Exhibitor deadlines.  
* Setup/dismantling schedules.  
* Staff badge allocations.  
* Exhibitor directory.

## 

## 

## **Module B — Xing SponsorMatch**

### **Sponsor Accounts**

Sponsors can register as organizations and create commercial profiles.

Required fields:

* Organization name.  
* Industry.  
* Location.  
* Brand description.  
* Sponsorship interests.  
* Target audiences.  
* Preferred event categories.  
* Preferred geographical markets.  
* Typical activation requirements.  
* Indicative sponsorship budget range.  
* Decision-maker contact.  
* Authorized representatives.

Budget information can remain private and only be disclosed through authorized matching or negotiations.

### **Event Sponsorship Profile**

An organizer specifies:

* Event category.  
* Location and dates.  
* Expected attendance.  
* Verified historical attendance.  
* Audience profile.  
* Industry sectors.  
* Sponsorship requirements.  
* Available advertising inventory.  
* Exhibitor opportunities.  
* Networking opportunities.  
* Sponsorship packages.  
* Pricing.  
* Evidence from previous events.

Clearly distinguish verified past results from future projections.

### **Sponsorship Packages**

Organizers can create packages such as:

* Title Sponsor.  
* Platinum Sponsor.  
* Gold Sponsor.  
* Silver Sponsor.  
* Session Sponsor.  
* Workshop Sponsor.  
* Networking Sponsor.  
* Lanyard/Badge Sponsor.  
* Hospitality Sponsor.  
* Technology Sponsor.  
* Media Partner.

Each package contains:

* Price.  
* Availability.  
* Included benefits.  
* Exclusivity provisions.  
* Deliverables.  
* Deadlines.  
* Reporting requirements.

### **Sponsor Matching**

Match based on:

1. Industry relevance.  
2. Audience compatibility.  
3. Geographic market.  
4. Event scale.  
5. Commercial objectives.  
6. Sponsorship budget.  
7. Available benefits.  
8. Organizer credibility.  
9. Historical performance.  
10. Sponsor preferences.

Initial recommendations can be rules-based and manually reviewed.

Do not require AI for the first working version.

### **Sponsorship Marketplace**

**Organizer functions**

* Publish opportunities.  
* Search sponsors.  
* Request introductions.  
* Send proposals.  
* Track responses.  
* Negotiate terms.

**Sponsor functions**

* Browse suitable events.  
* Filter opportunities.  
* Save events.  
* Request proposals.  
* Make offers.  
* Evaluate package benefits.  
* Track commitments.

### **Sponsorship Pipeline**

Prospect → Matched → Contacted → Interested → Proposal Sent → Negotiation → Contracted → Invoiced → Paid → Fulfilment → Closed.

### **Contract and Payment Tracking**

* Sponsorship proposal.  
* Contract.  
* Agreement value.  
* Payment schedule.  
* Invoice.  
* Receipt status.  
* Sponsor benefits.  
* Fulfilment checklist.

### **Sponsor Fulfilment**

Track delivery of:

* Logos and branding.  
* Stage recognition.  
* Sponsored sessions.  
* Booth space.  
* Digital placements.  
* Sponsored announcements.  
* Lead-generation opportunities.  
* Speaking opportunities.  
* Networking activations.  
* Other contractually approved benefits.

Allow photographic, documentary and system-generated proof.

### **Sponsor Analytics**

* Sponsor profile views.  
* Proposal acceptance.  
* Commitments.  
* Paid sponsorship value.  
* Branding exposure where measurable.  
* Booth engagement.  
* Session engagement.  
* Consented lead captures.  
* Qualified meetings.  
* Fulfilment completion.  
* Campaign results.

### **Revenue Model**

Potentially:

* Sponsorship success fees.  
* Sponsor intelligence subscriptions.  
* Organizer sponsorship tools.  
* Premium sponsor reporting.  
* Managed sponsorship sourcing.

Fees must be clearly disclosed and contractually agreed.

### **Acceptance Criteria**

* Organizers can create sponsorship packages.  
* Sponsors can create verified profiles.  
* Matching suggestions use structured criteria.  
* Parties can exchange and track proposals.  
* Sponsorship agreements and payment states are recorded.  
* Deliverables and proof of fulfilment can be tracked.  
* Unpaid proposals do not appear as cash revenue.  
* Sponsored inventory cannot be sold twice where exclusivity applies.

**Deliverable:** Exhibitor management and a working, initially assisted SponsorMatch marketplace.

---

# 

# 

# **SPRINT 5 — EVENT VENDOR REGISTRY, BUDGET AND PROCUREMENT**

**Objective:** Manage suppliers and sourcing requirements.

### **Vendor Categories**

* Venue.  
* Catering.  
* AV.  
* Production.  
* Printing.  
* Decoration.  
* Security.  
* Logistics.  
* Equipment rental.  
* Internet.  
* Transportation.  
* Accommodation.  
* Other services.

### **Vendor Registry**

* Organization profile.  
* Company details.  
* Contact information.  
* Services.  
* Capabilities.  
* Portfolio.  
* Compliance records.  
* Payment beneficiary details.  
* Previous delivery records.  
* Verification status.

### **Event Budget**

* Budget categories.  
* Planned costs.  
* Approved costs.  
* Committed costs.  
* Actual costs.  
* Outstanding obligations.  
* Contingency.  
* Budget revisions.

### 

### **Sourcing**

* Procurement requests.  
* Technical specifications.  
* Vendor invitations.  
* RFQs.  
* Quotation uploads.  
* Comparative bid evaluation.  
* Approval.  
* Vendor selection.

### **Tracker Integration**

Approved procurement requests generate linked event tasks with owners, deadlines and document references.

### **Acceptance Criteria**

* Vendors can be onboarded.  
* Organizers can create and approve procurement requests.  
* RFQs can be issued.  
* Quotations can be compared.  
* Approved vendor awards update budget commitments.

**Deliverable:** Vendor sourcing and procurement system.

---

# 

# 

# 

# 

# 

# **SPRINT 6 — CONTRACTS, PURCHASE ORDERS AND VENDOR DELIVERY**

**Objective:** Complete the procurement-to-delivery workflow.

### **Purchase Orders**

* Generate PO from approved quotation.  
* Unique PO reference.  
* Supplier.  
* Line items.  
* Quantity.  
* Unit price.  
* Tax.  
* Terms.  
* Delivery date.  
* Delivery location.  
* Approval workflow.  
* PDF output.  
* Vendor acceptance.

### **Vendor Contracts**

* Scope of work.  
* Contract value.  
* Deliverables.  
* Milestones.  
* Payment terms.  
* Supporting documents.  
* Amendments.  
* Change orders.  
* Contract approval.

### **Delivery Management**

* Delivery schedules.  
* Vendor task assignments.  
* Progress submissions.  
* Proof of delivery.  
* Inspection.  
* Acceptance.  
* Rejection.  
* Rework.  
* Completion sign-off.

### **Supplier Payments**

* Invoice submission.  
* Invoice-to-PO validation.  
* Delivery-to-invoice validation.  
* Payment request.  
* Approval status.  
* Payment confirmation.  
* Outstanding balance.

### **Funding Readiness**

Each approved PO must be capable of being marked:

* Organizer-funded.  
* Sponsor-funded.  
* Institutionally funded.  
* Mixed-funded.  
* Funding required.

These are financing classifications, not evidence that actual funds exist.

### **Acceptance Criteria**

* Approved procurements create traceable POs.  
* Vendor obligations are linked to event tasks.  
* Deliverables require authorization before acceptance.  
* Invoices are reconciled against approved work.  
* Funding status is linked to specific approved obligations.

**Deliverable:** Complete vendor contracting and execution workflow.

---

# 

# 

# 

# 

# **SPRINT 7 — EVENT APP, ATTENDEE NETWORKING AND MEETINGS**

**Objective:** Provide a usable conference application.

### **Event App**

Mobile-first PWA, containing:

* Event Home.  
* Programme.  
* Speakers.  
* Exhibitors.  
* Sponsors.  
* Attendee Directory.  
* Networking.  
* Meetings.  
* Messages.  
* Announcements.  
* My Agenda.  
* My Pass.

### **Attendee Profiles**

Opt-in information:

* Photo.  
* Name.  
* Organization.  
* Role.  
* Bio.  
* Interests.  
* Professional links.

### **Networking**

* Attendee search.  
* Company filters.  
* Professional filters.  
* Connection requests.  
* Accept/decline.  
* Direct messaging.  
* Blocking/reporting.

### **Meeting Booking**

* Availability.  
* Meeting requests.  
* Acceptance.  
* Proposed locations.  
* Reminders.  
* Rescheduling.  
* Calendar integration hooks.

### **Event Community**

* Global event chat.  
* Session-specific chat.  
* Interest-based groups.  
* Moderation.  
* Announcement channels.

### **Acceptance Criteria**

* Authorized attendees can access the event app.  
* Profiles follow privacy permissions.  
* Meetings can be booked.  
* Messages are restricted to authorized participants.  
* Moderators can manage reported content.  
* Programme changes appear in personal schedules.

**Deliverable:** Conference networking and event app.

---

# 

# 

# 

# 

# 

# **SPRINT 8 — PASSES, ACCESS ENTITLEMENTS, SEATING AND PRINTING**

**Objective:** Control physical access and event privileges.

### **Pass Categories**

* Standard.  
* VIP.  
* Delegate.  
* Speaker.  
* Exhibitor.  
* Sponsor.  
* Media.  
* Staff.  
* All Access.

### **Access Privileges**

* Event entrance.  
* Specific dates.  
* Sessions.  
* Workshops.  
* Exhibition halls.  
* VIP lounges.  
* Backstage.  
* Staff areas.  
* Food.  
* Refreshments.  
* Special activities.

### **Entitlements**

Each privilege supports:

* Validity period.  
* Permitted locations.  
* Usage limit.  
* Redemption rules.  
* Audit history.  
* Revocation.

### **Seating**

* Venue layout.  
* Sections.  
* Rows.  
* Seats.  
* Reserved seating.  
* Seat selection.  
* Assignment.  
* Reassignment.  
* Availability.  
* Occupancy reporting.

### **Pass Printing**

* Badge templates.  
* Attendee information.  
* Photo where applicable.  
* QR code.  
* Access category.  
* Organizer branding.  
* Single printing.  
* Batch printing.  
* Reprint history.

### **Acceptance Criteria**

* Pass types enforce access rules.  
* Restricted areas reject invalid permissions.  
* Food entitlements cannot exceed configured usage limits.  
* Seats cannot be assigned twice.  
* Printed and digital credentials reference valid underlying passes.

**Deliverable:** Digital and physical event credential system.

---

# 

# 

# 

# **SPRINT 9 — PHYGITAL VENUE COMMERCE**

**Objective:** Manage physical event sales and redemptions.

### **Commercial Vendor Profiles**

* Food vendors.  
* Merchandise.  
* Services.  
* Exhibition retail.  
* Hospitality.

### **Venue Sales**

* Product catalog.  
* Prices.  
* Inventory.  
* Orders.  
* Digital receipts.  
* Vendor staff.  
* Settlement reporting.

### **Payment Channels**

* Dynamic payment QR.  
* Payment links.  
* Virtual POS.  
* Physical POS.  
* Bank transfer.  
* Authorized cash records.

### **Entitlement Redemption**

* Meals.  
* Refreshments.  
* Merchandise vouchers.  
* Workshop benefits.  
* Other configured privileges.

### **Analytics**

* Vendor sales.  
* Payment methods.  
* Vendor earnings.  
* Payment exceptions.  
* Redemptions.  
* Unused entitlements.  
* Venue revenue.

### **Acceptance Criteria**

* Orders are linked to their correct vendor.  
* Verified payments produce valid receipts.  
* Vendor and organizer money remain distinguishable.  
* Entitlements are correctly redeemed.  
* Financial transactions reconcile.

**Deliverable:** Event venue commerce system.

---

# 

# 

# 

# 

# 

# 

# 

# **SPRINT 10 — XING EVENT FUND I: INSTITUTIONAL FINANCING MARKETPLACE**

**Objective:** Build a private financing application and assessment system.

**Critical rule:** Xing Event Fund is not a retail crowdfunding product.

### **Eligible Funders**

Subject to applicable law and verification:

* Banks.  
* Licensed financial institutions.  
* Authorized investment firms.  
* Eligible institutional funds.  
* Qualified professional financing counterparties.

### **Funder Onboarding**

* Legal identity.  
* Regulatory status where relevant.  
* Authorized representatives.  
* Financing mandate.  
* Investment criteria.  
* Target event categories.  
* Funding limits.  
* Supporting documents.  
* Approval workflow.

### **Organizer Funding Application**

* Event information.  
* Organizer information.  
* Funding requirement.  
* Funding purpose.  
* Event budget.  
* Vendor POs.  
* Existing ticket sales.  
* Sponsorship contracts.  
* Exhibitor commitments.  
* Revenue history.  
* Expected event receipts.  
* Execution risks.

### **Funding Types**

The application can request financing against eligible event activity including:

* Verified ticket receipts.  
* Signed sponsor receivables.  
* Contracted exhibition revenue.  
* Approved event purchase orders.  
* A defined combination of eligible event revenue sources.

### **Assessment Dashboard**

Show:

* Confirmed revenue.  
* Contracted but unpaid revenue.  
* Forecast revenue.  
* Vendor commitments.  
* Budget shortfall.  
* Event readiness.  
* Organizer history.  
* Funding exposure.  
* Available supporting documents.

The dashboard must not combine forecasts with cleared revenue.

### **Funding Workflow**

Draft → Submitted → Verification → Under Review → Offer → Negotiation → Accepted/Declined.

### **Acceptance Criteria**

* Only approved financing counterparties can access opportunities.  
* Organizers can submit complete applications.  
* Funders can inspect permitted verified records.  
* Offers and decisions are auditable.  
* No public investment solicitation or live financing happens without legal authorization.

**Deliverable:** Institutional event funding origination and evaluation.

---

# **SPRINT 11 — XING EVENT FUND II: VENDOR-DIRECTED FUNDING**

**Objective:** Finance event execution through approved supplier obligations.

### **Funding Allocation**

Link financing to:

* Purchase orders.  
* Vendor contracts.  
* Approved milestones.  
* Verified invoices.  
* Specific budget lines.

### **Payment Models**

**Upfront vendor payment**

An approved funder pays a designated vendor according to contractual terms.

**PO-backed funding**

An approved PO receives committed financing with controlled payment conditions.

**Escrow funding**

An authorized provider holds financing proceeds under agreed release conditions.

**Milestone funding**

Payments are released after approved delivery stages.

### **Sponsor-Funded Vendor Procurement**

Sponsors may also support event delivery directly.

Examples:

* A technology sponsor finances venue internet.  
* A food sponsor supplies or pays for meals.  
* A mobility sponsor covers attendee transport.  
* An equipment sponsor provides event production equipment.

Such support must be recorded as cash sponsorship, direct payment or in-kind contribution, as applicable.

In-kind benefits are not distributable cash.

### **Operational Workflow**

1. Vendor obligation is approved.  
2. Funding allocation is agreed.  
3. Funding source is verified.  
4. Payment instructions are authorized.  
5. Vendor receives payment through an approved arrangement.  
6. Delivery proceeds.  
7. Evidence is submitted.  
8. Completion is approved.  
9. Financial records reconcile.

### **Controls**

* Beneficiary verification.  
* Approval thresholds.  
* Duplicate invoice checks.  
* Funding allocation limits.  
* Payment release conditions.  
* Dispute holds.  
* Audit history.

### **Acceptance Criteria**

* Funding can be allocated to a PO or milestone.  
* Vendors and beneficiaries are validated.  
* Funds are not released without the required authorization.  
* Disbursements are reconciled.  
* Sponsor-funded and funder-financed expenses are recorded separately.  
* Xing only initiates actions permitted by the applicable payment or escrow provider.

**Deliverable:** Approved vendor-directed financing administration.

---

# **SPRINT 12 — XING EVENT FUND III: REVENUE SHARING AND RECOUPMENT**

**Objective:** Administer contracted commercial returns and organizer distributions.

### **Financing Structures**

#### **Model A — Revenue Share**

The funder receives an agreed percentage of defined distributable event receipts, subject to contractual limits.

#### **Model B — Recoup Then Share**

The funder receives eligible distributions toward recovery of its original contribution.

Remaining proceeds are shared according to an agreed ratio.

#### **Model C — Organizer Priority**

The organizer receives a defined first allocation where distributable cash is available.

The funder receives the agreed balance, subject to a contractual cap or termination conditions.

#### **Model D — Designated Revenue Participation**

The funder participates in particular revenue sources such as:

* Ticket receipts.  
* Sponsorship payments.  
* Exhibition fees.  
* Registration fees.  
* Agreed venue commissions.

#### **Model E — Sponsorship Receivables Financing**

An approved financing counterparty advances funds against an eligible, enforceable sponsorship payment obligation, subject to proper diligence, contractual rights and applicable law.

### **Sponsorship Verification**

Record:

* Sponsor proposal.  
* Contract.  
* Payment conditions.  
* Sponsor acceptance.  
* Invoice.  
* Due date.  
* Contractual receivable.  
* Payment confirmation.  
* Rights assignment, where applicable.  
* Fulfilment obligations.

An unsigned sponsorship proposal must never be treated as collateral or a verified receivable.

### **Revenue Collection and Control**

Subject to valid agreements and approved payment arrangements, support:

* Dedicated collection accounts.  
* Split settlements.  
* Contractually authorized payment allocations.  
* Escrow settlements.  
* Verified external payment reconciliations.  
* Agreed revenue assignments.

When an organizer retains an external ticketing platform, Xing may report incoming revenue without having the authority to control or distribute it.

### **Contractual Revenue Waterfall**

1. Cleared eligible receipts.  
2. Refunds and chargebacks.  
3. Applicable taxes, fees and reserves.  
4. Contractually senior obligations.  
5. Distributable receipts.  
6. Funder distributions.  
7. Organizer distributions.

The system must prevent previously financed expenses from being deducted twice.

### **Example**

Funder contribution: ₦10 million.

Cleared event receipts: ₦20 million.

Approved deductions and reserves: ₦4 million.

Distributable receipts: ₦16 million.

Contract: Recoup first, then split remaining receipts 40% to funder and 60% to organizer.

Result:

* Funder capital recoupment: ₦10 million.  
* Remaining receipts: ₦6 million.  
* Additional funder distribution: ₦2.4 million.  
* Organizer distribution: ₦3.6 million.  
* Total funder receipts: ₦12.4 million.

These results assume all amounts are eligible, available and valid under the contract. They are not guaranteed returns.

### **Financial Reporting**

* Committed capital.  
* Actual disbursements.  
* Revenue source breakdown.  
* Cleared receipts.  
* Eligible distributions.  
* Capital recovered.  
* Unrecovered capital.  
* Contractual profit shares.  
* Organizer earnings.  
* Holds.  
* Refunds.  
* Disputes.  
* Settlement status.

### **Acceptance Criteria**

* Each contract model is configurable.  
* Distribution formulas can be independently verified.  
* Payment instructions never exceed authorized available funds.  
* Refunds and reserves are correctly handled.  
* Unpaid sponsor commitments are excluded from cash distribution.  
* Insufficient-revenue scenarios follow the applicable agreement.  
* Financial records retain full audit history.

**Regulatory launch condition:** Live financing arrangements require legal classification, approved contractual structures, eligible counterparties and any required licensed partner involvement.

**Deliverable:** Sponsor-linked and event-revenue-linked institutional distribution administration.

---

# **SPRINT 13 — XING ADS: NATIVE ADVERTISING**

**Objective:** Monetize marketplace visibility and event promotion.

### **Features**

* Create advertising campaign.  
* Select event or eligible Xing Page.  
* Choose campaign objective.  
* Set budget.  
* Set duration.  
* Choose location.  
* Select event category.  
* Preview placements.  
* Pay for campaign.  
* Pause/resume.  
* View reports.

### **Ad Placements**

* Featured events.  
* Promoted marketplace results.  
* Category sponsorship.  
* Sponsored event recommendations.  
* Approved site advertising placements.

### **Analytics**

* Impressions.  
* Clicks.  
* Registrations.  
* Paid registrations.  
* Cost per registration.  
* Revenue attribution.  
* Campaign performance.

### **Acceptance Criteria**

* Ads are clearly identified.  
* Campaign spending is controlled.  
* Performance is traceable.  
* Campaigns cannot exceed approved spending rules.  
* Organizer reports reflect measurable rather than assumed results.

**Deliverable:** Xing native advertising system.

---

# **SPRINT 14 — EXTERNAL ADVERTISING INTEGRATIONS**

**Objective:** Allow authorized ad management through Xing.

### **Target Networks**

* Meta.  
* Google Ads.  
* TikTok Ads.

### **Features**

* Connect advertiser account.  
* Create campaign.  
* Set audience.  
* Set budget.  
* Select objective.  
* Upload creatives.  
* Draft campaign copy.  
* Preview.  
* Submit campaign.  
* Retrieve status.  
* Report results.  
* Attribute verified ticket purchases.

### **Release Method**

Build a shared campaign management interface and separate network adapters.

Activate integrations individually after obtaining required API access and production permissions.

### **Commercial Model**

* Campaign management fees.  
* Monthly subscriptions.  
* Optional AI campaign optimization.  
* Managed services.

Advertiser media spend must be clearly separated from Xing fees.

### **Acceptance Criteria**

* The first production provider supports authorized campaign submission and reporting.  
* Duplicate submissions are prevented.  
* Platform restrictions are respected.  
* Additional networks pass their own launch tests.

**Deliverable:** Unified external advertising dashboard with progressively activated networks.

---

# **SPRINT 15 — EVENT OSINT AND SPONSOR INTELLIGENCE**

**Objective:** Build structured event and commercial opportunity intelligence.

### **Event OSINT**

* Discover public events.  
* Extract dates and venues.  
* Identify organizers.  
* Classify industries.  
* Link original sources.  
* Detect duplicates.  
* Monitor changes.  
* Identify conflicting events.  
* Maintain historical records.

### **Sponsor Intelligence**

* Publicly announced past sponsorships.  
* Brand event interests.  
* Industry alignment.  
* Geographical preferences.  
* Historical sponsor categories.  
* Event activation patterns.  
* Sponsor engagement history within Xing.

### **SponsorMatch Improvements**

Use intelligence to improve:

* Sponsor compatibility.  
* Event recommendations.  
* Audience matching.  
* Package suggestions.  
* Sponsor prospect lists.  
* Sponsorship pricing analysis.  
* Opportunity alerts.

Sponsor information must be sourced lawfully, and private contacts must not be harvested or exposed without an appropriate basis.

### **Organizer Intelligence**

* Competing events.  
* Industry event calendars.  
* Market timing.  
* Event themes.  
* Sponsorship trends.  
* Historical performance comparisons.

### **Acceptance Criteria**

* Imported event records retain source attribution.  
* Duplicates are identified.  
* Unverified claims are labeled.  
* SponsorMatch can use structured sponsor intelligence.  
* External listings cannot impersonate official organizers.

**Deliverable:** Event and sponsorship intelligence engine.

---

# **SPRINT 16 — PERSONAL EVENT MANAGER**

**Objective:** Provide a permanent event workspace for individuals.

### **Features**

* My Events.  
* Calendar.  
* Saved events.  
* Registered events.  
* Digital ticket wallet.  
* Past events.  
* Reminders.  
* Invitations.  
* Schedule conflict detection.  
* Personal interests.  
* Event recommendations.

### **Future External Connections**

* Google Calendar.  
* Outlook.  
* Phone contacts.  
* Email invitations.  
* Supported social accounts.

### **Social Event Planning**

* Invite contacts.  
* Coordinate attendance.  
* Track RSVPs.  
* Compare available dates.  
* Organize event itineraries.

### **Acceptance Criteria**

* Users can manage their event schedule.  
* External events can be saved.  
* Calendar conflicts are identified.  
* Private data remains permission-controlled.  
* The system remains functional without external accounts.

**Deliverable:** Personal event management workspace.

---

# **SPRINT 17 — ORGANIZER AI AGENT**

**Objective:** Introduce an assistant with authorized access to event operations.

### **Capabilities**

* Draft event plans.  
* Generate programme outlines.  
* Identify schedule conflicts.  
* Suggest ticket structures.  
* Summarize registrations.  
* Analyze budgets.  
* Draft RFQs.  
* Compare vendor quotations.  
* Summarize vendor delivery risks.  
* Recommend sponsor matches.  
* Draft sponsorship proposals.  
* Prepare marketing campaigns.  
* Analyze funding scenarios.  
* Explain contract distribution calculations.  
* Generate event reports.

### **Controlled Actions**

Actions involving spending, payments, contract changes, sponsorship outreach, publication or external messaging require authorized approval.

### **Acceptance Criteria**

* The AI uses permitted event records.  
* Recommendations indicate uncertainty.  
* Actions require the correct permissions.  
* Financial facts are not invented.  
* Activity is auditable.

**Deliverable:** Organizer AI operations assistant.

---

# **SPRINT 18 — PERSONAL EVENT AI AGENT**

**Objective:** Help individual users plan and manage event participation.

### **Capabilities**

* Search for events conversationally.  
* Recommend events by interests.  
* Recommend events by location.  
* Compare costs and dates.  
* Build event itineraries.  
* Suggest sessions.  
* Detect calendar conflicts.  
* Find eligible networking opportunities.  
* Assist meeting schedules.  
* Summarize programmes.  
* Organize reminders.

### **Acceptance Criteria**

* User permissions are respected.  
* Recommendations use verified event details when available.  
* Personal data remains protected.  
* Bookings and purchases require explicit approval.

**Deliverable:** Personal event planning AI assistant.

---

# **SPRINT 19 — HIGH-SPEED QR ACCESS**

**Objective:** Support large-scale admissions using professional scanners.

### **Features**

* Dedicated 2D barcode scanner support.  
* Continuous scanning.  
* Gate-specific permissions.  
* Secure validation.  
* Duplicate detection.  
* Offline credential caching.  
* Local access logs.  
* Synchronization.  
* Staff identification.  
* Device status.  
* Admission analytics.

### **Gate Dashboard**

* Valid admissions.  
* Invalid attempts.  
* Duplicate scans.  
* Entry throughput.  
* Device connectivity.  
* Access exceptions.  
* Gate activity.

### **Acceptance Criteria**

* Compatible scanners validate credentials correctly.  
* Offline operation follows approved security rules.  
* Access events synchronize.  
* Performance is tested with representative arrival volumes.

**Deliverable:** Enterprise QR admission system.

---

# **SPRINT 20 — NFC, RFID AND AUTOMATED ACCESS**

**Objective:** Support contactless entry and professional physical access infrastructure.

### **NFC**

* NFC credentials.  
* Cards and wristbands.  
* Compatible readers.  
* Tap-to-enter.  
* Credential revocation.  
* Permission enforcement.

### **RFID**

* UHF credentials.  
* Fixed readers.  
* Configurable read zones.  
* Reader antennas.  
* Gate controllers.  
* Credential mapping.  
* Controlled validation.

### **Gate Integration**

* Turnstiles.  
* Access displays.  
* Allow/deny decisions.  
* Entry and exit records.  
* Anti-passback.  
* Occupancy monitoring.  
* Staff override.  
* Emergency modes.

### **Pilot Requirements**

* Controlled deployment.  
* Reader accuracy tests.  
* Adjacent-lane interference tests.  
* Passage confirmation.  
* Network failure testing.  
* Security testing.  
* Throughput measurement.

### **Acceptance Criteria**

* Credential detection is distinguished from actual admission.  
* Access rights are enforced.  
* Security exceptions are logged.  
* Hardware outages have safe fallbacks.  
* Technology passes a controlled venue pilot.

**Deliverable:** Hardware-integrated event admission infrastructure.

---

# **PART 5 — CRITICAL CROSS-PRODUCT WORKFLOWS**

## **5.1 Event Organizer Lifecycle**

Create Event Workspace → Plan Tasks → Publish Event → Configure Tickets → Develop Programme → Register Exhibitors → Source Sponsors → Procure Vendors → Arrange Eligible Financing → Promote Event → Manage Attendees → Execute Event → Reconcile Finances → Close Event.

## **5.2 Sponsorship Lifecycle**

Organizer Creates Packages → Sponsor Profile and Matching → Introduction → Proposal → Negotiation → Contract → Invoice → Payment → Benefit Delivery → Measurement → Reporting → Closure.

## **5.3 Event Funding Lifecycle**

Organizer Requests Funding → Verified Revenue/Contracts Reviewed → Institutional Funder Evaluation → Financing Agreement → Vendor Allocation → Approved Payment Arrangement → Delivery Verification → Revenue Collection → Distribution → Reconciliation.

## **5.4 Sponsor-Linked Financing Lifecycle**

Sponsor Signs Agreement → Receivable Verified → Financing Eligibility Assessed → Approved Funder Agrees Terms → Vendor Expenses Financed → Sponsor Pays Through Authorized Arrangement → Contractual Distribution Calculated → Funds Distributed → Agreement Closed.

## **5.5 External Conference Integration**

Organizer Creates Tracker → Imports Existing Attendees → Imports Programme → Onboards Speakers → Manages Exhibitors and Sponsors → Coordinates Vendors → Publishes Event App → Runs Networking → Generates Reports.

Ticketing replacement is optional.

---

# **PART 6 — FINANCIAL DATA RULES**

## **6\. Financial Categories**

The platform must distinguish:

| Category | Meaning |
| ----- | ----- |
| Forecast Revenue | Estimated future receipts |
| Proposed Sponsorship | Sponsorship not yet contracted |
| Contracted Sponsorship | Executed agreement with defined obligations |
| Invoiced Sponsorship | Invoice issued |
| Sponsorship Receivable | Contractually owed amount, subject to enforceability and conditions |
| Paid Sponsorship | Verified received payment |
| In-Kind Sponsorship | Non-cash contribution |
| Funding Offer | Proposed financing |
| Funding Commitment | Executed financing obligation |
| Funding Disbursement | Verified actual financing payment |
| Vendor Commitment | Approved procurement obligation |
| Vendor Payment | Verified supplier payment |
| Distributable Receipts | Cleared, contractually eligible amount after appropriate deductions |
| Funder Distribution | Actual amount allocated or paid under financing terms |
| Organizer Distribution | Actual amount allocated or paid to organizer |

The system must never silently treat these categories as interchangeable.

---

# **PART 7 — TECHNICAL DESIGN**

## **7.1 Shared System Services**

* Authentication.  
* Organization membership.  
* Roles and permissions.  
* Event records.  
* Task management.  
* File storage.  
* Notifications.  
* Payment orchestration.  
* QR credentials.  
* Analytics.  
* Audit logs.  
* API integration services.

## **7.2 Database Modules**

**Tracker**

* event\_projects  
* event\_workstreams  
* event\_tasks  
* event\_task\_dependencies  
* event\_milestones  
* event\_issues  
* event\_activity\_logs

**Event Commerce**

* events  
* ticket\_types  
* registrations  
* orders  
* payments  
* tickets  
* checkins  
* qr\_campaigns

**Conference**

* event\_programmes  
* programme\_versions  
* sessions  
* speakers  
* rooms  
* exhibitors  
* booths  
* exhibitor\_staff

**SponsorMatch**

* sponsor\_profiles  
* sponsor\_preferences  
* sponsorship\_opportunities  
* sponsorship\_packages  
* sponsor\_matches  
* sponsor\_proposals  
* sponsor\_contracts  
* sponsor\_invoices  
* sponsor\_payments  
* sponsor\_deliverables  
* sponsorship\_performance

**Procurement**

* vendors  
* vendor\_verifications  
* event\_budgets  
* procurement\_requests  
* rfqs  
* quotations  
* purchase\_orders  
* vendor\_contracts  
* vendor\_milestones  
* deliveries  
* invoices  
* payment\_approvals

**Event Fund**

* institutional\_funders  
* funder\_verifications  
* funding\_applications  
* funding\_offers  
* funding\_agreements  
* funding\_allocations  
* funding\_disbursements  
* escrow\_transactions  
* revenue\_rights  
* revenue\_distribution\_rules  
* funding\_distributions  
* reconciliation\_records

**Event Experience**

* attendee\_profiles  
* attendee\_connections  
* meeting\_requests  
* chat\_channels  
* chat\_messages  
* pass\_types  
* passes  
* entitlements  
* entitlement\_redemptions  
* seats  
* badge\_print\_jobs

**Growth and Intelligence**

* ad\_campaigns  
* advertising\_accounts  
* attribution\_records  
* osint\_sources  
* event\_intelligence  
* sponsor\_intelligence  
* ai\_agent\_sessions  
* ai\_action\_logs

**Access**

* scanner\_devices  
* access\_gates  
* credential\_reads  
* access\_decisions  
* offline\_sync\_batches  
* passage\_records

These names are conceptual. Reuse existing Xing entities and avoid unnecessary duplication.

## **7.3 Architecture Requirements**

* Centralized event and organization identifiers.  
* Server-side permissions.  
* Modular services.  
* Versioned database migrations.  
* Secure APIs.  
* Idempotent payment processing.  
* Background jobs.  
* Scheduled tasks.  
* File validation.  
* Reliable payment-provider webhooks.  
* Transaction audit trails.  
* Offline synchronization.  
* Provider-specific adapters.

The system may begin on PHP-compatible hosting, but heavy workloads such as messaging, AI processing and large-scale device coordination may require dedicated services.

---

# **PART 8 — UNIFIED ANALYTICS**

## **8\. Analytics Requirements**

Analytics must be implemented progressively rather than deferred.

### **Tracker Analytics**

* Task completion.  
* Milestone completion.  
* Workstream readiness.  
* Delayed tasks.  
* Issue resolution.  
* Event execution readiness.

### **Ticketing Analytics**

* Page traffic.  
* Registration funnel.  
* Ticket sales.  
* Conversion.  
* Revenue.  
* Check-ins.  
* No-show rate.

### **Sponsorship Analytics**

* Sponsor matches.  
* Proposals.  
* Conversion rates.  
* Signed agreements.  
* Paid sponsorship.  
* Outstanding receivables.  
* Benefit fulfilment.  
* Sponsor exposure and engagement where measurable.

### **Vendor Analytics**

* Procurement value.  
* Contract awards.  
* Vendor commitments.  
* Delivery completion.  
* Late milestones.  
* Payment status.  
* Budget variance.

### **Funding Analytics**

* Funding requested.  
* Funding committed.  
* Vendor financing.  
* Actual disbursements.  
* Revenue collections.  
* Capital recoupment.  
* Contractual distributions.  
* Financing shortfalls.  
* Disputes.

### **Marketing Analytics**

* Campaign cost.  
* Reach and impressions.  
* Clicks.  
* Registrations.  
* Paid ticket conversions.  
* Attributed revenue.

### **Event App Analytics**

* Active users.  
* Profile participation.  
* Meeting requests.  
* Meetings accepted.  
* Session engagement.  
* Exhibitor activity.

### **Venue Analytics**

* Gate admissions.  
* Entry throughput.  
* Pass validation.  
* Attendance by zone.  
* Entitlement redemption.  
* Onsite transactions.

Analytics must distinguish verified facts from forecasts and attributed estimates.

---

# **PART 9 — COMMERCIAL MODEL**

## **9.1 Core Revenue Sources**

1. Ticket transaction fees.  
2. Per-event software licensing.  
3. Enterprise Conference OS fees.  
4. Event Tracker subscriptions or enterprise licences.  
5. SponsorMatch success fees.  
6. Sponsorship management subscriptions.  
7. Vendor procurement administration fees.  
8. Permitted Event Fund administration fees.  
9. Native advertising.  
10. External advertising management.  
11. Premium analytics.  
12. AI subscriptions.  
13. Badge printing services.  
14. Scanner and access technology services.  
15. Managed onsite event support.

## **9.2 Sponsorship Revenue Model**

Xing may charge:

* A disclosed success fee on completed sponsorship deals.  
* Sponsor intelligence subscription.  
* Organizer access to premium matching tools.  
* Managed sponsorship sourcing.  
* Campaign measurement and fulfilment reporting.

A success fee must define when it becomes earned, such as after a contracted sponsorship payment is verified, and how reversals are handled.

## **9.3 Event Fund Revenue Model**

Potentially charge:

* Permitted sourcing or origination service fees.  
* Procurement administration.  
* Financing workflow software fees.  
* Document verification administration.  
* Settlement reconciliation services.  
* Contract management subscriptions.

Any compensation dependent on arranging investments or financial transactions must be legally reviewed.

**Important:** Xing should not automatically take a share of a funder's returns unless this is legally permitted and expressly agreed.

## **9.4 Separate Financial Relationships**

There are three separate commercial arrangements:

**Sponsorship Contract:** Sponsor and organizer.

**Financing Contract:** Organizer, eligible funder and other necessary counterparties.

**Xing Services Contract:** Xing and the users purchasing Xing's software or services.

These relationships may exchange authorized data but must not be merged into one ambiguous contract.

---

# **PART 10 — INITIAL SPE-TYPE COMMERCIAL PILOT**

## **10.1 Target Offering**

**Xing Conference OS — Professional Event Operations**

Pitch these capabilities first:

* Event Tracker.  
* Programme Command Centre.  
* Speaker Management.  
* Exhibitor and Booth Management.  
* Sponsor Fulfilment.  
* Vendor Management.  
* Delegate Networking.  
* Meeting Booking.

The organizer keeps its existing registration and ticketing infrastructure.

## **10.2 Pilot Demonstration**

Create a representative three-day professional conference with:

* Five programme tracks.  
* Twenty sample speakers.  
* Ten sample exhibitors.  
* Several sponsorship packages.  
* Vendor procurement tasks.  
* A sample exhibition floor.  
* Delegate profiles.  
* Meeting booking.  
* Operational readiness dashboard.

Use synthetic demonstration data unless permission is obtained to use actual organizer information.

## **10.3 Pilot Validation**

Measure:

* Organizer coordination time.  
* Programme update accuracy.  
* Speaker confirmation completion.  
* Exhibitor readiness.  
* Vendor delivery status.  
* Sponsor benefit delivery.  
* Attendee app engagement.  
* Meeting bookings.  
* Operational incident handling.

## **10.4 Commercial Objective**

Secure an initial paid enterprise pilot demonstrating that Xing Conference OS provides measurable operational value independently of ticketing.

A successful pilot should provide both direct revenue and evidence for selling to other conference organizers.

---

# **PART 11 — QUALITY, COMPLIANCE AND RELEASE CONTROL**

## **11.1 Sprint Definition of Done**

Every sprint must deliver:

* Working backend.  
* Working responsive interface.  
* Correct database changes.  
* Permission enforcement.  
* Required audit logs.  
* Analytics events.  
* Error handling.  
* Test coverage for critical workflows.  
* Documentation.  
* Deployment verification.  
* Demonstrable end-to-end user journey.

## **11.2 Financial Release Rules**

No production financing activity without:

* Legal classification of the arrangement.  
* Counterparty eligibility verification.  
* Approved contract templates.  
* Authorized payment and settlement arrangements.  
* Appropriate KYC/AML controls.  
* Refund and dispute procedures.  
* Explicit allocation of financial risk.  
* Reconciliation and audit controls.

## **11.3 Operational Release Rules**

* External API integrations require authorized production access.  
* Organizer data imports require appropriate authorization.  
* Attendee networking is opt-in.  
* Paid promotions are identified.  
* Sponsorship results are supported by evidence.  
* AI cannot independently authorize material financial actions.  
* RFID and NFC systems require physical pilots.  
* Offline credential operation requires documented security and synchronization procedures.

---

# **PART 12 — DEVELOPMENT MILESTONES**

| Milestone | Sprints | Outcome |
| ----- | ----- | ----- |
| M0 | 0 | Working event planning and execution tracker |
| M1 | 1–2 | Live public events, ticketing, marketplace and QR |
| M2 | 3–4 | Conference management, exhibitors and SponsorMatch |
| M3 | 5–6 | Vendor sourcing, contracts and delivery operations |
| M4 | 7–9 | Full conference app, passes and onsite commerce |
| M5 | 10–12 | Institutional Event Fund and financial distribution infrastructure |
| M6 | 13–14 | Native and external advertising |
| M7 | 15–16 | OSINT, sponsor intelligence and personal event management |
| M8 | 17–18 | AI-enabled event management |
| M9 | 19–20 | Professional access-control technology |

## **Final Acceptance**

The completed Xing Events ecosystem must allow a professional event organizer to:

1. Plan and monitor event preparation.  
2. Create an event and sell tickets where required.  
3. Maintain programmes and speaker schedules.  
4. Manage exhibitors and exhibition spaces.  
5. Identify, approach and contract suitable sponsors.  
6. Track sponsorship deliverables and commercial results.  
7. Source vendors and approve purchase orders.  
8. Manage delivery and vendor payment obligations.  
9. Apply to approved institutions for eligible event financing.  
10. Fund approved event expenses under authorized arrangements.  
11. Administer contracted funder and organizer revenue distributions.  
12. Promote the event through Xing and authorized advertising platforms.  
13. Provide a professional attendee application.  
14. Enable networking, meetings and messaging.  
15. Issue and verify configurable event passes.  
16. Manage physical and digital venue payments.  
17. Operate event admission and access privileges.  
18. Measure financial and operational performance.  
19. Use AI assistance with appropriate controls.  
20. Complete post-event reconciliation, reporting and closure.

---

## **FINAL PRODUCT DEFINITION**

**Xing Events is an integrated event operating system that connects planning, sponsorship, procurement, financing, promotion, participation and physical venue operations.**

Its competitive advantage is intended to come from the integration of these activities, verified execution data, and measurable commercial outcomes—not simply from offering more features than existing ticketing providers.

