## **Xinng Creator Revenue Calculator**

# **Product Requirements Document**

**Product:** Xinng  
**Feature:** Creator Revenue Calculator  
**MVP Platform:** X  
**Version:** 1.0  
**Status:** Build Specification  
**Primary Route:** `/tools/creator-calculator/x`  
**Future Parent Route:** `/tools/creator-calculator/{platform}`

---

## **1\. Product Summary**

The **Xinng Creator Revenue Calculator** is a free creator monetization planning tool.

The MVP focuses entirely on X.

The product answers two questions:

### **If the creator does not qualify:**

> **What do I need to qualify, and how long could it take at my current rate?**

### **If the creator qualifies:**

> **What could I earn, and how would changes in my performance affect that estimate?**

There is only **one calculator and one UX**.

The user's inputs determine which result state is shown.

The primary hook is:

> **How long until X starts paying you?**

Supporting copy:

> See how close you are to X monetization, what you need to qualify, and what your account could earn.

---

# **2\. Product Problem**

Creators frequently understand that X pays creators but struggle to determine:

* whether their account is currently large enough;  
* whether their recent reach is sufficient;  
* whether their current posting frequency can ever reach the required threshold;  
* how many more impressions they need;  
* how many more verified followers they need;  
* how long qualification could take;  
* what X could pay once they qualify.

Most online calculators focus on followers and generic CPM estimates.

That misses the most urgent question:

> **When can I qualify?**

Xinng should solve qualification first and earnings second.

---

# **3\. Current X Program**

The MVP should calculate against X's **Original Content Rewards Program**.

X currently requires, among other conditions:

* at least **500 verified followers**;  
* at least **500,000 Home Timeline impressions from verified users in the previous 90 days**;  
* active posting of original content.

