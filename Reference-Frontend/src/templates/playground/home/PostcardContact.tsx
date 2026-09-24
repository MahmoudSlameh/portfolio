import { Check, Copy, Send } from 'lucide-react';
import { useEffect, useRef, useState, type ChangeEvent, type FormEvent } from 'react';
import { useClipboard } from '@/hooks/useClipboard';
import { useNow } from '@/hooks/useLocalTime';
import { useTranslation } from '@/hooks/useTranslation';
import { validateContact, type ContactErrors, type ContactField } from '@/lib/contactSchema';
import { submitContactMessage } from '@/lib/content';
import { formatTime } from '@/lib/utils';
import { useToast } from '@/providers/ToastProvider';
import type { ContactMessage, Profile } from '@/types/content';
import { usePlaygroundCopy } from '../copy';
import { PopInput, PopTextArea } from '../components/PopField';
import { PopButton } from '../components/PopButton';
import { SectionHeading } from '../components/SectionHeading';

const initialValues: ContactMessage = { name: '', email: '', topic: 'advisory', message: '' };

const TOPICS: ContactMessage['topic'][] = ['advisory', 'role', 'speaking', 'hello'];

const FIELD_ORDER: ContactField[] = ['name', 'email', 'topic', 'message'];

type FormStatus = 'idle' | 'submitting' | 'success';

function PostageStamp({ profile }: { profile: Profile }) {
  return (
    <div aria-hidden className="relative rotate-[4deg] rounded-md border-2 border-edge bg-raised p-1.5 shadow-[var(--pg-shadow)]">
      <div className="flex size-24 flex-col items-center justify-center rounded-sm border-2 border-dashed border-edge bg-pop-red text-on-pop">
        <span className="font-display text-3xl font-black [font-stretch:125%]">{profile.initials}</span>
        <span className="font-mono text-[0.625rem] font-bold">{profile.currentVersion}</span>
      </div>
    </div>
  );
}

function AddressSide({ profile }: { profile: Profile }) {
  const { t, l, locale } = useTranslation();
  const p = usePlaygroundCopy();
  const now = useNow();
  const { copied, copy } = useClipboard();
  const { notify } = useToast();
  const CopyIcon = copied ? Check : Copy;

  const handleCopy = async (): Promise<void> => {
    if (await copy(profile.email)) notify(t('contact.copied'));
  };

  return (
    <div className="relative flex flex-col gap-6 p-6 md:p-8">
      <div className="flex items-start justify-between gap-4">
        <div
          aria-hidden
          className="flex size-24 rotate-[-12deg] items-center justify-center rounded-full border-2 border-dashed border-ink-subtle p-2 text-center font-mono text-[0.5625rem] leading-tight font-bold text-ink-subtle uppercase"
        >
          {p('contact.postmark', { city: l(profile.location) })}
        </div>
        <PostageStamp profile={profile} />
      </div>

      <div className="flex flex-col gap-2">
        <p className="pg-label text-ink-subtle">{p('contact.to')}</p>
        <a href={`mailto:${profile.email}`} className="ltr-isolate border-b-2 border-edge pb-2 font-display text-xl font-extrabold break-all text-ink [font-stretch:110%] hover:text-signal-ink md:text-2xl">
          {profile.email}
        </a>
        <PopButton tone="plain" size="sm" onClick={handleCopy} className="mt-2 self-start">
          <CopyIcon aria-hidden className="size-4" strokeWidth={2.5} />
          {copied ? t('contact.copied') : t('contact.copyEmail')}
        </PopButton>
      </div>

      <dl className="flex flex-col gap-4">
        <div className="border-b-2 border-dashed border-line-strong/40 pb-3">
          <dt className="pg-label text-ink-subtle">{t('contact.availability')}</dt>
          <dd className="mt-1 font-semibold text-ink">{l(profile.availability.label)}</dd>
          <dd className="text-sm text-ink-muted">{l(profile.availability.note)}</dd>
        </div>
        <div className="border-b-2 border-dashed border-line-strong/40 pb-3">
          <dt className="pg-label text-ink-subtle">{t('contact.timezone')}</dt>
          <dd className="mt-1 flex flex-wrap items-baseline gap-2 font-semibold text-ink">
            {l(profile.location)}
            <time dateTime={now.toISOString()} className="ltr-isolate font-mono text-sm">
              {formatTime(now, profile.timezone, locale)}
            </time>
            <span className="ltr-isolate font-mono text-xs text-ink-subtle">{profile.timezoneLabel}</span>
          </dd>
        </div>
      </dl>
    </div>
  );
}

