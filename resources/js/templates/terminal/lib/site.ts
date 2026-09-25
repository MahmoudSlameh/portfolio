import type { Profile } from '@/types/content';

/** Splits the domain of the profile email into a "name" + ".tld" wordmark, e.g. adamrahman + .dev */
export const siteWordmark = (
    profile: Profile,
): { name: string; tld: string } => {
    const domain = profile.email.split('@')[1] ?? '';
    const dot = domain.lastIndexOf('.');
    if (dot <= 0) return { name: profile.initials.toLowerCase(), tld: '.dev' };
    return { name: domain.slice(0, dot), tld: domain.slice(dot) };
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
