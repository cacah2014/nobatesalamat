<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\View\View;

/**
 * نمایش عمومی مقالات سایت.
 */
class ArticleController extends Controller
{
    public function index(): View
    {
        return view('articles.index', [
            'articles' => Article::where('is_published', true)->latest()->paginate(9),
        ]);
    }

    public function show(Article $article): View
    {
        abort_unless($article->is_published, 404);

        return view('articles.show', [
            'article' => $article,
            'related' => Article::where('is_published', true)
                ->whereKeyNot($article->getKey())
                ->latest()
                ->limit(3)
                ->get(),
        ]);
    }
}