function DeliveredCard({ name, messageId, onReset }: { name: string; messageId: string; onReset: () => void }) {
  const { t } = useTranslation();
  const p = usePlaygroundCopy();
  const headingRef = useRef<HTMLHeadingElement>(null);

  useEffect(() => {
    headingRef.current?.focus();
  }, []);

  return (
    <div role="status" className="relative flex min-h-96 flex-col items-start justify-center gap-5 p-6 md:p-10">
      <span
        aria-hidden
        className="pg-stamp-in absolute end-6 top-6 rounded-xl border-4 border-pop-green px-4 py-2 font-display text-3xl font-black text-pop-green uppercase [font-stretch:125%]"
      >
        ✓ {p('contact.sentStamp')}
      </span>
      <p className="ltr-isolate font-mono text-xs font-bold text-ink-subtle">{messageId}</p>
      <h3 ref={headingRef} tabIndex={-1} className="pg-display pg-keep-case max-w-md text-4xl text-ink focus-visible:outline-none md:text-5xl">
        {t('contact.successTitle')}
      </h3>
      <p className="max-w-md text-base leading-relaxed text-ink-muted">{t('contact.successBody', { name, id: messageId })}</p>
      <PopButton tone="yellow" onClick={onReset}>
        {t('contact.sendAnother')}
      </PopButton>
    </div>
  );
}

export function PostcardContact({ profile }: { profile: Profile }) {
  const { t } = useTranslation();
  const p = usePlaygroundCopy();
  const [values, setValues] = useState<ContactMessage>(initialValues);
  const [errors, setErrors] = useState<ContactErrors>({});
  const [hasAttempted, setHasAttempted] = useState(false);
  const [status, setStatus] = useState<FormStatus>('idle');
  const [messageId, setMessageId] = useState('');
  const summaryRef = useRef<HTMLDivElement>(null);
  const nameRef = useRef<HTMLInputElement>(null);
  const errorCount = Object.keys(errors).length;

  const handleChange = (event: ChangeEvent<HTMLInputElement | HTMLTextAreaElement>): void => {
    const nextValues = { ...values, [event.target.name]: event.target.value };
    setValues(nextValues);
    if (!hasAttempted) return;
    const result = validateContact(nextValues);
    setErrors(result.success ? {} : result.errors);
  };

  const handleSubmit = async (event: FormEvent<HTMLFormElement>): Promise<void> => {
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
  };

  const handleReset = (): void => {
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

  return (
    <section id="contact" aria-labelledby="contact-title" className="pg-shell scroll-mt-8 py-16">
      <SectionHeading
        id="contact"
        kicker={p('contact.kicker')}
        title={p('contact.title')}
        pop="blue"
        aside={<p className="max-w-sm text-base text-ink-muted">{t('contact.intro')}</p>}
      />
      <div className="pg-card grid overflow-hidden bg-raised shadow-[var(--pg-shadow-lg)] lg:grid-cols-[minmax(0,1.35fr)_minmax(0,1fr)]">
        <div className="border-b-2 border-dashed border-edge lg:border-e-2 lg:border-b-0">
          {status === 'success' ? (
            <DeliveredCard name={values.name.trim()} messageId={messageId} onReset={handleReset} />
          ) : (
            <form noValidate onSubmit={handleSubmit} className="flex flex-col gap-6 p-6 md:p-10" aria-describedby={errorCount ? 'contact-summary' : undefined}>
              {errorCount > 0 && (
                <div
                  id="contact-summary"
                  ref={summaryRef}
                  tabIndex={-1}
                  role="alert"
                  className="rounded-xl border-2 border-danger bg-danger-soft px-4 py-3 text-sm text-danger"
                >
                  <p className="font-bold">{t('contact.errorSummary', { count: errorCount })}</p>
                  <ul className="mt-1.5 flex flex-col gap-0.5">
                    {FIELD_ORDER.filter((field) => errors[field]).map((field) => (
                      <li key={field}>
                        <a href={`#contact-${field}`} className="underline underline-offset-2">
                          {errorText(field)}
                        </a>
                      </li>
                    ))}
                  </ul>
                </div>
              )}

              <div className="grid gap-5 sm:grid-cols-2">
                <PopInput
                  ref={nameRef}
                  id="contact-name"
                  name="name"
                  label={`${p('contact.from')} · ${t('contact.name')}`}
                  autoComplete="name"
                  required
                  requiredLabel={t('contact.required')}
                  value={values.name}
                  onChange={handleChange}
                  error={errorText('name')}
                />
                <PopInput
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

              <fieldset id="contact-topic" className="flex flex-col gap-3">
                <legend className="mb-2 text-sm font-bold text-ink">{p('contact.topicLegend')}</legend>
                <div className="flex flex-wrap gap-2">
                  {TOPICS.map((topic) => (
                    <label key={topic} className="pg-chip cursor-pointer">
                      <input
                        type="radio"
                        name="topic"
                        value={topic}
                        checked={values.topic === topic}
                        onChange={handleChange}
                        className="sr-only"
                      />
                      {t(`contact.topic.${topic}`)}
                    </label>
                  ))}
                </div>
              </fieldset>

              <PopTextArea
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
                className="bg-[repeating-linear-gradient(to_bottom,transparent_0,transparent_31px,var(--line)_31px,var(--line)_32px)] [background-attachment:local] leading-8"
              />

              <PopButton type="submit" tone="blue" disabled={status === 'submitting'} aria-busy={status === 'submitting'} className="self-start text-white">
                <Send aria-hidden className="size-4 rtl:-scale-x-100" strokeWidth={2.5} />
                {status === 'submitting' ? t('contact.sending') : t('contact.submit')}
              </PopButton>
            </form>
          )}
        </div>
        <AddressSide profile={profile} />
      </div>
    </section>
  );
}
