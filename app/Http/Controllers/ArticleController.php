<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Article;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;
use Exception;

class ArticleController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        try {
            $articles = Article::where('is_published', true)->get();
            return response()->json($articles);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch published articles',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function mine()
    {
        try {
            $userId = auth()->id();
            if (!$userId) {
                return response()->json(['error' => 'Unauthenticated'], 401);
            }
            $articles = Article::where('user_id', $userId)->get();
            return response()->json($articles);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch your articles',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $this->authorize('create', Article::class);

            $data = $request->validate([
                'title' => 'required|string|max:255',
                'content' => 'required|string',
            ]);

            $data['user_id'] = auth()->id();

            $article = Article::create($data);

            return response()->json($article, 201);
        } catch (AuthorizationException $e) {
            return response()->json(['error' => 'Unauthorized', 'message' => $e->getMessage()], 403);
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation failed', 'messages' => $e->errors()], 422);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to create article', 'message' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, Article $article)
    {
        try {
            $this->authorize('update', $article);

            $data = $request->validate([
                'title' => 'string',
                'content' => 'string',
            ]);

            $article->update($data);

            return response()->json($article);
        } catch (AuthorizationException $e) {
            return response()->json(['error' => 'Unauthorized', 'message' => $e->getMessage()], 403);
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation failed', 'messages' => $e->errors()], 422);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to update article', 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy(Article $article)
    {
        try {
            $this->authorize('delete', $article);

            $article->delete();

            return response()->json(['message' => 'Deleted']);
        } catch (AuthorizationException $e) {
            return response()->json(['error' => 'Unauthorized', 'message' => $e->getMessage()], 403);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to delete article', 'message' => $e->getMessage()], 500);
        }
    }

    public function publish(Article $article)
    {
        try {
            $this->authorize('publish', $article);

            $article->is_published = true;
            $article->save();

            return response()->json($article);
        } catch (AuthorizationException $e) {
            return response()->json(['error' => 'Unauthorized', 'message' => $e->getMessage()], 403);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to publish article', 'message' => $e->getMessage()], 500);
        }
    }
}
