const roadmapTask=(milestone,title,description,acceptance,refs,priority='High',dependency='')=>({milestone,title,description,acceptance,refs,priority,dependency});

const TRAINS = [
  {key:'platform',code:'A',name:'Platform & workspace',summary:'Identity, organizations, navigation and shared services',owner:'Platform Squad',color:'#d8ff57',tasks:[
    'Stabilize account creation, sign-in and password recovery',
    'Implement organizations, membership and workspace switching',
    'Define server-side RBAC and specialist role scopes',
    'Consolidate desktop, mobile and permission-aware navigation',
    'Build authorized cross-product global search',
    'Create cross-product dashboard health and activity views',
    'Standardize files, media, notifications and audit services',
    'Introduce versioned database migration discipline',
    'Add background jobs, schedules and provider adapters',
    'Verify workspace isolation, accessibility and release controls'
  ]},
  {key:'presence',code:'B',name:'Presence',summary:'Pages, short links and QR experiences',owner:'Presence Squad',color:'#d7ebff',tasks:[
    'Complete canonical personal and organization Page records',
    'Build reusable Page blocks, themes and live preview',
    'Harden public Page publishing, slugs and analytics',
    'Complete short-link lifecycle and safe redirect validation',
    'Add attribution, expiry and exportable link analytics',
    'Unify Page-based and destination-based QR creation',
    'Complete QR styling, logo and error-correction controls',
    'Generate reliable PNG, SVG and print-ready outputs',
    'Deliver anonymous QR result and email-unlock flow',
    'Validate scan attribution, privacy and abuse prevention'
  ]},
  {key:'audience',code:'C',name:'Audience',summary:'Contacts, companies, lists, forms and lead capture',owner:'Audience Squad',color:'#f0dafa',tasks:[
    'Create the canonical contact and contact-method schema',
    'Add companies, owners, lifecycle stages and custom fields',
    'Build contact dashboard, search and quick-view surfaces',
    'Implement contact profiles and unified Moments timeline',
    'Deliver CSV import with mapping and partial-success reports',
    'Implement deduplication, normalization and merge controls',
    'Build static lists and dynamic AND/OR segments',
    'Create hosted and embeddable lead forms',
    'Connect form submissions to contacts, lists and attribution',
    'Add consent, suppression, export and deletion controls'
  ]},
  {key:'communications',code:'D',name:'Communications',summary:'Campaigns, email, SMS, inbox and notifications',owner:'Messaging Squad',color:'#ffd9c9',tasks:[
    'Create canonical campaign records and lifecycle states',
    'Build channel messages, audiences and approval workflow',
    'Deliver email composer, plain-text fallback and test sends',
    'Enforce unsubscribe, suppression and sender requirements',
    'Add provider delivery, bounce, complaint and click events',
    'Implement SMS segments, cost estimates and opt-out handling',
    'Build contact-centered unified inbox and assignment',
    'Complete account, security and subscription emails',
    'Automate weekly analytics communications',
    'Harden in-app notifications, retries and delivery logs'
  ]},
  {key:'flow',code:'E',name:'Flow automation',summary:'Triggered journeys, branching and reliable execution',owner:'Automation Squad',color:'#d8efe5',tasks:[
    'Create flows, versions, nodes and connection schemas',
    'Build desktop visual canvas and node inspector',
    'Implement contact, list, tag, form and manual triggers',
    'Implement email, SMS, delay and contact-update actions',
    'Add if/else branching, goals and terminal nodes',
    'Create publish validation and immutable run versions',
    'Add enrollment, re-enrollment and frequency limits',
    'Build idempotent execution queue, retries and dead letters',
    'Deliver test-contact simulation and run inspection',
    'Add monitoring, cost controls and mobile operations'
  ]},
  {key:'pr',code:'F',name:'PR & publishers',summary:'PR workflow, publisher marketplace and reporting',owner:'PR Squad',color:'#ffe8a8',tasks:[
    'Build PR dashboard and performance overview',
    'Create campaign-linked and standalone release briefs',
    'Implement development, review and approval workflow',
    'Add market, outlet, package and order configuration',
    'Connect PR orders to verified payment and invoices',
    'Build publisher profiles, markets and core offers',
    'Add optional paid add-ons and transparent cart totals',
    'Create publisher request, evidence and payout workflow',
    'Generate distribution evidence and exportable reports',
    'Add PR status notifications, permissions and audits'
  ]},
  {key:'social',code:'G',name:'Social, content & tasks',summary:'Publishing, content assistance and nano-task distribution',owner:'Growth Squad',color:'#e4dcff',tasks:[
    'Create authorized social account connection framework',
    'Build platform-aware post composer and preview',
    'Implement scheduling calendar, queue and failure recovery',
    'Create shared media library and asset permissions',
    'Add post engagement and performance analytics',
    'Build review-first multi-channel AI content assistance',
    'Create nano-task campaign basics and eligibility rules',
    'Add budget, reward, geography and participation limits',
    'Implement evidence submission, review and disputes',
    'Enforce anti-deception rules and authorized payouts'
  ]},
  {key:'intelligence',code:'H',name:'Intelligence',summary:'Analytics, narrative, monitoring and controlled AI',owner:'Data & AI',color:'#dce8ff',tasks:[
    'Define the shared versioned analytics event envelope',
    'Separate verified, observed, attributed and forecast metrics',
    'Build product and portfolio analytics pipelines',
    'Create source provenance, freshness and confidence model',
    'Deliver media and social monitoring foundation',
    'Prototype narrative snapshots and explainable scores',
    'Add spike, crisis and opportunity detection',
    'Define SEO, journalist and LLM visibility data models',
    'Build provider-neutral AI gateway and validation',
    'Add permission-aware RAG, telemetry and AI action logs'
  ]},
  {key:'events',code:'I',name:'Events',summary:'Planning, commerce and professional event operations',owner:'Events Squad',color:'#f6e4ff',tasks:[
    'Deliver Event Tracker workspace, tasks and readiness',
    'Build event pages, registration, tickets and check-in',
    'Launch discovery, print QR and offline validation',
    'Build programmes, speakers, rooms and conflict checks',
    'Deliver exhibitors, SponsorMatch and fulfilment tracking',
    'Add vendors, budgets, RFQs, POs and delivery controls',
    'Build attendee app, networking, passes and venue commerce',
    'Prepare legally gated Event Fund and reconciliation flows',
    'Add event advertising, intelligence and AI assistance',
    'Pilot high-speed QR, NFC and RFID access integrations'
  ]},
  {key:'creator',code:'J',name:'Creator tools',summary:'Qualification, revenue planning and platform insight',owner:'Creator Tools',color:'#e7f3c0',tasks:[
    'Complete anonymous and optional-handle calculator entry',
    'Version X rules and calculation records',
    'Harden qualification gaps and rolling-window velocity',
    'Complete reverse posting-frequency and reach calculations',
    'Version earnings ranges and scenario modelling',
    'Unify qualified and non-qualified result presentation',
    'Complete Xinng share cards and tokenized public results',
    'Add analytics, methodology and MVP acceptance tests',
    'Build provider-neutral screenshot extraction with confirmation',
    'Prepare Instagram, TikTok and unified creator adapters'
  ]},
  {key:'commerce',code:'K',name:'Commerce & finance',summary:'Credits, subscriptions, payments and financial controls',owner:'Finance Systems',color:'#ffe3b8',tasks:[
    'Define canonical products, plans and entitlement model',
    'Consolidate provider-neutral checkout sessions',
    'Verify payment outcomes exclusively on the server',
    'Make payment and webhook processing idempotent',
    'Replace balance-only credits with an auditable ledger',
    'Build receipts, transaction history and reconciliation',
    'Implement refunds, reversals and dispute records',
    'Complete subscription renewal, expiry and cancellation',
    'Enforce currency, minor-unit and immutable reference rules',
    'Separate approval, execution and manual adjustment roles'
  ]},
  {key:'admin',code:'L',name:'Admin & operations',summary:'Governance, reliability, moderation and release control',owner:'Operations Squad',color:'#ece9e2',tasks:[
    'Build authorized user and organization administration',
    'Create plan, credit and product configuration controls',
    'Add campaign, delivery and provider health monitoring',
    'Create PR, publisher and payment exception queues',
    'Build template, abuse and moderation operations',
    'Add background-job inspection, retry and failure tools',
    'Implement feature flags and staged release controls',
    'Centralize structured logs, health checks and alerts',
    'Verify backup, restore and analytics freshness procedures',
    'Audit impersonation, exports and financial adjustments'
  ]},
  {key:'nano',code:'M',name:'Xinng Nano',summary:'Missions, creators, influencers, campaigns and a lightweight gamified work economy',owner:'Nano Product',color:'#d9f7e8',tasks:[
    roadmapTask('N0','Define Nano architecture, safety policies and economy rules','Establish the canonical mission, campaign, participant, evidence, reward, dispute, reputation and payout boundaries. Keep credits, XP, earnings, stablecoins and any future token financially separate.','Approved state diagrams, safety rules, anti-fraud controls, data ownership and audit events are documented; prohibited missions cannot be published.','nano.md; XINNGPRDfull.md; config.php','Critical','Platform identity, campaigns and finance contracts'),
    roadmapTask('N1','Build Nano identity and creator profiles','Extend shared identity with worker, creator and influencer profiles, portfolios, connected accounts, eligibility attributes and verified performance evidence.','A user can maintain one permission-scoped profile; account connections show provenance and freshness; private attributes are not publicly exposed.','nano.md; users; organizations; contacts','High','N0'),
    roadmapTask('N2','Create and fund Nano missions','Let authorized businesses create digital, physical, social, event and professional missions with budgets, rewards, eligibility, capacity and deadlines.','Publishing requires a valid brief, funded or approved budget, eligibility rules and safety validation; totals are calculated server-side.','nano.md; campaigns; payments; event records','Critical','N0, Platform Finance ledger'),
    roadmapTask('N3','Deliver mission claiming and execution','Support discovery, matching, applications or claims, capacity reservation, deadlines, instructions and participant progress.','Eligible users can claim without oversubscription; concurrent claims are safe; expiry releases capacity; every transition is auditable.','nano.md; mission marketplace; notifications','High','N1, N2'),
    roadmapTask('N4','Verify evidence, disputes and payouts','Provide structured evidence submission, review queues, approval, rejection, appeals, disputes and funded reward settlement.','Evidence is immutable after review begins; reviewer conflicts are controlled; approved rewards create real-money obligations and idempotent payout records.','nano.md; files; payouts; audit logs','Critical','N3, payment adapters'),
    roadmapTask('N5','Add XP, levels, achievements and reputation','Create non-withdrawable progression based on verified participation, quality, reliability and safety outcomes.','XP and reputation cannot be purchased or withdrawn; reversals follow overturned decisions; rules are versioned and explainable.','nano.md; gamification ledger','Medium','N4'),
    roadmapTask('N6','Launch the influencer marketplace','Add discovery, matching, rate cards, briefs, contracts, content submissions, deliverables, analytics and settlements for creators.','Brands can shortlist and contract eligible creators; deliverables and performance retain evidence; settlement is separate from credits and XP.','nano.md; creator profiles; campaigns; analytics','High','N1, N4'),
    roadmapTask('N7','Support mega influencer and agency management','Model celebrities, talent agencies, rosters, private negotiations, ambassador contracts, appearances, licensing, exclusivity and manager commissions.','Commercial terms are private and permission-scoped; commission and exclusivity rules are explicit; enterprise reports reconcile obligations.','nano.md; organizations; contracts; finance','Medium','N6, enterprise permissions'),
    roadmapTask('N8','Unify multi-tier campaigns','Allow one campaign to coordinate mega contracts, influencer collaborations and Nano missions against a shared objective and budget.','A campaign shows tier-specific commitments and outcomes without double counting spend, reach, deliverables or conversions.','nano.md; campaigns; contacts; events; analytics','High','N4, N6, N7'),
    roadmapTask('N9','Release the lightweight 2D Nano World','Create avatar-led mission districts, guilds, leaderboards, team missions and seasonal challenges as a responsive 2D experience.','Core mission work remains usable without the game layer; reduced-motion and accessible alternatives exist; no complex 3D dependency is introduced.','nano.md; Nano World web client','Medium','N5, N8'),
    roadmapTask('N10','Integrate approved stablecoin payment rails','Add provider-adapted stablecoin collection or payout records where legally and operationally approved.','Stablecoin records retain asset, network, fiat equivalent, provider reference and reconciliation state; custody is not mixed with credits.','nano.md; payment adapters; reconciliation','Low','N4, compliance approval'),
    roadmapTask('N11','Complete XING token economics and legal assessment','Evaluate fixed supply, utility, treasury, vesting, distribution, wallet, merchant, security and regulatory requirements without issuing a token.','Legal, security, commercial and technical decisions are documented; Nano remains fully functional without a native token.','nano.md; legal assessment; threat model','Low','N5, N10'),
    roadmapTask('N12','Prepare a controlled XING token pilot','Define a reversible, jurisdiction-limited pilot with explicit approvals, limits, monitoring and exit criteria.','No launch occurs without signed legal and security gates; guaranteed mission rewards remain fiat or approved stable-value obligations.','nano.md; pilot runbook; compliance approvals','Low','N11')
  ]},
  {key:'platform_finance',code:'N',name:'Platform Finance — Subscription & Credits',summary:'Entitlements, configurable plans, metered usage, credit ledgers and provider-neutral payments',owner:'Platform Finance',color:'#ffe0b5',tasks:[
    roadmapTask('PF0','Audit existing subscriptions, credits and payments','Inventory current credit balances, transactions, Paystack and NOWPayments flows, checkout paths, webhooks and finance UI before migration.','A signed audit maps implemented, partial and missing capabilities; working integrations are preserved; migration risks and data gaps have owners.','config.php; credits.php; credit-checkout.js; payment callbacks','Critical','None'),
    roadmapTask('PF1','Create configurable subscription plan records','Model Nigeria Starter, Basic, Plus, Pro and Enterprise prices, billing periods, currencies, entitlements and allowance parameters outside source code.','Administrators can version plan configuration; displayed price matches checkout; no final credit allocations are hardcoded.','plans; products; admin finance configuration','Critical','PF0'),
    roadmapTask('PF2','Enforce Nigeria Starter eligibility','Use verified Nigerian billing/payment signals and anti-abuse checks rather than IP alone.','Ineligible checkout is rejected server-side with an explanation; eligibility evidence is auditable and privacy-minimized.','eligibility service; payment customer data','High','PF1'),
    roadmapTask('PF3','Implement entitlement management','Resolve active product access independently from remaining credits across subscription lifecycle states.','Zero credits never deactivates an otherwise active subscription; non-metered entitled features remain usable.','subscriptions; entitlement resolver','Critical','PF1'),
    roadmapTask('PF4','Build separate subscription and purchased credit ledgers','Replace balance-only accounting with immutable buckets, grants, debits, expiries, reversals and adjustments.','Subscription credits debit first; purchased credits remain separate; balances reconcile to ledger entries under concurrency.','credit ledger; users migration','Critical','PF0, PF3'),
    roadmapTask('PF5','Create versioned usage rate cards','Configure credit pricing by operation, provider, model, resource unit and effective date using a cost-and-margin formula.','Rates are administrative data; estimates cite a rate-card version; one credit is not assumed to equal fiat currency.','usage_rate_cards; admin console','High','PF4'),
    roadmapTask('PF6','Meter AI and variable provider consumption','Emit identifiable usage events for AI text, documents, images, agents, OSINT, extraction, research, automation and bulk external services.','Each metered execution records actor, product, provider, units, actual provider cost and rate-card version; ordinary viewing is unmetered.','AI gateway; provider adapters; usage_events','High','PF5'),
    roadmapTask('PF7','Reserve and finalize credits atomically','Estimate expensive work, reserve available credits before execution, then finalize actual debits and release unused amounts.','Duplicate requests cannot double-charge; concurrent requests cannot overspend; reservation expiry is recoverable.','credit_reservations; ledger service','Critical','PF4, PF5'),
    roadmapTask('PF8','Recover credits for failed operations','Reverse or release charges for provider failure, cancellation and verified partial execution according to policy.','Recovery is idempotent, traceable to the usage event and reflected in user history and reconciliation.','usage_events; credit ledger; job runner','High','PF7'),
    roadmapTask('PF9','Deliver credit top-up checkout','Offer configurable packages through provider-neutral checkout while preserving Paystack and NOWPayments.','Only verified server callbacks grant purchased credits; duplicate callbacks are harmless; receipts show price, currency and credits.','credits.php; Paystack; NOWPayments; checkout sessions','Critical','PF4, PF13'),
    roadmapTask('PF10','Allocate and reset subscription credits at renewal','Grant cycle allowances, expire/reset them under disclosed terms and retain immutable history.','Exactly one allowance posts per successful billing cycle; retries do not duplicate grants; purchased credits do not expire with the cycle.','subscription cycles; credit ledger','Critical','PF3, PF4'),
    roadmapTask('PF11','Handle upgrades, downgrades and cancellation','Define entitlement timing, proration, allowance changes, failed payments, grace periods, refunds and reversals.','Every lifecycle transition is deterministic, provider-verified, notified and auditable; downgrade cannot silently erase purchased credits.','subscriptions; invoices; webhooks','High','PF3, PF10'),
    roadmapTask('PF12','Build Enterprise seats and shared credit pools','Support five included seats, paid additional seats, organization admins, assignments, shared pools and per-user limits.','Extra seats add access only and do not automatically add 10× Pro credits; allowances and contractual limits remain configurable.','organizations; memberships; enterprise billing','High','PF1, PF3, PF4'),
    roadmapTask('PF13','Implement payment provider adapters','Normalize Paystack and NOWPayments as integrated adapters and retain Pandascrow as planned until production verification.','Provider payloads map to one transaction contract; secrets stay outside source; signed callbacks and idempotency are verified per adapter.','payments; Paystack; NOWPayments; planned Pandascrow','Critical','PF0'),
    roadmapTask('PF14','Send balance and lifecycle notifications','Notify users and organization admins about low/exhausted credits, renewals, failed payments, plan changes and top-ups.','Thresholds and recipients are configurable; notifications are deduplicated, accessible and linked to the relevant account action.','notifications; emails_log; subscriptions','Medium','PF3, PF4, PF10'),
    roadmapTask('PF15','Provide usage and cost analytics','Show credit consumption by product, user, team, provider and period plus provider cost and margin indicators.','Reports reconcile to the ledger, respect permissions and distinguish estimates, reserved usage and finalized usage.','analytics; usage_events; enterprise reports','High','PF6, PF7, PF12'),
    roadmapTask('PF16','Reconcile payments, credits and obligations','Reconcile provider receipts, subscription invoices, credit grants, refunds, creator earnings, mission rewards and vendor obligations without merging balances.','Exceptions are queued; fiat, credits, XP, stablecoins and future tokens remain separate ledgers and report lines.','reconciliation; payments; payouts','Critical','PF4, PF9, PF13'),
    roadmapTask('PF17','Create finance administration controls','Allow authorized operators to manage plans, rate cards, allowances, adjustments, provider state and exceptions with separation of duties.','Every change is permission-checked, versioned and audited; adjustment approvers cannot silently execute their own requests.','admin console; audit_logs; RBAC','High','PF1, PF5, PF16'),
    roadmapTask('PF18','Harden finance security and automated tests','Add concurrency, idempotency, webhook-signature, authorization, migration and end-to-end tests for subscription and credit flows.','Critical finance suites pass; replay and race tests prove no duplicate money or credit movement; logs contain no credentials.','test suite; CI; security runbooks','Critical','PF3–PF17'),
    roadmapTask('PF19','Complete finance documentation and deployment readiness','Publish operating procedures, migration and rollback plans, support playbooks, dashboards and launch gates.','Staging reconciliation passes; provider readiness is recorded; Pandascrow remains planned until verified; owners approve rollout.','finance runbook; deployment checklist; PRDs','High','PF18')
  ]}
];

