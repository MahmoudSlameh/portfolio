import type { Skill, SkillCategory } from '@/types/content';

export const skillCategories: SkillCategory[] = [
  {
    id: 'languages',
    label: { en: 'Languages', ar: 'اللغات' },
    description: { en: 'What I think in.', ar: 'ما أفكّر به.' },
  },
  {
    id: 'frontend',
    label: { en: 'Interfaces', ar: 'الواجهات' },
    description: { en: 'What people touch.', ar: 'ما يلمسه الناس.' },
  },
  {
    id: 'backend',
    label: { en: 'Services & data', ar: 'الخدمات والبيانات' },
    description: { en: 'What keeps the promises.', ar: 'ما يفي بالوعود.' },
  },
  {
    id: 'infrastructure',
    label: { en: 'Infrastructure', ar: 'البنية التحتية' },
    description: { en: 'What it all runs on.', ar: 'ما يعمل عليه كل شيء.' },
  },
];

export const skills: Skill[] = [
  { id: 'typescript', name: 'TypeScript', categoryId: 'languages', proficiency: 5, years: 9 },
  { id: 'go', name: 'Go', categoryId: 'languages', proficiency: 4, years: 5 },
  { id: 'sql', name: 'SQL', categoryId: 'languages', proficiency: 5, years: 11 },
  { id: 'kotlin', name: 'Kotlin', categoryId: 'languages', proficiency: 3, years: 3 },
  { id: 'rust', name: 'Rust', categoryId: 'languages', proficiency: 2, years: 2 },

  { id: 'react', name: 'React', categoryId: 'frontend', proficiency: 5, years: 9 },
  { id: 'nextjs', name: 'Next.js', categoryId: 'frontend', proficiency: 4, years: 6 },
  { id: 'css', name: 'CSS & design tokens', categoryId: 'frontend', proficiency: 5, years: 11 },
  { id: 'a11y', name: 'Accessibility', categoryId: 'frontend', proficiency: 4, years: 7 },
  { id: 'react-native', name: 'React Native', categoryId: 'frontend', proficiency: 3, years: 3 },

  { id: 'postgres', name: 'PostgreSQL', categoryId: 'backend', proficiency: 5, years: 10 },
  { id: 'kafka', name: 'Kafka', categoryId: 'backend', proficiency: 4, years: 5 },
  { id: 'node', name: 'Node.js', categoryId: 'backend', proficiency: 5, years: 9 },
  { id: 'graphql', name: 'GraphQL', categoryId: 'backend', proficiency: 4, years: 5 },
  { id: 'redis', name: 'Redis', categoryId: 'backend', proficiency: 4, years: 7 },

  { id: 'kubernetes', name: 'Kubernetes', categoryId: 'infrastructure', proficiency: 4, years: 5 },
  { id: 'terraform', name: 'Terraform', categoryId: 'infrastructure', proficiency: 4, years: 5 },
  { id: 'aws', name: 'AWS', categoryId: 'infrastructure', proficiency: 4, years: 7 },
  { id: 'otel', name: 'OpenTelemetry', categoryId: 'infrastructure', proficiency: 3, years: 3 },
  { id: 'github-actions', name: 'GitHub Actions', categoryId: 'infrastructure', proficiency: 5, years: 6 },
];
