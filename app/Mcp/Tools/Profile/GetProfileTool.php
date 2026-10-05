<?php

namespace App\Mcp\Tools\Profile;

use App\Mcp\Support\Payload;
use App\Models\Profile;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Name('get_profile')]
#[Title('Get the profile')]
#[Description('Returns the owner\'s profile and bio: name, role, headline, summary, story, focus areas, availability, stats and principles.')]
#[IsReadOnly]
class GetProfileTool extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        return Response::structured(['profile' => Payload::profile(Profile::current())]);
    }
}
