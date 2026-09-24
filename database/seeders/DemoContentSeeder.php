<?php

namespace Database\Seeders;

use App\Enums\ArticleStatus;
use App\Enums\EmploymentType;
use App\Enums\WorkMode;
use App\Models\Article;
use App\Models\Book;
use App\Models\Certification;
use App\Models\Company;
use App\Models\Education;
use App\Models\Experience;
use App\Models\NowPage;
use App\Models\Profile;
use App\Models\Project;
use App\Models\ProjectGalleryItem;
use App\Models\SiteSetting;
use App\Models\Skill;
use App\Models\SkillCategory;
use App\Models\Social;
use App\Models\Testimonial;
use App\Models\UsesGroup;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;

/**
 * Imports the demo portfolio from Reference-Frontend (exported to database/seeders/data/*.json,
 * images in database/seeders/images). Safe to run repeatedly.
 */
class DemoContentSeeder extends Seeder
{
    /**
     * @var array<string, int> reference experience id => experiences.id
     */
    private array $experienceIds = [];

    public function run(): void
    {
        $this->profile();
        $this->socials();
        $this->skills();
        $this->companies();
        $this->experiences();
        $this->education();
        $this->certifications();
        $this->testimonials();
        $this->projects();
        $this->articles();
        $this->books();
        $this->uses();
        $this->now();

        SiteSetting::current()->update(['active_template' => $this->data('settings')['activeTemplate']]);
    }

    private function profile(): void
    {
        $data = $this->data('profile');
        $profile = Profile::current();

        $profile->update([
            'name' => $data['name'],
            'initials' => $data['initials'],
            'role' => $data['role'],
            'headline' => $data['headline'],
            'focus_areas' => $data['focusAreas'],
            'summary' => $data['summary'],
            'story' => $data['story'],
            'location' => $data['location'],
            'timezone' => $data['timezone'],
            'timezone_label' => $data['timezoneLabel'],
            'email' => $data['email'],
            'current_version' => $data['currentVersion'],
            'availability_status' => $data['availability']['status'],
            'availability_label' => $data['availability']['label'],
            'availability_note' => $data['availability']['note'],
            'latest_release' => $data['latestRelease'],
            'stats' => $this->withoutIds($data['stats']),
            'status' => $this->withoutIds($data['status']),
            'principles' => $this->withoutIds($data['principles']),
            'portrait_alt' => $data['portrait']['alt'],
        ]);

        $this->attachImage($profile, 'portrait', $data['portrait']['base']);
    }

    private function socials(): void
    {
        foreach ($this->data('socials') as $index => $social) {
            Social::query()->updateOrCreate(['url' => $social['url']], [
                'platform' => $social['icon'],
                'label' => $social['label'],
                'handle' => $social['handle'],
                'sort_order' => $index,
            ]);
        }
    }

    private function skills(): void
    {
        $data = $this->data('skills');

        foreach ($data['skillCategories'] as $index => $category) {
            SkillCategory::query()->updateOrCreate(['slug' => $category['id']], [
                'name' => $category['label'],
                'description' => $category['description'],
                'sort_order' => $index,
            ]);
        }

        $categories = SkillCategory::query()->pluck('id', 'slug');

        foreach ($data['skills'] as $index => $skill) {
            Skill::query()->updateOrCreate(['slug' => $skill['id']], [
                'name' => $skill['name'],
                'skill_category_id' => $categories[$skill['categoryId']] ?? null,
                'proficiency' => $skill['proficiency'],
                'years' => $skill['years'],
                'sort_order' => $index,
            ]);
        }
    }

    private function companies(): void
    {
        foreach ($this->data('companies') as $index => $company) {
            Company::query()->updateOrCreate(['slug' => $company['id']], [
                'name' => $company['name'],
                'kind' => $company['kind'],
                'website_url' => $company['url'],
                'industry' => $company['industry'],
                'city' => $company['location'],
                'period_label' => $company['period'],
                'engagement' => $company['engagement'],
                'wordmark_style' => $company['wordmark'],
                'sort_order' => $index,
            ]);
        }
    }

