<?php

namespace App\Mcp\Tools\Companies;

use App\Mcp\Support\CompanyFields;
use App\Mcp\Support\Payload;
use App\Mcp\Support\Records;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;

#[Name('update_company')]
#[Title('Update a company or client')]
#[Description('Changes a company or client. Send only the fields to change.')]
#[IsIdempotent]
class UpdateCompanyTool extends Tool
{
    public function handle(Request $request): Response|ResponseFactory
    {
        $reference = $request->validate(['company' => ['required']])['company'];
        $company = Records::company($reference);

        if ($company === null) {
            return Records::notFound('company', $reference, 'list_companies');
        }

        $data = $request->validate(CompanyFields::rules($company));

        if ($data === []) {
            return Response::error('Nothing to update: send at least one field to change.');
        }

        CompanyFields::save($company, $data);

        return Response::structured([
            'message' => "Updated company \"{$company->name}\": ".implode(', ', array_keys($data)).'.',
            'company' => Payload::company($company->refresh()),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'company' => $schema->string()->description('The company\'s id or slug.')->required(),
            ...CompanyFields::schema($schema),
        ];
    }
}
