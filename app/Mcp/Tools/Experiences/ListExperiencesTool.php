<?php

namespace App\Mcp\Tools\Experiences;

use App\Mcp\Support\Payload;
use App\Models\Experience;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Name('list_experiences')]
#[Title('List work experience')]
#[Description('Lists the owner\'s roles (jobs, freelance periods, open source…), newest first, with their ids for linking projects (experience_id).')]
#[IsReadOnly]
class ListExperiencesTool extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        $experiences = Experience::query()
            ->with(['company', 'skills'])
            ->orderByRaw('end_date is not null')
            ->orderByDesc('end_date')
            ->orderByDesc('start_date')
            ->get();

        return Response::structured([
            'experiences' => $experiences->map(fn (Experience $experience): array => Payload::experience($experience))->values()->all(),
        ]);
    }
}
