<?php

use App\Support\Content\ArticleDocument;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * P11-01: article bodies move from Filament Builder blocks (`[{type, data}]`) to rich editor
 * documents (`{"type":"doc", ...}`). Rows already in the target format are left alone.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->convert(fn (mixed $body): ?array => is_array($body) && array_is_list($body)
            ? ArticleDocument::fromBuilder($body)
            : null);
    }

    public function down(): void
    {
        $this->convert(fn (mixed $body): ?array => is_array($body) && ($body['type'] ?? null) === 'doc'
            ? ArticleDocument::toBuilder($body)
            : null);
    }

    /**
     * @param  Closure(mixed): (array<array-key, mixed>|null)  $convert
     */
    private function convert(Closure $convert): void
    {
        DB::table('articles')->select(['id', 'body'])->orderBy('id')->each(function (object $row) use ($convert): void {
            $converted = $convert(json_decode((string) $row->body, true));

            if ($converted !== null) {
                DB::table('articles')->where('id', $row->id)->update([
                    'body' => json_encode($converted, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                ]);
            }
        });
    }
};
