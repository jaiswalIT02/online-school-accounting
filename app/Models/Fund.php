<?php

namespace App\Models;

use App\Models\Traits\AutoAssignSessionYear;
use App\Models\Traits\HasSessionYear;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class Fund extends Model
{
    use HasFactory, HasSessionYear, AutoAssignSessionYear;

    protected $fillable = [
        'fund_date',
        'component_name',
        'component_code',
        'component_type',
        'amount',
        'remark',
        'session_year_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    protected static function booted()
    {
        static::addGlobalScope('school', function (Builder $builder) {
            if (Auth::check()) {
                $builder->where('school_id', Auth::user()->school_id);
            }
        });

        static::creating(function ($model) {
            if (Auth::check()) {
                $model->school_id = Auth::user()->school_id;
            }
        });
    }
}
