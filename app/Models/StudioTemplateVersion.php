<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * One saved design of a studio template: a validated Template Spec with its CSS already sanitised.
 * Create versions with StudioTemplate::addVersion(), never directly.
 *
 * @property int $id
 * @property string $studio_template_id
 * @property int $number
 * @property array<string, mixed> $spec
 * @property list<string>|null $notes What the sanitiser removed, for the owner and the AI
 * @property string|null $prompt Prompt (v1) or refine instruction (v2+)
 * @property int|null $parent_id
 * @property string|null $provider
 * @property string|null $model
 * @property int|null $input_tokens
 * @property int|null $output_tokens
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read StudioTemplate $template
 * @property-read StudioTemplateVersion|null $parent
 */
#[Fillable(['number', 'spec', 'notes', 'prompt', 'parent_id', 'provider', 'model', 'input_tokens', 'output_tokens'])]
class StudioTemplateVersion extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'spec' => 'array',
            'notes' => 'array',
            'number' => 'integer',
            'input_tokens' => 'integer',
            'output_tokens' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<StudioTemplate, $this>
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(StudioTemplate::class, 'studio_template_id');
    }

    /**
     * @return BelongsTo<StudioTemplateVersion, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }
}
