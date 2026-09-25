import { useTranslation } from '@/hooks/useTranslation';
import { buttonClasses } from './buttons';

export function EmptyState({ onReset }: { onReset: () => void }) {
    const { t } = useTranslation();

    return (
        <div className="tm-box flex flex-col items-center gap-3 px-6 py-16 text-center">
            <p className="mb-0 text-tm-300">
                <span className="text-tm-primary">$</span> grep -r &quot;…&quot;{' '}
                <span className="text-tm-secondary">→ 0 matches</span>
            </p>
            <h2 className="mb-0 text-2xl">{t('empty.title')}</h2>
            <p className="mb-2 text-tm-300">{t('empty.body')}</p>
            <button type="button" onClick={onReset} className={buttonClasses()}>
                {t('empty.reset')}
            </button>
        </div>
    );
}
