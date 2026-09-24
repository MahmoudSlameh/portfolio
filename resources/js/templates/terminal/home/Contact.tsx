import {
    ArrowUpRight,
    Check,
    Clock,
    Mail,
    MapPin,
    Radio,
    type LucideIcon,
} from 'lucide-react';
import {
    useId,
    useRef,
    useState,
    type ChangeEvent,
    type FormEvent,
    type ReactNode,
} from 'react';
import { useNow } from '@/hooks/useLocalTime';
import { useTranslation } from '@/hooks/useTranslation';
import {
    validateContact,
    type ContactErrors,
    type ContactField,
} from '@/lib/contactSchema';
import { submitContactMessage } from '@/lib/content';
import { cn, formatTime } from '@/lib/utils';
import type { ContactMessage, Profile } from '@/types/content';
import { useTerminalCopy } from '../copy';

const initialValues: ContactMessage = {
    name: '',
    email: '',
    topic: 'advisory',
    message: '',
};
const TOPICS: ContactMessage['topic'][] = [
    'advisory',
    'role',
    'speaking',
    'hello',
];
const FIELD_ORDER: ContactField[] = ['name', 'email', 'topic', 'message'];

type FormStatus = 'idle' | 'submitting' | 'success';

function FieldError({ id, message }: { id: string; message?: string }) {
    if (!message) return null;
    return (
        <p id={id} className="text-danger mt-1.5 mb-0 text-sm">
            {message}
        </p>
    );
}

function InfoItem({
    icon: Icon,
    label,
    children,
    href,
}: {
    icon: LucideIcon;
    label: string;
    children: ReactNode;
    href?: string;
}) {
    return (
        <li className="relative mb-4 flex items-center">
            <span
                aria-hidden
                className="border-tm-border bg-tm-card inline-flex size-16 shrink-0 items-center justify-center rounded-lg border"
            >
                <Icon className="text-tm-primary size-[26px]" />
            </span>
            <span className="flex min-w-0 flex-col ps-4">
                <span className="text-tm-400">{label}</span>
                <span className="text-ink text-[19px] font-medium break-words">
                    {href ? (
                        <a
                            href={href}
                            className="after:absolute after:inset-0 hover:text-[#62a92b]"
                        >
                            {children}
                        </a>
                    ) : (
                        children
                    )}
                </span>
            </span>
        </li>
    );
}

