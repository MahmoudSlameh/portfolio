<?php

namespace App\Mcp\Tools\Profile;

use App\Enums\AvailabilityStatus;
use App\Mcp\Support\Payload;
use App\Models\Profile;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;

#[Name('update_profile')]
#[Title('Update the profile')]
#[Description('Changes the owner\'s profile and bio. Send only the fields to change; lists replace the stored lists. Only change what the owner asked for.')]
#[IsIdempotent]
class UpdateProfileTool extends Tool
{
    private const COLUMNS = [
        'name', 'initials', 'role', 'headline', 'summary', 'story', 'focus_areas', 'location', 'timezone',
        'email', 'phone', 'availability_status', 'availability_label', 'availability_note', 'stats', 'principles',
    ];

    public function handle(Request $request): Response|ResponseFactory
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'initials' => ['sometimes', 'nullable', 'string', 'max:4'],
            'role' => ['sometimes', 'string', 'max:255'],
            'headline' => ['sometimes', 'nullable', 'string', 'max:255'],
            'summary' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'story' => ['sometimes', 'array', 'max:10'],
            'story.*' => ['string', 'max:5000'],
            'focus_areas' => ['sometimes', 'array', 'max:12'],
            'focus_areas.*' => ['string', 'max:100'],
            'location' => ['sometimes', 'nullable', 'string', 'max:255'],
            'timezone' => ['sometimes', 'timezone:all'],
            'email' => ['sometimes', 'nullable', 'email', 'max:255'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:50'],
            'availability_status' => ['sometimes', Rule::enum(AvailabilityStatus::class)],
            'availability_label' => ['sometimes', 'nullable', 'string', 'max:255'],
            'availability_note' => ['sometimes', 'nullable', 'string', 'max:500'],
            'stats' => ['sometimes', 'array', 'max:8'],
            'stats.*.value' => ['required', 'string', 'max:50'],
            'stats.*.label' => ['required', 'string', 'max:100'],
            'principles' => ['sometimes', 'array', 'max:12'],
            'principles.*.title' => ['required', 'string', 'max:255'],
            'principles.*.body' => ['required', 'string', 'max:2000'],
        ]);

        if ($data === []) {
            return Response::error('Nothing to update: send at least one field to change.');
        }

        $profile = Profile::current();
        $profile->fill(Arr::only($data, self::COLUMNS))->save();

        return Response::structured([
            'message' => 'Updated the profile: '.implode(', ', array_keys($data)).'.',
            'profile' => Payload::profile($profile),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string(),
            'initials' => $schema->string()->description('Up to 4 letters; derived from the name when empty.'),
            'role' => $schema->string()->description('e.g. "Senior Software Engineer".'),
            'headline' => $schema->string()->description('The hero sentence on the home page.'),
            'summary' => $schema->string()->description('Short bio, 2–3 sentences.'),
            'story' => $schema->array()->items($schema->string())->description('Longer "about" text, one paragraph per item.'),
            'focus_areas' => $schema->array()->items($schema->string())->description('Short tags, e.g. ["Distributed systems", "Developer tooling"].'),
            'location' => $schema->string(),
            'timezone' => $schema->string()->description('IANA timezone, e.g. "Europe/Berlin".'),
            'email' => $schema->string(),
            'phone' => $schema->string(),
            'availability_status' => $schema->string()->enum(AvailabilityStatus::class),
            'availability_label' => $schema->string()->description('e.g. "Open to senior roles".'),
            'availability_note' => $schema->string(),
            'stats' => $schema->array()->items($schema->object([
                'value' => $schema->string()->required(),
                'label' => $schema->string()->required(),
            ]))->description('Headline numbers, e.g. [{"value": "10+", "label": "years shipping"}]. Real figures only.'),
            'principles' => $schema->array()->items($schema->object([
                'title' => $schema->string()->required(),
                'body' => $schema->string()->required(),
            ]))->description('How the owner works, each {title, body}.'),
        ];
    }
}
