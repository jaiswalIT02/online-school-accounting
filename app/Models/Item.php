<?php

namespace App\Models;

use App\Models\Traits\HasSessionYear;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
