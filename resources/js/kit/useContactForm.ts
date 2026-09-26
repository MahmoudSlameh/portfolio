import {
    useRef,
    useState,
    type ChangeEvent,
    type FormEvent,
    type RefObject,
} from 'react';
import { useTranslation } from '@/hooks/useTranslation';
import {
    validateContact,
    type ContactErrors,
    type ContactField,
} from '@/lib/contactSchema';
import { submitContactMessage } from '@/lib/content';
import { useToast } from '@/providers/ToastProvider';
import type { ContactMessage } from '@/types/content';

export type ContactFormStatus = 'idle' | 'submitting' | 'success';

/** Order used for the error summary (matches the field order in every form). */
export const CONTACT_FIELDS: ContactField[] = [
    'name',
    'email',
    'topic',
    'message',
];

const initialValues: ContactMessage = {
    name: '',
    email: '',
    topic: 'advisory',
    message: '',
};

export interface ContactForm {
    values: ContactMessage;
    errors: ContactErrors;
    errorCount: number;
    status: ContactFormStatus;
    /** Id returned by the server once the message is stored. */
    messageId: string;
    /** Translated error message for a field, if it has one. */
    errorText: (field: ContactField) => string | undefined;
    handleChange: (
        event: ChangeEvent<
            HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement
        >,
    ) => void;
    handleSubmit: (event: FormEvent<HTMLFormElement>) => Promise<void>;
    /** Clears the form after a success and focuses the name field. */
    reset: () => void;
    /** Attach to the error summary: it is focused when validation fails. */
    summaryRef: RefObject<HTMLDivElement | null>;
    /** Attach to the name input: it is focused after `reset()`. */
    nameRef: RefObject<HTMLInputElement | null>;
}

/**
 * Everything a template's contact form needs: values, client validation (errors appear after the
 * first submit and update as the visitor types), `POST /contact`, a toast on failure and focus
 * management. Templates only render the markup.
 */
export function useContactForm({
    onSuccess,
}: { onSuccess?: () => void } = {}): ContactForm {
    const { t } = useTranslation();
    const { notify } = useToast();
    const [values, setValues] = useState<ContactMessage>(initialValues);
    const [errors, setErrors] = useState<ContactErrors>({});
    const [hasAttempted, setHasAttempted] = useState(false);
    const [status, setStatus] = useState<ContactFormStatus>('idle');
    const [messageId, setMessageId] = useState('');
    const summaryRef = useRef<HTMLDivElement>(null);
    const nameRef = useRef<HTMLInputElement>(null);

    const handleChange: ContactForm['handleChange'] = (event) => {
        const nextValues = {
            ...values,
            [event.target.name]: event.target.value,
        };
        setValues(nextValues);
        if (!hasAttempted) return;
        const result = validateContact(nextValues);
        setErrors(result.success ? {} : result.errors);
    };

    const handleSubmit: ContactForm['handleSubmit'] = async (event) => {
        event.preventDefault();
        setHasAttempted(true);

        const result = validateContact(values);
        if (!result.success) {
            setErrors(result.errors);
            window.requestAnimationFrame(() => summaryRef.current?.focus());
            return;
        }

        setErrors({});
        setStatus('submitting');
        let response: Awaited<ReturnType<typeof submitContactMessage>>;
        try {
            response = await submitContactMessage(result.data);
        } catch {
            setStatus('idle');
            notify(t('contact.failed'));
            return;
        }
        setMessageId(response.id);
        setStatus('success');
        onSuccess?.();
    };

    const reset = (): void => {
        setValues(initialValues);
        setErrors({});
        setHasAttempted(false);
        setStatus('idle');
        window.requestAnimationFrame(() => nameRef.current?.focus());
    };

    const errorText = (field: ContactField): string | undefined => {
        const key = errors[field];
        return key ? t(key) : undefined;
    };

    return {
        values,
        errors,
        errorCount: Object.keys(errors).length,
        status,
        messageId,
        errorText,
        handleChange,
        handleSubmit,
        reset,
        summaryRef,
        nameRef,
    };
}
