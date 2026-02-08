<?php

namespace App\Models;

use App\Models\Traits\AutoAssignSessionYear;
use App\Models\Traits\HasSessionYear;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cashbook extends Model
{
    use HasFactory, HasSessionYear, AutoAssignSessionYear;

    protected $fillable = [
        'name',
        'period_month',
        'period_year',
        'opening_cash',
        'opening_bank',
        'description',
    ];

    public function entries()
    {
        return $this->hasMany(CashbookEntry::class);
    }
}
