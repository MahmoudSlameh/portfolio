import type {
    Article,
    ArticleBlock,
    ArticleSeries,
    ArticleSource,
    ArticleVideo as ArticleVideoData,
} from '@/types/content';
import { InlineText, type InlineTextClassNames } from './InlineText';

/**
 * `lang` and `dir` for elements that hold article content (title, excerpt, body): an Arabic article
 * reads right to left whatever the interface language is.
 */
export function articleLocale(
    article: Pick<Article, 'language' | 'direction'>,
): { lang: string; dir: 'ltr' | 'rtl' } {
    return { lang: article.language, dir: article.direction };
}

/** "System Design · 01", or just the series name when it has no position. */
export function seriesLabel(series: ArticleSeries): string {
    return series.order === null
        ? series.name
        : `${series.name} · ${String(series.order).padStart(2, '0')}`;
}

export interface ArticleTableClassNames {
    /** Scroll container around the table. */
    root?: string;
    table?: string;
    th?: string;
    td?: string;
    row?: string;
    inline?: InlineTextClassNames;
}

/** A table block: optional header row, cells are inline Markdown. Scrolls sideways on small screens. */
export function ArticleTable({
    block,
    classNames = {},
}: {
    block: Extract<ArticleBlock, { type: 'table' }>;
    classNames?: ArticleTableClassNames;
}) {
    return (
        <div className={classNames.root} tabIndex={0}>
            <table className={classNames.table}>
                {block.header && (
                    <thead>
                        <tr className={classNames.row}>
                            {block.header.map((cell, index) => (
                                <th
                                    key={index}
                                    scope="col"
                                    className={classNames.th}
                                >
                                    <InlineText
                                        text={cell}
                                        classNames={classNames.inline}
                                    />
                                </th>
                            ))}
                        </tr>
                    </thead>
                )}
                <tbody>
                    {block.rows.map((row, rowIndex) => (
                        <tr key={rowIndex} className={classNames.row}>
                            {row.map((cell, index) => (
                                <td key={index} className={classNames.td}>
                                    <InlineText
                                        text={cell}
                                        classNames={classNames.inline}
                                    />
                                </td>
                            ))}
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
}

/** The article's video (YouTube or Vimeo player) in a 16:9 frame. */
export function ArticleVideo({
    video,
    title,
    className,
}: {
    video: ArticleVideoData;
    title: string;
    className?: string;
}) {
    return (
        <div className={className}>
            <div className="relative aspect-video w-full overflow-hidden">
                <iframe
                    src={video.embedUrl}
                    title={title}
                    loading="lazy"
                    allow="accelerometer; clipboard-write; encrypted-media; gyroscope; picture-in-picture; fullscreen"
                    referrerPolicy="strict-origin-when-cross-origin"
                    allowFullScreen
                    className="absolute inset-0 size-full border-0"
                />
            </div>
        </div>
    );
}

export interface ArticleSourcesClassNames {
    root?: string;
    heading?: string;
    list?: string;
    item?: string;
    link?: string;
}

/** Numbered list of the article's sources; renders nothing when there are none. */
export function ArticleSources({
    sources,
    heading,
    classNames = {},
}: {
    sources: ArticleSource[];
    heading: string;
    classNames?: ArticleSourcesClassNames;
}) {
    if (sources.length === 0) return null;

    return (
        <section aria-label={heading} className={classNames.root}>
            <h2 className={classNames.heading}>{heading}</h2>
            <ol className={classNames.list}>
                {sources.map((source) => (
                    <li key={source.url} className={classNames.item}>
                        <a
                            href={source.url}
                            target="_blank"
                            rel="noopener noreferrer"
                            className={classNames.link}
                        >
                            {source.title}
                        </a>
                    </li>
                ))}
            </ol>
        </section>
    );
}
