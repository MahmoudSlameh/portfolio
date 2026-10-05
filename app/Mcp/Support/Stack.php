<?php

namespace App\Mcp\Support;

use App\Models\Contracts\HasStack;
use App\Models\Skill;
use Illuminate\Support\Str;

/**
 * Turns the skill names Claude sends as a "stack" into skills, reusing existing ones (matched by name,
 * case-insensitively) and creating the rest without a category: like in the panel, a skill
 * without a category is only a stack tag until it is given one.
 */
final class Stack
{
    /**
     * Replaces the model's stack, keeping the given order.
     *
     * @param  list<string>  $names
     * @return list<string> names of the skills that had to be created
     */
    public static function sync(HasStack $model, array $names): array
    {
        $ids = [];
        $created = [];

        foreach ($names as $name) {
            $name = trim($name);

            if ($name === '') {
                continue;
            }

            $skill = self::find($name);

            if ($skill === null) {
                $skill = Skill::query()->create(['name' => $name]);
                $created[] = $skill->name;
            }

            $ids[$skill->id] = $skill->id;
        }

        $model->syncSkillsInOrder(array_values($ids));

        return $created;
    }

    public static function find(string $name): ?Skill
    {
        // Names only: slugs collide for names like "C", "C#" and "C++".
        return Skill::query()->whereRaw('lower(name) = ?', [Str::lower($name)])->first();
    }
}