const priorities=['Critical','High','High','Medium','Medium','Medium','Medium','Low','Low','Low'];
const phaseNames=['Foundation','Core build','Integration','Hardening','Release'];
const tasks=[];

const trainGuidance={
  platform:'workspace isolation, RBAC, auditability and shared-service contracts',presence:'public rendering, branded assets, attribution and safe destination handling',audience:'contact identity, consent, deduplication and lifecycle history',communications:'channel compliance, provider events, deliverability and message history',flow:'versioned definitions, deterministic execution, retries and run inspection',pr:'editorial workflow, publisher evidence, order state and transparent reporting',social:'authorized accounts, platform constraints, evidence review and anti-abuse controls',intelligence:'source provenance, confidence, freshness, permissions and explainability',events:'organizer operations, attendee experience, fulfilment and onsite resilience',creator:'rule versions, user-confirmed inputs, methodology and shareable outcomes',commerce:'server verification, immutable ledgers, reconciliation and financial separation of duties',admin:'least privilege, exception queues, audit evidence and operational recovery',nano:'mission safety, evidence, funded rewards, creator trust and campaign reuse',platform_finance:'entitlements, immutable ledgers, metering, provider verification and reconciliation'
};

function buildSubtasks(task){
  const subject=task.title.replace(/^(Add|Build|Complete|Consolidate|Create|Define|Deliver|Enforce|Generate|Harden|Implement|Introduce|Launch|Prepare|Prototype|Replace|Separate|Stabilize|Standardize|Unify|Validate|Verify|Version)\s+/i,'');
  const context=trainGuidance[task.train];
  const short=subject.charAt(0).toUpperCase()+subject.slice(1);
  return [
    {title:`Define ${subject} contract`,detail:`Document the actors, happy path, edge cases, inputs, outputs and measurable acceptance criteria for ${task.title.toLowerCase()}. Align the contract with ${context}.`},
    {title:`Model ${subject} data and access`,detail:`Specify the records, state transitions, validation rules, ownership boundaries and permission checks needed for ${subject.toLowerCase()}. Include migration and audit requirements where existing Xinng data is affected.`},
    {title:`Implement ${subject} experience`,detail:`Build the primary desktop and mobile workflow for ${subject.toLowerCase()}, including loading, empty, error and recovery states. Use shared Xinng navigation, components and accessibility conventions.`},
    {title:`Connect ${subject} to the Xinng spine`,detail:`Integrate ${subject.toLowerCase()} with the relevant identity, organization, contact, media, notification, commerce and analytics services. Preserve the dependency on ${task.dependency} and make failure handling observable.`},
    {title:`Verify and release ${short}`,detail:`Add unit, integration and permission tests; verify analytics and audit events; document operational ownership; and capture evidence that the ${task.phaseName.toLowerCase()} acceptance criteria are satisfied before handoff.`}
  ].map((item,index)=>({...item,id:`${task.id}-S${index+1}`,done:false}));
}

