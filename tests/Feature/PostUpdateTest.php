<?php

namespace Tests\Feature;

use App\Mail\PostUpdated;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PostUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_post_update_successful()
    {
        Mail::fake();

        $post = Post::factory()->create();
        $author1 = User::factory()->create();
        $author2 = User::factory()->create();
        $post->authors()->attach([$author1->id, $author2->id]);
        $admin = User::factory()->create(['is_admin' => 1]);

        $updatedPost = [
            'title' => 'New title',
            'post_text' => 'New text',
        ];

        $response = $this->actingAs($admin)->put('/posts/' . $post->id, $updatedPost);

        // Assert that the update was successful with the redirect
        $response->assertRedirect('posts');

        // Was the data actually updated?
        $this->assertDatabaseHas('posts', $updatedPost + [
            'id' => $post->id,
        ]);

        // Was the words count for authors successfully updated?
        $this->assertDatabaseHas('users', [
            'id' => $author1->id,
            'words_count' => str_word_count( $updatedPost['post_text'])
        ]);
        $this->assertDatabaseHas('users', [
            'id' => $author2->id,
            'words_count' => str_word_count( $updatedPost['post_text'])
        ]);

        // Was the email successfully sent to admin?
        Mail::assertSent(PostUpdated::class, function($mail) use ($admin) {
            return $mail->hasTo($admin->email);
        });
    }
}
