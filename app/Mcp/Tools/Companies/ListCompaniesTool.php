<?php

namespace App\Mcp\Tools\Companies;

use App\Enums\CompanyKind;
use App\Mcp\Support\Payload;
use App\Models\Company;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Name('list_companies')]
#[Title('List companies & clients')]
#[Description('Lists the employers and clients (used by experiences, projects and testimonials, and shown on the clients wall).')]
#[IsReadOnly]
class ListCompaniesTool extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        $data = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'kind' => ['nullable', Rule::enum(CompanyKind::class)],
        ]);

        $companies = Company::query()
            ->with('media')
            ->withCount(['experiences', 'projects'])
            ->when($data['search'] ?? null, fn (Builder $query, string $search) => $query->where('name', 'like', "%{$search}%"))
            ->when($data['kind'] ?? null, fn (Builder $query, string $kind) => $query->where('kind', $kind))
            ->ordered()
            ->get();

        return Response::structured([
            'companies' => $companies->map(fn (Company $company): array => [
                ...Payload::company($company),
                'experiences' => (int) $company->getAttribute('experiences_count'),
                'projects' => (int) $company->getAttribute('projects_count'),
            ])->values()->all(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'search' => $schema->string()->description('Only companies whose name contains this text.'),
            'kind' => $schema->string()->enum(CompanyKind::class),
        ];
    }
}
