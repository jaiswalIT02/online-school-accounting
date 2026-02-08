<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SessionYear extends Model
{
    protected $fillable = [
        'session_name',
        'slug',
        'start_date',
        'end_date',
        'status'
    ];

    public function scopeBySlug($query, string $slug)
    {
        return $query->where('slug', $slug);
    }
}
