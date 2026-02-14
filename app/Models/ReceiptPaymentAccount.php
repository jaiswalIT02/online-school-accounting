<?php

namespace App\Models;

use App\Models\Traits\AutoAssignAccountType;
use App\Models\Traits\AutoAssignSessionYear;
use App\Models\Traits\HasAccountType;
use App\Models\Traits\HasSessionYear;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReceiptPaymentAccount extends Model
{
    use HasFactory, HasSessionYear, AutoAssignSessionYear, HasAccountType, AutoAssignAccountType;

    protected $fillable = [
        'name',
        'header_title',
        'header_subtitle',
        'period_from',
        'period_to',
        'date',
        'description',
        'session_year_id',
        'account_type_id',
    ];

    protected $casts = [
        'period_from' => 'date',
        'period_to' => 'date',
        'date' => 'date',
    ];

    public function entries()
    {
        return $this->hasMany(ReceiptPaymentEntry::class);
    }
}