TRAIN_DEFS();

function TRAIN_DEFS(){
  let number=1;
  TRAINS.forEach((train,trainIndex)=>train.tasks.forEach((definition,index)=>{
    const item=typeof definition==='string'?{title:definition}:definition;
    const title=item.title;
    const start=new Date('2026-10-19T00:00:00');
    start.setDate(start.getDate()+trainIndex*7+index*3);
    const due=new Date(start); due.setDate(due.getDate()+7);
    const initial=index===0&&['platform','presence','communications','creator'].includes(train.key)?'In progress':'To do';
    const fallbackPhaseIndex=Math.min(phaseNames.length-1,Math.floor(index/2));
    const phaseName=item.milestone||phaseNames[fallbackPhaseIndex];
    tasks.push({
      id:`XNG-${String(number).padStart(3,'0')}`,
      train:train.key,trainCode:train.code,trainName:train.name,
      phase:`${train.key}-${String(phaseName).toLowerCase().replace(/[^a-z0-9]+/g,'-')}`,
      phaseName,
      title,description:item.description||train.summary,
      acceptance:item.acceptance||`The documented ${title.toLowerCase()} workflow passes functional, permission, accessibility, analytics and audit verification.`,
      refs:item.refs||'XINNGPRDfull.md',
      owner:train.owner,priority:item.priority||priorities[index]||'Medium',status:initial,
      start:dateLabel(start),due:dateLabel(due),
      dependency:item.dependency||(index===0?(trainIndex===0?'None':`Train ${String.fromCharCode(64+trainIndex)} foundation`):`XNG-${String(number-1).padStart(3,'0')}`),
      notes:'',subtasks:[]
    });
    tasks[tasks.length-1].subtasks=buildSubtasks(tasks[tasks.length-1]);
    number++;
  }));
}

