<?php

namespace App\Support\Content;

use App\Enums\ReadingStatus;
use App\Http\Resources\ArticleSummaryResource;
use App\Http\Resources\BookResource;
use App\Http\Resources\CareerEntryResource;
use App\Http\Resources\CertificationResource;
use App\Http\Resources\CompanyResource;
use App\Http\Resources\EducationResource;
use App\Http\Resources\ExperienceResource;
use App\Http\Resources\ProfileResource;
use App\Http\Resources\ProjectCardResource;
use App\Http\Resources\ProjectResource;
use App\Http\Resources\SkillCategoryResource;
use App\Http\Resources\SkillResource;
use App\Http\Resources\SocialResource;
use App\Http\Resources\TestimonialResource;
use App\Http\Resources\UsesGroupResource;
use App\Models\Article;
use App\Models\Book;
use App\Models\Certification;
use App\Models\Company;
use App\Models\Education;
use App\Models\Experience;
use App\Models\NowPage;
use App\Models\Profile;
use App\Models\Project;
use App\Models\SkillCategory;
use App\Models\Social;
use App\Models\Testimonial;
use App\Models\UsesGroup;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

/**
 * Server-side port of Reference-Frontend/src/lib/content.ts: every query the templates need,
 * returned in the exact shapes of resources/js/types/content.ts.
 *
 * @phpstan-type ProjectFilters array{featured?: bool|null, search?: string|null, category?: string|null, tech?: string|null, sort?: 'newest'|'oldest'|null}
 * @phpstan-type ArticleFilters array{search?: string|null, tag?: string|null}
 * @phpstan-type BookFilters array{status?: string|null, category?: string|null}
 */
final class PortfolioContent
{
    public function __construct(private readonly Request $request) {}

