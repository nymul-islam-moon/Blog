<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticleFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_list_published_articles()
    {
        $user = User::factory()->create();
        Article::factory()->create(['is_published' => true]);
        Article::factory()->create(['is_published' => false]);

        $response = $this->actingAs($user)->getJson('/api/articles');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json());
    }

    public function test_list_mine_articles()
    {
        $user = User::factory()->create();
        Article::factory()->create(['user_id' => $user->id]);
        Article::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/articles/mine');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json());
    }

    public function test_author_can_create_article()
    {
        $author = User::factory()->create(['role' => 'author']);

        $response = $this->actingAs($author)->postJson('/api/articles', [
            'title' => 'New Article',
            'content' => 'Article content here',
        ]);

        $response->assertStatus(201)
            ->assertJsonFragment(['title' => 'New Article']);
    }

    public function test_editor_cannot_create_article()
    {
        $editor = User::factory()->create(['role' => 'editor']);

        $response = $this->actingAs($editor)->postJson('/api/articles', [
            'title' => 'New Article',
            'content' => 'Article content here',
        ]);

        $response->assertStatus(403);
    }

    public function test_author_can_update_own_article()
    {
        $author = User::factory()->create(['role' => 'author']);
        $article = Article::factory()->create(['user_id' => $author->id]);

        $response = $this->actingAs($author)->putJson("/api/articles/{$article->id}", [
            'title' => 'Updated Title',
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['title' => 'Updated Title']);
    }

    public function test_author_cannot_update_others_article()
    {
        $author = User::factory()->create(['role' => 'author']);
        $otherArticle = Article::factory()->create();

        $response = $this->actingAs($author)->putJson("/api/articles/{$otherArticle->id}", [
            'title' => 'Updated Title',
        ]);

        $response->assertStatus(403);
    }

    public function test_admin_can_update_any_article()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $article = Article::factory()->create();

        $response = $this->actingAs($admin)->putJson("/api/articles/{$article->id}", [
            'title' => 'Admin Updated Title',
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['title' => 'Admin Updated Title']);
    }

    public function test_author_can_delete_own_article()
    {
        $author = User::factory()->create(['role' => 'author']);
        $article = Article::factory()->create(['user_id' => $author->id]);

        $response = $this->actingAs($author)->deleteJson("/api/articles/{$article->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Deleted']);
    }

    public function test_author_cannot_delete_others_article()
    {
        $author = User::factory()->create(['role' => 'author']);
        $otherArticle = Article::factory()->create();

        $response = $this->actingAs($author)->deleteJson("/api/articles/{$otherArticle->id}");

        $response->assertStatus(403);
    }

    public function test_admin_can_delete_any_article()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $article = Article::factory()->create();

        $response = $this->actingAs($admin)->deleteJson("/api/articles/{$article->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Deleted']);
    }

    public function test_editor_can_publish_article()
    {
        $editor = User::factory()->create(['role' => 'editor']);
        $article = Article::factory()->create(['is_published' => false]);

        $response = $this->actingAs($editor)->patchJson("/api/articles/{$article->id}/publish");

        $response->assertStatus(200)
            ->assertJsonPath('is_published', true);
    }

    public function test_author_cannot_publish_article()
    {
        $author = User::factory()->create(['role' => 'author']);
        $article = Article::factory()->create(['is_published' => false]);

        $response = $this->actingAs($author)->patchJson("/api/articles/{$article->id}/publish");

        $response->assertStatus(403);
    }

    public function test_unauthenticated_cannot_access_articles()
    {
        $response = $this->getJson('/api/articles');

        $response->assertStatus(401);
    }
}
