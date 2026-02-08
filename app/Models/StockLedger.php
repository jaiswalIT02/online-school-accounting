<?php

namespace App\Models;

use App\Models\Traits\AutoAssignSessionYear;
use App\Models\Traits\HasSessionYear;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
