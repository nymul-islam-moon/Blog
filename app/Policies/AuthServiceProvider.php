<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Article;
use App\Policies\ArticlePolicy;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Article::class => ArticlePolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        // Gate::define('isAdmin', fn($user) => $user->role === 'admin');
        Gate::define('isAdmin', function ($user) {
            \Log::info('Checking isAdmin gate for user id: ' . $user->id . ', role: ' . $user->role);
            return $user->role === 'admin';
        });

        Gate::define('isEditor', fn($user) => $user->role === 'editor');
        Gate::define('isAuthor', fn($user) => $user->role === 'author');
    }
}
