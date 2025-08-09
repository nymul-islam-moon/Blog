<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Article;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ArticleController extends Controller
{
    use AuthorizesRequests;

    public function __construct()
    {
        // $this->authorizeResource(Article::class, 'article');
    }

    public function index()
    {
        return response()->json(Article::where('is_published', true)->get());
    }

    public function mine()
    {
        return response()->json(Article::where('user_id', auth()->id())->get());
    }

    public function store(Request $request)
    {
        // $this->authorize('create', Article::class);

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $data['user_id'] = auth()->id();

        $article = Article::create($data);

        return response()->json($article, 201);
    }


    public function update(Request $request, Article $article)
    {
        $this->authorize('update', $article);

        $data = $request->validate([
            'title' => 'string',
            'content' => 'string',
        ]);

        $article->update($data);
        return response()->json($article);
    }

    public function destroy(Article $article)
    {
        $this->authorize('delete', $article);
        $article->delete();

        return response()->json(['message' => 'Deleted']);
    }

    public function publish(Article $article)
    {
        $this->authorize('publish', $article);
        $article->is_published = true;
        $article->save();

        return response()->json($article);
    }
}
