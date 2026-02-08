<?php

namespace App\Models;

use App\Models\Traits\AutoAssignSessionYear;
use App\Models\Traits\HasSessionYear;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ledger extends Model
{
    use HasFactory, HasSessionYear, AutoAssignSessionYear;

    protected $fillable = [
        'name',
        'opening_balance',
        'opening_balance_type',
        'description',
    ];

    public function entries()
    {
        return $this->hasMany(LedgerEntry::class);
    }
}
