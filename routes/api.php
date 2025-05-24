<?php

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', static function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/search', static function (Request $request) {
    $query = $request->get('query');
    $size = $request->get('size', 10);

    $posts = (new Post())->search($query, $size);
    return response()->json([
        'posts' => $posts
    ]);
});

Route::post('/posts/randomize', function () {
    $createCount = random_int(0, 1000);
    $created = Post::factory()->count($createCount)->create();

    $updateCount = random_int(0, 1000);
    $existing = Post::query()->inRandomOrder()->limit($updateCount)->get();

    foreach ($existing as $post) {
        $post->update([
            'title' => fake('ru')->sentence(),
            'content' => fake('ru')->paragraph(),
        ]);
    }

    return response()->json([
        'created_count' => $createCount,
        'updated_count' => $existing->count(),
    ]);
});
