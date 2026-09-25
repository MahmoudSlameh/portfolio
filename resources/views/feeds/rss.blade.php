{!! '<'.'?xml version="1.0" encoding="UTF-8"?>' !!}
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
    <channel>
        <title>{{ $settings->site_name }} — Writing</title>
        <link>{{ \App\Support\Seo\Seo::url('/writing') }}</link>
        <atom:link href="{{ \App\Support\Seo\Seo::url('/rss.xml') }}" rel="self" type="application/rss+xml" />
        <description>{{ $settings->meta_description ?: $profile->headline }}</description>
        <language>en</language>
@if ($articles->isNotEmpty())
        <lastBuildDate>{{ $articles->first()->published_at?->toRssString() }}</lastBuildDate>
@endif
@foreach ($articles as $article)
        <item>
            <title>{{ $article->title }}</title>
            <link>{{ \App\Support\Seo\Seo::url("/writing/{$article->slug}") }}</link>
            <guid isPermaLink="true">{{ \App\Support\Seo\Seo::url("/writing/{$article->slug}") }}</guid>
            <pubDate>{{ $article->published_at?->toRssString() }}</pubDate>
            <description>{{ $article->excerpt }}</description>
@foreach ($article->tags as $tag)
            <category>{{ $tag }}</category>
@endforeach
        </item>
@endforeach
    </channel>
</rss>
