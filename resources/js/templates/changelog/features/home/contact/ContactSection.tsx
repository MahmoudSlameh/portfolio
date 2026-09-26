import { useEffect, useRef } from 'react';
import { Button } from '@/templates/changelog/components/ui/Button';
import { CopyButton } from '@/templates/changelog/components/ui/CopyButton';
import {
    SelectField,
    TextAreaField,
    TextField,
} from '@/templates/changelog/components/ui/FormField';
import { Section } from '@/templates/changelog/components/ui/Section';
import { StatusBadge } from '@/templates/changelog/components/ui/StatusBadge';
import { sectionIndex } from '@/config/navigation';
import { useNow } from '@/hooks/useLocalTime';
import { useTranslation } from '@/hooks/useTranslation';
import { formatTime } from '@/lib/utils';
import type { Profile } from '@/types/content';
import { CONTACT_FIELDS, useContactForm } from '@/kit';

function ContactAside({ profile }: { profile: Profile }) {
    const { t } = useTranslation();
    const now = useNow();

    return (
        <aside className="flex flex-col gap-8">
            <div>
                <p className="eyebrow mb-3 text-ink-subtle">
                    {t('contact.orEmail')}
                </p>
                <a
                    href={`mailto:${profile.email}`}
                    className="link-draw ltr-isolate font-display text-[1.75rem] text-ink sm:text-3xl"
                >
                    {profile.email}
                </a>
                <div className="mt-4">
                    <CopyButton
                        text={profile.email}
                        label={t('contact.copyEmail')}
                        copiedLabel={t('contact.copied')}
                    />
                </div>
            </div>
            <dl className="grid gap-6 border-t border-line pt-6">
                <div>
                    <dt className="eyebrow mb-2 text-ink-subtle">
                        {t('contact.availability')}
                    </dt>
                    <dd>
                        <StatusBadge
                            label={profile.availability.label}
                            tone="signal"
                        />
                        <p className="mt-2 text-[0.9375rem] leading-relaxed text-ink-muted">
                            {profile.availability.note}
                        </p>
                    </dd>
                </div>
                <div>
                    <dt className="eyebrow mb-2 text-ink-subtle">
                        {t('contact.timezone')}
                    </dt>
                    <dd className="flex flex-wrap items-baseline gap-x-3 gap-y-1">
                        <span className="text-[0.9375rem] text-ink">
                            {profile.location}
                        </span>
                        <time
                            dateTime={now.toISOString()}
                            className="ltr-isolate font-mono text-sm text-ink"
                        >
                            {formatTime(now, profile.timezone)}
                        </time>
                        <span className="ltr-isolate font-mono text-[0.6875rem] text-ink-subtle">
                            {profile.timezoneLabel}
                        </span>
                    </dd>
                </div>
            </dl>
        </aside>
    );
}

function ContactSuccess({
    name,
    messageId,
    onReset,
}: {
    name: string;
    messageId: string;
    onReset: () => void;
}) {
    const { t } = useTranslation();
    const headingRef = useRef<HTMLHeadingElement>(null);

    useEffect(() => {
        headingRef.current?.focus();
    }, []);

    return (
        <div
            role="status"
            className="animate-rise flex flex-col items-start gap-5 border border-signal/40 bg-signal-soft p-8"
        >
            <p
                aria-hidden
                className="ltr-isolate font-mono text-xs text-signal-ink"
            >
                ✓ merged · {messageId}
            </p>
            <h3
                ref={headingRef}
                tabIndex={-1}
                className="font-display text-4xl leading-tight text-ink focus-visible:outline-none"
            >
                {t('contact.successTitle')}
            </h3>
            <p className="max-w-md text-[0.9375rem] leading-relaxed text-ink-muted">
                {t('contact.successBody', { name, id: messageId })}
            </p>
            <Button variant="secondary" onClick={onReset}>
                {t('contact.sendAnother')}
            </Button>
        </div>
    );
}

