import type { TemplateSpec } from '@/templates/studio/spec';
import type { Profile, SearchIndex, Social } from '@/types/content';

/** A template id from the registry (`resources/js/templates/<id>/template.json`). */
export type TemplateId = string;

export type ToggleablePage = 'writing' | 'books' | 'uses' | 'now';

/** Props shared with every page by App\Http\Middleware\HandleInertiaRequests. */
export interface SharedProps {
    [key: string]: unknown;
    site: {
        name: string;
        url: string;
        enabledPages: Record<ToggleablePage, boolean>;
    };
    profile: Profile;
    socials: Social[];
    /** Loaded after the first render (deferred prop). */
    searchIndex?: SearchIndex;
    template: {
        id: TemplateId;
        /** Display name from the template manifest. */
        name: string;
        isPreview: boolean;
    };
    /** The rendered studio template's spec; null for code templates. */
    studio: { spec: TemplateSpec } | null;
    theme: 'light' | 'dark' | null;
    flash: { success?: string | null };
}
