<?php

namespace App\Http\Controllers;

use App\Mail\PostUpdated;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    public function update($post_id, Request $request)
    {
        $post = Post::find($post_id);
        if (!$post) {
            abort(404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'post_text' => 'required'
        ]);
        if ($validator->fails()) {
            return back()->withInput()->withErrors($validator);
        }

        $post->update([
            'title' => $request->title,
            'post_text' => $request->post_text
        ]);

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

        return redirect()->route('posts.index');
    }
}