const STORAGE='xinng-master-tracker-v3';
const saved=JSON.parse(localStorage.getItem(STORAGE)||'{}');
tasks.forEach((task,index)=>{
  const stored=saved[task.id];
  if(!stored)return;
  const storedSubtasks=stored.subtasks||{};
  tasks[index]={...task,status:stored.status||task.status,notes:stored.notes||'',subtasks:task.subtasks.map(subtask=>({...subtask,done:Boolean(storedSubtasks[subtask.id])}))};
});

let activeTask=null;
let currentView='list';
let focusMode=false;
let activeTrain='all';
const $=selector=>document.querySelector(selector);

function dateLabel(date){return date.toLocaleDateString('en-GB',{day:'2-digit',month:'short',year:'numeric'});}
function slug(value){return value.toLowerCase().replaceAll(' ','-');}
function scopedTasks(){return activeTrain==='all'?tasks:tasks.filter(task=>task.train===activeTrain);}
function persist(){const state={};tasks.forEach(task=>state[task.id]={status:task.status,notes:task.notes,subtasks:Object.fromEntries(task.subtasks.map(subtask=>[subtask.id,subtask.done]))});localStorage.setItem(STORAGE,JSON.stringify(state));$('#saveState').textContent='Saved locally · just now';updateMetrics();}

