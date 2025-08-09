<?php

namespace Database\Factories;

use App\Models\Article;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Article>
 */
class ArticleFactory extends Factory
{
    protected $model = Article::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(), // create a user if none provided
            'title' => $this->faker->sentence,
            'content' => $this->faker->paragraph,
            'is_published' => $this->faker->boolean(50),
        ];
    }
}