    private function experiences(): void
    {
        $companies = Company::query()->pluck('id', 'slug');

        foreach ($this->data('experiences') as $index => $experience) {
            $model = Experience::query()
                ->where('role', $experience['role'])
                ->whereDate('start_date', "{$experience['start']}-01")
                ->firstOrNew();

            $model->fill([
                'role' => $experience['role'],
                'start_date' => "{$experience['start']}-01",
                'company_id' => $companies[$experience['companyId']] ?? null,
                'organization_name' => $experience['companyId'] === null ? $experience['organization'] : null,
                'employment_type' => $experience['type'],
                'work_mode' => $this->workMode($experience['location'], $experience['type']),
                'city' => $this->cityFrom($experience['location']),
                'end_date' => $experience['end'] !== null ? "{$experience['end']}-01" : null,
                'summary' => $experience['summary'],
                'highlights' => $experience['highlights'],
                'branch' => $experience['branch'],
                'version' => $experience['version'],
                'commit_hash' => $experience['commit'],
                'commit_message' => $experience['message'],
                'sort_order' => $index,
            ])->save();

            $model->syncSkillsInOrder($this->skillIds($experience['stack']));
            $this->experienceIds[$experience['id']] = $model->id;
        }
    }

    private function education(): void
    {
        foreach ($this->data('education') as $index => $education) {
            Education::query()->updateOrCreate(
                ['degree' => $education['degree'], 'institution' => $education['institution']],
                [
                    'field_of_study' => $education['field'],
                    'city' => $education['location'],
                    'start_date' => "{$education['start']}-09-01",
                    'end_date' => "{$education['end']}-06-01",
                    'achievements' => $education['notes'],
                    'sort_order' => $index,
                ],
            );
        }
    }

    private function certifications(): void
    {
        foreach ($this->data('certifications') as $index => $certification) {
            Certification::query()->updateOrCreate(['credential_id' => $certification['credentialId']], [
                'name' => $certification['name'],
                'issuer' => $certification['issuer'],
                'issued_at' => "{$certification['year']}-01-01",
                'credential_url' => $certification['url'],
                'sort_order' => $index,
            ]);
        }
    }

    private function testimonials(): void
    {
        $companies = Company::query()->pluck('id', 'slug');

        foreach ($this->data('testimonials') as $index => $testimonial) {
            Testimonial::query()->updateOrCreate(['author_name' => $testimonial['author']], [
                'quote' => $testimonial['quote'],
                'author_role' => $testimonial['role'],
                'company_id' => $companies[$testimonial['companyId']] ?? null,
                'relation' => $testimonial['relation'],
                'sort_order' => $index,
            ]);
        }
    }

    private function projects(): void
    {
        $companies = Company::query()->pluck('id', 'slug');

        foreach ($this->data('projects') as $index => $project) {
            $model = Project::withTrashed()->updateOrCreate(['slug' => $project['slug']], [
                'title' => $project['title'],
                'tagline' => $project['tagline'],
                'summary' => $project['summary'],
                'year' => $project['year'],
                'status' => $project['status'],
                'category' => $project['category'],
                'is_featured' => $project['featured'],
                'version' => $project['version'],
                'company_id' => $companies[$project['companyId']] ?? null,
                'experience_id' => $this->experienceIds[$project['experienceId']] ?? null,
                'role' => $project['role'],
                'team' => $project['team'],
                'timeline' => $project['timeline'],
                'overview' => $project['overview'],
                'problem' => $project['problem'],
                'approach' => $project['approach'],
                'architecture' => $project['architecture'],
                'features' => $project['features'],
                'challenges' => $project['challenges'],
                'metrics' => $this->withoutIds($project['metrics']),
                'links' => $project['links'],
                'cover_alt' => $project['cover']['alt'],
                'is_published' => true,
                'published_at' => now()->subDay(),
                'sort_order' => $index,
                'deleted_at' => null,
            ]);

            $model->syncSkillsInOrder($this->skillIds($project['stack']));
            $this->attachImage($model, 'cover', $project['cover']['base']);

            foreach ($project['gallery'] as $position => $image) {
                $item = ProjectGalleryItem::query()->updateOrCreate(
                    ['project_id' => $model->id, 'sort_order' => $position],
                    ['alt' => $image['alt'], 'caption' => $image['caption']],
                );

                $this->attachImage($item, 'image', $image['base']);
            }
        }
    }

