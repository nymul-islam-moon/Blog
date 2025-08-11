<?php

namespace App\Repositories\Contracts;

use App\Models\Article;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ArticleRepository
{
    public function paginateFor(User $user, int $perPage = 10): LengthAwarePaginator;

    public function find(int $id): ?Article;

    public function create(User $author, array $data): Article;

    public function update(Article $article, array $data): Article;

    public function publish(Article $article): Article;

    public function delete(Article $article): void;
}
