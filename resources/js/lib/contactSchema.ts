import type { DictionaryKey } from '@/i18n/dictionary';
import type { ContactMessage } from '@/types/content';

export type ContactField = keyof ContactMessage;

export type ContactErrors = Partial<Record<ContactField, DictionaryKey>>;

/** Topics offered in the contact form (validated again by the server). */
export const CONTACT_TOPICS: ContactMessage['topic'][] = [
    'advisory',
    'role',
    'speaking',
    'hello',
];

const EMAIL_PATTERN = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

/**
 * Client-side check of the contact form (the server validates again in ContactMessageRequest).
 * Returns trimmed values, or the first error key per field.
 */
export const validateContact = (
    values: ContactMessage,
):
    | { success: true; data: ContactMessage }
    | { success: false; errors: ContactErrors } => {
    const data: ContactMessage = {
        name: values.name.trim(),
        email: values.email.trim(),
        topic: values.topic,
        message: values.message.trim(),
    };
    const errors: ContactErrors = {};

    if (data.name === '') errors.name = 'contact.error.nameRequired';
    if (data.email === '') errors.email = 'contact.error.emailRequired';
    else if (!EMAIL_PATTERN.test(data.email))
        errors.email = 'contact.error.emailInvalid';
    if (!CONTACT_TOPICS.includes(data.topic)) data.topic = 'hello';
    if (data.message.length < 20) errors.message = 'contact.error.messageShort';

    return Object.keys(errors).length === 0
        ? { success: true, data }
        : { success: false, errors };
};