function filtered(){
  const query=$('#searchInput').value.toLowerCase();
  const phase=$('#phaseFilter').value;
  const status=$('#statusFilter').value;
  const owner=$('#ownerFilter').value;
  return scopedTasks().filter(task=>(phase==='all'||task.phase===phase)&&(status==='all'||task.status===status)&&(owner==='all'||task.owner===owner)&&(!query||`${task.id} ${task.title} ${task.trainName} ${task.owner} ${task.phaseName} ${task.subtasks.map(item=>`${item.title} ${item.detail}`).join(' ')}`.toLowerCase().includes(query))&&(!focusMode||['In progress','Blocked','Review'].includes(task.status)||(task.status==='To do'&&task.priority==='Critical')));
}

function updateMetrics(){
  const scope=scopedTasks();
  const done=scope.filter(task=>task.status==='Done').length;
  const active=scope.filter(task=>task.status==='In progress').length;
  const blocked=scope.filter(task=>task.status==='Blocked').length;
  const critical=scope.filter(task=>task.priority==='Critical'&&task.status!=='Done').length;
  const percent=Math.round(done/scope.length*100)||0;
  $('#totalMetric').textContent=scope.length;
  $('#overallPercent').textContent=`${percent}%`;
  $('#progressRing').style.setProperty('--progress',`${percent*3.6}deg`);
  $('#doneSummary').textContent=`${done} of ${scope.length} tasks done`;
  $('#activeMetric').textContent=active;
  $('#blockedMetric').textContent=blocked;
  $('#criticalMetric').textContent=critical;
  $('#readinessLabel').textContent=percent<15?'Foundation':percent<40?'Core product':percent<70?'Expansion':percent<95?'Launch hardening':'Release ready';
}

