import type { NowPage } from '@/types/content';

export const nowPage: NowPage = {
  updatedAt: '2026-09-12',
  location: { en: 'Cairo, with a week in Dubai each month', ar: 'القاهرة، مع أسبوع في دبي كل شهر' },
  focus: [
    {
      id: 'settlement',
      title: { en: 'Multi-currency settlement', ar: 'التسوية متعددة العملات' },
      body: {
        en: 'Shipping Ledgerline v3, which settles 14 currencies continuously instead of in nightly batches. The last three currencies go live in October.',
        ar: 'أعمل على إطلاق Ledgerline v3 الذي يسوّي 14 عملة بشكل مستمر بدلًا من الدفعات الليلية. آخر ثلاث عملات تنطلق في أكتوبر.',
      },
    },
    {
      id: 'guild',
      title: { en: 'Growing staff engineers', ar: 'تنمية المهندسين الرئيسيين' },
      body: {
        en: 'Running a monthly staff guild and writing a short internal handbook on design reviews that people actually enjoy.',
        ar: 'أدير لقاءً شهريًا للمهندسين الرئيسيين وأكتب دليلًا داخليًا قصيرًا عن مراجعات تصميم يستمتع بها الناس فعلًا.',
      },
    },
    {
      id: 'quire',
      title: { en: 'Quire 1.0', ar: 'الإصدار الأول من Quire' },
      body: {
        en: 'Stabilising the plugin API so Quire can reach 1.0 before the end of the year.',
        ar: 'أعمل على تثبيت واجهة الإضافات حتى يصل Quire إلى الإصدار 1.0 قبل نهاية العام.',
      },
    },
  ],
  learning: [
    {
      id: 'tla',
      title: { en: 'TLA+', ar: 'لغة TLA+' },
      body: {
        en: 'Modelling the settlement state machine to find the race conditions before production does.',
        ar: 'أنمذج آلة حالات التسوية لاكتشاف حالات التسابق قبل أن يكتشفها الإنتاج.',
      },
    },
    {
      id: 'calligraphy',
      title: { en: 'Arabic calligraphy', ar: 'الخط العربي' },
      body: {
        en: 'Weekly naskh lessons. Humbling, analogue and very good for Quire’s kashida rules.',
        ar: 'دروس أسبوعية في خط النسخ. تجربة متواضعة وتناظرية ومفيدة جدًا لقواعد الكشيدة في Quire.',
      },
    },
  ],
  readingBookIds: ['ddia', 'crafting-interpreters'],
  availability: {
    en: 'I am taking one advisory engagement for Q1 2027 — architecture reviews, platform strategy or ledger design. I am not looking for a full-time role.',
    ar: 'أقبل مشروعًا استشاريًا واحدًا للربع الأول من 2027: مراجعات معمارية أو استراتيجية منصات أو تصميم دفاتر حسابات. لا أبحث عن وظيفة بدوام كامل.',
  },
};
