<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MovieGenre extends Pivot
{
    protected $table = 'movie_genre';
    public $incrementing = true;
    protected $fillable = [
        'movie_id',
        'genre_id',
    ];

    public function movie(): BelongsTo {
        return $this->belongsTo(Movie::class, 'movie_id', 'id');
    }

    public function genre(): BelongsTo {
        return $this->belongsTo(Genre::class, 'genre_id', 'id');
    }
}
