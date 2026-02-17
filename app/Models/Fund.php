<?php

namespace App\Models;

use App\Models\Traits\AutoAssignSessionYear;
use App\Models\Traits\HasSessionYear;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fund extends Model
{
    use HasFactory, HasSessionYear, AutoAssignSessionYear;

    protected $fillable = [
        'fund_date',
        'component_name',
        'component_code',
        'component_type',
        'amount',
        'remark',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];
}
