import { z } from 'zod';
import type { DictionaryKey } from '@/i18n/dictionary';
import type { ContactMessage } from '@/types/content';

const errorKey = (key: DictionaryKey): string => key;

export const contactSchema = z.object({
  name: z.string().trim().min(1, errorKey('contact.error.nameRequired')),
  email: z
    .string()
    .trim()
    .min(1, errorKey('contact.error.emailRequired'))
    .pipe(z.email(errorKey('contact.error.emailInvalid'))),
  topic: z.enum(['advisory', 'role', 'speaking', 'hello']),
  message: z.string().trim().min(20, errorKey('contact.error.messageShort')),
});

export type ContactField = keyof ContactMessage;

export type ContactErrors = Partial<Record<ContactField, DictionaryKey>>;

export const validateContact = (
  values: ContactMessage,
): { success: true; data: ContactMessage } | { success: false; errors: ContactErrors } => {
  const result = contactSchema.safeParse(values);
  if (result.success) return { success: true, data: result.data };

  const errors: ContactErrors = {};
  for (const issue of result.error.issues) {
    const field = issue.path[0] as ContactField;
    errors[field] ??= issue.message as DictionaryKey;
  }
  return { success: false, errors };
};
