<?php

use App\Models\Article;

class ArticleRepository implements ArticleRepositoryInterface
{
    public function all()
    {
        return Article::where('is_published', true)->get();
    }

    public function mine($userId)
    {
        return Article::where('user_id', $userId)->get();
    }

    public function store(array $data)
    {
        return Article::create($data);
    }

    public function update($id, array $data)
    {
        $article = Article::findOrFail($id);
        $article->update($data);
        return $article;
    }

    public function delete($id)
    {
        return Article::destroy($id);
    }

    public function publish($id)
    {
        $article = Article::findOrFail($id);
        $article->is_published = true;
        $article->save();
        return $article;
    }
}
