/**
 * The template kit: everything a template needs besides its own markup and styles.
 * Templates import from `@/kit` and own only how things look. See docs/11-template-kit.md.
 */

// Behaviour
export {
    CONTACT_FIELDS,
    useContactForm,
    type ContactForm,
    type ContactFormStatus,
} from './useContactForm';
export { CONTACT_TOPICS } from '@/lib/contactSchema';
export { useArchiveFilters } from './useArchiveFilters';
export { rankByQuery, useSiteSearch } from './useSiteSearch';
export {
    usePreferences as useTheme,
    type Theme,
} from '@/providers/PreferencesProvider';
export { useCommandPalette } from '@/providers/CommandPaletteProvider';
export { useToast } from '@/providers/ToastProvider';
export { useClipboard } from '@/hooks/useClipboard';
export { useNow } from '@/hooks/useLocalTime';
export { useReveal } from '@/hooks/useReveal';
export { useTranslation } from '@/hooks/useTranslation';
export {
    ArticleBlocks,
    type ArticleBlockContext,
    type ArticleBlockRenderers,
    type ArticleBlockType,
} from './ArticleBlocks';

// Navigation (Inertia links with the route helpers' shape)
export { Link, useNavigate, useRouterState } from '@/lib/router';

// Shared components
export { ArchitectureDiagram } from '@/shared/content/ArchitectureDiagram';
export { CommandPalette } from '@/shared/command/CommandPalette';
export { SeoHead, type SeoData } from '@/shared/seo/SeoHead';
export { BrandIcon } from '@/shared/ui/BrandIcon';
export { CompanyLogo } from '@/shared/ui/CompanyLogo';
export { CountUp } from '@/shared/ui/CountUp';
export { ResponsiveImage } from '@/shared/ui/ResponsiveImage';
export { RotatingText } from '@/shared/ui/RotatingText';
export { withTemplateLayout } from '@/shared/inertia/withTemplateLayout';

// Helpers
export {
    cn,
    durationInMonths,
    formatDate,
    formatIsoDate,
    formatMonth,
    formatNumber,
    formatTime,
    yearOf,
    yearRange,
} from '@/lib/utils';
