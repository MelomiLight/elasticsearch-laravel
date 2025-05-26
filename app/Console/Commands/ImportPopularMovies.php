<?php

namespace App\Console\Commands;

use App\Models\Genre;
use App\Models\Movie;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class ImportPopularMovies extends Command
{
    protected $signature = 'movies:import-popular';
    protected $description = 'Import popular movies from TMDb';

    /**
     * @throws \Illuminate\Http\Client\ConnectionException
     */
    public function handle(): void {
        for ($i = 1; $i <= 10; $i++) {
            $response = Http::withoutVerifying()
                ->get("https://api.themoviedb.org/3/movie/popular", [
                    'api_key' => env('TMDB_API_KEY'),
                    'page' => $i,
                ]);
            $movies = $response->json('results');

            foreach ($movies as $movie) {
                $ourMovie = Movie::query()->updateOrCreate(
                    ['tmdb_id' => $movie['id']],
                    [
                        'title' => $movie['title'],
                        'overview' => $movie['overview'],
                        'release_date' => $movie['release_date'],
                        'poster_path' => $movie['poster_path'],
                        'vote_average' => $movie['vote_average'],
                        'vote_count' => $movie['vote_count'],
                    ],
                );

                if(isset($movie['genre_ids'])){
                    foreach ($movie['genre_ids'] as $genreId) {
                        $genre = Genre::query()->where('tmdb_id', $genreId)->first();
                        if ($genre) {
                            $ourMovie->genres()->attach($genre->id);
                        }
                    }
                }
            }
        }

        $this->info('Movies imported successfully.');
    }
}
