<?php

namespace App\Mcp\Tools\Companies;

use App\Mcp\Support\Records;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsDestructive;

#[Name('delete_company')]
#[Title('Delete a company or client')]
#[Description('Deletes a company or client for good, with its logos. Experiences, projects and testimonials that referenced it are kept but lose the link. Only when the owner asked for it.')]
#[IsDestructive]
class DeleteCompanyTool extends Tool
{
    public function handle(Request $request): Response|ResponseFactory
    {
        $reference = $request->validate(['company' => ['required']])['company'];
        $company = Records::company($reference);

        if ($company === null) {
            return Records::notFound('company', $reference, 'list_companies');
        }

        $company->delete();

        return Response::structured(['message' => "Deleted company \"{$company->name}\"."]);
    }

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'company' => $schema->string()->description('The company\'s id or slug.')->required(),
        ];
    }
}
