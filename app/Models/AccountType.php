<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountType extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'status',
    ];

    public function scopeBySlug($query, string $slug)
    {
        return $query->where('slug', $slug);
    }
}
