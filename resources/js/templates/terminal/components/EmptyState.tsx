import { useTranslation } from '@/hooks/useTranslation';
import { buttonClasses } from './buttons';

export function EmptyState({ onReset }: { onReset: () => void }) {
    const { t } = useTranslation();

    return (
        <div className="tm-box flex flex-col items-center gap-3 px-6 py-16 text-center">
            <p className="text-tm-300 mb-0">
                <span className="text-tm-primary">$</span> grep -r &quot;…&quot;{' '}
                <span className="text-tm-secondary">→ 0 matches</span>
            </p>
            <h2 className="mb-0 text-2xl">{t('empty.title')}</h2>
            <p className="text-tm-300 mb-2">{t('empty.body')}</p>
            <button type="button" onClick={onReset} className={buttonClasses()}>
                {t('empty.reset')}
            </button>
        </div>
    );
}
