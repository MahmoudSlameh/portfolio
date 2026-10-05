<?php

namespace App\Mcp\Tools\Companies;

use App\Mcp\Support\CompanyFields;
use App\Mcp\Support\Payload;
use App\Models\Company;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('create_company')]
#[Title('Create a company or client')]
#[Description('Adds an employer or client. Check list_companies first so it is not added twice. Add a logo afterwards with set_company_logo.')]
class CreateCompanyTool extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        $data = $request->validate(CompanyFields::rules());

        $company = new Company;
        CompanyFields::save($company, $data);

        return Response::structured([
            'message' => "Created company \"{$company->name}\".",
            'company' => Payload::company($company->refresh()),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        $fields = CompanyFields::schema($schema);
        $fields['name'] = $fields['name']->required();

        return $fields;
    }
}
