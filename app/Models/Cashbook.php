<?php

namespace App\Models;

use App\Models\Traits\AutoAssignAccountType;
use App\Models\Traits\AutoAssignSessionYear;
use App\Models\Traits\HasAccountType;
use App\Models\Traits\HasSessionYear;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class Cashbook extends Model
{
    use HasFactory, HasSessionYear, AutoAssignSessionYear, HasAccountType, AutoAssignAccountType;

    protected $fillable = [
        'name',
        'period_month',
        'period_year',
        'opening_cash',
        'opening_bank',
        'description',
        'session_year_id',
        'account_type_id',
    ];

    public function entries()
    {
        return $this->hasMany(CashbookEntry::class);
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
