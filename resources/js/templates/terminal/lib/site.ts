import type { Profile } from '@/types/content';

/** Webmail domains say nothing about the site, so they never become the wordmark. */
const WEBMAIL_DOMAINS = new Set([
    'gmail.com',
    'googlemail.com',
    'outlook.com',
    'hotmail.com',
    'live.com',
    'yahoo.com',
    'icloud.com',
    'me.com',
    'proton.me',
    'protonmail.com',
    'aol.com',
]);

const splitDomain = (domain: string): { name: string; tld: string } | null => {
    const dot = domain.lastIndexOf('.');
    if (dot <= 0) return null;
    return { name: domain.slice(0, dot), tld: domain.slice(dot) };
};

const hostOf = (url: string): string => {
    try {
        return new URL(url).hostname.replace(/^www\./, '');
    } catch {
        return '';
    }
};

/**
 * A "name" + ".tld" wordmark, e.g. adamrahman + .dev: the site's own domain,
 * else the profile email's domain unless it is a webmail one, else the initials.
 */
export const siteWordmark = (
    profile: Profile,
    siteUrl = '',
): { name: string; tld: string } => {
    const host = hostOf(siteUrl);
    const emailDomain = (profile.email.split('@')[1] ?? '').toLowerCase();

    return (
        (/[a-z]/i.test(host) ? splitDomain(host) : null) ??
        (WEBMAIL_DOMAINS.has(emailDomain)
            ? null
            : splitDomain(emailDomain)) ?? {
            name: profile.initials.toLowerCase(),
            tld: '.dev',
        }
    );
};

export const firstName = (name: string): string => name.split(' ')[0] ?? name;

/** Wraps the middle words of a role in braces: "Staff {Software} Engineer". */
export const splitRole = (
    role: string,
): { lead: string; accent: string; tail: string } => {
    const words = role.split(' ');
    if (words.length < 2) return { lead: '', accent: role, tail: '' };
    if (words.length === 2)
        return { lead: '', accent: words[0], tail: words[1] };
    return {
        lead: words[0],
        accent: words.slice(1, -1).join(' '),
        tail: words[words.length - 1],
    };
};
