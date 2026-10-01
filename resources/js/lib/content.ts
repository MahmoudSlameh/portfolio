/**
 * Content types for the templates (data comes from Laravel as Inertia props) and the contact form request.
 */
import type { ContactMessage } from '@/types/content';

export type {
    ArticleDetail,
    ArticleSummary,
    BookStats,
    CareerEntry,
    NowDetail,
    ProjectDetail,
    ProjectFacets,
    ProjectReference,
    SearchIndex,
    ServicesSection,
    SkillGroup,
    TestimonialEntry,
} from '@/types/content';

export class ContactSubmissionError extends Error {
    constructor(
        message: string,
        public readonly errors: Partial<
            Record<keyof ContactMessage, string>
        > = {},
    ) {
        super(message);
    }
}

const xsrfToken = (): string => {
    const match = document.cookie.match(/(?:^|; )XSRF-TOKEN=([^;]*)/);
    return match ? decodeURIComponent(match[1]) : '';
};

/** Sends the contact form to Laravel (stored in the panel inbox and emailed to the owner). */
export const submitContactMessage = async (
    message: ContactMessage,
): Promise<{ id: string; receivedAt: string }> => {
    const response = await fetch('/contact', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-XSRF-TOKEN': xsrfToken(),
        },
        body: JSON.stringify(message),
    });

    if (!response.ok) {
        const body = (await response.json().catch(() => ({}))) as {
            message?: string;
            errors?: Record<string, string[]>;
        };
        const errors = Object.fromEntries(
            Object.entries(body.errors ?? {}).map(([field, messages]) => [
                field,
                messages[0],
            ]),
        );
        throw new ContactSubmissionError(
            body.message ?? 'The message could not be sent.',
            errors,
        );
    }

    return (await response.json()) as { id: string; receivedAt: string };
};
