<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasSessionYear
{
    public function sessionYear()
    {
        return $this->belongsTo(
            \App\Models\SessionYear::class,
            'session_year_id'
        );
    }

    public function scopeBySessionSlug(Builder $query, string $slug)
    {
        return $query->whereHas('sessionYear', function ($q) use ($slug) {
            $q->where('slug', $slug);
        });
    }
}
