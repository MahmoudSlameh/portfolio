<?php

use App\Models\Article;
use Illuminate\Support\Facades\DB;

test('builder bodies are converted to documents and back', function () {
    $migration = require database_path('migrations/2026_09_27_100000_convert_article_bodies_to_documents.php');
    $article = Article::factory()->create();
    $builder = [
        ['type' => 'paragraph', 'data' => ['text' => 'Hello **there**.']],
        ['type' => 'code', 'data' => ['language' => 'php', 'filename' => 'a.php', 'code' => 'echo 1;']],
        ['type' => 'code', 'data' => ['language' => 'sql', 'filename' => null, 'code' => 'select 1;']],
        ['type' => 'callout', 'data' => ['title' => 'Note', 'text' => 'Careful.']],
        ['type' => 'image', 'data' => ['media_uuid' => 'abc', 'alt' => 'Alt', 'caption' => 'Cap']],
    ];
    DB::table('articles')->where('id', $article->id)->update(['body' => json_encode($builder)]);

    $migration->up();
    $doc = json_decode((string) DB::table('articles')->where('id', $article->id)->value('body'), true);

    expect($doc['type'])->toBe('doc')
        ->and(array_column($doc['content'], 'type'))->toBe(['paragraph', 'customBlock', 'codeBlock', 'customBlock', 'image'])
        ->and($doc['content'][4]['attrs'])->toBe(['id' => 'abc', 'alt' => 'Alt', 'title' => 'Cap']);

    $migration->up(); // already converted rows are left alone
    expect(json_decode((string) DB::table('articles')->where('id', $article->id)->value('body'), true))->toBe($doc);

    $migration->down();

    expect(json_decode((string) DB::table('articles')->where('id', $article->id)->value('body'), true))->toBe([
        ['type' => 'paragraph', 'data' => ['text' => 'Hello **there**.']],
        ['type' => 'code', 'data' => ['language' => 'php', 'filename' => 'a.php', 'code' => 'echo 1;']],
        ['type' => 'code', 'data' => ['language' => 'sql', 'code' => 'select 1;']],
        ['type' => 'callout', 'data' => ['title' => 'Note', 'text' => 'Careful.']],
        ['type' => 'image', 'data' => ['media_uuid' => 'abc', 'alt' => 'Alt', 'caption' => 'Cap']],
    ]);
});
