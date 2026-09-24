import type { Profile } from '@/types/content';

export const profile: Profile = {
  name: { en: 'Adam Rahman', ar: 'آدم رحمن' },
  initials: 'AR',
  role: { en: 'Staff Software Engineer', ar: 'مهندس برمجيات رئيسي' },
  headline: {
    en: 'I build calm, well-typed systems for money, freight and health — and the teams that keep them running.',
    ar: 'أبني أنظمة هادئة ومحكمة الأنواع للمال والشحن والصحة، وأبني الفرق التي تُبقيها تعمل.',
  },
  focusAreas: {
    en: ['payment ledgers', 'design systems', 'event-driven platforms', 'right-to-left interfaces', 'developer tooling'],
    ar: ['دفاتر المدفوعات', 'أنظمة التصميم', 'المنصات المعتمدة على الأحداث', 'واجهات من اليمين لليسار', 'أدوات المطورين'],
  },
  summary: {
    en: 'Eleven years across platform, product and design-systems work. Currently leading the payments platform at Tessellate, where I care about ledgers that balance, interfaces that explain themselves, and releases nobody has to stay up for.',
    ar: 'أحد عشر عامًا بين هندسة المنصات والمنتجات وأنظمة التصميم. أقود حاليًا منصة المدفوعات في Tessellate، وأهتم بدفاتر حسابات متوازنة، وواجهات تشرح نفسها، وإصدارات لا تتطلب سهر أحد.',
  },
  location: { en: 'Cairo, Egypt', ar: 'القاهرة، مصر' },
  timezone: 'Africa/Cairo',
  timezoneLabel: 'EET · UTC+2/+3',
  email: 'hello@adamrahman.dev',
  currentVersion: 'v5.2.0',
  updatedAt: '2026-09-12',
  availability: {
    status: 'limited',
    label: { en: 'Open to advisory work', ar: 'متاح للاستشارات' },
    note: {
      en: 'Taking one advisory engagement for Q1 2027. Not looking for full-time roles.',
      ar: 'أقبل مشروعًا استشاريًا واحدًا للربع الأول من 2027. لا أبحث عن وظيفة بدوام كامل.',
    },
  },
  latestRelease: {
    added: {
      en: ['Multi-currency settlement in Ledgerline v3', 'Staff engineering guild, 14 members'],
      ar: ['التسوية متعددة العملات في Ledgerline v3', 'نقابة المهندسين الرئيسيين، 14 عضوًا'],
    },
    changed: {
      en: ['Mentoring shifted from 1:1 to design-review office hours'],
      ar: ['تحوّل الإرشاد من جلسات فردية إلى ساعات مراجعة التصميم'],
    },
    removed: {
      en: ['Nightly batch reconciliation — replaced by streaming'],
      ar: ['التسوية الليلية المجمّعة، واستُبدلت بالتدفق اللحظي'],
    },
  },
  stats: [
    { id: 'years', value: '11', label: { en: 'years shipping software', ar: 'عامًا في بناء البرمجيات' } },
    { id: 'releases', value: '140+', label: { en: 'production releases led', ar: 'إصدارًا إنتاجيًا قُدته' } },
    { id: 'engineers', value: '23', label: { en: 'engineers mentored', ar: 'مهندسًا تتلمذوا معي' } },
    { id: 'volume', value: '$2.1B', label: { en: 'annual volume on my ledger', ar: 'حجم سنوي عبر دفتري' } },
  ],
  status: [
    {
      id: 'building',
      label: { en: 'Building', ar: 'أعمل على' },
      value: { en: 'Ledgerline v3 settlement engine', ar: 'محرك التسوية في Ledgerline v3' },
      tone: 'signal',
    },
    {
      id: 'reading',
      label: { en: 'Reading', ar: 'أقرأ' },
      value: { en: 'Designing Data-Intensive Applications, 2nd ed.', ar: 'تصميم التطبيقات كثيفة البيانات، الطبعة الثانية' },
      tone: 'neutral',
    },
    {
      id: 'learning',
      label: { en: 'Learning', ar: 'أتعلّم' },
      value: { en: 'Formal methods with TLA+', ar: 'الأساليب الرسمية باستخدام TLA+' },
      tone: 'neutral',
    },
    {
      id: 'shipped',
      label: { en: 'Last shipped', ar: 'آخر إصدار' },
      value: { en: 'Atlas 4.1 — RTL tokens', ar: 'Atlas 4.1 — رموز الاتجاه من اليمين لليسار' },
      tone: 'neutral',
    },
  ],
  story: {
    en: [
      'I started writing software in a Cairo agency basement, building checkout flows for merchants who had never sold online. That job taught me the first rule I still work by: the system is only as good as the moment it fails in front of a real person.',
      'Since then I have moved down the stack and back up again — from interfaces to event pipelines to ledgers — mostly following the problems that kept people up at night. At Lattice Freight that meant replacing a polling tracker with a streaming one. At Halcyon it meant a triage queue that nurses trusted enough to stop printing.',
      'Today I lead the payments platform at Tessellate. I spend my weeks on architecture reviews, sharp-edged migrations and the unglamorous work of making the right thing the easy thing. Outside of work I maintain Quire, an open-source typesetting engine, and read far more than my shelf can hold.',
    ],
    ar: [
      'بدأت كتابة البرمجيات في قبو وكالة صغيرة بالقاهرة، أبني صفحات الدفع لتجار لم يبيعوا عبر الإنترنت من قبل. علّمني ذلك العمل القاعدة الأولى التي ما زلت أعمل بها: قيمة النظام تظهر في اللحظة التي يتعطل فيها أمام إنسان حقيقي.',
      'منذ ذلك الحين تنقّلت بين طبقات النظام صعودًا ونزولًا، من الواجهات إلى خطوط الأحداث إلى دفاتر الحسابات، متتبعًا المشكلات التي تُسهر الناس. في Lattice Freight كان ذلك استبدال متتبع يعتمد الاستطلاع بآخر لحظي، وفي Halcyon كان طابور فرز وثق به الممرضون حتى توقفوا عن الطباعة.',
      'أقود اليوم منصة المدفوعات في Tessellate. أقضي أسابيعي في مراجعات المعمارية، والترحيلات الحساسة، والعمل غير اللامع الذي يجعل الطريق الصحيح هو الأسهل. وخارج العمل أصون Quire، محرك تنضيد مفتوح المصدر، وأقرأ أكثر بكثير مما يتسع له رفّي.',
    ],
  },
  principles: [
    {
      id: 'boring',
      title: { en: 'Boring is a feature', ar: 'الرتابة ميزة' },
      body: {
        en: 'Pick the dull technology, then spend the novelty budget on the problem itself.',
        ar: 'اختر التقنية المملة، ثم أنفق ميزانية الابتكار على المشكلة نفسها.',
      },
    },
    {
      id: 'types',
      title: { en: 'Make illegal states unrepresentable', ar: 'اجعل الحالات الخاطئة مستحيلة التمثيل' },
      body: {
        en: 'Types, schemas and constraints are the cheapest documentation that never goes stale.',
        ar: 'الأنواع والمخططات والقيود هي أرخص توثيق لا يتقادم أبدًا.',
      },
    },
    {
      id: 'reversible',
      title: { en: 'Prefer reversible decisions', ar: 'فضّل القرارات القابلة للتراجع' },
      body: {
        en: 'Feature flags, dual writes and small releases turn scary launches into routine ones.',
        ar: 'مفاتيح الميزات والكتابة المزدوجة والإصدارات الصغيرة تحوّل الإطلاقات المخيفة إلى روتين.',
      },
    },
    {
      id: 'write',
      title: { en: 'Write it down first', ar: 'اكتبها أولًا' },
      body: {
        en: 'A two-page design doc saves two weeks of confident, parallel misunderstanding.',
        ar: 'وثيقة تصميم من صفحتين توفّر أسبوعين من سوء الفهم الواثق والمتوازي.',
      },
    },
    {
      id: 'people',
      title: { en: 'Systems are people', ar: 'الأنظمة هي الناس' },
      body: {
        en: 'On-call rotations, onboarding and review culture are architecture too.',
        ar: 'جداول المناوبة والتأهيل وثقافة المراجعة جزء من المعمارية أيضًا.',
      },
    },
  ],
  portrait: {
    base: 'portrait',
    widths: [432, 864],
    width: 864,
    height: 1152,
    alt: 'Black-and-white portrait of Adam Rahman in a charcoal sweater, looking off-camera in soft window light.',
  },
};
