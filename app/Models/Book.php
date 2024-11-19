<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Book extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'author_id',
        'title',
        'description',
        'publish_date',
    ];

    public function Author(): BelongsTo
    {
        return $this->belongsTo(Author::class, 'author_id');
    }
}
