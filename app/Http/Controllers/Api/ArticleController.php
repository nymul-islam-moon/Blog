<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Repositories\Contracts\ArticleRepository;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function __construct(private ArticleRepository $articles)
    {
        // Repo injected via RepositoryServiceProvider
    }

    // Request name: Articles — List
    public function index(Request $request)
    {
        $perPage = $request->integer('per_page', 10);
        $data = $this->articles->paginateFor($request->user(), $perPage);
        return response()->json($data);
    }

    // Request name: Articles — Show
    public function show(Request $request, Article $article)
    {
        $this->authorize('view', $article);
        $found = $this->articles->find($article->id);
        if (!$found) {
            abort(404);
        }
        return response()->json($found);
    }

    // Request name: Articles — Create
    public function store(Request $request)
    {
        $this->authorize('create', Article::class);

        $data = $request->validate([
            'title' => ['required','string','max:255'],
            'body'  => ['nullable','string'],
        ]);

        $created = $this->articles->create($request->user(), $data);

        return response()->json($created, 201);
    }

    // Request name: Articles — Update
    public function update(Request $request, Article $article)
    {
        $this->authorize('update', $article);

        $data = $request->validate([
            'title' => ['sometimes','required','string','max:255'],
            'body'  => ['nullable','string'],
        ]);

        $updated = $this->articles->update($article, $data);

        return response()->json($updated);
    }

    // Request name: Articles — Publish
    public function publish(Request $request, Article $article)
    {
        $this->authorize('publish', $article);

        $updated = $this->articles->publish($article);

        return response()->json([
            'message' => 'Article published',
            'article' => $updated,
        ]);
    }

    // Request name: Articles — Delete
    public function destroy(Request $request, Article $article)
    {
        $this->authorize('delete', $article);

        $this->articles->delete($article);

        return response()->json(['message' => 'Article deleted']);
    }
}
