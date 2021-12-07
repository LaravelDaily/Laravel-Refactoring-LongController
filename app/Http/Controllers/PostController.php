<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdatePostRequest;
use App\Models\Post;

class PostController extends Controller
{
    public function update(Post $post, UpdatePostRequest $request)
    {
        $post->update($request->validated());

        return redirect()->route('posts.index');
    }
}
