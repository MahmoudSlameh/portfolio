import { useTranslation } from '@/kit';
import type { UsesPageProps } from '@/templates/types';
import { PageHeader, Section } from '../components/Section';

/** /uses. `groups` are the hardware / software / development lists from the panel. */
export function UsesPage({ groups }: UsesPageProps) {
    const { t } = useTranslation();

    return (
        <>
            <PageHeader title={t('uses.title')} intro={t('uses.intro')} />
            {groups.map((group) => (
                <Section key={group.id} id={group.id} title={group.title}>
                    <dl className="grid gap-4">
                        {group.items.map((item) => (
                            <div key={item.id}>
                                <dt className="font-medium text-ink">
                                    {item.url ? (
                                        <a href={item.url} className="mn-link">
                                            {item.name}
                                        </a>
                                    ) : (
                                        item.name
                                    )}
                                </dt>
                                <dd className="text-ink-muted">
                                    {item.description}
                                </dd>
                            </div>
                        ))}
                    </dl>
                </Section>
            ))}
        </>
    );
}
