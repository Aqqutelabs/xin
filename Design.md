# Xing Design System

**Document:** Product and Marketing Design Standard  
**Product:** Xing  
**Mascot:** Xinng, the goofy dragon  
**Version:** 1.0  
**Status:** Source of truth for product UI and public landing pages

---

## 1. Purpose

This document defines how Xing should look, feel, and behave across the core web application, product modules, public landing pages, onboarding, dashboards, marketing campaigns, and the lightweight WordPress experience.

The system preserves the strongest qualities of the earlier Kiki SaaS design:

- clean, product-led layouts;
- generous white space;
- pale tinted feature sections;
- high-contrast dark sections;
- large interface screenshots;
- simple step-by-step explanations;
- modular feature cards;
- approachable illustrations and mascot moments;
- strong hierarchy with minimal visual noise.

The new identity replaces Kiki's blue-led system with **Xing Ember**: a warm red brand supported by oxblood, blush, warm white, gold, sky blue, green, and neutral ink.

Xing should feel useful before it feels impressive. It should be friendly without becoming childish, energetic without becoming aggressive, and modern without looking like a generic AI startup.

---

## 2. Brand Idea

### 2.1 Brand character

Xing is an energetic business companion for founders and small businesses. It helps people organise their business presence, communicate more effectively, publish useful information, and grow across digital channels.

The brand personality is:

- ambitious;
- clear;
- energetic;
- helpful;
- resourceful;
- slightly mischievous;
- commercially serious.

### 2.2 Visual promise

> Warm, calm software surfaces powered by confident red moments and a mischievous dragon.

### 2.3 Product principles

1. **Show the product.** Interface screenshots and realistic workflows should carry more weight than abstract graphics.
2. **Explain one thing at a time.** Every section, card, modal, and screen should have a dominant job.
3. **Keep the canvas calm.** Red directs attention; it should not cover the interface.
4. **Make complexity feel manageable.** Use steps, progressive disclosure, plain language, and strong defaults.
5. **Let Xinng add humanity.** The mascot supports meaning and emotion but never replaces usability.
6. **Design for mobile reality.** Many Xing customers will operate primarily from smartphones.

---

## 3. Colour System — Xing Ember

### 3.1 Core palette

| Token | Name | Hex | Primary use |
| --- | --- | --- | --- |
| `red-50` | Mist Pink | `#FFF5F3` | Page canvas, alternating sections |
| `red-100` | Soft Blush | `#FDE9E6` | Selected cards, onboarding panels |
| `red-200` | Rose Tint | `#FACBC5` | Borders, charts, decorative fills |
| `red-300` | Warm Coral | `#F59B92` | Illustrations and secondary accents |
| `red-400` | Flame Coral | `#FF6A5F` | Dark-background highlights |
| `red-500` | Xing Red | `#E83B32` | Primary action and brand colour |
| `red-600` | Fire Red | `#C92A24` | Hover and pressed states |
| `red-700` | Ember Red | `#A6201D` | Strong accents |
| `red-800` | Dragon Red | `#8F1717` | Dark brand areas and illustrations |
| `red-900` | Oxblood | `#4A1012` | Hero bands, premium dark sections |

### 3.2 Neutral palette

| Token | Hex | Use |
| --- | --- | --- |
| `canvas` | `#FFF9F7` | Default warm page background |
| `surface` | `#FFFFFF` | Cards, menus, modals and inputs |
| `surface-muted` | `#F7F3F1` | Secondary panels and table headers |
| `border` | `#E7DEDB` | Standard borders and dividers |
| `border-strong` | `#CFC3BF` | Focused or structural borders |
| `text-primary` | `#201A19` | Primary text |
| `text-secondary` | `#6F6562` | Descriptions and metadata |
| `text-muted` | `#978B87` | Placeholders and low-priority text |

### 3.3 Supporting accents

| Token | Name | Hex | Use |
| --- | --- | --- | --- |
| `gold` | Dragon Gold | `#FFBF3F` | Mascot details, special badges, sparks |
| `sky` | Sky Blue | `#3BA7E8` | Neutral information and mascot eyes |
| `success` | Growth Green | `#1F9D68` | Completed and successful states |
| `warning` | Amber | `#D97706` | Warnings and attention states |
| `error` | Crimson | `#A61924` | Errors and destructive actions |

### 3.4 Brand gradient

