import type { ImageAsset, Project } from '@/types/content';

const cover = (base: string, alt: string): ImageAsset => ({
  base,
  widths: [640, 1280],
  width: 1280,
  height: 720,
  alt,
});

const galleryImage = (base: string, alt: string, caption: string): ImageAsset & { caption: string } => ({
  base,
  widths: [576, 1152],
  width: 1152,
  height: 864,
  alt,
  caption,
});

export const projects: Project[] = [
  {
    id: 'ledgerline',
    slug: 'ledgerline',
    title: 'Ledgerline',
    tagline: 'A double-entry ledger that has never been out of balance.',
    summary:
      'The core money-movement ledger behind Tessellate: immutable journals, streaming settlement and multi-currency accounting for $2.1B in annual volume.',
    year: 2025,
    status: 'live',
    category: 'platform',
    featured: true,
    version: 'v3.2.0',
    companyId: 'tessellate',
    experienceId: 'tessellate-staff',
    role: 'Tech lead & architect',
    team: '7 engineers, 1 PM, 1 finance partner',
    timeline: 'Mar 2024 — ongoing',
    stack: ['Go', 'PostgreSQL', 'Kafka', 'Terraform', 'OpenTelemetry'],
    cover: cover(
      'cover-ledgerline',
      'Risograph illustration of two offset ledger sheets with balanced black bars and one green entry.',
    ),
    gallery: [
      galleryImage(
        'gallery-ledgerline-1',
        'A balance scale with equal stacks on each pan and a green fulcrum.',
        'Every journal balances before it is written: debits and credits are checked in the same transaction.',
      ),
      galleryImage(
        'gallery-ledgerline-2',
        'A chain of identical black blocks ending in a single green block.',
        'Append-only journals: corrections are new entries, never edits.',
      ),
    ],
    overview: [
      'Tessellate moves money for 3,000 marketplaces. Before Ledgerline, balances lived in six services, each with its own idea of what “settled” meant, and the finance team reconciled them by hand every night.',
      'Ledgerline became the single source of truth: an append-only, double-entry ledger with a small, strict API that every product team now writes through.',
    ],
    problem: [
      'Nightly reconciliation took nine hours and failed roughly once a week. When it failed, merchant payouts were delayed and support queues doubled the next morning.',
      'Worse, nobody could answer “what was this account’s balance at 14:03 last Tuesday?” without a data engineer and an afternoon.',
    ],
    approach: [
      {
        title: 'Model the domain before the database',
        description:
          'Two weeks with the finance team produced a glossary and a set of invariants — every journal sums to zero, accounts never change currency — that became types and database constraints.',
      },
      {
        title: 'Immutable journals, derived balances',
        description:
          'Entries are append-only. Balances are projections that can be rebuilt from any point in time, which made historical queries a feature instead of a project.',
      },
      {
        title: 'Dual-write, then cut over',
        description:
          'For eleven weeks every payment was written to both the old services and Ledgerline, with a diff job alerting on any divergence. We cut over when the diff had been empty for 30 days.',
      },
    ],
    architecture: {
      caption: 'Writes flow through a single journal service; everything else is a projection of the journal stream.',
      columns: 4,
      rows: 3,
      nodes: [
        { id: 'products', label: 'Product services', detail: 'Payments, payouts, billing', kind: 'client', column: 0, row: 1 },
        { id: 'journal', label: 'Journal API', detail: 'Go · gRPC · invariants', kind: 'service', column: 1, row: 1 },
        { id: 'pg', label: 'Journal store', detail: 'PostgreSQL · append-only', kind: 'store', column: 2, row: 0 },
        { id: 'stream', label: 'Journal stream', detail: 'Kafka · CDC', kind: 'queue', column: 2, row: 2 },
        { id: 'balances', label: 'Balance projector', detail: 'Point-in-time balances', kind: 'service', column: 3, row: 1 },
        { id: 'settlement', label: 'Settlement', detail: 'Streaming reconciliation', kind: 'service', column: 3, row: 2 },
        { id: 'banks', label: 'Bank partners', detail: 'ISO 20022 files', kind: 'external', column: 3, row: 0 },
      ],
      edges: [
        { from: 'products', to: 'journal', label: 'post journal' },
        { from: 'journal', to: 'pg', label: 'commit' },
        { from: 'pg', to: 'stream', label: 'CDC' },
        { from: 'stream', to: 'balances' },
        { from: 'stream', to: 'settlement' },
        { from: 'settlement', to: 'banks' },
      ],
    },
    features: [
      { title: 'Point-in-time balances', description: 'Query any account at any timestamp in under 40ms.' },
      { title: 'Multi-currency journals', description: 'FX legs are first-class entries with locked rates and audit trails.' },
      { title: 'Streaming settlement', description: 'Bank files are matched continuously instead of once a night.' },
      { title: 'Idempotent writes', description: 'Every request carries a key; retries are always safe.' },
    ],
    challenges: [
      {
        title: 'Hot accounts',
        description:
          'A handful of platform accounts received 40% of all writes. We sharded them into sub-accounts that roll up, removing the lock contention without changing the public API.',
      },
      {
        title: 'Earning finance’s trust',
        description:
          'The finance team had been burned by “new systems” before. Weekly diff reports in their own spreadsheet format did more than any architecture diagram.',
      },
    ],
    metrics: [
      { id: 'close', value: '12 min', label: 'Daily close', detail: 'down from 9 hours' },
      { id: 'volume', value: '$2.1B', label: 'Annual volume', detail: 'across 14 currencies' },
      { id: 'imbalance', value: '0', label: 'Unbalanced journals', detail: 'since cut-over' },
      { id: 'latency', value: '38ms', label: 'p99 write', detail: 'at 1,800 writes/s' },
    ],
    links: [
      { label: 'Engineering blog post', url: 'https://tessellate.example/blog/ledgerline', kind: 'writeup' },
      { label: 'Talk: Ledgers are just logs', url: 'https://youtube.com/watch?v=ledgerline', kind: 'talk' },
    ],
  },
  {
    id: 'relay',
    slug: 'relay',
    title: 'Relay',
    tagline: 'Shipment tracking rebuilt around events instead of polling.',
    summary:
      'A streaming visibility platform for Lattice Freight that turned four-minute-old tracking data into three-second updates for 1,200 carriers.',
    year: 2023,
    status: 'maintained',
    category: 'platform',
    featured: true,
    version: 'v2.4.1',
    companyId: 'lattice',
    experienceId: 'lattice-senior',
    role: 'Tech lead',
    team: '5 engineers, 1 designer',
    timeline: 'Jan 2022 — Sep 2023',
    stack: ['TypeScript', 'Node.js', 'Kafka', 'PostgreSQL', 'React', 'Kubernetes'],
    cover: cover(
      'cover-relay',
      'Risograph illustration of a routing network on a cartographic grid with one green route highlighted.',
    ),
    gallery: [
      galleryImage(
        'gallery-relay-1',
        'Parallel lanes with black rounded blocks moving along them and one green block ahead.',
        'The dispatch board view: every shipment is a position on a lane, updated as events arrive.',
      ),
      galleryImage(
        'gallery-relay-2',
        'Scattered dots funnelling through converging lines into an ordered column.',
        'Carrier events arrive out of order and in bursts; Relay orders and de-duplicates them per shipment.',
      ),
    ],
    overview: [
      'Lattice Freight coordinates road freight for European retailers. Customers wanted to know where their pallets were; the old tracker polled carrier APIs every five minutes and often showed trucks in yesterday’s city.',
      'Relay replaced polling with an event pipeline that ingests webhooks, EDI files and GPS pings, normalises them into a single shipment timeline and pushes updates to customers in real time.',
    ],
    problem: [
      'Polling 1,200 carrier APIs on a schedule was slow, expensive and fragile. Each new carrier took six weeks of bespoke integration work.',
      'Customers compensated by calling support: 38% of all tickets were “where is my shipment?”.',
    ],
    approach: [
      {
        title: 'One canonical event model',
        description:
          'We defined a small vocabulary — picked up, departed, arrived, delayed, delivered — and mapped every carrier format to it at the edge.',
      },
      {
        title: 'An SDK for integrations',
        description:
          'Carrier adapters became small, tested TypeScript packages with a shared contract test suite, so partners could write their own.',
      },
      {
        title: 'Timeline per shipment',
        description:
          'Events are keyed by shipment and folded into a timeline, which made out-of-order and duplicate events a solved problem rather than a daily bug.',
      },
    ],
    architecture: {
      caption: 'Carrier adapters normalise events at the edge; everything downstream speaks one event model.',
      columns: 4,
      rows: 3,
      nodes: [
        { id: 'carriers', label: 'Carriers', detail: 'Webhooks · EDI · GPS', kind: 'external', column: 0, row: 1 },
        { id: 'adapters', label: 'Adapter fleet', detail: 'TypeScript SDK', kind: 'service', column: 1, row: 1 },
        { id: 'events', label: 'Event bus', detail: 'Kafka · keyed by shipment', kind: 'queue', column: 2, row: 1 },
        { id: 'timeline', label: 'Timeline service', detail: 'Fold · dedupe · ETA', kind: 'service', column: 3, row: 0 },
        { id: 'store', label: 'Timeline store', detail: 'PostgreSQL', kind: 'store', column: 3, row: 2 },
        { id: 'app', label: 'Tracking app', detail: 'React · SSE', kind: 'client', column: 2, row: 0 },
      ],
      edges: [
        { from: 'carriers', to: 'adapters' },
        { from: 'adapters', to: 'events', label: 'normalised' },
        { from: 'events', to: 'timeline' },
        { from: 'timeline', to: 'store' },
        { from: 'timeline', to: 'app', label: 'push' },
      ],
    },
    features: [
      { title: 'Live dispatch board', description: 'Every active shipment on one screen, updated as events arrive.' },
      { title: 'Predictive ETAs', description: 'Arrival estimates from historical lane data, refreshed per event.' },
      { title: 'Carrier SDK', description: 'Typed adapters with contract tests; onboarding in days, not weeks.' },
      { title: 'Customer webhooks', description: 'Retailers subscribe to the same timeline events we use internally.' },
    ],
    challenges: [
      {
        title: 'Clock skew across carriers',
        description:
          'Some carriers reported local time without zones. We added per-carrier clock profiles and flagged impossible sequences rather than silently reordering them.',
      },
      {
        title: 'Migrating without a freeze',
        description:
          'Customers could not lose tracking for a day. We ran both systems for three months and moved carriers over in cohorts by volume.',
      },
    ],
    metrics: [
      { id: 'latency', value: '3s', label: 'p95 update latency', detail: 'down from 4 minutes' },
      { id: 'onboarding', value: '4 days', label: 'Carrier onboarding', detail: 'down from 6 weeks' },
      { id: 'tickets', value: '−61%', label: '“Where is it?” tickets', detail: 'in six months' },
      { id: 'events', value: '42M', label: 'Events per day', detail: 'at peak season' },
    ],
    links: [{ label: 'Case study on the Lattice blog', url: 'https://latticefreight.example/relay', kind: 'writeup' }],
  },
  {
    id: 'atlas',
    slug: 'atlas-design-system',
    title: 'Atlas Design System',
    tagline: 'Tokens, components and RTL support for four product teams.',
    summary:
      'The shared design system at Tessellate: 60 accessible components, a token pipeline shared by web and mobile, and first-class right-to-left layouts.',
    year: 2024,
    status: 'live',
    category: 'design-system',
    featured: true,
    version: 'v4.1.0',
    companyId: 'tessellate',
    experienceId: 'tessellate-staff',
    role: 'Engineering lead',
    team: '3 engineers, 2 designers',
    timeline: 'Jun 2024 — ongoing',
    stack: ['TypeScript', 'React', 'CSS & design tokens', 'Accessibility', 'React Native'],
    cover: cover(
      'cover-atlas',
      'Risograph specimen sheet of geometric primitives in a grid with one green square.',
    ),
    gallery: [
      galleryImage(
        'gallery-atlas-1',
        'A row of interface control outlines on a baseline: button, toggle with green knob, checkbox, slider and input.',
        'Component specimens share one baseline grid and one set of interaction states.',
      ),
      galleryImage(
        'gallery-atlas-2',
        'A scale of black squares growing in size above a row of grey swatches ending in green.',
        'Spacing and colour tokens are generated once and shipped to web, iOS and Android.',
      ),
    ],
    overview: [
      'Four product teams were building four slightly different buttons. Atlas started as a token file and a promise; two years later it is the default way Tessellate builds interfaces.',
      'The system ships as a React library, a React Native library and a token package consumed by marketing sites, all generated from one source.',
    ],
    problem: [
      'Inconsistent components meant inconsistent accessibility: keyboard support varied by team and two products failed their accessibility audit.',
      'Arabic-speaking markets were growing, but every screen had been built assuming left-to-right.',
    ],
    approach: [
      {
        title: 'Tokens as the contract',
        description:
          'Colour, spacing, type and motion live in a single token source, compiled to CSS variables, Swift and Kotlin constants.',
      },
      {
        title: 'Logical properties everywhere',
        description:
          'Every component uses logical CSS properties, so right-to-left layouts are a document attribute rather than a fork.',
      },
      {
        title: 'Adoption through migration tooling',
        description:
          'Codemods moved 1,400 call sites from legacy components to Atlas, so teams adopted it during normal sprints.',
      },
    ],
    architecture: {
      caption: 'One token source compiles to every platform; components consume tokens, never raw values.',
      columns: 4,
      rows: 3,
      nodes: [
        { id: 'figma', label: 'Design source', detail: 'Figma variables', kind: 'external', column: 0, row: 1 },
        { id: 'tokens', label: 'Token pipeline', detail: 'Style Dictionary', kind: 'service', column: 1, row: 1 },
        { id: 'web', label: 'Atlas React', detail: '60 components', kind: 'client', column: 2, row: 0 },
        { id: 'native', label: 'Atlas Native', detail: 'iOS · Android', kind: 'client', column: 2, row: 2 },
        { id: 'docs', label: 'Docs site', detail: 'Live examples', kind: 'client', column: 3, row: 1 },
        { id: 'registry', label: 'Package registry', detail: 'Semver releases', kind: 'store', column: 3, row: 0 },
      ],
      edges: [
        { from: 'figma', to: 'tokens', label: 'sync' },
        { from: 'tokens', to: 'web' },
        { from: 'tokens', to: 'native' },
        { from: 'web', to: 'registry', label: 'publish' },
        { from: 'web', to: 'docs' },
      ],
    },
    features: [
      { title: 'RTL by default', description: 'Every component mirrors correctly with a single dir attribute.' },
      { title: 'Accessible primitives', description: 'Dialog, combobox, tabs and menu tested with screen readers each release.' },
      { title: 'Theme layers', description: 'Brand, product and dark themes compose without overrides.' },
      { title: 'Codemods', description: 'Automated migrations for every breaking change.' },
    ],
    challenges: [
      {
        title: 'Numbers in RTL',
        description:
          'Currency amounts mix RTL labels with LTR numerals. We added an isolation primitive after finding balances rendered backwards in QA.',
      },
      {
        title: 'Saying no',
        description:
          'The hardest part of a design system is declining one-off variants. A public RFC process made those decisions visible and less personal.',
      },
    ],
    metrics: [
      { id: 'components', value: '60', label: 'Components', detail: 'web and native' },
      { id: 'adoption', value: '94%', label: 'UI on Atlas', detail: 'across four products' },
      { id: 'a11y', value: 'AA', label: 'WCAG 2.2', detail: 'every component' },
      { id: 'callsites', value: '1,400', label: 'Call sites migrated', detail: 'by codemod' },
    ],
    links: [
      { label: 'Atlas documentation', url: 'https://atlas.tessellate.example', kind: 'live' },
      { label: 'Token pipeline source', url: 'https://github.com/tessellate/atlas-tokens', kind: 'source' },
    ],
  },
  {
    id: 'pulse',
    slug: 'pulse-triage',
    title: 'Pulse',
    tagline: 'An emergency-department triage board nurses actually trust.',
    summary:
      'A real-time triage dashboard for Halcyon Health, now running in 14 emergency departments and cutting median time-to-triage by 31%.',
    year: 2020,
    status: 'maintained',
    category: 'product',
    featured: true,
    version: 'v1.8.0',
    companyId: 'halcyon',
    experienceId: 'halcyon-engineer',
    role: 'Full-stack engineer',
    team: '4 engineers, 1 designer, 2 clinical advisors',
    timeline: 'Aug 2019 — Feb 2021',
    stack: ['TypeScript', 'React', 'GraphQL', 'Node.js', 'PostgreSQL', 'Accessibility'],
    cover: cover('cover-pulse', 'Risograph illustration of three waveform traces on a grid, the middle one green with a single peak.'),
    gallery: [
      galleryImage(
        'gallery-pulse-1',
        'Three rows of black dots separated by lines, with one green dot moving up a lane.',
        'Priority lanes make escalation visible: a patient moving up is the most important event on the board.',
      ),
      galleryImage(
        'gallery-pulse-2',
        'Three gauge dials with tick marks, the centre dial partially filled green, above a thin sparkline.',
        'Department load at a glance, designed for a wall-mounted screen read from across the room.',
      ),
    ],
    overview: [
      'Emergency departments triage patients by urgency, but in the hospitals we worked with, the queue lived on a whiteboard and in a charge nurse’s head.',
      'Pulse put the queue on a shared screen, integrated with the hospital record system, and made escalations impossible to miss.',
    ],
    problem: [
      'Patients who deteriorated while waiting were not always re-triaged quickly, and handovers between shifts lost context.',
      'Previous digital attempts had failed because they added clicks to an already overloaded job.',
    ],
    approach: [
      {
        title: 'Shadow shifts first',
        description: 'The team spent 60 hours shadowing night and day shifts before writing a line of code.',
      },
      {
        title: 'Zero-click defaults',
        description: 'Vital signs flow in from bedside monitors; nurses confirm instead of type.',
      },
      {
        title: 'Designed for the wall',
        description: 'Large type, colour-blind-safe status, and layouts readable from five metres away.',
      },
    ],
    architecture: {
      caption: 'Vitals and record updates are merged into a single patient stream and pushed to every screen.',
      columns: 4,
      rows: 3,
      nodes: [
        { id: 'monitors', label: 'Bedside monitors', detail: 'HL7 feeds', kind: 'external', column: 0, row: 0 },
        { id: 'ehr', label: 'Hospital record', detail: 'FHIR API', kind: 'external', column: 0, row: 2 },
        { id: 'ingest', label: 'Clinical gateway', detail: 'Node.js · validation', kind: 'service', column: 1, row: 1 },
        { id: 'queue', label: 'Triage engine', detail: 'Scores · escalations', kind: 'service', column: 2, row: 1 },
        { id: 'db', label: 'Audit store', detail: 'PostgreSQL', kind: 'store', column: 2, row: 2 },
        { id: 'board', label: 'Wall board', detail: 'React · subscriptions', kind: 'client', column: 3, row: 1 },
      ],
      edges: [
        { from: 'monitors', to: 'ingest' },
        { from: 'ehr', to: 'ingest' },
        { from: 'ingest', to: 'queue' },
        { from: 'queue', to: 'db', label: 'audit' },
        { from: 'queue', to: 'board', label: 'subscribe' },
      ],
    },
    features: [
      { title: 'Automatic re-triage prompts', description: 'Deteriorating vitals raise a visible, audible escalation.' },
      { title: 'Shift handover view', description: 'A one-screen summary of every waiting patient for incoming staff.' },
      { title: 'Full audit trail', description: 'Every score change is attributable and reviewable.' },
    ],
    challenges: [
      {
        title: 'Alarm fatigue',
        description: 'Early versions escalated too often. We tuned thresholds with clinicians until alerts were rare enough to be trusted.',
      },
      {
        title: 'Hospital networks',
        description: 'Some sites blocked WebSockets; we built a long-polling fallback that degraded without anyone noticing.',
      },
    ],
    metrics: [
      { id: 'triage', value: '−31%', label: 'Median time-to-triage', detail: 'first pilot site' },
      { id: 'sites', value: '14', label: 'Emergency departments', detail: 'in the UK' },
      { id: 'uptime', value: '99.98%', label: 'Uptime', detail: 'over 24 months' },
    ],
    links: [{ label: 'Clinical pilot summary', url: 'https://halcyon.example/pulse-pilot', kind: 'writeup' }],
  },
  {
    id: 'quire',
    slug: 'quire',
    title: 'Quire',
    tagline: 'Open-source typesetting from Markdown to print-ready PDF.',
    summary:
      'A Rust and WebAssembly typesetting engine with Knuth–Plass line breaking and proper Arabic justification, used by publishers and documentation teams.',
    year: 2022,
    status: 'maintained',
    category: 'open-source',
    featured: true,
    version: 'v0.14.0',
    companyId: null,
    experienceId: 'quire-oss',
    role: 'Creator & maintainer',
    team: 'Me and 90 contributors',
    timeline: 'Jan 2021 — ongoing',
    stack: ['Rust', 'TypeScript', 'WebAssembly'],
    cover: cover(
      'cover-quire',
      'Risograph illustration of folded black paper signatures fanned out beside a page grid, with a green ribbon.',
    ),
    gallery: [
      galleryImage(
        'gallery-quire-1',
        'An open book spread with a precise page grid, grey paragraph blocks and a green drop-cap square.',
        'Quire lays out pages on a baseline grid, so facing pages always align.',
      ),
      galleryImage(
        'gallery-quire-2',
        'A minimal printer outline with sheets fanning out, the top sheet marked with a green line.',
        'Output is print-ready PDF/X with crop marks, bleeds and embedded fonts.',
      ),
    ],
    overview: [
      'I wanted my long-form writing to look like a book, not a browser printout. Existing tools were either LaTeX or HTML-to-PDF, and neither handled Arabic well.',
      'Quire compiles Markdown with a small set of extensions into beautifully set pages, in the browser or on the command line.',
    ],
    problem: [
      'Browser print engines break lines greedily and cannot justify Arabic text with kashida, so bilingual documents looked broken.',
    ],
    approach: [
      {
        title: 'Optimal line breaking',
        description: 'An implementation of the Knuth–Plass algorithm that considers whole paragraphs, not single lines.',
      },
      {
        title: 'Script-aware justification',
        description: 'Arabic lines are justified by elongating letter connections rather than stretching spaces.',
      },
      {
        title: 'Runs everywhere',
        description: 'The core is Rust compiled to WebAssembly, so the same engine powers the CLI and the live web editor.',
      },
    ],
    architecture: {
      caption: 'A single Rust core compiled to both native and WebAssembly targets.',
      columns: 4,
      rows: 2,
      nodes: [
        { id: 'md', label: 'Markdown source', detail: 'Quire extensions', kind: 'client', column: 0, row: 0 },
        { id: 'parser', label: 'Parser', detail: 'Rust · AST', kind: 'service', column: 1, row: 0 },
        { id: 'layout', label: 'Layout engine', detail: 'Knuth–Plass · shaping', kind: 'service', column: 2, row: 0 },
        { id: 'pdf', label: 'PDF writer', detail: 'PDF/X-4', kind: 'store', column: 3, row: 0 },
        { id: 'wasm', label: 'Web editor', detail: 'WASM · live preview', kind: 'client', column: 2, row: 1 },
      ],
      edges: [
        { from: 'md', to: 'parser' },
        { from: 'parser', to: 'layout' },
        { from: 'layout', to: 'pdf' },
        { from: 'layout', to: 'wasm', label: 'preview' },
      ],
    },
    features: [
      { title: 'Kashida justification', description: 'Correct Arabic justification, a first for open-source tools.' },
      { title: 'Live preview', description: 'Edit in the browser and see pages reflow instantly.' },
      { title: 'Print-ready output', description: 'Bleeds, crop marks and PDF/X compliance built in.' },
    ],
    challenges: [
      {
        title: 'Maintaining momentum',
        description: 'A public roadmap, good-first-issue labels and a monthly release train kept contributors engaged.',
      },
    ],
    metrics: [
      { id: 'stars', value: '4.8k', label: 'GitHub stars', detail: 'and growing' },
      { id: 'contributors', value: '90', label: 'Contributors', detail: 'from 23 countries' },
      { id: 'releases', value: '41', label: 'Releases', detail: 'monthly since 2021' },
    ],
    links: [
      { label: 'Source on GitHub', url: 'https://github.com/adamrahman/quire', kind: 'source' },
      { label: 'Playground', url: 'https://quire.dev', kind: 'live' },
      { label: 'JSConf EU talk', url: 'https://youtube.com/watch?v=quire', kind: 'talk' },
    ],
  },
  {
    id: 'souk-checkout',
    slug: 'souk-checkout',
    title: 'Souk Checkout',
    tagline: 'An Arabic-first mobile checkout that lifted conversion by 19%.',
    summary:
      'A rebuilt checkout for Souk.co, designed right-to-left from the start, with local payment methods and a three-step flow.',
    year: 2018,
    status: 'archived',
    category: 'product',
    featured: true,
    version: 'v1.0.0',
    companyId: 'souk',
    experienceId: 'kiosk-frontend',
    role: 'Lead frontend engineer',
    team: '3 engineers, 1 designer',
    timeline: 'Feb 2018 — Aug 2018',
    stack: ['JavaScript', 'React', 'CSS & design tokens', 'Node.js'],
    cover: cover(
      'cover-souk',
      'Risograph illustration of an eight-point star lattice dissolving into a paper receipt with a green check.',
    ),
    gallery: [
      galleryImage(
        'gallery-souk-1',
        'A phone outline showing right-aligned bars over a geometric pattern and a green button.',
        'Right-to-left from the first sketch, not mirrored afterwards.',
      ),
      galleryImage(
        'gallery-souk-2',
        'Three payment cards fanned out, the front one with a green stripe, linked to a padlock.',
        'Cash on delivery, local cards and wallets as equals, not afterthoughts.',
      ),
    ],
    overview: [
      'Souk.co’s checkout was a translated copy of a Western template: seven steps, card-first, and mirrored in CSS at the last minute.',
      'We redesigned it around how customers in the Gulf actually paid, and built it right-to-left from the start.',
    ],
    problem: [
      'Mobile checkout abandonment was 78%. Session recordings showed customers hunting for cash-on-delivery and fighting mirrored input fields.',
    ],
    approach: [
      {
        title: 'Research in the market',
        description: 'Fifteen customer interviews in Riyadh and Jeddah reshaped the payment-method order.',
      },
      {
        title: 'Three steps, one screen each',
        description: 'Address, payment and review, with saved details making repeat purchases a single tap.',
      },
    ],
    architecture: {
      caption: 'A thin checkout client over the existing order API, with a new payment orchestration layer.',
      columns: 3,
      rows: 2,
      nodes: [
        { id: 'client', label: 'Checkout client', detail: 'React · RTL', kind: 'client', column: 0, row: 0 },
        { id: 'bff', label: 'Checkout BFF', detail: 'Node.js', kind: 'service', column: 1, row: 0 },
        { id: 'orders', label: 'Order API', detail: 'Existing', kind: 'store', column: 2, row: 0 },
        { id: 'payments', label: 'Payment providers', detail: 'Cards · wallets · COD', kind: 'external', column: 2, row: 1 },
      ],
      edges: [
        { from: 'client', to: 'bff' },
        { from: 'bff', to: 'orders' },
        { from: 'bff', to: 'payments' },
      ],
    },
    features: [
      { title: 'Local payment methods', description: 'Cash on delivery, mada cards and wallets side by side.' },
      { title: 'Arabic address autocomplete', description: 'Neighbourhood-level suggestions for Saudi addresses.' },
      { title: 'One-tap reorder', description: 'Saved details turn repeat purchases into a single confirmation.' },
    ],
    challenges: [
      {
        title: 'Mixed-direction inputs',
        description: 'Phone numbers and card numbers stay left-to-right inside a right-to-left form; we isolated them explicitly.',
      },
    ],
    metrics: [
      { id: 'conversion', value: '+19%', label: 'Mobile conversion', detail: 'in 60 days' },
      { id: 'steps', value: '3', label: 'Checkout steps', detail: 'down from 7' },
      { id: 'abandon', value: '−24pt', label: 'Abandonment', detail: 'on mobile' },
    ],
    links: [],
  },
  {
    id: 'tidewater',
    slug: 'tidewater',
    title: 'Tidewater',
    tagline: 'A tiny feature-flag service that runs at the edge.',
    summary: 'An open-source, single-binary feature-flag service with edge evaluation and a 4KB client.',
    year: 2021,
    status: 'maintained',
    category: 'open-source',
    featured: false,
    version: 'v0.6.2',
    companyId: 'nilepay',
    experienceId: 'freelance',
    role: 'Sole developer',
    team: 'Solo',
    timeline: 'Mar 2020 — Jan 2021',
    stack: ['Go', 'Redis', 'TypeScript', 'AWS'],
    cover: cover('cover-tidewater', 'Risograph illustration of flowing tide contour lines above a row of switches, one green.'),
    gallery: [],
    overview: [
      'A client needed feature flags without a SaaS contract. Tidewater is the smallest thing that could work: one Go binary, one Redis, and flags evaluated close to users.',
    ],
    problem: ['Commercial flag services were too expensive for a small fintech and too slow from the region.'],
    approach: [
      { title: 'Evaluate at the edge', description: 'Rules are compiled to a compact format and evaluated in edge workers.' },
      { title: 'Keep it small', description: 'A single binary, no dashboard dependencies, and a 4KB browser client.' },
    ],
    architecture: {
      caption: 'Flags are authored centrally and compiled to edge-evaluable snapshots.',
      columns: 3,
      rows: 1,
      nodes: [
        { id: 'admin', label: 'Admin API', detail: 'Go', kind: 'service', column: 0, row: 0 },
        { id: 'redis', label: 'Flag store', detail: 'Redis', kind: 'store', column: 1, row: 0 },
        { id: 'edge', label: 'Edge workers', detail: 'Snapshot eval', kind: 'client', column: 2, row: 0 },
      ],
      edges: [
        { from: 'admin', to: 'redis' },
        { from: 'redis', to: 'edge', label: 'snapshot' },
      ],
    },
    features: [
      { title: 'Percentage rollouts', description: 'Deterministic bucketing by user or account.' },
      { title: 'Kill switches', description: 'Global off switch propagates in under two seconds.' },
    ],
    challenges: [
      { title: 'Consistency', description: 'Snapshots are versioned so clients never see half-applied rule changes.' },
    ],
    metrics: [
      { id: 'client', value: '4KB', label: 'Client size', detail: 'gzipped' },
      { id: 'eval', value: '<1ms', label: 'Evaluation', detail: 'at the edge' },
    ],
    links: [{ label: 'Source on GitHub', url: 'https://github.com/adamrahman/tidewater', kind: 'source' }],
  },
  {
    id: 'almanac',
    slug: 'almanac',
    title: 'Almanac',
    tagline: 'A reading tracker that became this site’s bookshelf.',
    summary: 'A small personal app for tracking books, reading streaks and notes. Retired in 2022; its data model powers the bookshelf here.',
    year: 2019,
    status: 'archived',
    category: 'product',
    featured: false,
    version: 'v0.3.0',
    companyId: null,
    experienceId: 'kiosk-frontend',
    role: 'Designer & developer',
    team: 'Solo',
    timeline: 'Weekends, 2019',
    stack: ['React Native', 'TypeScript', 'SQL'],
    cover: cover('cover-almanac', 'Risograph illustration of book spines in black and grey with one green spine above a calendar grid.'),
    gallery: [],
    overview: [
      'Almanac was a weekend project to replace a spreadsheet of books. It taught me React Native and the limits of streak-based motivation.',
    ],
    problem: ['Reading apps optimised for social feeds; I wanted a quiet log with notes and yearly summaries.'],
    approach: [
      { title: 'Local first', description: 'All data stored on-device in SQLite with optional export.' },
      { title: 'Yearly reports', description: 'A printable summary of every year of reading.' },
    ],
    architecture: {
      caption: 'A local-first mobile app with a single export path.',
      columns: 2,
      rows: 1,
      nodes: [
        { id: 'app', label: 'Mobile app', detail: 'React Native', kind: 'client', column: 0, row: 0 },
        { id: 'sqlite', label: 'On-device store', detail: 'SQLite', kind: 'store', column: 1, row: 0 },
      ],
      edges: [{ from: 'app', to: 'sqlite' }],
    },
    features: [
      { title: 'Reading log', description: 'Books, dates, pages and notes.' },
      { title: 'Year in books', description: 'A generated summary with a books-per-month chart.' },
    ],
    challenges: [
      { title: 'Streak anxiety', description: 'Removing streaks made the app more pleasant, and I read more.' },
    ],
    metrics: [{ id: 'books', value: '212', label: 'Books logged', detail: 'before retirement' }],
    links: [],
  },
];
