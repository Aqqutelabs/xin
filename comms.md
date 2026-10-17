# **PRD: Xinng Communications & In-App Notifications**

## **1\. Product Overview**

Xinng needs a simple communication system that sends important account, security, billing, and product emails, while also showing key updates inside the web app. The goal is to keep users informed without overwhelming them.

The system should support two channels:

1. **Transactional emails** — sent for account, security, password, subscription, and analytics events.  
2. **In-app notifications** — shown inside the Xinng dashboard for important product, account, and activity updates.

This is not a marketing newsletter system. The focus is on essential, high-value user communications.

---

## **2\. Objectives**

* Send reliable emails for critical user events.  
* Provide weekly performance insights via email.  
* Show important updates inside the app.  
* Reduce support requests around account activity and usage.  
* Build a foundation for future analytics and reporting features.  
* Keep communication minimal, useful, and non-spammy.

---

## **3\. Email Notification Events**

## **3.1 New User / Account Created**

Triggered when a user successfully creates an account.

Purpose:

* Confirm account creation  
* Introduce Xinng  
* Guide user to first action

### **Email Content**

Subject:

Welcome to Xinng 🚀

Body:

Hi {{first\_name}},

Welcome to Xinng\!

Your account has been successfully created.

You can now:  
\- Create QR codes  
\- Build short links  
\- Design your landing pages  
\- Track analytics in real time

Get started here:  
{{dashboard\_link}}

If you have any questions, we’re here to help.

— The Xinng Team

---

## **3.2 Welcome / Onboarding Email (Optional Merge with Signup)**

Purpose:

* Help users take first action quickly

Subject:

Let’s get you started with Xinng

Body:

Hi {{first\_name}},

Let’s set up your first project.

Start by creating:  
\- A QR code  
\- A short link  
\- Or a landing page

Everything is tracked automatically so you can see performance in real time.

Start here:  
{{dashboard\_link}}

— Xinng

---

## **3.3 Forgot Password Email**

Purpose:

* Secure password reset

Subject:

Reset your Xinng password

Body:

Hi,

We received a request to reset your password.

Reset it here:  
{{reset\_link}}

This link will expire in 15 minutes.

If you didn’t request this, you can ignore this email.

— Xinng Security

---

## **3.4 New Login Alert**

Purpose:

* Security notification for new device or location

Subject:

New login detected on your Xinng account

Body:

Hi {{first\_name}},

We detected a new login to your account.

Details:  
\- Device: {{device}}  
\- Location: {{location}}  
\- Time: {{time}}

If this was you, no action is needed.

If this wasn’t you, please reset your password immediately:  
{{security\_link}}

— Xinng Security

---

## **3.5 Subscription Started**

Purpose:

* Confirm payment and plan activation

Subject:

Your Xinng subscription is now active

Body:

Hi {{first\_name}},

Your subscription is now active.

Plan: {{plan\_name}}  
Billing cycle: {{billing\_cycle}}

You now have full access to premium features.

Manage your subscription:  
{{billing\_link}}

— Xinng

---

## **3.6 Subscription Ending / Expiring**

Purpose:

* Prevent accidental loss of access

Subject:

Your Xinng subscription is ending soon

Body:

Hi {{first\_name}},

Your subscription will end on {{end\_date}}.

After this date, premium features will be disabled.

Renew here to continue access:  
{{billing\_link}}

— Xinng

---

## **3.7 Weekly Analytics Email (NEW \- MVP INCLUDED)**

This is a core MVP feature.

Purpose:

* Give users weekly performance insights  
* Encourage product usage  
* Show value of tracking

Sent every 7 days.

### **Email Content**

Subject:

Your weekly Xinng performance report 📊

Body:

Hi {{first\_name}},

Here’s your weekly performance summary from Xinng.

This week:  
\- Total scans: {{total\_scans}}  
\- Unique visitors: {{unique\_visitors}}  
\- Top performing link: {{top\_link}}  
\- Top location: {{top\_location}}

Compared to last week:  
\- Scans: {{scan\_change}}%  
\- Engagement: {{engagement\_change}}%

View full analytics:  
{{analytics\_link}}

Keep optimizing your links and QR codes to improve performance.

— Xinng Analytics

---

## **4\. Email Events NOT in MVP**

Do NOT include:

* Marketing campaigns  
* Promotional offers  
* Daily summaries  
* Social engagement emails  
* Every scan or click event  
* Feature announcements

---

## **5\. In-App Notifications**

Displayed inside dashboard via notification bell.

### **Types**

* Account  
* Security  
* Subscription  
* Product activity  
* System updates  
* Analytics highlights (optional future expansion)

---

## **6\. Notification Behavior**

Each notification includes:

* id  
* user\_id  
* type  
* title  
* message  
* action\_url  
* is\_read  
* created\_at  
* read\_at

Features:

* Mark as read  
* Mark all as read  
* Click to navigate  
* Unread counter badge

---

## **7\. Database Schema**

### **emails\_log**

* id  
* user\_id nullable  
* email  
* event\_type  
* subject  
* status  
* provider\_message\_id nullable  
* error\_message nullable  
* sent\_at  
* created\_at

### **notifications**

* id  
* user\_id  
* type  
* title  
* message  
* action\_url nullable  
* is\_read default false  
* created\_at  
* read\_at nullable

---

## **8\. Acceptance Criteria**

* All transactional emails are sent reliably  
* Weekly analytics email is automatically scheduled every 7 days  
* Email templates match provided wording  
* In-app notifications are visible in dashboard  
* Users can mark notifications as read  
* Unread count updates correctly  
* Email logs are stored for all sends  
* Failed emails are tracked  
* System avoids spam-like behavior

