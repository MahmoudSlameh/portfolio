<?php

namespace App\Models\Concerns;

use App\Models\Skill;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * Ordered "tech stack" of an experience or project, stored in the `skillables` pivot.
 *
 * @phpstan-require-extends Model
 *
 * @property-read list<string> $stack
 */
trait HasSkills
{
    /**
     * @return MorphToMany<Skill, $this>
     */
    public function skills(): MorphToMany
    {
        return $this->morphToMany(Skill::class, 'skillable')
            ->withPivot('sort_order')
            ->orderByPivot('sort_order');
    }

    /**
     * Replace the stack, keeping the given order.
     *
     * @param  list<int>  $skillIds
     */
    public function syncSkillsInOrder(array $skillIds): void
    {
        $this->skills()->sync(
            collect($skillIds)->values()->mapWithKeys(fn (int $id, int $index): array => [$id => ['sort_order' => $index]])->all(),
        );
    }

    /**
     * Skill names in stack order.
     *
     * @return Attribute<list<string>, never>
     */
    protected function stack(): Attribute
    {
        return Attribute::get(fn (): array => array_values($this->skills->map(fn (Skill $skill): string => $skill->name)->all()));
    }
}
