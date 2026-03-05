<?php

namespace App\Models;

use App\Models\Traits\AutoAssignSessionYear;
use App\Models\Traits\HasSessionYear;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class StockLedger extends Model
{
    use HasFactory, HasSessionYear, AutoAssignSessionYear;

    protected $fillable = [
        'ledger_name',
        'item_id',
        'date_from',
        'date_to',
        'opening_balance',
        'opening_type',
        'description',
    ];

    protected $casts = [
        'date_from' => 'date',
        'date_to' => 'date',
        'opening_balance' => 'decimal:2',
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