```css
background: linear-gradient(135deg, #FF554A 0%, #E83B32 45%, #8F1717 100%);
```

Use the brand gradient for the logo, key campaign surfaces, hero accents, premium plan highlights, and restrained illustration details. Do not use it on every button or container.

### 3.5 Colour proportion

Typical screens should contain approximately:

- 65% white and warm-neutral surfaces;
- 20% blush and mist-pink areas;
- 10% ink or oxblood contrast;
- 5% red, gold, blue, and semantic accents.

### 3.6 Semantic rule

Because red is also commonly associated with errors, Xing must never communicate status through colour alone. Errors require an error icon and explanatory text. Brand actions use Xing Red; destructive actions use Crimson and require explicit language.

---

## 4. Typography

### 4.1 Recommended family

Use **Manrope** for the primary interface and marketing typeface. It is clean, readable, slightly rounded, and sufficiently distinctive for a friendly SaaS product.

Fallback:

```css
font-family: "Manrope", "Inter", system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
```

The cursive Xing logo is a brand mark, not a display font. Do not use script or handwritten typography elsewhere in the interface.

### 4.2 Type scale

| Style | Desktop | Mobile | Weight | Line height |
| --- | ---: | ---: | ---: | ---: |
| Display | 64px | 42px | 750 | 1.05 |
| H1 | 52px | 36px | 750 | 1.10 |
| H2 | 40px | 30px | 700 | 1.15 |
| H3 | 28px | 24px | 700 | 1.25 |
| H4 | 21px | 19px | 650 | 1.30 |
| Body large | 18px | 17px | 450 | 1.60 |
| Body | 16px | 16px | 450 | 1.55 |
| Small | 14px | 14px | 500 | 1.45 |
| Label | 12px | 12px | 650 | 1.30 |

Headlines should be compact and direct. Body text should remain comfortable and should rarely span more than 68 characters per line.

---

## 5. Layout System

### 5.1 Grid

- Maximum marketing content width: `1200px`.
- Maximum dashboard content width: fluid, with a practical cap around `1440px`.
- Desktop grid: 12 columns.
- Tablet grid: 8 columns.
- Mobile grid: 4 columns.
- Desktop outer margin: `32–64px`.
- Mobile outer margin: `20px`.
- Base spacing unit: `4px`.

### 5.2 Spacing scale

Use `4, 8, 12, 16, 24, 32, 48, 64, 80, 112`.

Large landing-page sections should normally use `80–112px` vertical padding on desktop and `56–72px` on mobile. Dashboard cards should use `20–24px` internal padding.

### 5.3 Shape language

- Small controls: `8px` radius.
- Inputs and standard buttons: `10px` radius.
- Cards: `14–16px` radius.
- Large feature panels: `20–24px` radius.
- Pills and badges: full radius.

Avoid excessive bubbles. The interface should be soft but structurally disciplined.

### 5.4 Borders and shadows

Cards should rely primarily on borders and surface contrast. Use soft shadows only to create hierarchy or make floating product panels believable.

```css
--shadow-sm: 0 2px 8px rgba(74, 16, 18, 0.06);
--shadow-md: 0 12px 32px rgba(74, 16, 18, 0.10);
--shadow-float: 0 20px 50px rgba(32, 26, 25, 0.14);
```

---

## 6. Logo System

The Xing wordmark uses a continuous red script and upward arrow tail. The mark represents momentum, connection, and commercial progress.

Required logo variants:

- full red gradient on white or warm-white;
- solid Xing Red for constrained use;
- white on Oxblood or Warm Ink;
- single-colour Warm Ink for documents;
- compact symbol or monogram for favicons and app icons.

Do not:

- add shadows or glows to the wordmark;
- place it on a visually noisy background;
- use the wordmark as a decorative signature;
- stretch or compress it;
- introduce additional colours into the logo;
- repeat it excessively inside a single screen.

The mascot and wordmark must be treated as separate assets. Xinng may accompany the logo in selected brand moments, but should not be permanently attached to every logo instance.

---

## 7. Mascot — Xinng the Dragon

### 7.1 Role

Xinng is a goofy, competent dragon who makes business software feel less intimidating. He is a guide, helper, celebrator, and occasional comic observer.

Xinng should never become the primary navigation system or obstruct product tasks.

### 7.2 Colour application