function taskRow(task){const complete=task.subtasks.filter(item=>item.done).length;return `<div class="task-row" data-id="${task.id}"><button class="task-check ${task.status==='Done'?'done':''}" data-check="${task.id}" aria-label="${task.status==='Done'?'Reopen':'Complete'} ${task.id}">${task.status==='Done'?'✓':''}</button><span class="task-id">${task.id}</span><div class="task-name"><strong>${task.title}</strong><small>Train ${task.trainCode} · ${task.phaseName} · ${complete}/${task.subtasks.length} subtasks · depends on ${task.dependency}</small></div><span class="task-owner">${task.owner}</span><span class="priority ${slug(task.priority)}">${task.priority}</span><span class="status ${slug(task.status)}">${task.status}</span><button class="task-open" aria-label="Open task">›</button></div>`;}

function renderList(list){
  const grouped=new Map();
  list.forEach(task=>{
    if(!grouped.has(task.phase))grouped.set(task.phase,{train:TRAINS.find(train=>train.key===task.train),phaseName:task.phaseName,phase:task.phase,items:[]});
    grouped.get(task.phase).items.push(task);
  });
  const dynamicGroups=[...grouped.values()];
  if(!dynamicGroups.length)return '<div class="phase-group"><div class="phase-head">No tasks match these filters.</div></div>';
  return dynamicGroups.map(group=>{
    const all=tasks.filter(task=>task.phase===group.phase);
    const done=all.filter(task=>task.status==='Done').length;
    const percent=Math.round(done/all.length*100);
    return `<section class="phase-group"><header class="phase-head"><div class="phase-title"><span class="phase-index">${group.phaseName}</span><div><span class="phase-family">Train ${group.train.code} - ${group.train.name}</span><h3>${group.phaseName}</h3><small>${group.train.summary}</small></div></div><div class="phase-progress"><span>${done}/${all.length}</span><span class="mini-track"><i style="width:${percent}%"></i></span></div></header><div>${group.items.map(taskRow).join('')}</div></section>`;
  }).join('');
  const groups=[];
  TRAINS.forEach(train=>phaseNames.forEach((phaseName,index)=>{
    const phase=`${train.key}-${index+1}`;
    const items=list.filter(task=>task.phase===phase);
    if(items.length)groups.push({train,phaseName,phase,items});
  }));
  if(!groups.length)return '<div class="phase-group"><div class="phase-head">No tasks match these filters.</div></div>';
  return groups.map(group=>{
    const all=tasks.filter(task=>task.phase===group.phase);
    const done=all.filter(task=>task.status==='Done').length;
    const percent=Math.round(done/all.length*100);
    return `<section class="phase-group"><header class="phase-head"><div class="phase-title"><span class="phase-index">${group.train.code}${phaseNames.indexOf(group.phaseName)+1}</span><div><span class="phase-family">Train ${group.train.code} · ${group.train.name}</span><h3>${group.phaseName}</h3><small>${group.train.summary}</small></div></div><div class="phase-progress"><span>${done}/${all.length}</span><span class="mini-track"><i style="width:${percent}%"></i></span></div></header><div>${group.items.map(taskRow).join('')}</div></section>`;
  }).join('');
}