export function ContactSection({ profile }: { profile: Profile }) {
    const { t } = useTranslation();
    const {
        values,
        errors,
        errorCount,
        status,
        messageId,
        errorText,
        handleChange,
        handleSubmit,
        reset: handleReset,
        summaryRef,
        nameRef,
    } = useContactForm();

    return (
        <Section
            id="contact"
            index={sectionIndex('contact')}
            label={t('section.contact')}
            version="CONTRIBUTING.md"
            title={t('contact.title')}
            intro={t('contact.intro')}
        >
            <div className="editorial-grid gap-y-14">
                <div className="col-span-4 md:col-span-7">
                    {status === 'success' ? (
                        <ContactSuccess
                            name={values.name.trim()}
                            messageId={messageId}
                            onReset={handleReset}
                        />
                    ) : (
                        <form
                            noValidate
                            onSubmit={handleSubmit}
                            className="flex flex-col gap-6"
                            aria-describedby={
                                errorCount ? 'contact-summary' : undefined
                            }
                        >
                            {errorCount > 0 && (
                                <div
                                    id="contact-summary"
                                    ref={summaryRef}
                                    tabIndex={-1}
                                    role="alert"
                                    className="border border-danger/40 bg-danger-soft px-4 py-3 text-sm text-danger focus-visible:outline-danger"
                                >
                                    <p className="font-medium">
                                        {t('contact.errorSummary', {
                                            count: errorCount,
                                        })}
                                    </p>
                                    <ul className="mt-1.5 flex flex-col gap-0.5">
                                        {CONTACT_FIELDS.filter(
                                            (field) => errors[field],
                                        ).map((field) => (
                                            <li key={field}>
                                                <a
                                                    href={`#contact-${field}`}
                                                    className="underline underline-offset-2"
                                                >
                                                    {errorText(field)}
                                                </a>
                                            </li>
                                        ))}
                                    </ul>
                                </div>
                            )}

                            <div className="grid gap-6 sm:grid-cols-2">
                                <TextField
                                    ref={nameRef}
                                    id="contact-name"
                                    name="name"
                                    label={t('contact.name')}
                                    autoComplete="name"
                                    required
                                    requiredLabel={t('contact.required')}
                                    value={values.name}
                                    onChange={handleChange}
                                    error={errorText('name')}
                                />
                                <TextField
                                    id="contact-email"
                                    name="email"
                                    type="email"
                                    inputMode="email"
                                    dir="ltr"
                                    label={t('contact.email')}
                                    autoComplete="email"
                                    required
                                    requiredLabel={t('contact.required')}
                                    value={values.email}
                                    onChange={handleChange}
                                    error={errorText('email')}
                                    className="text-start rtl:text-end"
                                />
                            </div>

                            <SelectField
                                id="contact-topic"
                                name="topic"
                                label={t('contact.topic')}
                                value={values.topic}
                                onChange={handleChange}
                                options={[
                                    {
                                        value: 'advisory',
                                        label: t('contact.topic.advisory'),
                                    },
                                    {
                                        value: 'role',
                                        label: t('contact.topic.role'),
                                    },
                                    {
                                        value: 'speaking',
                                        label: t('contact.topic.speaking'),
                                    },
                                    {
                                        value: 'hello',
                                        label: t('contact.topic.hello'),
                                    },
                                ]}
                            />

                            <TextAreaField
                                id="contact-message"
                                name="message"
                                label={t('contact.message')}
                                hint={t('contact.messageHint')}
                                required
                                requiredLabel={t('contact.required')}
                                rows={6}
                                value={values.message}
                                onChange={handleChange}
                                error={errorText('message')}
                            />

                            <div className="flex flex-wrap items-center gap-4">
                                <Button
                                    type="submit"
                                    disabled={status === 'submitting'}
                                    aria-busy={status === 'submitting'}
                                >
                                    {status === 'submitting'
                                        ? t('contact.sending')
                                        : t('contact.submit')}
                                </Button>
                                <span className="ltr-isolate font-mono text-[0.6875rem] text-ink-subtle">
                                    git push origin inbox
                                </span>
                            </div>
                        </form>
                    )}
                </div>

                <div className="col-span-4 md:col-span-4 md:col-start-9">
                    <ContactAside profile={profile} />
                </div>
            </div>
        </Section>
    );
}
