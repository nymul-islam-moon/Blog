<?php

namespace App\Policies;

use App\Models\Article;
use App\Models\User;

class ArticlePolicy
{
    /**
     * Admins bypass everything.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }
        return null;
    }

    public function viewAny(User $user): bool
    {
        // editors can list all; authors will be limited in controller (own only)
        return $user->hasRole('editor') || $user->hasRole('author');
    }

    public function view(User $user, Article $article): bool
    {
        // anyone authenticated can view published; editors can view any; authors can view own
        if ($article->status === 'published') {
            return true;
        }
        if ($user->hasRole('editor')) {
            return true;
        }
        return $user->id === $article->user_id;
    }

    public function create(User $user): bool
    {
        // authors and editors can create
        return $user->hasRole('author') || $user->hasRole('editor');
    }

    public function update(User $user, Article $article): bool
    {
        // editors can update any; authors can update their own drafts
        if ($user->hasRole('editor')) {
            return true;
        }
        return $user->id === $article->user_id && $article->status === 'draft';
    }

    public function publish(User $user, Article $article): bool
    {
        // only editors (and admins via before) can publish
        return $user->hasRole('editor');
    }

    public function delete(User $user, Article $article): bool
    {
        // editors can delete any; authors can delete their own drafts
        if ($user->hasRole('editor')) {
            return true;
        }
        return $user->id === $article->user_id && $article->status === 'draft';
    }

    public function restore(User $user, Article $article): bool
    {
        return $user->hasRole('editor');
    }

    public function forceDelete(User $user, Article $article): bool
    {
        return $user->hasRole('editor');
    }
}
