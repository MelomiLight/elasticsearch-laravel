<?php

namespace App\Models;

use App\Traits\ESearchable;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int $id
 * @property int $tmdb_id
 * @property string $title
 * @property string $overview
 * @property string $release_date
 * @property string $poster_path
 * @property float $vote_average
 * @property float $vote_count
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Database\Eloquent\Collection|\App\Models\Genre[] $genres
 */
class Movie extends Model
{
    use ESearchable;

    protected $table = 'movies';

    protected $fillable = [
        'tmdb_id',
        'title',
        'overview',
        'release_date',
        'poster_path',
        'vote_average',
        'vote_count',
    ];

    protected $casts = [
        'release_date' => 'date',
    ];

    protected static array $esSearchableFields = [
        'title' => 3,
        'overview' => 1,
    ];
    protected static array $esSearchableRelations = [
        'genres' => [
            'name' => 2,
        ],
    ];

    public function toEsDocument(): array {
        return [
            'title' => $this->title,
            'overview' => $this->overview,
            'genres' => $this->genres->map(function ($genre) {
                return ['name' => $genre->name];
            })->all(),
        ];
    }

    public static function getMoviesFromSearch(array $ids): Collection {
        return self::with('genres')
            ->whereIn('id', $ids)
            ->get()
            ->sortBy(fn($model) => array_search($model->getKey(), $ids, true))
            ->values();
    }

    public function genres(): BelongsToMany {
        return $this
            ->belongsToMany(
                Genre::class,
                'movie_genre',
                'movie_id',
                'genre_id',
            )->using(MovieGenre::class)
            ->withTimestamps();
    }
}
