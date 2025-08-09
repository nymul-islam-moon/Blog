<?php

namespace App\Policies;

use App\Models\Article;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ArticlePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Article $article): bool
    {
        return $article->is_published || $user->id === $article->user_id || $user->role === 'admin';
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['author', 'admin']);
    }

    public function update(User $user, Article $article): bool
    {
        return $user->id === $article->user_id || $user->role === 'admin';
    }

    public function delete(User $user, Article $article): bool
    {
        return $user->id === $article->user_id || $user->role === 'admin';
    }

    public function restore(User $user, Article $article): bool
    {
        return false;
    }

    public function forceDelete(User $user, Article $article): bool
    {
        return false;
    }

    public function publish(User $user, Article $article): bool
    {
        return in_array($user->role, ['admin', 'editor']);
    }
}
