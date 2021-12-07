<?php

namespace App\Observers;

use App\Mail\PostUpdated;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class PostObserver
{
    public function updated(Post $post)
    {
        foreach ($post->authors as $author) {
            $words_count = 0;
            foreach ($author->posts as $post) {
                $words_count += str_word_count($post->post_text);
            }
            $author->update(['words_count' => $words_count]);
        }

        $admins = User::where('is_admin', 1)->get();
        foreach ($admins as $admin) {
            Mail::to($admin)->send(new PostUpdated($post));
        }
    }

}
