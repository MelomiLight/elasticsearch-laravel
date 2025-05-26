<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int $id
 * @property int $tmdb_id
 * @property string $name
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class Genre extends Model
{
    protected $table = 'genres';
    protected $fillable = [
        'tmdb_id',
        'name',
    ];

    public function movies(): BelongsToMany {
        return $this
            ->belongsToMany(
                Movie::class,
                'movie_genre',
                'genre_id',
                'movie_id',
            )->using(MovieGenre::class)
            ->withTimestamps();
    }
}
