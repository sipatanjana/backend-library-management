<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Author extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'bio',
        'birth_date',
    ];

    public function Books(): HasMany
    {
        return $this->hasMany(Book::class, 'author_id');
    }
}