    /**
     * @return array<string, mixed>
     */
    public function profile(): array
    {
        return (new ProfileResource(Profile::current()->load('media')))->toArray($this->request);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function socials(): array
    {
        return $this->resolve(SocialResource::class, Social::query()->visible()->ordered()->get());
    }

    /**
     * @return list<array{category: array<string, mixed>, skills: list<array<string, mixed>>}>
     */
    public function skillGroups(): array
    {
        return array_values(SkillCategory::query()
            ->ordered()
            ->with(['skills' => fn ($query) => $query->visible()->ordered()->with('category')])
            ->get()
            ->map(fn (SkillCategory $category): array => [
                'category' => (new SkillCategoryResource($category))->toArray($this->request),
                'skills' => $this->resolve(SkillResource::class, $category->skills),
            ])
            ->all());
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function companies(): array
    {
        return $this->resolve(CompanyResource::class, Company::query()->visible()->ordered()->with(['media', 'experiences'])->get());
    }

    /**
     * Visible experiences, newest first, with company, published projects and changelog metadata.
     *
     * @return list<array<string, mixed>>
     */
    public function career(): array
    {
        return $this->resolve(CareerEntryResource::class, $this->experiences());
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function testimonials(): array
    {
        return $this->resolve(TestimonialResource::class, Testimonial::query()->visible()->ordered()->with(['media', 'company.media', 'company.experiences'])->get());
    }

    /**
     * Current studies first, then by end date (newest first).
     *
     * @return list<array<string, mixed>>
     */
    public function education(): array
    {
        $education = Education::query()->visible()->with('media')->get()
            ->sort(fn (Education $a, Education $b): int => [$b->end_date === null, $b->end_date, $b->start_date] <=> [$a->end_date === null, $a->end_date, $a->start_date])
            ->values();

        return $this->resolve(EducationResource::class, $education);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function certifications(): array
    {
        return $this->resolve(CertificationResource::class, Certification::query()->visible()->with('media')->orderByDesc('issued_at')->orderBy('sort_order')->get());
    }

    /**
     * @param  ProjectFilters  $filters
     * @return list<array<string, mixed>>
     */
    public function projects(array $filters = []): array
    {
        $direction = ($filters['sort'] ?? 'newest') === 'oldest' ? 1 : -1;
        $featured = $filters['featured'] ?? null;
        $category = $filters['category'] ?? null;
        $tech = $filters['tech'] ?? null;
        $search = $filters['search'] ?? null;

        $projects = $this->publishedProjects()
            ->filter(fn (Project $project): bool => $featured === null || $project->is_featured === $featured)
            ->filter(fn (Project $project): bool => blank($category) || $project->category->value === $category)
            ->filter(fn (Project $project): bool => blank($tech) || in_array($tech, $project->stack, true))
            ->filter(fn (Project $project): bool => self::matches($search, [
                $project->title, (string) $project->tagline, (string) $project->summary, (string) $project->role, ...$project->stack,
            ]))
            ->sort(fn (Project $a, Project $b): int => (($a->year <=> $b->year) * $direction) ?: strcmp($a->title, $b->title))
            ->values();

        return $this->resolve(ProjectCardResource::class, $projects);
    }

    /**
     * @return array{technologies: list<string>, categories: list<string>}
     */
    public function projectFacets(): array
    {
        $projects = $this->publishedProjects();

        $technologies = array_values($projects->flatMap(fn (Project $project): array => $project->stack)->unique()->sort(SORT_NATURAL | SORT_FLAG_CASE)->all());
        $categories = array_values($projects->map(fn (Project $project): string => $project->category->value)->unique()->all());

        return ['technologies' => $technologies, 'categories' => $categories];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function projectBySlug(string $slug): ?array
    {
        $ordered = $this->publishedProjects()
            ->sort(fn (Project $a, Project $b): int => ($b->year <=> $a->year) ?: ($a->sort_order <=> $b->sort_order) ?: ($a->id <=> $b->id))
            ->values();

        $index = $ordered->search(fn (Project $project): bool => $project->slug === $slug);

        if ($index === false) {
            return null;
        }

        /** @var Project $project */
        $project = $ordered[$index];
        $project->loadMissing(['galleryItems.media', 'experience.company', 'experience.skills', 'experience.projects', 'articles' => fn ($query) => $query->published()->with(['media', 'projects'])]);
        $previous = $ordered->get($index - 1);
        $next = $ordered->get($index + 1);

        return [
            ...(new ProjectResource($project))->toArray($this->request),
            'company' => $project->company ? (new CompanyResource($project->company))->toArray($this->request) : null,
            'experience' => $project->experience ? (new ExperienceResource($project->experience))->toArray($this->request) : null,
            'previous' => $previous ? ProjectResource::reference($previous) : null,
            'next' => $next ? ProjectResource::reference($next) : null,
            'relatedArticles' => $this->resolve(ArticleSummaryResource::class, $project->articles->sortByDesc('published_at')->values()),
        ];
    }

    /**
     * @param  ArticleFilters  $filters
     * @return list<array<string, mixed>>
     */
    public function articles(array $filters = []): array
    {
        $tag = $filters['tag'] ?? null;
        $search = $filters['search'] ?? null;

        $articles = $this->publishedArticles()
            ->filter(fn (Article $article): bool => blank($tag) || in_array($tag, $article->tags, true))
            ->filter(fn (Article $article): bool => self::matches($search, [$article->title, (string) $article->excerpt, ...$article->tags]))
            ->values();

        return $this->resolve(ArticleSummaryResource::class, $articles);
    }

    /**
     * @return list<string>
     */
    public function articleTags(): array
    {
        return array_values($this->publishedArticles()->flatMap(fn (Article $article): array => $article->tags)->unique()->sort(SORT_NATURAL | SORT_FLAG_CASE)->all());
    }

    /**
     * @return array<string, mixed>|null
     */
    public function articleBySlug(string $slug): ?array
    {
        $ordered = $this->publishedArticles();
        $index = $ordered->search(fn (Article $article): bool => $article->slug === $slug);

        if ($index === false) {
            return null;
        }

        /** @var Article $article */
        $article = $ordered[$index];
        $newer = $ordered->get($index - 1);
        $older = $ordered->get($index + 1);

        $summary = (new ArticleSummaryResource($article))->toArray($this->request);
        unset($summary['readingMinutes']);

        return [
            ...$summary,
            'body' => ArticleBody::toBlocks($article),
            'readingMinutes' => $article->readingMinutes(),
            'relatedProjects' => $article->projects
                ->filter(fn (Project $project): bool => $project->isPublished())
                ->map(fn (Project $project): array => ProjectResource::reference($project))
                ->values()
                ->all(),
            'previous' => $older ? (new ArticleSummaryResource($older))->toArray($this->request) : null,
            'next' => $newer ? (new ArticleSummaryResource($newer))->toArray($this->request) : null,
        ];
    }

    /**
     * Reading first, then read (latest first), then the queue.
     *
     * @param  BookFilters  $filters
     * @return list<array<string, mixed>>
     */
    public function books(array $filters = []): array
    {
        $status = $filters['status'] ?? null;
        $category = $filters['category'] ?? null;

        $books = $this->visibleBooks()
            ->filter(fn (Book $book): bool => blank($status) || $book->status->value === $status)
            ->filter(fn (Book $book): bool => blank($category) || $book->category->value === $category)
            ->values();

        return $this->resolve(BookResource::class, $books);
    }

    /**
     * @return array{total: int, read: int, reading: int, queued: int, pagesRead: int, averageRating: float, perYear: list<array{year: int, count: int}>, categories: list<string>}
     */
    public function bookStats(): array
    {
        $books = $this->visibleBooks();
        $finished = $books->filter(fn (Book $book): bool => $book->status === ReadingStatus::Read);
        $rated = $finished->filter(fn (Book $book): bool => $book->rating !== null);
        $years = $finished->map(fn (Book $book): ?int => $book->finished_at?->year)->filter()->values();

        $perYear = $years->isEmpty() ? [] : array_map(
            fn (int $year): array => ['year' => $year, 'count' => $years->filter(fn (int $finishedYear): bool => $finishedYear === $year)->count()],
            range((int) $years->min(), (int) $years->max()),
        );

        return [
            'total' => $books->count(),
            'read' => $finished->count(),
            'reading' => $books->filter(fn (Book $book): bool => $book->status === ReadingStatus::Reading)->count(),
            'queued' => $books->filter(fn (Book $book): bool => $book->status === ReadingStatus::ToRead)->count(),
            'pagesRead' => (int) $finished->sum(fn (Book $book): int => (int) $book->pages),
            'averageRating' => $rated->isEmpty() ? 0.0 : round($rated->sum(fn (Book $book): int => (int) $book->rating) / $rated->count(), 2),
            'perYear' => $perYear,
            'categories' => array_values($books->map(fn (Book $book): string => $book->category->value)->unique()->all()),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function uses(): array
    {
        return $this->resolve(UsesGroupResource::class, UsesGroup::query()->ordered()->with('items.media')->get());
    }

    /**
     * @return array<string, mixed>
     */
    public function now(): array
    {
        $now = NowPage::current();
        $reading = $now->currentlyReading()->load('media');

        return [
            'updatedAt' => ($now->updated_at ?? now())->format('Y-m-d'),
            'location' => (string) $now->location,
            'focus' => ProfileResource::withIds($now->focus, 'title'),
            'learning' => ProfileResource::withIds($now->learning, 'title'),
            'readingBookIds' => $reading->pluck('slug')->values()->all(),
            'availability' => (string) $now->availability,
            'reading' => $this->resolve(BookResource::class, $reading),
        ];
    }

    /**
     * @return array{projects: list<array{slug: string, title: string}>, articles: list<array{slug: string, title: string}>, books: list<array{slug: string, title: string, author: string}>}
     */
    public function searchIndex(): array
    {
        return [
            'projects' => array_values(Project::query()->published()->orderByDesc('year')->get(['slug', 'title'])
                ->map(fn (Project $project): array => ProjectResource::reference($project))->all()),
            'articles' => array_values(Article::query()->published()->orderByDesc('published_at')->get(['slug', 'title'])
                ->map(fn (Article $article): array => ['slug' => $article->slug, 'title' => $article->title])->all()),
            'books' => array_values(Book::query()->visible()->orderBy('title')->get(['slug', 'title', 'author'])
                ->map(fn (Book $book): array => ['slug' => $book->slug, 'title' => $book->title, 'author' => $book->author])->all()),
        ];
    }

    /**
     * @return Collection<int, Experience>
     */
    private function experiences(): Collection
    {
        $experiences = Experience::query()
            ->visible()
            ->with(['company.media', 'company.experiences', 'skills', 'projects'])
            ->orderByDesc('start_date')
            ->orderByDesc('id')
            ->get();

        $metadata = ChangelogMetadata::for($experiences);

        return $experiences->each(fn (Experience $experience) => $experience->withChangelog($metadata[$experience->id]));
    }

    /**
     * @return Collection<int, Project>
     */
    private function publishedProjects(): Collection
    {
        return Project::query()
            ->published()
            ->with(['media', 'skills', 'company.media', 'company.experiences'])
            ->get();
    }

    /**
     * Newest first.
     *
     * @return Collection<int, Article>
     */
    private function publishedArticles(): Collection
    {
        return Article::query()->published()->with(['media', 'projects'])->orderByDesc('published_at')->orderByDesc('id')->get();
    }

    /**
     * @return Collection<int, Book>
     */
    private function visibleBooks(): Collection
    {
        $statusOrder = [ReadingStatus::Reading->value => 0, ReadingStatus::Read->value => 1, ReadingStatus::ToRead->value => 2];

        return Book::query()->visible()->with('media')->get()
            ->sort(fn (Book $a, Book $b): int => ($statusOrder[$a->status->value] <=> $statusOrder[$b->status->value])
                ?: strcmp((string) $b->finished_at?->format('Y-m'), (string) $a->finished_at?->format('Y-m')))
            ->values();
    }

    /**
     * Case- and accent-insensitive "contains" over several fields (like the reference `matchesSearch`).
     *
     * @param  list<string>  $fields
     */
    private static function matches(?string $search, array $fields): bool
    {
        if (blank($search)) {
            return true;
        }

        $needle = Str::lower(Str::ascii(trim($search)));

        foreach ($fields as $field) {
            if (str_contains(Str::lower(Str::ascii($field)), $needle)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @template TModel of \Illuminate\Database\Eloquent\Model
     *
     * @param  class-string<JsonResource>  $resource
     * @param  iterable<TModel>  $models
     * @return list<array<string, mixed>>
     */
    private function resolve(string $resource, iterable $models): array
    {
        $resolved = [];

        foreach ($models as $model) {
            $resolved[] = (new $resource($model))->resolve($this->request);
        }

        return $resolved;
    }
}
