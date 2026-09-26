import {
    CONTACT_FIELDS,
    CONTACT_TOPICS,
    useContactForm,
    useTranslation,
} from '@/kit';

/** The contact form: behaviour from `useContactForm()`, markup styled by the spec tokens. */
export function ContactForm() {
    const { t } = useTranslation();
    const form = useContactForm();

    if (form.status === 'success') {
        return (
            <div role="status" className="st-card st-contact-success p-6">
                <p className="text-lg font-bold text-ink">
                    {t('contact.successTitle')}
                </p>
                <p className="mt-2 text-ink-muted">
                    {t('contact.successBody', {
                        name: form.values.name.trim(),
                        id: form.messageId,
                    })}
                </p>
                <button
                    type="button"
                    onClick={form.reset}
                    className="st-link mt-4"
                >
                    {t('contact.sendAnother')}
                </button>
            </div>
        );
    }

    return (
        <form
            noValidate
            onSubmit={form.handleSubmit}
            className="st-contact-form grid gap-4"
        >
            {form.errorCount > 0 && (
                <div
                    ref={form.summaryRef}
                    tabIndex={-1}
                    role="alert"
                    className="rounded-[var(--st-radius)] bg-danger-soft p-4 text-sm text-danger"
                >
                    <p className="font-bold">
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
            <div className="grid gap-4 sm:grid-cols-2">
                <label className="grid gap-1.5 text-sm text-ink-muted">
                    {t('contact.name')}
                    <input
                        ref={form.nameRef}
                        id="contact-name"
                        name="name"
                        autoComplete="name"
                        value={form.values.name}
                        onChange={form.handleChange}
                        aria-invalid={Boolean(form.errors.name)}
                        className="st-field"
                    />
                </label>
                <label className="grid gap-1.5 text-sm text-ink-muted">
                    {t('contact.email')}
                    <input
                        id="contact-email"
                        name="email"
                        type="email"
                        autoComplete="email"
                        value={form.values.email}
                        onChange={form.handleChange}
                        aria-invalid={Boolean(form.errors.email)}
                        className="st-field"
                    />
                </label>
            </div>
            <label className="grid gap-1.5 text-sm text-ink-muted">
                {t('contact.topic')}
                <select
                    id="contact-topic"
                    name="topic"
                    value={form.values.topic}
                    onChange={form.handleChange}
                    className="st-field"
                >
                    {CONTACT_TOPICS.map((topic) => (
                        <option key={topic} value={topic}>
                            {t(`contact.topic.${topic}`)}
                        </option>
                    ))}
                </select>
            </label>
            <label className="grid gap-1.5 text-sm text-ink-muted">
                {t('contact.message')}
                <textarea
                    id="contact-message"
                    name="message"
                    rows={5}
                    value={form.values.message}
                    onChange={form.handleChange}
                    aria-invalid={Boolean(form.errors.message)}
                    className="st-field"
                />
            </label>
            <div>
                <button
                    type="submit"
                    disabled={form.status === 'submitting'}
                    aria-busy={form.status === 'submitting'}
                    className="st-button"
                >
                    {form.status === 'submitting'
                        ? t('contact.sending')
                        : t('contact.submit')}
                </button>
            </div>
        </form>
    );
}
