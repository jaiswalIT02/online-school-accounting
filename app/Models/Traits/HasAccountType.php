<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasAccountType
{
    public function accountType()
    {
        return $this->belongsTo(
            \App\Models\AccountType::class,
            'account_type_id'
        );
    }

    public function scopeByAccountType(Builder $query, string $slug)
    {
        return $query->whereHas('accountType', function ($q) use ($slug) {
            $q->where('slug', $slug);
        });
    }
}
