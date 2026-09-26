<?php

namespace App\Http\Controllers\Dev;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Project;
use App\Support\Templates\TemplateManager;
use App\Support\Templates\TemplateRegistry;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * /dev/templates: every page of every template side by side, in light or dark, at desktop or mobile
 * width. For people building templates; enabled only when `portfolio.templates.dev_gallery` is on
 * (the local environment by default). Pages are the real site rendered in iframes with `?_template=`.
 */
class TemplateGalleryController extends Controller
{
    public function __invoke(Request $request, TemplateRegistry $registry): View
    {
        abort_unless(config('portfolio.templates.dev_gallery') === true, 404);

        $project = Project::query()->published()->orderBy('id')->value('slug');
        $article = Article::query()->published()->orderBy('id')->value('slug');

        $pages = array_filter([
            'home' => ['Home', '/'],
            'projects' => ['Project archive', '/projects'],
            'case-study' => $project ? ['Case study', "/projects/{$project}"] : null,
            'writing' => ['Writing archive', '/writing'],
            'article' => $article ? ['Article', "/writing/{$article}"] : null,
            'books' => ['Books', '/books'],
            'uses' => ['Uses', '/uses'],
            'now' => ['Now', '/now'],
            'not-found' => ['404', '/this-page-does-not-exist'],
        ]);

        $templates = $registry->all();
        $template = $request->string('template')->toString();
        $template = $registry->has($template) ? $template : null;
        $page = $request->string('page')->toString();
        $page = array_key_exists($page, $pages) ? $page : 'home';

        return view('dev.templates', [
            'templates' => $templates,
            'pages' => $pages,
            'template' => $template,
            'page' => $page,
            'theme' => $request->query('theme') === 'dark' ? 'dark' : 'light',
            'mobile' => $request->boolean('mobile'),
            'query' => TemplateManager::GALLERY_QUERY,
            'hasContent' => $project !== null,
        ]);
    }
}
