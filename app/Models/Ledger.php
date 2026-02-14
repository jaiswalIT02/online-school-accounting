<?php

namespace App\Models;

use App\Models\Traits\AutoAssignAccountType;
use App\Models\Traits\AutoAssignSessionYear;
use App\Models\Traits\HasAccountType;
use App\Models\Traits\HasSessionYear;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ledger extends Model
{
    use HasFactory, HasSessionYear, AutoAssignSessionYear, HasAccountType, AutoAssignAccountType;

    protected $fillable = [
        'name',
        'opening_balance',
        'opening_balance_type',
        'description',
        'session_year_id',
        'account_type_id',
    ];

    public function entries()
    {
        return $this->hasMany(LedgerEntry::class);
    }
}