| Element | Colour |
| --- | --- |
| Main body | Xing Red `#E83B32` |
| Belly and muzzle | Warm Cream `#FFE8D2` |
| Wings and tail ridge | Dragon Red `#8F1717` |
| Horns and small sparks | Dragon Gold `#FFBF3F` |
| Eyes | Sky Blue `#3BA7E8` |
| Outline | Warm Ink `#201A19` |
| Cheeks and highlights | Flame Coral `#FF6A5F` |

### 7.3 Personality through pose

Approved uses include:

- peeking from behind a card;
- carrying an oversized document;
- breathing a small celebratory flame;
- sleeping in an empty state;
- studying a chart with exaggerated seriousness;
- appearing confused beside a recoverable error;
- holding a plug while a user connects WordPress;
- wearing small work accessories relevant to a Xing module.

### 7.4 Usage levels

- **High:** onboarding welcome, empty states, completion moments, marketing pages.
- **Medium:** help panels, tips, upgrade education, friendly errors.
- **Low:** dense dashboards, tables, payment confirmation, security screens.
- **None:** destructive confirmation screens, legal notices, serious system failures.

---

## 8. Core Component System

### 8.1 Buttons

**Primary:** Xing Red background, white label, Fire Red hover.  
**Secondary:** white background, Warm Ink label, Border outline, Soft Blush hover.  
**Tertiary:** text or icon button with red label.  
**Dark-surface primary:** white button with Oxblood text or Flame Coral button.  
**Destructive:** Crimson with explicit destructive wording.

Buttons should use verbs: `Create campaign`, `Connect WordPress`, `Review draft`. Avoid vague labels such as `Continue` when the destination can be named.

### 8.2 Inputs

- White surface with a visible warm-grey border.
- Persistent label above the field.
- Helper text below when needed.
- Red focus ring may be used without implying an error.
- Error states must add icon and text.
- Large onboarding text fields may use softly tinted panels.

### 8.3 Cards

Card types:

- standard information card;
- selectable card;
- metric card;
- action card;
- product feature card;
- campaign card;
- editorial or article card;
- dark promotional card.

One card should communicate one primary idea. Avoid nesting more than one card inside another card.

### 8.4 Tabs

Tabs should resemble the clean Kiki feature switcher:

- white track;
- muted inactive labels;
- Soft Blush selected background;
- Ember Red selected label;
- optional subtle transition, `160–220ms`.

### 8.5 Status indicators

Use a badge, icon, and text together:

- Draft: neutral grey;
- Needs review: amber;
- Approved: sky blue;
- Scheduled: purple-red tint where needed;
- Published or complete: green;
- Failed: crimson.

### 8.6 Tables

Tables should remain white with subtle row dividers, sticky headers where useful, comfortable row height, and clear row actions. Use red only for active filters or the principal action.

On mobile, convert complex rows into stacked cards or provide a deliberate horizontal table experience.

### 8.7 Charts

Default chart sequence:

1. Xing Red;
2. Sky Blue;
3. Dragon Gold;
4. Growth Green;
5. Warm Coral;
6. Stone.

Use pale tints for areas and grid lines. Never build charts using multiple nearly identical red tones when categories need to be distinguished.

### 8.8 Screenshots and product compositions

Preserve the Kiki approach:

- use real application interfaces;
- place the main screenshot on a quiet tinted panel;
- enlarge the most important workflow;
- float one or two supporting panels above it;
- use restrained shadows;
- add Xinng only when he helps explain or humanise the scene.

Do not surround screenshots with abstract neon effects, fake holograms, or meaningless UI fragments.

---

## 9. Application Design

### 9.1 Application shell

The default desktop shell contains:

- a left sidebar;
- a compact top bar;
- the main content canvas;
- contextual panels or drawers when required.

The default background is `#FFF9F7`; cards and tables use white surfaces.

### 9.2 Sidebar

- Width: approximately `248px` expanded and `72px` collapsed.
- Background: white.
- Right border: `#E7DEDB`.
- Inactive text: Stone.
- Active item: Soft Blush background with Fire Red icon and label.
- Workspace or business switcher near the top.
- Profile, settings, and help near the bottom.

Do not make the entire sidebar red. A red sidebar would compete with every workflow and make semantic errors difficult to distinguish.

### 9.3 Top bar

The top bar may contain:

- page title or breadcrumb;
- global search where justified;
- notifications;
- contextual help;
- primary page action;
- profile control.

