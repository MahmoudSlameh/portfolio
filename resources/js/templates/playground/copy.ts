import { useCallback } from 'react';
import { interpolate } from '@/hooks/useTranslation';

const en = {
    'top.sayHi': 'Say hi',
    'top.search': 'Search',
    'dock.label': 'Quick navigation',
    'hero.hello': 'Hey, I’m',
    'hero.into': 'Into',
    'hero.seeWork': 'See the work',
    'hero.sayHi': 'Say hello',
    'hero.dragMe': 'drag me',
    'hero.localTime': 'Local time',
    'hero.statusTitle': 'Right now',
    'hero.freshTitle': 'Fresh this season',
    'hero.portrait': 'Portrait of {name}',
    'about.kicker': 'The story so far',
    'about.principles': 'House rules',
    'stack.kicker': 'Toolbox',
    'stack.title': 'Stuff I build with',
    'stack.level': 'Level {level} of 5',
    'career.kicker': 'Tickets so far',
    'career.title': 'Every stop on the ride',
    'career.previous': 'Scroll to newer roles',
    'career.next': 'Scroll to older roles',
    'career.more': 'More',
    'career.less': 'Less',
    'career.boarding': 'Boarding',
    'career.arrival': 'Arrival',
    'work.kicker': 'Selected work',
    'work.title': 'Things I shipped',
    'work.all': 'Everything',
    'clients.kicker': 'Good company',
    'clients.title': 'Teams I built with',
    'clients.intro':
        'Employers and consulting clients. Every tile opens their site.',
    'clients.notes': 'Notes they left me',
    'education.kicker': 'Paperwork',
    'education.title': 'Degrees & badges',
    'writing.kicker': 'Blog',
    'writing.title': 'Things I wrote down',
    'books.kicker': 'Nightstand',
    'books.title': 'The reading pile',
    'books.pick': 'Pick a book from the pile to read my note.',
    'books.close': 'Close book',
    'books.noRating': 'Not rated yet',
    'books.pageCount': '{count} pages',
    'books.viewStack': 'Pile',
    'books.viewGrid': 'Covers',
    'books.inProgress': 'In progress',
    'contact.kicker': 'Mailbox',
    'contact.title': 'Send me a postcard',
    'contact.to': 'To',
    'contact.from': 'From',
    'contact.postmark': 'Posted from {city}',
    'contact.sentStamp': 'Delivered',
    'contact.topicLegend': 'What is it about?',
    'footer.builtWith': 'Set in Archivo, Space Grotesk and Space Mono.',
    'footer.madeWith': 'Made with stubbornness and a lot of tea.',
    'footer.top': 'Back to top',
    'page.count': '{count} total',
    'projects.cards': 'Cards',
    'projects.list': 'List',
    'case.facts': 'Quick facts',
    'case.sections': 'Jump to',
    'case.step': 'Step {index}',
    'article.stickerRead': '{count} min',
    'now.kicker': 'Status update',
    'now.where': 'Where',
    'uses.kicker': 'My kit',
    'uses.visit': 'Visit {name}',
    'notFound.kicker': 'Error 404',
    'notFound.title': 'This page wandered off.',
    'notFound.body':
        'Nothing lives at this address. While you are here, drag the numbers around, then pick a way out.',
    'notFound.shuffle': 'Shuffle',
    'empty.title': 'Nothing in here.',
    'empty.body': 'Loosen the filters and try again.',
    'template.name': 'Playground',
} as const;

export type PlaygroundKey = keyof typeof en;

const copy = { en } as const;

type Variables = Record<string, string | number>;

export function usePlaygroundCopy(): (
    key: PlaygroundKey,
    variables?: Variables,
) => string {
    return useCallback(
        (key: PlaygroundKey, variables?: Variables): string =>
            interpolate(copy.en[key], variables),
        [],
    );
}