    private function articles(): void
    {
        $projects = Project::query()->pluck('id', 'slug');

        foreach ($this->data('articles') as $article) {
            $model = Article::withTrashed()->updateOrCreate(['slug' => $article['slug']], [
                'title' => $article['title'],
                'excerpt' => $article['excerpt'],
                'body' => array_map(fn (array $block): array => [
                    'type' => $block['type'],
                    'data' => array_diff_key($block, ['type' => true]),
                ], $article['body']),
                'tags' => $article['tags'],
                'status' => ArticleStatus::Published,
                'published_at' => $article['publishedAt'],
                'deleted_at' => null,
            ]);

            $model->projects()->sync($projects->only($article['projectIds'])->values()->all());
        }
    }

    private function books(): void
    {
        foreach ($this->data('books') as $book) {
            Book::query()->updateOrCreate(['slug' => $book['slug']], [
                'title' => $book['title'],
                'author' => $book['author'],
                'published_year' => $book['publishedYear'],
                'category' => $book['category'],
                'status' => $book['status'],
                'finished_at' => $book['finishedAt'] !== null ? "{$book['finishedAt']}-01" : null,
                'rating' => $book['rating'],
                'pages' => $book['pages'],
                'note' => $book['note'],
                'cover_background' => $book['cover']['background'],
                'cover_ink' => $book['cover']['ink'],
                'cover_accent' => $book['cover']['accent'],
                'cover_style' => $book['cover']['style'],
            ]);
        }
    }

    private function uses(): void
    {
        foreach ($this->data('uses') as $index => $group) {
            $model = UsesGroup::query()->updateOrCreate(['kind' => $group['kind']], ['title' => $group['title'], 'sort_order' => $index]);

            foreach ($group['items'] as $position => $item) {
                $model->items()->updateOrCreate(['name' => $item['name']], [
                    'description' => $item['description'],
                    'url' => $item['url'] ?? null,
                    'sort_order' => $position,
                ]);
            }
        }
    }

    private function now(): void
    {
        $data = $this->data('now');
        $books = $this->data('books');
        $now = NowPage::current();

        $now->update([
            'location' => $data['location'],
            'availability' => $data['availability'],
            'focus' => $this->withoutIds($data['focus']),
            'learning' => $this->withoutIds($data['learning']),
        ]);

        $slugs = collect($books)->whereIn('id', $data['readingBookIds'])->pluck('slug');
        $bookIds = Book::query()->whereIn('slug', $slugs)->pluck('id', 'slug');

        $now->readingBooks()->sync(
            $slugs->values()->mapWithKeys(fn (string $slug, int $index): array => [$bookIds[$slug] => ['sort_order' => $index]])->all(),
        );
    }

    /**
     * @param  list<string>  $names
     * @return list<int>
     */
    private function skillIds(array $names): array
    {
        return array_map(
            fn (string $name): int => Skill::query()->firstOrCreate(['name' => $name])->id,
            $names,
        );
    }

    private function workMode(string $location, string $type): WorkMode
    {
        if ($type === EmploymentType::OpenSource->value || Str::contains($location, 'Remote')) {
            return Str::contains($location, '·') ? WorkMode::Hybrid : WorkMode::Remote;
        }

        return WorkMode::OnSite;
    }

    private function cityFrom(string $location): ?string
    {
        $city = trim(Str::before($location, '·'));

        return in_array($city, ['Remote', 'Open source', ''], true) ? null : $city;
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @return list<array<string, mixed>>
     */
    private function withoutIds(array $rows): array
    {
        return array_map(fn (array $row): array => array_diff_key($row, ['id' => true]), $rows);
    }

    private function attachImage(HasMedia $model, string $collection, string $base): void
    {
        $path = database_path("seeders/images/{$base}.webp");

        if ($model->hasMedia($collection) || ! is_file($path)) {
            return;
        }

        $model->addMedia($path)->preservingOriginal()->toMediaCollection($collection);
    }

    /**
     * @return array<mixed>
     */
    private function data(string $name): array
    {
        return json_decode((string) file_get_contents(database_path("seeders/data/{$name}.json")), true, flags: JSON_THROW_ON_ERROR);
    }
}