Keep its height between `64px` and `72px` and avoid duplicating sidebar navigation.

### 9.4 Dashboard home

The dashboard should answer:

1. What is happening?
2. What needs my attention?
3. What should I do next?

Recommended order:

- compact welcome and business context;
- one strong recommended-next-action panel;
- three to four important metrics;
- active work or campaign;
- tasks requiring review;
- recent activity.

Avoid filling the home screen with charts simply because data exists.

### 9.5 Workspace pattern

Major workflows should use:

- title and short explanation;
- visible progress or state;
- primary workspace in the centre;
- supporting context in a right-side panel;
- sticky action bar for multi-step review tasks.

### 9.6 Business onboarding

Business onboarding must feel calm, serious, and conversational.

Use:

- a visible section-based progress indicator;
- one conceptual group per screen;
- examples inside fields;
- save-and-exit at every stage;
- review cards for AI-extracted facts;
- clear distinction between verified, inferred, private, and rejected information;
- Xinng for welcome, explanation, and completion—not on every step.

Onboarding should not resemble a tax form. Long sections may use a two-column layout with guidance on the left and fields on the right. Mobile should use a single column.

### 9.7 Product modules

Every Xing module should inherit the same shell and primitives. Modules may have a unique secondary accent, but Xing Red remains the platform anchor.

Potential module accent mapping:

| Module type | Accent |
| --- | --- |
| Content and publishing | Flame Coral |
| Messaging and customer communication | Sky Blue |
| Commerce and payments | Growth Green |
| Campaigns and promotion | Dragon Gold |
| Analytics | Dragon Red plus chart palette |

Accent colours are navigational aids, not separate product brands.

### 9.8 WordPress plugin

The WordPress plugin should be visually quieter than the main Xing app and respect WordPress admin conventions.

Use Xing styling for:

- the connection header;
- primary actions;
- article status badges;
- small Xinng empty or connection states;
- links back to the full Xing workspace.

Do not reproduce the full Xing dashboard inside WordPress.

---

## 10. Public Landing-Page System

### 10.1 Navigation

Desktop navigation should include:

- Xing logo;
- Product or Features dropdown;
- Solutions;
- Resources or Learn;
- Pricing;
- Sign in;
- one high-contrast primary CTA.

Use a white or translucent warm-white header. The dropdown should preserve Kiki's product-mega-menu idea: compact product explanations, clear icons, and a highlighted Xinng assistance card.

### 10.2 Homepage structure

#### Section 1: Hero

- Oxblood or Warm Ink background.
- Short outcome-led headline.
- One supporting paragraph.
- Primary and secondary CTAs.
- Large real product composition.
- Small red, coral, or gold accent—not excessive gradients.
- Optional Xinng pose connected to the product story.

#### Section 2: Problem

- Warm-white or pale gold/blush panel.
- One recognisable customer problem.
- Short list of specific frustrations.
- Human photograph or grounded illustration.
- Avoid fear-heavy messaging.

#### Section 3: Solution pillars

- Three or four large white cards.
- Clear titles, short explanations, simple illustrations.
- Each card connects to a real Xing workflow.

#### Section 4: How it works

- Three steps where possible.
- Product screenshot for each step.
- Connected visual rhythm similar to the Kiki system.
- Labels should describe outcomes, not generic process names.

#### Section 5: Use cases

- Horizontal or responsive card system.
- Mix dark red, gold, coral, and photo-led cards.
- Use cases should be tied to user goals.

#### Section 6: Why Xing

- Dark Oxblood contrast band.
- Evidence-led benefits.
- Product proof, customer evidence, or measurable advantages.
- Xinng may appear as a guide, not the main subject.

#### Section 7: Featured product/module

- Soft Blush panel.
- Text on one side and a large product screenshot on the other.
- One primary action.

#### Section 8: Proof

- Testimonials, outcomes, recognised publishers, or customer logos.
- Use real proof only; never create placeholder claims that look factual.

#### Section 9: Final CTA

- Branded gradient or Oxblood panel.
- One concise promise.
- One primary CTA and optional low-commitment alternative.

#### Section 10: FAQ and footer

- Simple accordion with generous spacing.
- Warm Ink or Oxblood footer.
- Clear product, company, resource, support, and legal links.

### 10.3 Product landing pages

Each product or module landing page should use the same framework:

