import { Head } from '@inertiajs/react';

export interface SeoData {
    title: string;
    description: string;
    canonical: string;
    robots: string;
    type: 'website' | 'profile' | 'article';
    siteName: string;
    image: {
        url: string;
        width: number | null;
        height: number | null;
        alt: string;
    } | null;
    twitterSite: string | null;
    publishedTime: string | null;
    modifiedTime: string | null;
    tags: string[];
    jsonLd: Record<string, unknown>[];
}

/** Every head tag of a page, rendered on the server by Inertia SSR (see app/Support/Seo/SeoData.php). */
export function SeoHead({ seo }: { seo: SeoData }) {
    return (
        <Head>
            <title>{seo.title}</title>
            <meta
                head-key="description"
                name="description"
                content={seo.description}
            />
            <meta head-key="robots" name="robots" content={seo.robots} />
            <link head-key="canonical" rel="canonical" href={seo.canonical} />
            <meta head-key="og:type" property="og:type" content={seo.type} />
            <meta
                head-key="og:site_name"
                property="og:site_name"
                content={seo.siteName}
            />
            <meta head-key="og:title" property="og:title" content={seo.title} />
            <meta
                head-key="og:description"
                property="og:description"
                content={seo.description}
            />
            <meta head-key="og:url" property="og:url" content={seo.canonical} />
            <meta head-key="og:locale" property="og:locale" content="en_US" />
            {seo.image && (
                <meta
                    head-key="og:image"
                    property="og:image"
                    content={seo.image.url}
                />
            )}
            {seo.image?.width && (
                <meta
                    head-key="og:image:width"
                    property="og:image:width"
                    content={String(seo.image.width)}
                />
            )}
            {seo.image?.height && (
                <meta
                    head-key="og:image:height"
                    property="og:image:height"
                    content={String(seo.image.height)}
                />
            )}
            {seo.image && (
                <meta
                    head-key="og:image:alt"
                    property="og:image:alt"
                    content={seo.image.alt}
                />
            )}
            {seo.publishedTime && (
                <meta
                    head-key="article:published_time"
                    property="article:published_time"
                    content={seo.publishedTime}
                />
            )}
            {seo.modifiedTime && (
                <meta
                    head-key="article:modified_time"
                    property="article:modified_time"
                    content={seo.modifiedTime}
                />
            )}
            {seo.tags.map((tag) => (
                <meta
                    key={tag}
                    head-key={`article:tag:${tag}`}
                    property="article:tag"
                    content={tag}
                />
            ))}
            <meta
                head-key="twitter:card"
                name="twitter:card"
                content={seo.image ? 'summary_large_image' : 'summary'}
            />
            {seo.twitterSite && (
                <meta
                    head-key="twitter:site"
                    name="twitter:site"
                    content={seo.twitterSite}
                />
            )}
            <meta
                head-key="twitter:title"
                name="twitter:title"
                content={seo.title}
            />
            <meta
                head-key="twitter:description"
                name="twitter:description"
                content={seo.description}
            />
            {seo.image && (
                <meta
                    head-key="twitter:image"
                    name="twitter:image"
                    content={seo.image.url}
                />
            )}
            {seo.jsonLd.map((graph, index) => (
                <script
                    key={index}
                    head-key={`jsonld-${index}`}
                    type="application/ld+json"
                    dangerouslySetInnerHTML={{
                        __html: JSON.stringify(graph).replace(/</g, '\\u003c'),
                    }}
                />
            ))}
        </Head>
    );
}
