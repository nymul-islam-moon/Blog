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

        Gate::define('view-users', fn($user) => /*$user->isAdmin()*/ true);
        Gate::define('assign-roles', fn($user) => $user->isAdmin());
        Gate::define('publish-article', fn($user) => $user->isAdmin() || $user->isEditor());
        Gate::define('isAdmin', fn($user) => true);
    }
}
