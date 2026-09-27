<?php

namespace App\Models;

use App\Enums\StudioStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * One AI generation attempt (docs/12-ai-templates.md §5): what was asked, what it cost, how it ended.
 * The daily limit counts these rows, so failed attempts count too.
 *
 * @property int $id
 * @property string|null $studio_template_id
 * @property StudioStatus $status Queued, InProgress, Ready (succeeded) or Failed
 * @property string|null $prompt
 * @property string|null $start_from Template id the design started from
 * @property string|null $provider
 * @property string|null $model
 * @property int $turns
 * @property int $input_tokens
 * @property int $output_tokens
 * @property string|null $error
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read StudioTemplate|null $template
 */
#[Fillable(['studio_template_id', 'status', 'prompt', 'start_from', 'provider', 'model', 'turns', 'input_tokens', 'output_tokens', 'error'])]
class StudioGeneration extends Model
{
    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => 'queued',
        'turns' => 0,
        'input_tokens' => 0,
        'output_tokens' => 0,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => StudioStatus::class,
            'turns' => 'integer',
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
     * Attempts started today (app timezone), for the daily limit.
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function today(Builder $query): void
    {
        $query->where('created_at', '>=', now()->startOfDay());
    }
}
