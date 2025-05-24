<?php

namespace App\Models;

use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $id
 * @property string $title
 * @property string $content
 * @property string $last_indexed_at
 */
class Post extends Model
{
    use HasFactory;
    use Searchable;

    protected $table = 'posts';
    protected $fillable = [
        'title',
        'content',
        'last_indexed_at',
    ];

    protected $casts = [
        'last_indexed_at' => 'datetime',
    ];

    protected string $index = 'posts';

    protected static function booted(): void {
        static::deleted(static function ($model) {
            $model->deleteDocument();
        });
    }

    public function toSearchableArray(): array {
        return [
            'title' => $this->title,
            'content' => $this->content,
        ];
    }

    public function searchableFields(): array {
        return ['title^2', 'content'];
    }
}
