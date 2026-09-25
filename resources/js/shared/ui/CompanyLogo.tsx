import type { ReactNode } from 'react';
import { cn } from '@/lib/utils';
import type { Company } from '@/types/content';

interface CompanyLogoProps {
    company: Company;
    /** Rendered when no logo was uploaded (the template's wordmark). */
    fallback: ReactNode;
    className?: string;
}

/** The company's uploaded logo (with an optional dark-theme variant), or the template wordmark. */
export function CompanyLogo({
    company,
    fallback,
    className,
}: CompanyLogoProps) {
    if (!company.logo) {
        return <>{fallback}</>;
    }

    const imageClass = cn('h-8 w-auto max-w-40 object-contain', className);

    return (
        <>
            <img
                src={company.logo.src}
                alt={company.name}
                width={company.logo.width || undefined}
                height={company.logo.height || undefined}
                loading="lazy"
                decoding="async"
                className={cn(imageClass, company.logoDark && 'dark:hidden')}
            />
            {company.logoDark && (
                <img
                    src={company.logoDark.src}
                    alt={company.name}
                    loading="lazy"
                    decoding="async"
                    className={cn(imageClass, 'hidden dark:inline-block')}
                />
            )}
        </>
    );
}