1. Product promise and interface preview.
2. User problem.
3. Core workflow.
4. Feature groups shown through actual screens.
5. Three-step setup.
6. Use cases.
7. Trust or proof.
8. Pricing or plan connection.
9. FAQ.
10. Final CTA.

Do not create a unique visual identity for every product page. Variation should come from content composition, photography, illustrations, and controlled secondary accents.

### 10.4 Scaffold landing page

Scaffold should use the main Xing identity with additional editorial cues:

- documents, structured content cards, article outlines, and publishing flows;
- screenshots of business onboarding and content campaigns;
- Flame Coral as its secondary accent;
- Xinng carrying papers, reviewing drafts, or connecting a WordPress cable;
- language focused on business knowledge and discoverability—not bulk generation.

### 10.5 Marketplace landing page

The publishing marketplace should feel credible and editorial rather than like a backlink bazaar.

Use:

- publication cards with audience, region, subject, and editorial requirements;
- calm white surfaces;
- limited mascot use;
- dark red editorial feature bands;
- clear distinctions between sponsored, contributed, and editorial opportunities.

### 10.6 Learn and resource pages

The `/learn` area should use an editorial layout:

- strong typographic hierarchy;
- category navigation;
- comfortable reading width;
- warm-neutral article canvas;
- restrained illustration;
- related articles and product pathways;
- visible authorship, dates, and update history.

---

## 11. Photography and Illustration

### 11.1 Photography

Photography should feel real, bright, and commercially grounded. Prioritise African founders, small teams, service providers, professionals, and business environments relevant to the market.

Avoid:

- blue-toned generic corporate stock;
- exaggerated handshakes;
- fake futuristic screens;
- overly staged office celebrations;
- excessive laptop-pointing poses;
- images unrelated to the actual workflow.

### 11.2 Illustration

Use simple, bold illustration with Warm Ink outlines and the Xing palette. Illustrations may combine people, documents, storefronts, charts, messages, and Xinng.

Do not mix several unrelated illustration styles on the same page.

### 11.3 Icons

Use a consistent rounded-outline icon family. Standard stroke width should be visually balanced at `1.75–2px`. Icons should support labels, not replace important language.

---

## 12. Motion

Motion should communicate cause and effect.

- Hover and control transitions: `160–220ms`.
- Drawers and modals: `220–300ms`.
- Marketing entrance motion: `300–500ms`.
- Use standard ease-out for entrances and ease-in for exits.
- Respect `prefers-reduced-motion`.

Approved mascot motion includes blinking, a small tail movement, peeking, a tiny flame, or a short celebratory bounce. Avoid continuous distracting animation.

---

## 13. Responsive Behaviour

### Mobile

- Single-column content by default.
- `20px` page margins.
- Full-width primary actions where helpful.
- Sticky bottom action bar for long review flows.
- Sidebar becomes a drawer or compact bottom-level navigation where justified.
- Dashboard tables become cards or deliberate horizontal scroll regions.
- Product screenshot compositions simplify rather than shrink illegibly.

### Tablet

- Two-column cards where space permits.
- Collapsible sidebar.
- Reduce floating decorative screenshot layers.

### Desktop

- Use wide product compositions without stretching reading text.
- Keep actions near the content they affect.
- Do not fill empty space with decorative elements merely because room exists.

---

## 14. Accessibility

- Meet WCAG 2.2 AA contrast requirements.
- Provide visible keyboard focus.
- Maintain minimum `44px` touch targets.
- Do not use colour as the only status signal.
- Provide alternative text for informative images.
- Treat product screenshots as meaningful content when they explain a workflow.
- Support keyboard operation for menus, tabs, dialogs, tables, and accordions.
- Keep body text at `16px` or larger in normal contexts.
- Avoid placing small white text on the brighter parts of the red gradient.
- Honour reduced motion and browser zoom.

---

## 15. Voice and Interface Copy

Xing should use short, practical, encouraging language.

### Prefer

- “Tell us about your business.”
- “Review what Xing understood.”
- “Connect your WordPress site.”
- “Three articles need your approval.”
- “Your changes have been saved.”

### Avoid

- “Leverage our revolutionary AI ecosystem.”
- “Generate unlimited content at scale.”
- “Dominate Google instantly.”
- unexplained technical SEO terminology;
- overly cute dragon-related puns in serious workflows.

Xinng may add short personality lines, but the main instruction must remain clear without the joke.

---

## 16. Kiki-to-Xing Translation Rules

