<?php

namespace App\Repositories\Eloquent;

use App\Models\Article;
use App\Models\User;
use App\Repositories\Contracts\ArticleRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentArticleRepository implements ArticleRepository
{
    public function paginateFor(User $user, int $perPage = 10): LengthAwarePaginator
    {
        if ($user->hasRole('admin') || $user->hasRole('editor')) {
            return Article::with('user:id,name,email')
                ->orderByDesc('id')
                ->paginate($perPage);
        }

        // author: published + own
        return Article::with('user:id,name,email')
            ->where(function ($q) use ($user) {
                $q->where('status', 'published')
                  ->orWhere('user_id', $user->id);
            })
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    public function find(int $id): ?Article
    {
        return Article::with('user:id,name,email')->find($id);
    }

    public function create(User $author, array $data): Article
    {
        return Article::create([
            'user_id' => $author->id,
            'title'   => $data['title'],
            'body'    => $data['body'] ?? null,
            'status'  => 'draft',
        ]);
    }

    public function update(Article $article, array $data): Article
    {
        $article->fill($data)->save();
        return $article;
    }

    public function publish(Article $article): Article
    {
        $article->update([
            'status'       => 'published',
            'published_at' => now(),
        ]);
        return $article;
    }

    public function delete(Article $article): void
    {
        $article->delete();
    }
}
