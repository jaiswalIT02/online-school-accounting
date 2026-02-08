<?php

namespace App\Models;

use App\Models\Traits\AutoAssignSessionYear;
use App\Models\Traits\HasSessionYear;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReceiptPaymentAccount extends Model
{
    use HasFactory, HasSessionYear, AutoAssignSessionYear;

    protected $fillable = [
        'name',
        'header_title',
        'header_subtitle',
        'period_from',
        'period_to',
        'date',
        'description',
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