| Kiki system | Xing system |
| --- | --- |
| Pale blue canvas | Mist Pink or warm-white canvas |
| Light-blue feature panel | Soft Blush feature panel |
| Royal-blue CTA | Xing Red CTA |
| Dark navy hero | Oxblood hero |
| Cyan highlights | Flame Coral, with Sky Blue reserved for information |
| Yellow accent cards | Dragon Gold accent cards, used selectively |
| Blue active tabs | Blush active tabs with red label |
| Dark navy footer | Warm Ink or Oxblood footer |
| Blue mascot | Red dragon with cream, gold, and blue details |

The translation is structural, not mechanical. Do not recolour every blue pixel red. Preserve neutral space, introduce red only where it establishes hierarchy, and retain supporting colours when they serve comprehension.

---

## 17. Anti-Patterns

Do not:

- use red as the background for most of the application;
- turn every card into a gradient;
- use Xinng on every section or screen;
- use cursive fonts outside the logo;
- create fake dashboard screenshots;
- mix glassmorphism, neumorphism, and flat design;
- use excessive shadows or glowing red effects;
- add abstract AI brains, robots, neural networks, or holograms by default;
- use more than one dominant CTA per section;
- create landing pages made only of feature lists;
- shrink desktop dashboard screenshots until they are unreadable on mobile;
- use red alone to communicate errors;
- sacrifice clarity for mascot humour.

---

## 18. CSS Foundation

```css
:root {
  --xing-red-50: #fff5f3;
  --xing-red-100: #fde9e6;
  --xing-red-200: #facbc5;
  --xing-red-300: #f59b92;
  --xing-red-400: #ff6a5f;
  --xing-red-500: #e83b32;
  --xing-red-600: #c92a24;
  --xing-red-700: #a6201d;
  --xing-red-800: #8f1717;
  --xing-red-900: #4a1012;

  --xing-canvas: #fff9f7;
  --xing-surface: #ffffff;
  --xing-surface-muted: #f7f3f1;
  --xing-border: #e7dedb;
  --xing-border-strong: #cfc3bf;
  --xing-text: #201a19;
  --xing-text-secondary: #6f6562;
  --xing-text-muted: #978b87;

  --xing-gold: #ffbf3f;
  --xing-sky: #3ba7e8;
  --xing-success: #1f9d68;
  --xing-warning: #d97706;
  --xing-error: #a61924;

  --xing-radius-control: 10px;
  --xing-radius-card: 16px;
  --xing-radius-panel: 24px;

  --xing-shadow-sm: 0 2px 8px rgba(74, 16, 18, 0.06);
  --xing-shadow-md: 0 12px 32px rgba(74, 16, 18, 0.10);
  --xing-shadow-float: 0 20px 50px rgba(32, 26, 25, 0.14);
}
```

---

## 19. Implementation Order

### Phase 1: Foundations

- finalise wordmark variants;
- design Xinng's canonical character sheet;
- implement colour, typography, spacing, radius, shadow, and icon tokens;
- create button, input, badge, card, tabs, navigation, modal, and table primitives.

### Phase 2: Application shell

- sidebar and top bar;
- workspace switcher;
- dashboard home;
- responsive navigation;
- loading, empty, error, and success states.

### Phase 3: Core journeys

- account creation;
- business onboarding;
- business profile;
- module overview;
- campaign and article workspaces;
- basic WordPress plugin screens.

### Phase 4: Public website

- global header and mega menu;
- homepage;
- reusable product landing-page template;
- Scaffold page;
- marketplace page;
- pricing;
- `/learn` editorial system;
- global footer.

### Phase 5: Quality control

- mobile and tablet refinement;
- accessibility audit;
- component consistency review;
- screenshot and illustration standards;
- content and empty-state review;
- performance optimisation.

---

## 20. Final Standard

A Xing screen or landing page is successful when it:

1. has one obvious purpose;
2. makes the next action clear;
3. feels calm despite the complexity underneath;
4. uses red to establish hierarchy rather than fill space;
5. shows real product value;
6. remains useful without the mascot;
7. becomes more human when Xinng is present;
8. feels recognisably related to the Kiki SaaS system while clearly belonging to Xing.

The governing visual statement is:

> **Kiki's clean, product-led SaaS system—rebuilt in warm Xing Ember, grounded in real business workflows, and given personality by Xinng the dragon.**
