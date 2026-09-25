/**
 * A small TanStack-Router-compatible API (Link, useRouterState, useNavigate) on top of Inertia,
 * so the templates ported from the reference design keep their navigation code unchanged.
 */
import { Link as InertiaLink, router, usePage } from '@inertiajs/react';
import type { AnchorHTMLAttributes, MouseEvent, ReactNode } from 'react';
import { useSyncExternalStore } from 'react';
import { cn } from '@/lib/utils';

type SearchValue = string | number | boolean | null | undefined;

export type Search = Record<string, SearchValue>;

export interface NavigateOptions {
    to?: string;
    params?: Record<string, string | number>;
    search?: Search;
    hash?: string;
    replace?: boolean;
}

export const buildHref = ({
    to = '',
    params = {},
    search,
    hash,
}: NavigateOptions): string => {
    const path = to.replace(/\$(\w+)/g, (_, name: string) =>
        encodeURIComponent(String(params[name] ?? '')),
    );
    const query = new URLSearchParams();

    for (const [key, value] of Object.entries(search ?? {})) {
        if (value !== undefined && value !== null && value !== '') {
            query.set(key, String(value));
        }
    }

    const queryString = query.toString();

    return `${path}${queryString ? `?${queryString}` : ''}${hash ? `#${hash}` : ''}`;
};

const splitUrl = (url: string): { pathname: string; searchStr: string } => {
    const [pathname = '/', searchStr = ''] = url.split('#')[0].split('?');

    return {
        pathname: pathname || '/',
        searchStr: searchStr ? `?${searchStr}` : '',
    };
};

const subscribeToHash = (onChange: () => void): (() => void) => {
    window.addEventListener('hashchange', onChange);
    const stopNavigate = router.on('navigate', onChange);

    return () => {
        window.removeEventListener('hashchange', onChange);
        stopNavigate();
    };
};

/** Current location hash without the leading `#` (always empty during SSR). */
export const useLocationHash = (): string =>
    useSyncExternalStore(
        subscribeToHash,
        () => window.location.hash.replace(/^#/, ''),
        () => '',
    );

export interface RouterLocation {
    pathname: string;
    searchStr: string;
    hash: string;
    href: string;
}

export function useRouterState<T>({
    select,
}: {
    select: (state: { location: RouterLocation }) => T;
}): T {
    const { url } = usePage();
    const hash = useLocationHash();
    const { pathname, searchStr } = splitUrl(url);

    return select({
        location: {
            pathname,
            searchStr,
            hash,
            href: `${pathname}${searchStr}${hash ? `#${hash}` : ''}`,
        },
    });
}

export function useNavigate(): (options: NavigateOptions) => void {
    const { url } = usePage();

    return (options) => {
        const href = buildHref(options);
        const target = splitUrl(href);

        if (
            options.hash &&
            (!options.to || target.pathname === splitUrl(url).pathname)
        ) {
            document
                .getElementById(options.hash)
                ?.scrollIntoView({ behavior: 'smooth' });
            window.history.replaceState(
                window.history.state,
                '',
                `#${options.hash}`,
            );

            return;
        }

        router.visit(href, { replace: options.replace });
    };
}

const isActive = (currentUrl: string, to: string, search?: Search): boolean => {
    const current = splitUrl(currentUrl);
    const target = to.split('?')[0] || '/';
    const pathMatches =
        current.pathname === target ||
        (target !== '/' && current.pathname.startsWith(`${target}/`));

    if (!pathMatches || !search) {
        return pathMatches;
    }

    const query = new URLSearchParams(current.searchStr);

    return Object.entries(search).every(
        ([key, value]) =>
            value === undefined ||
            value === null ||
            query.get(key) === String(value),
    );
};

export interface LinkProps
    extends
        Omit<AnchorHTMLAttributes<HTMLAnchorElement>, 'href'>,
        NavigateOptions {
    activeProps?: { className?: string };
    activeOptions?: {
        exact?: boolean;
        includeHash?: boolean;
        includeSearch?: boolean;
    };
    preserveScroll?: boolean;
    children?: ReactNode;
}

export function Link({
    to,
    params,
    search,
    hash,
    replace,
    activeProps,
    activeOptions,
    preserveScroll,
    className,
    onClick,
    children,
    ...rest
}: LinkProps) {
    const { url } = usePage();
    const href = buildHref({ to, params, search, hash });
    const samePageHash =
        Boolean(hash) &&
        (!to || splitUrl(href).pathname === splitUrl(url).pathname);
    const target = buildHref({ to, params });
    const active =
        Boolean(to) &&
        !hash &&
        (activeOptions?.exact
            ? splitUrl(url).pathname === splitUrl(target).pathname
            : isActive(
                  url,
                  target,
                  activeOptions?.includeSearch === false ? undefined : search,
              ));

    if (samePageHash) {
        return (
            <a
                href={`#${hash}`}
                className={className}
                onClick={(event: MouseEvent<HTMLAnchorElement>) => {
                    onClick?.(event);
                }}
                {...rest}
            >
                {children}
            </a>
        );
    }

    return (
        <InertiaLink
            href={href}
            replace={replace}
            preserveScroll={preserveScroll}
            className={cn(className, active && activeProps?.className)}
            aria-current={active ? 'page' : undefined}
            data-status={active ? 'active' : undefined}
            onClick={
                onClick
                    ? (event) => onClick(event as MouseEvent<HTMLAnchorElement>)
                    : undefined
            }
            {...(rest as Record<string, unknown>)}
        >
            {children}
        </InertiaLink>
    );
}