function renderBoard(list){
  const statuses=['To do','In progress','Blocked','Review','Done'];
  return `<div class="board">${statuses.map(status=>{const items=list.filter(task=>task.status===status);return `<section class="board-column"><header class="board-title"><strong>${status}</strong><span>${items.length}</span></header>${items.map(task=>`<article class="board-card" data-id="${task.id}"><span class="task-id">${task.id} · Train ${task.trainCode}</span><strong>${task.title}</strong><footer><span class="priority ${slug(task.priority)}">${task.priority}</span><span class="task-id">${task.owner}</span></footer></article>`).join('')}</section>`;}).join('')}</div>`;
}

function render(){
  const list=filtered();
  const subtaskCount=list.reduce((total,task)=>total+task.subtasks.length,0);
  $('#resultCount').textContent=`${list.length} task${list.length===1?'':'s'} · ${subtaskCount} subtask${subtaskCount===1?'':'s'}`;
  $('#taskView').innerHTML=currentView==='list'?renderList(list):renderBoard(list);
  document.querySelectorAll('[data-id]').forEach(element=>element.addEventListener('click',event=>{
    if(event.target.dataset.check){const task=tasks.find(item=>item.id===event.target.dataset.check);task.status=task.status==='Done'?'To do':'Done';persist();render();event.stopPropagation();return;}
    openTask(element.dataset.id);
  }));
  updateMetrics();
}

function openTask(id){
  activeTask=tasks.find(task=>task.id===id);
  $('#dialogCode').textContent=`${activeTask.id} · Train ${activeTask.trainCode}`;
  $('#dialogTitle').textContent=activeTask.title;
  $('#dialogDescription').textContent=activeTask.description;
  $('#dialogMeta').innerHTML=[['Train',activeTask.trainName],['Owner',activeTask.owner],['Priority',activeTask.priority],['Milestone',activeTask.phaseName],['Due',activeTask.due],['Dependencies',activeTask.dependency],['Acceptance criteria',activeTask.acceptance],['Files / modules',activeTask.refs]].map(([term,value])=>`<div><dt>${term}</dt><dd>${value}</dd></div>`).join('');
  $('#dialogStatus').value=activeTask.status;
  $('#dialogNotes').value=activeTask.notes||'';
  renderDialogSubtasks();
  $('#taskDialog').showModal();
}

