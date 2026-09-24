import type { Article } from '@/types/content';

export const articles: Article[] = [
  {
    id: 'ledgers-are-logs',
    slug: 'ledgers-are-just-logs',
    title: 'Ledgers are just logs with opinions',
    excerpt:
      'What building a double-entry ledger taught me about append-only data, invariants and why balances should always be derived.',
    publishedAt: '2026-06-18',
    tags: ['architecture', 'payments', 'postgres'],
    projectIds: ['ledgerline'],
    body: [
      {
        type: 'paragraph',
        text: 'Every engineer who has worked near money eventually has the same realisation: a ledger is an append-only log with a very strict schema. Once you see it that way, most of the hard problems — auditing, history, reconciliation — become questions about logs, which we know how to answer.',
      },
      { type: 'heading', id: 'invariants-first', text: 'Start with invariants, not tables' },
      {
        type: 'paragraph',
        text: 'Before we designed a single table for Ledgerline, we wrote down the rules finance would never allow us to break. There were only four, and the most important one fits in a sentence: every journal must sum to zero.',
      },
      {
        type: 'code',
        language: 'ts',
        filename: 'journal.ts',
        code: `type Minor = bigint & { readonly __brand: 'Minor' };

interface Entry {
  accountId: string;
  currency: 'USD' | 'EUR' | 'AED' | 'EGP';
  amount: Minor;
}

export function assertBalanced(entries: readonly Entry[]): void {
  const totals = new Map<string, bigint>();
  for (const { currency, amount } of entries) {
    totals.set(currency, (totals.get(currency) ?? 0n) + amount);
  }
  for (const [currency, total] of totals) {
    if (total !== 0n) throw new Error(\`Unbalanced \${currency} journal: \${total}\`);
  }
}`,
      },
      {
        type: 'paragraph',
        text: 'The same rule also lives in the database as a deferred constraint trigger. Types catch mistakes at build time; constraints catch the ones that slip through at three in the morning.',
      },
      { type: 'heading', id: 'derive-balances', text: 'Never store what you can derive' },
      {
        type: 'paragraph',
        text: 'A stored balance is a cache, and caches drift. In Ledgerline, balances are projections of the journal stream. They can be rebuilt from zero, which means historical balances are a query, not a forensic investigation.',
      },
      {
        type: 'code',
        language: 'sql',
        filename: 'balance_at.sql',
        code: `select account_id,
       currency,
       sum(amount) as balance
from ledger.entries
where account_id = $1
  and posted_at <= $2
group by account_id, currency;`,
      },
      {
        type: 'callout',
        title: 'A rule of thumb',
        text: 'If a support engineer cannot answer “what was the balance then?” without paging you, your ledger is storing state it should be deriving.',
      },
      { type: 'heading', id: 'corrections', text: 'Corrections are new facts' },
      {
        type: 'paragraph',
        text: 'Nothing in a ledger is ever edited. A wrong entry is reversed by an equal and opposite one, with a pointer to the original. It feels bureaucratic until the first audit, when it feels like a superpower.',
      },
      {
        type: 'quote',
        text: 'The log is the truth. Everything else is an opinion about the log.',
        cite: 'Ledgerline design doc, v1',
      },
    ],
  },
  {
    id: 'rtl-is-not-mirroring',
    slug: 'rtl-is-not-mirroring',
    title: 'Right-to-left is not a mirror',
    excerpt:
      'Logical properties get you 80% of the way to a good Arabic interface. The last 20% is numbers, icons and the places where direction should not flip.',
    publishedAt: '2026-03-02',
    tags: ['design-systems', 'accessibility', 'css'],
    projectIds: ['atlas', 'souk-checkout'],
    body: [
      {
        type: 'paragraph',
        text: 'The first Arabic interface I shipped was a CSS transform away from its English original. It was also wrong in dozens of small ways that native readers noticed immediately.',
      },
      { type: 'heading', id: 'logical-properties', text: 'Use logical properties everywhere' },
      {
        type: 'paragraph',
        text: 'Replace left and right with start and end. Margin-inline-start, padding-inline-end and inset-inline-start describe intent rather than direction, and they flip automatically with the document.',
      },
      {
        type: 'code',
        language: 'tsx',
        filename: 'Callout.tsx',
        code: `export function Callout({ children }: { children: React.ReactNode }) {
  return (
    <aside className="border-s-2 border-signal ps-4 text-start">
      {children}
    </aside>
  );
}`,
      },
      { type: 'heading', id: 'what-not-to-flip', text: 'Know what should not flip' },
      {
        type: 'list',
        items: [
          'Numbers, phone numbers and card numbers stay left-to-right.',
          'Media controls follow the direction of time, which does not change with language.',
          'Brand marks and code samples keep their original orientation.',
          'Directional icons — back, next, reply — should mirror.',
        ],
      },
      { type: 'heading', id: 'isolation', text: 'Isolate mixed-direction text' },
      {
        type: 'paragraph',
        text: 'A price like “AED 1,250” inside an Arabic sentence can render in surprising orders. Wrapping it in an isolated span tells the bidirectional algorithm to treat it as a single unit.',
      },
      {
        type: 'code',
        language: 'tsx',
        code: `<p>
  المجموع: <bdi dir="ltr">AED 1,250.00</bdi>
</p>`,
      },
      {
        type: 'callout',
        title: 'Test with real content',
        text: 'Lorem ipsum hides every bidi bug. Test with real Arabic copy containing numbers, brand names and punctuation.',
      },
    ],
  },
  {
    id: 'dual-writes',
    slug: 'the-boring-migration',
    title: 'The boring migration playbook',
    excerpt:
      'Dual writes, diff jobs and cohort cut-overs: the unglamorous pattern I have used to replace three critical systems without a single outage.',
    publishedAt: '2025-11-20',
    tags: ['architecture', 'reliability'],
    projectIds: ['relay', 'ledgerline'],
    body: [
      {
        type: 'paragraph',
        text: 'Big-bang migrations are exciting in the way that car crashes are exciting. Over three large system replacements I have converged on a playbook that is intentionally dull.',
      },
      { type: 'heading', id: 'shadow', text: 'Step one: shadow' },
      {
        type: 'paragraph',
        text: 'Write to both systems. The old one stays the source of truth; the new one is a shadow whose output is compared, never served.',
      },
      {
        type: 'code',
        language: 'ts',
        filename: 'dual-write.ts',
        code: `export async function recordShipmentEvent(event: ShipmentEvent): Promise<void> {
  await legacyTracker.record(event);

  void relay
    .ingest(event)
    .catch((error: unknown) => metrics.increment('relay.shadow_write_failed', { error: String(error) }));
}`,
      },
      { type: 'heading', id: 'diff', text: 'Step two: diff until boring' },
      {
        type: 'paragraph',
        text: 'A scheduled job compares both systems and reports every difference. Each difference is a bug in the new system, the old system, or your understanding. All three are worth finding before customers do.',
      },
      { type: 'heading', id: 'cohorts', text: 'Step three: cut over in cohorts' },
      {
        type: 'list',
        items: [
          'Start with internal accounts, then the smallest customers.',
          'Move by volume, doubling the cohort each week.',
          'Keep a one-line flag to route any cohort back.',
        ],
      },
      {
        type: 'quote',
        text: 'The best migration is the one nobody outside the team noticed.',
      },
    ],
  },
  {
    id: 'design-docs',
    slug: 'design-docs-people-read',
    title: 'Writing design docs people actually read',
    excerpt: 'Two pages, one decision, and a section called “what would change my mind”.',
    publishedAt: '2025-07-09',
    tags: ['leadership', 'writing'],
    projectIds: [],
    body: [
      {
        type: 'paragraph',
        text: 'Most design docs fail not because they are wrong, but because nobody finishes them. After reviewing a few hundred, I think the fix is mostly about shape.',
      },
      { type: 'heading', id: 'one-decision', text: 'One document, one decision' },
      {
        type: 'paragraph',
        text: 'If a document asks reviewers to agree on five things, it will get feedback on the easiest one. Split it until each document has a single decision in its title.',
      },
      { type: 'heading', id: 'template', text: 'A template that fits on a screen' },
      {
        type: 'code',
        language: 'bash',
        filename: 'rfc-template.md',
        code: `# RFC: <the decision, as a sentence>
## Context        — why now, in five sentences
## Proposal       — what we will do
## Alternatives   — what we will not do, and why
## Risks          — what could go wrong
## Change my mind — evidence that would reverse this`,
      },
      { type: 'heading', id: 'change-my-mind', text: 'The most important section' },
      {
        type: 'paragraph',
        text: '“What would change my mind” turns review from a debate into a search for evidence. It is also the section senior reviewers read first.',
      },
    ],
  },
  {
    id: 'typed-events',
    slug: 'typed-event-contracts',
    title: 'Typed event contracts across service boundaries',
    excerpt:
      'How we used schemas, generated types and contract tests to let forty services evolve their events without breaking each other.',
    publishedAt: '2025-02-14',
    tags: ['typescript', 'architecture', 'kafka'],
    projectIds: ['relay'],
    body: [
      {
        type: 'paragraph',
        text: 'An event bus without contracts is a very fast way to distribute undefined behaviour. At Lattice we had forty services and no shared definition of what a “shipment delayed” event contained.',
      },
      { type: 'heading', id: 'schemas', text: 'Schemas are the source of truth' },
      {
        type: 'code',
        language: 'json',
        filename: 'shipment-delayed.v2.json',
        code: `{
  "$id": "shipment.delayed/v2",
  "type": "object",
  "required": ["shipmentId", "reason", "estimatedArrival"],
  "properties": {
    "shipmentId": { "type": "string" },
    "reason": { "enum": ["traffic", "weather", "customs", "capacity"] },
    "estimatedArrival": { "type": "string", "format": "date-time" }
  }
}`,
      },
      { type: 'heading', id: 'generated-types', text: 'Generate types, never write them' },
      {
        type: 'paragraph',
        text: 'Types are generated from schemas at build time, so a producer cannot publish an event the schema does not allow, and consumers get autocomplete for free.',
      },
      {
        type: 'code',
        language: 'ts',
        code: `import type { ShipmentDelayedV2 } from '@lattice/events';

export function handleDelay(event: ShipmentDelayedV2): Notification {
  return {
    shipmentId: event.shipmentId,
    message: \`Delayed by \${event.reason}, now arriving \${event.estimatedArrival}\`,
  };
}`,
      },
      { type: 'heading', id: 'compatibility', text: 'Compatibility rules in CI' },
      {
        type: 'list',
        items: [
          'Adding optional fields is always allowed.',
          'Removing or renaming a field requires a new major version.',
          'Consumers declare which versions they accept; CI blocks producers that would break them.',
        ],
      },
    ],
  },
];
