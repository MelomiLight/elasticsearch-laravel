<?php

namespace App\Console\Commands;

use App\Models\Genre;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class ImportGenres extends Command
{
    protected $signature = 'genres:import';
    protected $description = 'Import genres from TMDb';

    /**
     * @throws \Illuminate\Http\Client\ConnectionException
     */
    public function handle(): void {
        $response = Http::withoutVerifying()
            ->get("https://api.themoviedb.org/3/genre/movie/list", [
                'api_key' => env('TMDB_API_KEY'),
            ]);
        $genres = $response->json('genres');
        foreach ($genres as $genre) {
            Genre::query()->updateOrCreate([
                'tmdb_id' => $genre['id'],
            ], [
                'name' => $genre['name'],
            ]);
        }

        $this->info('Genres imported successfully.');
    }
}