export function Contact({ profile }: { profile: Profile }) {
    const { t } = useTranslation();
    const c = useTerminalCopy();
    const now = useNow();
    const baseId = useId();
    const [values, setValues] = useState<ContactMessage>(initialValues);
    const [errors, setErrors] = useState<ContactErrors>({});
    const [hasAttempted, setHasAttempted] = useState(false);
    const [status, setStatus] = useState<FormStatus>('idle');
    const [messageId, setMessageId] = useState('');
    const summaryRef = useRef<HTMLDivElement>(null);
    const successRef = useRef<HTMLHeadingElement>(null);
    const nameRef = useRef<HTMLInputElement>(null);
    const errorCount = Object.keys(errors).length;

    const fieldId = (field: ContactField): string => `contact-${field}`;
    const errorId = (field: ContactField): string => `${baseId}-${field}-error`;
    const errorText = (field: ContactField): string | undefined => {
        const key = errors[field];
        return key ? t(key) : undefined;
    };
    const describedBy = (field: ContactField): string | undefined =>
        errors[field] ? errorId(field) : undefined;

    const handleChange = (
        event: ChangeEvent<
            HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement
        >,
    ): void => {
        const nextValues = {
            ...values,
            [event.target.name]: event.target.value,
        };
        setValues(nextValues);
        if (!hasAttempted) return;
        const result = validateContact(nextValues);
        setErrors(result.success ? {} : result.errors);
    };

    const handleSubmit = async (
        event: FormEvent<HTMLFormElement>,
    ): Promise<void> => {
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
        const response = await submitContactMessage(result.data);
        setMessageId(response.id);
        setStatus('success');
        window.requestAnimationFrame(() => successRef.current?.focus());
    };

    const handleReset = (): void => {
        setValues(initialValues);
        setErrors({});
        setHasAttempted(false);
        setStatus('idle');
        window.requestAnimationFrame(() => nameRef.current?.focus());
    };

    return (
        <section
            id="contact"
            aria-labelledby="contact-title"
            className="relative overflow-hidden pb-[60px]"
        >
            <div className="tm-container grid grid-cols-1 items-center gap-12 lg:grid-cols-[minmax(0,7fr)_minmax(0,5fr)] lg:gap-0">
                <div className="lg:pe-6">
                    <h2
                        id="contact-title"
                        className="text-tm-primary mb-4 text-[clamp(1.75rem,3vw,2.1875rem)] font-medium"
                    >
                        {c('contact.title')}
                    </h2>

                    {status === 'success' ? (
                        <div
                            role="status"
                            className="tm-box flex min-h-[420px] flex-col items-start justify-center gap-4 p-8"
                        >
                            <span
                                aria-hidden
                                className="inline-flex size-12 items-center justify-center rounded-full bg-[#62a92b] text-white"
                            >
                                <Check className="size-6" />
                            </span>
                            <p className="ltr-isolate text-tm-400 mb-0 text-sm">
                                {messageId}
                            </p>
                            <h3
                                ref={successRef}
                                tabIndex={-1}
                                className="mb-0 text-2xl focus-visible:outline-none"
                            >
                                {t('contact.successTitle')}
                            </h3>
                            <p className="text-tm-300 mb-2 max-w-md">
                                {t('contact.successBody', {
                                    name: values.name.trim(),
                                    id: messageId,
                                })}
                            </p>
                            <button
                                type="button"
                                onClick={handleReset}
                                className="text-ink inline-flex items-center gap-2 font-medium hover:text-[#62a92b]"
                            >
                                {t('contact.sendAnother')}
                                <ArrowUpRight
                                    aria-hidden
                                    className="size-5 rtl:-scale-x-100"
                                />
                            </button>
                        </div>
                    ) : (
                        <form
                            noValidate
                            onSubmit={handleSubmit}
                            aria-describedby={
                                errorCount ? `${baseId}-summary` : undefined
                            }
                        >
                            {errorCount > 0 && (
                                <div
                                    id={`${baseId}-summary`}
                                    ref={summaryRef}
                                    tabIndex={-1}
                                    role="alert"
                                    className="border-danger bg-danger-soft text-danger mb-4 rounded-lg border px-4 py-3 text-sm"
                                >
                                    <p className="mb-1 font-medium">
                                        {t('contact.errorSummary', {
                                            count: errorCount,
                                        })}
                                    </p>
                                    <ul className="flex flex-col gap-0.5">
                                        {FIELD_ORDER.filter(
                                            (field) => errors[field],
                                        ).map((field) => (
                                            <li key={field}>
                                                <a
                                                    href={`#${fieldId(field)}`}
                                                    className="underline underline-offset-2"
                                                >
                                                    {errorText(field)}
                                                </a>
                                            </li>
                                        ))}
                                    </ul>
                                </div>
                            )}

                            <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div>
                                    <label
                                        htmlFor={fieldId('name')}
                                        className="sr-only"
                                    >
                                        {t('contact.name')}
                                    </label>
                                    <input
                                        ref={nameRef}
                                        id={fieldId('name')}
                                        name="name"
                                        autoComplete="name"
                                        placeholder={t('contact.name')}
                                        value={values.name}
                                        onChange={handleChange}
                                        aria-invalid={Boolean(errors.name)}
                                        aria-describedby={describedBy('name')}
                                        aria-required
                                        className="tm-input"
                                    />
                                    <FieldError
                                        id={errorId('name')}
                                        message={errorText('name')}
                                    />
                                </div>
                                <div>
                                    <label
                                        htmlFor={fieldId('email')}
                                        className="sr-only"
                                    >
                                        {t('contact.email')}
                                    </label>
                                    <input
                                        id={fieldId('email')}
                                        name="email"
                                        type="email"
                                        inputMode="email"
                                        dir="ltr"
                                        autoComplete="email"
                                        placeholder={t('contact.email')}
                                        value={values.email}
                                        onChange={handleChange}
                                        aria-invalid={Boolean(errors.email)}
                                        aria-describedby={describedBy('email')}
                                        aria-required
                                        className="tm-input text-start rtl:text-end"
                                    />
                                    <FieldError
                                        id={errorId('email')}
                                        message={errorText('email')}
                                    />
                                </div>
                                <div className="md:col-span-2">
                                    <label
                                        htmlFor={fieldId('topic')}
                                        className="sr-only"
                                    >
                                        {c('contact.subject')}
                                    </label>
                                    <select
                                        id={fieldId('topic')}
                                        name="topic"
                                        value={values.topic}
                                        onChange={handleChange}
                                        className="tm-input text-ink"
                                    >
                                        {TOPICS.map((topic) => (
                                            <option key={topic} value={topic}>
                                                {c('contact.subject')}:{' '}
                                                {t(`contact.topic.${topic}`)}
                                            </option>
                                        ))}
                                    </select>
                                </div>
                                <div className="md:col-span-2">
                                    <label
                                        htmlFor={fieldId('message')}
                                        className="sr-only"
                                    >
                                        {t('contact.message')}
                                    </label>
                                    <textarea
                                        id={fieldId('message')}
                                        name="message"
                                        placeholder={`${t('contact.message')} — ${t('contact.messageHint')}`}
                                        value={values.message}
                                        onChange={handleChange}
                                        aria-invalid={Boolean(errors.message)}
                                        aria-describedby={describedBy(
                                            'message',
                                        )}
                                        aria-required
                                        className="tm-input"
                                    />
                                    <FieldError
                                        id={errorId('message')}
                                        message={errorText('message')}
                                    />
                                </div>
                                <div className="md:col-span-2">
                                    <button
                                        type="submit"
                                        disabled={status === 'submitting'}
                                        aria-busy={status === 'submitting'}
                                        className={cn(
                                            'text-ink inline-flex items-center gap-2 rounded-md px-6 py-4 text-sm font-bold transition-colors hover:text-[#62a92b]',
                                            status === 'submitting' &&
                                                'opacity-60',
                                        )}
                                    >
                                        {status === 'submitting'
                                            ? t('contact.sending')
                                            : c('contact.sendArrow')}
                                        <ArrowUpRight
                                            aria-hidden
                                            className="size-5 rtl:-scale-x-100"
                                        />
                                    </button>
                                </div>
                            </div>
                        </form>
                    )}
                </div>

                <ul className="flex flex-col lg:ps-16">
                    <InfoItem
                        icon={Mail}
                        label={c('menu.email')}
                        href={`mailto:${profile.email}`}
                    >
                        <span className="ltr-isolate">{profile.email}</span>
                    </InfoItem>
                    <InfoItem
                        icon={MapPin}
                        label={c('menu.location')}
                        href={`https://maps.google.com/maps?q=${encodeURIComponent(profile.location)}`}
                    >
                        {profile.location}
                    </InfoItem>
                    <InfoItem icon={Clock} label={c('menu.localTime')}>
                        <time
                            dateTime={now.toISOString()}
                            className="ltr-isolate"
                        >
                            {formatTime(now, profile.timezone)}
                        </time>{' '}
                        <span className="ltr-isolate text-tm-300 text-base">
                            {profile.timezoneLabel}
                        </span>
                    </InfoItem>
                    <InfoItem icon={Radio} label={c('menu.availability')}>
                        {profile.availability.label}
                    </InfoItem>
                </ul>
            </div>
        </section>
    );
}
