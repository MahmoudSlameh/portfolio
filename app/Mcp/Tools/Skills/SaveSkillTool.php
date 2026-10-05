<?php

namespace App\Mcp\Tools\Skills;

use App\Mcp\Support\Payload;
use App\Mcp\Support\Stack;
use App\Models\Skill;
use App\Models\SkillCategory;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;

#[Name('save_skill')]
#[Title('Create or update a skill')]
#[Description('Creates a skill, or updates it when "id" is given or a skill with the same name exists (case-insensitive). Give it a category to list it in the site\'s skills section; the category is created when it does not exist yet.')]
#[IsIdempotent]
class SaveSkillTool extends Tool
{
    public function handle(Request $request): Response|ResponseFactory
    {
        $data = $request->validate([
            'id' => ['nullable', 'integer'],
            'name' => [Rule::requiredIf(blank($request->get('id'))), 'string', 'max:255'],
            'category' => ['sometimes', 'nullable', 'string', 'max:255'],
            'proficiency' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:5'],
            'years' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:60'],
            'icon' => ['sometimes', 'nullable', 'string', 'max:100', 'regex:/^[a-z0-9.\-]+$/'],
            'is_visible' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ], ['icon.regex' => 'icon is a Simple Icons slug, e.g. "laravel" or "dotnet".']);

        $skill = filled($data['id'] ?? null) ? Skill::query()->find((int) $data['id']) : Stack::find((string) $data['name']);

        if (filled($data['id'] ?? null) && $skill === null) {
            return Response::error("No skill has id {$data['id']}. Call list_skills to find it.");
        }

        $skill ??= new Skill;
        $creating = ! $skill->exists;

        if (isset($data['name']) && Skill::query()->whereRaw('lower(name) = ?', [Str::lower($data['name'])])->whereKeyNot($skill->getKey())->exists()) {
            return Response::error("Another skill is already called \"{$data['name']}\".");
        }

        // A skill matched by name keeps its spelling ("laravel" updates "Laravel"); renaming needs the id.
        $columns = $creating || filled($data['id'] ?? null) ? ['name'] : [];
        $skill->fill(Arr::only($data, [...$columns, 'proficiency', 'years', 'icon', 'is_visible', 'sort_order']));

        if (array_key_exists('category', $data)) {
            $skill->skill_category_id = blank($data['category']) ? null : $this->category((string) $data['category'])->id;
        }

        $skill->save();

        return Response::structured([
            'message' => ($creating ? 'Created' : 'Updated')." skill \"{$skill->name}\".",
            'skill' => Payload::skill($skill->load('category')),
        ]);
    }

    private function category(string $name): SkillCategory
    {
        return SkillCategory::query()->whereRaw('lower(name) = ?', [Str::lower(trim($name))])->first()
            ?? SkillCategory::query()->create([
                'name' => trim($name),
                'sort_order' => (int) SkillCategory::query()->max('sort_order') + 1,
            ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->description('Update this skill (from list_skills). Without it, the skill is matched by name or created.'),
            'name' => $schema->string()->description('Official spelling, e.g. "PostgreSQL", "Next.js", "C#".'),
            'category' => $schema->string()->description('Category name, e.g. "Languages", "Frameworks", "Infrastructure". null removes the category (the skill stays a stack tag only).'),
            'proficiency' => $schema->integer()->min(1)->max(5)->description('1–5. Only when the owner told you.'),
            'years' => $schema->integer()->min(0)->description('Years of experience. Only when known.'),
            'icon' => $schema->string()->description('Simple Icons slug (https://simpleicons.org), e.g. "laravel", "react", "postgresql".'),
            'is_visible' => $schema->boolean(),
            'sort_order' => $schema->integer()->min(0)->description('Position inside its category; lower comes first.'),
        ];
    }
}
