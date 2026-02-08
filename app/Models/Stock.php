<?php

namespace App\Models;

use App\Models\Traits\AutoAssignSessionYear;
use App\Models\Traits\HasSessionYear;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
