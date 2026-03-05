<?php

namespace App\Models;

use App\Models\Traits\AutoAssignSessionYear;
use App\Models\Traits\HasSessionYear;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class Stock extends Model
{
    use HasFactory, HasSessionYear, AutoAssignSessionYear;

    protected $fillable = [
        'date',
        'item_id',
        'number',
        'stock_type',
        'stock_amount',
        'stock_unit',
        'stock_balance',
    ];

    protected $casts = [
        'date' => 'date',
        'number' => 'decimal:2',
        'stock_amount' => 'decimal:2',
        'stock_balance' => 'decimal:2',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

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
