import {
    CONTACT_FIELDS,
    CONTACT_TOPICS,
    useContactForm,
    useTranslation,
} from '@/kit';

/**
 * The contact form. All behaviour (validation, POST /contact, error summary focus, success state)
 * comes from `useContactForm()`; this component is only markup. The form works the same in every
 * template, so the inbox in the panel always receives the same fields.
 */
export function ContactForm() {
    const { t } = useTranslation();
    const form = useContactForm();

    if (form.status === 'success') {
        return (
            <div role="status" className="rounded-md border border-line p-6">
                <p className="font-medium text-ink">
                    {t('contact.successTitle')}
                </p>
                <p className="mt-1 text-ink-muted">
                    {t('contact.successBody', {
                        name: form.values.name.trim(),
                        id: form.messageId,
                    })}
                </p>
                <button
                    type="button"
                    onClick={form.reset}
                    className="mn-link mt-4"
                >
                    {t('contact.sendAnother')}
                </button>
            </div>
        );
    }

    return (
        <form noValidate onSubmit={form.handleSubmit} className="grid gap-4">
            {form.errorCount > 0 && (
                <div
                    ref={form.summaryRef}
                    tabIndex={-1}
                    role="alert"
                    className="rounded-md bg-danger-soft p-4 text-sm text-danger"
                >
                    <p className="font-medium">
                        {t('contact.errorSummary', { count: form.errorCount })}
                    </p>
                    <ul className="mt-1 list-disc ps-5">
                        {CONTACT_FIELDS.filter(
                            (field) => form.errors[field],
                        ).map((field) => (
                            <li key={field}>
                                <a href={`#contact-${field}`}>
                                    {form.errorText(field)}
                                </a>
                            </li>
                        ))}
                    </ul>
                </div>
            )}

            <label className="grid gap-1 text-sm">
                <span className="text-ink-muted">{t('contact.name')}</span>
                <input
                    ref={form.nameRef}
                    id="contact-name"
                    name="name"
                    autoComplete="name"
                    value={form.values.name}
                    onChange={form.handleChange}
                    aria-invalid={Boolean(form.errors.name)}
                    className="mn-field"
                />
            </label>
            <label className="grid gap-1 text-sm">
                <span className="text-ink-muted">{t('contact.email')}</span>
                <input
                    id="contact-email"
                    name="email"
                    type="email"
                    autoComplete="email"
                    value={form.values.email}
                    onChange={form.handleChange}
                    aria-invalid={Boolean(form.errors.email)}
                    className="mn-field"
                />
            </label>
            <label className="grid gap-1 text-sm">
                <span className="text-ink-muted">{t('contact.topic')}</span>
                <select
                    id="contact-topic"
                    name="topic"
                    value={form.values.topic}
                    onChange={form.handleChange}
                    className="mn-field"
                >
                    {CONTACT_TOPICS.map((topic) => (
                        <option key={topic} value={topic}>
                            {t(`contact.topic.${topic}`)}
                        </option>
                    ))}
                </select>
            </label>
            <label className="grid gap-1 text-sm">
                <span className="text-ink-muted">{t('contact.message')}</span>
                <textarea
                    id="contact-message"
                    name="message"
                    rows={5}
                    value={form.values.message}
                    onChange={form.handleChange}
                    aria-invalid={Boolean(form.errors.message)}
                    className="mn-field"
                />
            </label>
            <div>
                <button
                    type="submit"
                    disabled={form.status === 'submitting'}
                    aria-busy={form.status === 'submitting'}
                    className="mn-button"
                >
                    {form.status === 'submitting'
                        ? t('contact.sending')
                        : t('contact.submit')}
                </button>
            </div>
        </form>
    );
}