Qualified impressions are unique Home Timeline impressions from Premium users where at least 50% of the post was visible. Duplicate, promoted, fraudulent and artificially generated impressions are excluded. ([Help Center](https://help.x.com/en/using-x/original-content-rewards?utm_source=chatgpt.com))

X also maintains other administrative requirements such as Premium membership, age, account standing, supported country and account type. These should be explained in the methodology but **not included as primary calculator inputs**. ([Help Center](https://help.x.com/en/using-x/original-content-rewards?utm_source=chatgpt.com))

Meeting measurable thresholds does not guarantee acceptance; X reviews applications. ([Help Center](https://help.x.com/en/using-x/original-content-rewards?utm_source=chatgpt.com))

---

# **4\. Product Positioning**

Do not position this primarily as:

> Revenue calculator

Position it as:

# **How long until X starts paying you?**

The revenue estimate is the second payoff.

The calculator flow should be:

Your current account  
        ↓  
Do you qualify?  
        ↓  
 ┌───────────────┬────────────────┐  
 │      NO       │      YES       │  
 │               │                │  
 │ What you need │ What you could │  
 │ Time to goal  │ earn           │  
 │ Required pace │ Earnings model │  
 └───────────────┴────────────────┘  
        ↓  
Adjust assumptions  
        ↓  
Share result  
---

# **5\. Product Objectives**

The MVP must:

* calculate estimated X monetization eligibility;  
* show the user's verified-follower gap;  
* estimate qualifying impressions;  
* identify whether current posting velocity is sufficient;  
* estimate how long qualification could take;  
* reverse-calculate the posting rate required to qualify;  
* reverse-calculate the average reach required to qualify;  
* estimate creator earnings when qualification thresholds are reached;  
* allow users to manipulate assumptions through sliders/controls;  
* support an optional X handle;  
* work completely without an X handle;  
* generate a shareable result image;  
* collect anonymized calculator data;  
* establish the architecture for later Instagram, TikTok and other platforms.

---

# **6\. Non-Goals**

The MVP will not include:

* sponsorship pricing;  
* brand-deal estimates;  
* media-kit rates;  
* creator sponsorship tiers;  
* Subscriptions revenue;  
* automated posting;  
* content scheduling;  
* growth automation;  
* bots;  
* engagement farming;  
* artificial impressions;  
* full social-media analytics;  
* mandatory Xinng registration.

---

# **7\. Target Users**

### **Aspiring monetized creator**

Has some audience/reach but does not know if current performance is enough.

Primary question:

> How long before I qualify?

### **Nearly qualified creator**

Has substantial activity and wants to know precisely what remains.

Primary question:

> What exactly am I missing?

### **Qualified creator**

Already exceeds the measurable thresholds.

Primary question:

> What could my current activity earn?

No separate flow is required for this user.

---

# **8\. Core UX Principle**

There must be **one continuous calculator**.

Do not ask:

> Are you already monetized?

The calculator determines the result from the supplied metrics.

---

# **9\. Entry Experience**

## **Hero**

### **Heading**

> **How long until X starts paying you?**

### **Supporting copy**

> Check your monetization progress, see what you're missing, and estimate what your account could earn.

---

## **Optional handle input**

### **Label**

**Enter your X handle for faster results**

`@username`

CTA:

**Analyse my X**

Helper:

> We'll use available public information to pre-fill what we can. You can change every value.

Underneath:

**or**

CTA:

**Enter my numbers manually**

The handle must never be mandatory.

---

# **10\. Input Requirements**

The primary calculator should be deliberately lightweight.

## **10.1 Followers**

Required.

Example:

`10,000`

---

## **10.2 Verified Followers**

Required where known.

Example:

`640`

Display calculated ratio:

> **6.4% verified followers**

Formula:

Verified Follower Ratio \=  
Verified Followers / Followers

Allow:

**I don't know**

If unknown, use a configurable estimate.

---

## **10.3 Average Impressions Per Post**

Editable.

Default:

Followers × 10%

Example:

10,000 followers  
→ default 1,000 impressions/post

Show:

> Estimated as 10% of your followers. Adjust this if you know your actual average.

The 10% value is an internal Xinng starting assumption, not an official X benchmark.

---

## **10.4 Posts Per Week**

Required.

Use:

* slider;  
* numeric field.

Example:

`7`

Suggested range:

`1–100`

---

## **10.5 90-Day Impressions**

Optional.

If supplied, prioritize the real value.

If unknown:

Estimated Gross 90-Day Impressions \=  
Avg Impressions/Post  
× Posts/Week  
× (90 / 7\)  
---

## **10.6 Country**

Optional.

Do not make it a prerequisite for calculating.

It may later be used for:

* supported-country validation;  
* revenue modelling;  
* analytics.

---

# **11\. Inputs Removed From Main UX**

Do not ask for:

* Premium status;  
* age;  
* account age.

These remain part of X's wider program requirements, but this tool assumes users interested in the program will handle those administrative conditions separately.

Methodology should state:

> X has additional eligibility, policy and payout requirements beyond the audience and performance thresholds calculated here.

---

# **12\. Default Assumptions**

The calculator should expose assumptions instead of hiding them.

## **Average impressions**

Default:

### **10% of followers per post**

Avg Impressions \=  
Followers × 0.10  
---

## **Qualified impression ratio**

Default:

### **40%**

Estimated Qualified Impressions \=  
Relevant Gross Impressions × 0.40

This is an Xinng modelling assumption used when actual qualified-impression information is unavailable.

The user may adjust it.

---

## **Original content ratio**

Default:

### **40%**

Recommended adjustable range:

### **25%–50%**

Question:

> **How much of what you post is original?**

This should be presented as a slider or segmented control.

Original content is relevant because X's current program rewards eligible original content. ([Help Center](https://help.x.com/en/using-x/original-content-rewards?utm_source=chatgpt.com))

---

# **13\. Advanced Controls**

Collapsed by default:

**Fine-tune my estimate**

Controls:

* qualified impression ratio;  
* original-content percentage;  
* actual 90-day impressions;  
* country.

This keeps the initial calculator simple while making the model transparent.

---

# **14\. Important Calculation Separation**

Do not treat these as the same metric.

### **Qualified Impression Ratio**

Default:

`40%`

Models how much of total exposure may qualify as relevant verified/Premium Home Timeline exposure.

### **Original Content Ratio**

Default:

`40%`

Models how much of the creator's activity is likely to be eligible original content.

Internally keep them separate.

---

# **15\. Qualification Engine**

The primary measurable thresholds are:

Required Verified Followers \= 500  
Required Qualified 90-Day Impressions \= 500,000

These values must live in the database/configuration and not be permanently hardcoded.

X currently publishes those thresholds. ([Help Center](https://help.x.com/en/using-x/original-content-rewards?utm_source=chatgpt.com))

---

# **16\. Estimated Performance**

When the user does not provide actual 90-day analytics:

Gross 90-Day Impressions \=  
Avg Impressions/Post  
× Posts/Week  
× (90 / 7\)

Then:

Estimated Qualified 90-Day Impressions \=  
Gross 90-Day Impressions  
× Qualified Impression Ratio

Keep original-content eligibility available as a separate variable for revenue modelling.

---

# **17\. Example**

Input:

Followers: 10,000  
Verified Followers: 650  
Avg Impressions/Post: 1,000  
Posts/Week: 7  
Qualified Ratio: 40%

Estimated gross 90-day impressions:

1,000 × 7 × (90 / 7\)  
≈ 90,000

Estimated qualified impressions:

90,000 × 40%  
\=  
36,000

Result:

Verified Followers: PASS  
Qualified Impressions: BELOW THRESHOLD  
---

# **18\. Result State A — Does Not Qualify**

The result should never stop at:

> Not eligible.

It should show:

# **You're not there yet — here's what it takes.**

Example:

### **X Monetization Progress**

**63%**

### **Verified followers**

`570 / 500 ✓`

### **Estimated qualifying impressions**

`315K / 500K`

### **Remaining**

# **185K qualifying impressions**

Then calculate the user's velocity.

---

# **19\. Monetization Velocity**

The calculator must determine whether the creator's current performance can sustainably reach the rolling 90-day threshold.

Calculate:

Projected Qualified 90-Day Impressions

If:

Projected Qualified 90-Day Impressions  
\< Required Qualified Impressions

display:

# **Your current pace is below the qualification threshold**

Example:

> At your current rate, you're projected to generate approximately **315K qualifying impressions every 90 days**.

> The current requirement is **500K**.

Then:

> **You need approximately 59% more qualifying reach.**

Do **not** display a fake future qualification date when the current sustainable rate will never cross the rolling threshold.

---

# **20\. Time-to-Qualify**

Where the creator is growing toward the target and sufficient history exists, estimate:

# **Estimated time to qualify**

## **\~6–8 weeks**

Because X uses a rolling 90-day requirement, qualification-time calculations should account for old impressions leaving the window.

The MVP may begin with a simplified rolling model but should not use only:

remaining impressions / impressions per day

without considering the rolling window.

---

# **21\. Reverse Calculator**

This is a primary feature.

The user should see what change would make qualification mathematically possible.

## **Required posting frequency**

Example:

> **At your current average reach, you need approximately 9 original posts/week.**

Formula concept:

Required Posts/90 Days \=  
500,000 /  
Estimated Qualified Impressions/Post

Then:

Required Posts/Week \=  
Required Posts/90 Days /  
(90 / 7\)  
---

## **Required impressions/post**

Example:

> Or keep posting 5 times/week and increase your average reach to approximately **7,700 qualifying impressions/post**.

---

# **22\. Scenario Controls**

Below the result:

## **What if I posted more?**

Slider:

`Posts/week`

Results update instantly.

---

## **What if my posts reached more people?**

Slider:

`Average impressions/post`

Results update instantly.

---

## **What if more impressions qualify?**

Advanced slider:

`Qualified impression ratio`

---

## **What if more of my content is original?**

Slider:

`Original content ratio`

The result should update without full-page reload.

---

# **23\. Result State B — Qualifies**

When the thresholds are met:

# **You appear to meet the measurable X monetization thresholds**

Then immediately move into earnings.

No new workflow.

No separate earner form.

---

# **24\. Qualified Result Example**

### **Estimated qualifying impressions**

**742K / 90 days**

### **Verified followers**

**920 / 500 ✓**

Then:

# **What could that earn?**

### **Estimated monthly earnings**

# **$65–$110**

### **Estimated annual earnings**

**$780–$1,320**

Then:

> Adjust your performance below to see how your estimate changes.

---

# **25\. Earnings Calculator**

The same controls used for qualification become the earnings controls.

Conceptually:

Avg Impressions/Post  
× Posting Frequency  
× Qualified Impression Ratio  
× Original Content Ratio  
        ↓  
Estimated Monetizable Activity  
        ↓  
Xinng Revenue Model  
        ↓  
Estimated Earnings Range

Do not use a single hardcoded CPM.

X does not publish a universal fixed payout rate for Original Content Rewards. It states that creators earn based on qualified impressions generated by original content. ([Help Center](https://help.x.com/en/using-x/original-content-rewards?utm_source=chatgpt.com))

---

# **26\. Earnings Output**

Always return a range.

Example:

### **Conservative**

`$45/month`

### **Estimated**

# **`$65–$110/month`**

### **Strong performance**

`$145+/month`

Then:

### **Annual estimate**

`$780–$1,320`

Label:

> **Xinng estimate — not an official X payout quote.**

---

# **27\. Revenue Model Versioning**

Every calculation must record:

revenue\_model\_version

Example:

x\_original\_rewards\_v1

This allows Xinng to improve the payout model without altering historical calculations.

---

# **28\. One Unified Result Component**

The frontend should use one conditional result component.

Conceptually:

if qualification\_thresholds\_met:  
    show qualified state  
    show earnings  
    show earnings scenarios  
else:  
    show qualification gap  
    show velocity  
    show required pace  
    show projected earnings after qualification

Do not build separate calculator pages.

---

# **29\. Handle-Assisted Mode**

The X handle is optional but should visually appear to be the easiest route.

Possible automatically populated information:

* username;  
* display name;  
* avatar;  
* followers;  
* visible post history;  
* estimated posting frequency;  
* publicly visible engagement.

Any unavailable metric remains manual.

All automatic values must be editable.

---

# **30\. Data Collection**

When a handle is entered, Xinng may store:

* handle;  
* available public metrics;  
* calculator inputs;  
* generated estimates;  
* timestamp.

Disclosure:

> We may store publicly available account metrics and calculator results to improve Xinng's creator tools. We do not post to your account.

Users entering calculations manually should still be able to use the tool anonymously.

---

# **31\. Share Card**

Shareability is a core MVP requirement.

Primary size:

### **1200 × 675**

Optimized for X.

The card must include:

* Xinng logo;  
* **Xinng dragon beside the logo**;  
* handle where supplied;  
* core result;  
* xin.ng branding.

The dragon should use the existing established Xinng mascot design.

---

# **32\. Share Card — Not Qualified**

Example:

┌────────────────────────────────────────────┐

 \[XINNG LOGO\] \[DRAGON\]

 @username

 I'M 74% OF THE WAY  
 TO X MONETIZATION

 Estimated time  
 \~7 weeks

 126K qualifying  
 impressions to go

                 calculated on xin.ng

└────────────────────────────────────────────┘  
---

# **33\. Share Card — Qualified**

┌────────────────────────────────────────────┐

 \[XINNG LOGO\] \[DRAGON\]

 @username

 MY ESTIMATED  
 X CREATOR EARNINGS

 $65 – $110  
 per month

 742K estimated qualifying  
 impressions / 90 days

                 calculated on xin.ng

└────────────────────────────────────────────┘  
---

# **34\. Share Card Controls**

Before generation:

* Include handle — On/Off  
* Include earnings — On/Off  
* Include qualification progress — On/Off  
* Include estimated timeline — On/Off

Buttons:

* **Share on X**  
* **Download image**  
* **Copy result link**

---

# **35\. Persistent Result URL**

Example:

xin.ng/tools/creator-calculator/x/r/{token}

Use random non-sequential public tokens.

Only expose data explicitly intended for sharing.

---

# **36\. Page Structure**

HERO  
│  
├── Optional @handle  
└── Manual entry  
        ↓  
CALCULATOR  
│  
├── Followers  
├── Verified followers  
├── Avg impressions/post  
├── Posts/week  
└── Advanced assumptions  
        ↓  
LIVE RESULT  
        ↓  
┌──────────────────────┐  
│ NOT QUALIFIED        │  
│ qualification gap    │  
│ required pace        │  
│ time / velocity      │  
└──────────────────────┘

          OR

┌──────────────────────┐  
│ QUALIFIED            │  
│ earnings estimate    │  
│ earning scenarios    │  
└──────────────────────┘  
        ↓  
SCENARIO CONTROLS  
        ↓  
SHARE CARD  
        ↓  
HOW IT WORKS  
        ↓  
FAQ / METHODOLOGY  
---

# **37\. Mobile Layout**

Priority order:

Hero  
↓  
Handle/manual input  
↓  
Inputs  
↓  
Primary result  
↓  
Progress  
↓  
Required pace / earnings  
↓  
Scenario controls  
↓  
Share card  
↓  
Methodology  
---

# **38\. Rules Architecture**

Create:

creator\_platform\_rules

Suggested structure:

id  
platform  
program  
rule\_key  
rule\_value  
rule\_type  
source\_url  
effective\_date  
reviewed\_at  
active

Examples:

x  
original\_content\_rewards  
verified\_followers\_required  
500  
x  
original\_content\_rewards  
qualified\_impressions\_90d  
500000

This is essential because creator-platform rules change.

---

# **39\. Calculator Data Table**

Suggested:

creator\_calculations

Fields:

id  
public\_token  
platform  
handle  
country  
followers  
verified\_followers  
verified\_follower\_ratio  
avg\_impressions\_per\_post  
posts\_per\_week  
gross\_impressions\_90d  
qualified\_impression\_ratio  
estimated\_qualified\_impressions\_90d  
original\_content\_ratio  
qualification\_status  
qualification\_progress  
estimated\_days\_low  
estimated\_days\_high  
required\_posts\_per\_week  
required\_impressions\_per\_post  
monthly\_revenue\_low  
monthly\_revenue\_high  
annual\_revenue\_low  
annual\_revenue\_high  
revenue\_model\_version  
input\_source  
created\_at  
updated\_at  
---

# **40\. Technical Architecture**

Use the existing Xinng environment.

Expected stack:

* PHP;  
* MySQL;  
* HTML5;  
* CSS;  
* JavaScript;  
* cPanel-compatible deployment.

Separate calculator logic from frontend templates.

Suggested:

/services/creator-calculator/  
    PlatformCalculator.php  
    XCalculator.php  
    QualificationEngine.php  
    ScenarioEngine.php  
    RevenueEstimator.php  
---

# **41\. Platform Adapter**

Use:

interface CreatorPlatformCalculator  
{  
    public function getRequirements(): array;

    public function calculateQualification(array $inputs): array;

    public function calculateTimeline(array $inputs): array;

    public function calculateScenarios(array $inputs): array;

    public function estimateRevenue(array $inputs): array;  
}

MVP:

XCalculator

Future:

InstagramCalculator  
TikTokCalculator  
YouTubeCalculator  
FacebookCalculator  
---

# **42\. MVP Stage**

## **Stage 0 — X Manual Calculator**

Launch requirements:

* one calculator;  
* optional X handle;  
* manual mode;  
* 10% default impressions/follower;  
* verified follower input;  
* 40% qualified-impression assumption;  
* 25–50% original-content control;  
* configurable X qualification thresholds;  
* two conditional result states;  
* qualification gap;  
* current-pace analysis;  
* reverse calculation;  
* interactive scenarios;  
* earnings estimate;  
* X share card;  
* dragon \+ Xinng logo;  
* persistent result links;  
* anonymous use;  
* analytics.

---

# **43\. Stage 1 — AI Screenshot Analysis**

Users can upload X analytics screenshots instead of manually entering metrics.

CTA:

> **Upload your X analytics**

Supported examples:

* analytics overview;  
* follower screens;  
* Creator Studio;  
* Original Content Rewards analytics;  
* payout screens;  
* post-performance screenshots.

Workflow:

Screenshot Upload  
      ↓  
GPT / Gemini Vision  
      ↓  
Structured Data Extraction  
      ↓  
Confidence Checking  
      ↓  
User Confirmation  
      ↓  
Calculator  
---

# **44\. AI Extraction Requirements**

AI output must be structured.

Example:

{  
  "platform": "x",  
  "period\_days": 90,  
  "followers": 12500,  
  "verified\_followers": 710,  
  "impressions": 1480000,  
  "posts": 136,  
  "revenue": null,  
  "confidence": {  
    "followers": 0.99,  
    "impressions": 0.97,  
    "verified\_followers": 0.62  
  }  
}

Every extracted value should have confidence information.

---

# **45\. AI Confirmation Screen**

Never silently accept extracted data.

Show:

## **We found these numbers**

**Followers**  
12,500 ✓

**90-day impressions**  
1.48M ✓

**Posts**  
136 ✓

**Verified followers**  
710  
*Please confirm*

CTA:

**Use these stats**

All values remain editable.

---

# **46\. Multi-Screenshot Support**

Allow multiple screenshots.

The system should merge them into a single:

## **Creator Snapshot**

Audience  
Performance  
Activity  
Monetization  
Revenue

If values conflict:

1. prefer clearly dated newer data;  
2. prefer higher-confidence extraction;  
3. otherwise ask the user.

---

# **47\. AI Provider Architecture**

Do not bind the product directly to GPT or Gemini.

Create:

CreatorAnalyticsExtractor

Providers:

GPTVisionExtractor  
GeminiVisionExtractor

The application should be able to:

* switch providers;  
* use fallback;  
* compare cost;  
* compare accuracy.

---

# **48\. Stage 2 — Instagram**

Add Instagram under the same calculator framework.

The UX remains:

Your data  
↓  
Do you qualify?  
↓  
What do you need?  
or  
What could you earn?  
↓  
Scenario modelling  
↓  
Share

Instagram receives its own:

* rule adapter;  
* screenshot schema;  
* monetization engine;  
* platform inputs;  
* revenue model.

---

# **49\. Stage 3 — TikTok**

Add TikTok with the same architecture.

Potential inputs:

* followers;  
* video views;  
* qualifying views;  
* videos/week;  
* watch time;  
* eligible content;  
* payout data.

Same two outcome states.

---

# **50\. Stage 4+ — Additional Platforms**

Recommended future expansion:

YouTube  
Facebook  
Twitch  
Substack  
Other creator platforms

Do not expose these in the MVP navigation until they are actually supported.

---

# **51\. Long-Term Unified Creator Layer**

Eventually allow one creator to combine:

X  
Instagram  
TikTok  
YouTube

Then provide:

## **Creator Revenue Snapshot**

Example:

X          $90–$150/month  
Instagram  $XX–$XXX/month  
TikTok     $XX–$XXX/month  
YouTube    $XX–$XXX/month

And:

> X is closest to monetization.

> TikTok needs another X qualifying views.

> YouTube needs another X subscribers.

This is the eventual evolution from calculator to **creator monetization dashboard**.

---

# **52\. Analytics Events**

Track:

calculator\_opened  
handle\_entered  
handle\_lookup\_success  
handle\_lookup\_failed  
manual\_mode\_started  
calculation\_completed  
not\_qualified\_result  
qualified\_result  
below\_velocity\_result  
scenario\_changed  
advanced\_assumptions\_opened  
share\_card\_generated  
share\_x\_clicked  
share\_image\_downloaded  
result\_link\_copied  
screenshot\_upload\_started  
screenshot\_analysis\_completed  
screenshot\_values\_confirmed  
---

# **53\. Product KPIs**

Primary:

### **Calculator Completion Rate**

Completed calculations / Calculator visitors

Secondary:

* percentage using handle lookup;  
* percentage changing scenario controls;  
* percentage generating share cards;  
* share-to-X rate;  
* return visits;  
* repeat handles;  
* screenshot-analysis adoption;  
* amount of usable creator-performance data collected.

A particularly important long-term metric:

### **Returning creators who recalculate progress within 30 days.**

---

# **54\. Acceptance Criteria**

The X MVP is accepted when:

1. The calculator works without login.  
2. A handle is optional.  
3. The tool works completely in manual mode.  
4. Followers can be entered.  
5. Verified followers can be entered.  
6. Average impressions default to 10% of followers.  
7. Average impressions remain editable.  
8. Posts/week can be changed.  
9. Qualified-impression ratio defaults to 40%.  
10. Qualified ratio can be adjusted.  
11. Original-content ratio defaults around 40%.  
12. Original-content range supports approximately 25–50%.  
13. Country remains optional.  
14. Premium is not requested as an input.  
15. Age is not requested.  
16. Account age is not requested.  
17. X rules are configurable.  
18. Verified-follower gap is calculated.  
19. Qualified-impression gap is calculated.  
20. Rolling 90-day performance is modelled.  
21. Below-threshold velocity is detected.  
22. Users below sustainable qualification pace are not given a misleading inevitable qualification date.  
23. Required posts/week can be calculated.  
24. Required impressions/post can be calculated.  
25. Qualified users automatically see earnings.  
26. No separate qualified-creator workflow exists.  
27. Earnings respond to scenario changes.  
28. Earnings are shown as ranges.  
29. Earnings are labelled estimates.  
30. Sponsorship pricing appears nowhere.  
31. Shareable 1200 × 675 cards are generated.  
32. Share cards include the Xinng logo.  
33. Share cards include the Xinng dragon next to the logo.  
34. Share cards support qualified and non-qualified states.  
35. Users can download the card.  
36. Users can share directly to X.  
37. Public result URLs use random tokens.  
38. Core calculation logic is separated from frontend templates.  
39. Platform calculation logic supports future adapters.  
40. The architecture is ready for Stage 1 screenshot analysis.

---

# **55\. Build Order**

### **Sprint 1**

Calculator engine \+ manual UI.

### **Sprint 2**

Qualification velocity \+ reverse calculator.

### **Sprint 3**

Earnings model \+ scenario controls.

### **Sprint 4**

Share-card renderer \+ public results.

### **Sprint 5**

Optional X handle lookup.

### **Next Stage**

GPT/Gemini screenshot extraction.

Then:

**Instagram → TikTok → additional platforms.**

---

# **56\. Final MVP Boundary**

The X version should do one job exceptionally well:

> **Tell a creator whether their current X performance is enough to monetize, what they need if it isn't, and what they could earn if it is.**

The product should remain simple on the surface:

**Followers → Performance → Qualification → Earnings**

while the architecture underneath prepares Xinng for:

**screenshots → AI extraction → multiple platforms → unified creator revenue intelligence.**

