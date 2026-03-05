<?php

namespace App\Models;

use App\Models\Traits\HasSessionYear;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class Item extends Model
{
    use HasFactory, HasSessionYear;

    protected $fillable = [
        'name',
        'item_code',
        'description',
        'unit',
        'status',
    ];

    public function stocks()
    {
        return $this->hasMany(Stock::class)->orderBy('date');
    }

    public function stockLedgers()
    {
        return $this->hasMany(StockLedger::class)->orderByDesc('date_from');
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