function renderDialogSubtasks(){
  const done=activeTask.subtasks.filter(item=>item.done).length;
  const percent=Math.round(done/activeTask.subtasks.length*100);
  $('#subtaskProgress').textContent=`${done} / ${activeTask.subtasks.length} complete`;
  $('#subtaskProgressBar').style.width=`${percent}%`;
  $('#dialogSubtasks').innerHTML=activeTask.subtasks.map((subtask,index)=>`<label class="subtask-item ${subtask.done?'complete':''}"><input type="checkbox" data-subtask="${subtask.id}" ${subtask.done?'checked':''}><span class="subtask-number">${index+1}</span><span><strong>${subtask.title}</strong><small>${subtask.detail}</small></span></label>`).join('');
  document.querySelectorAll('[data-subtask]').forEach(input=>input.addEventListener('change',()=>{
    const subtask=activeTask.subtasks.find(item=>item.id===input.dataset.subtask);
    subtask.done=input.checked;
    const complete=activeTask.subtasks.every(item=>item.done);
    if(complete&&activeTask.status!=='Done')activeTask.status='Review';
    if(!complete&&activeTask.status==='Done')activeTask.status='In progress';
    $('#dialogStatus').value=activeTask.status;
    persist();renderDialogSubtasks();render();
  }));
}

function populatePhaseFilter(){
  const select=$('#phaseFilter');
  select.innerHTML='<option value="all">All phases</option>';
  const seen=new Set();
  scopedTasks().forEach(task=>{if(!seen.has(task.phase)){seen.add(task.phase);select.insertAdjacentHTML('beforeend',`<option value="${task.phase}">Train ${task.trainCode} - ${task.phaseName}</option>`);}});
  return;
  const trains=activeTrain==='all'?TRAINS:TRAINS.filter(train=>train.key===activeTrain);
  trains.forEach(train=>phaseNames.forEach((phase,index)=>select.insertAdjacentHTML('beforeend',`<option value="${train.key}-${index+1}">Train ${train.code} · ${phase}</option>`)));
}

function setup(){
  $('#trainTabs').innerHTML=`<button type="button" role="tab" data-train="all" class="active" aria-selected="true">All work<span>${tasks.length}</span></button>`+TRAINS.map(train=>`<button type="button" role="tab" data-train="${train.key}" aria-selected="false">${train.name}<span>${train.tasks.length}</span></button>`).join('');
  populatePhaseFilter();
  [...new Set(tasks.map(task=>task.owner))].sort().forEach(owner=>$('#ownerFilter').insertAdjacentHTML('beforeend',`<option>${owner}</option>`));
  document.querySelectorAll('[data-train]').forEach(button=>button.addEventListener('click',()=>{activeTrain=button.dataset.train;populatePhaseFilter();document.querySelectorAll('[data-train]').forEach(item=>{const selected=item===button;item.classList.toggle('active',selected);item.setAttribute('aria-selected',String(selected));});render();}));
  document.querySelectorAll('.filters input,.filters select').forEach(element=>element.addEventListener('input',render));
  $('#resetFilters').onclick=()=>{$('#searchInput').value='';$('#phaseFilter').value=$('#statusFilter').value=$('#ownerFilter').value='all';focusMode=false;$('#focusButton').textContent='Show next actions';render();};
  document.querySelectorAll('.view-switch button').forEach(button=>button.onclick=()=>{currentView=button.dataset.view;document.querySelectorAll('.view-switch button').forEach(item=>item.classList.toggle('active',item===button));render();});
  $('#focusButton').onclick=()=>{focusMode=!focusMode;$('#focusButton').textContent=focusMode?'Show all tasks':'Show next actions';render();};
  $('#saveTask').onclick=()=>{activeTask.status=$('#dialogStatus').value;activeTask.notes=$('#dialogNotes').value.trim();if(activeTask.status==='Done')activeTask.subtasks.forEach(item=>item.done=true);persist();render();$('#taskDialog').close();};
  $('#exportButton').onclick=()=>{const blob=new Blob([JSON.stringify({product:'Xinng Master Product',source:'XINNGPRDfull.md',exportedAt:new Date().toISOString(),tasks},null,2)],{type:'application/json'});const link=document.createElement('a');link.href=URL.createObjectURL(blob);link.download='xinng-master-progress.json';link.click();URL.revokeObjectURL(link.href);};
  $('#resetProgress').onclick=()=>{if(confirm('Reset every task to its original status and remove notes?')){localStorage.removeItem(STORAGE);location.reload();}};
  $('#streamGrid').innerHTML=TRAINS.map(train=>`<article class="stream-card" style="--accent:${train.color}"><header><span class="stream-icon">${train.code}</span><small>${train.tasks.length} tasks</small></header><h3>${train.name}</h3><p>${train.summary}. Reuses shared Xinng services and audit history.</p></article>`).join('');
  render();
}

setup();
