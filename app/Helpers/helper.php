<?php

use App\Models\SessionYear;
use App\Models\AccountType;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat\Wizard\Accounting;

if (! function_exists('current_session_year_id')) {
    function current_session_year_id()
    {
        return SessionYear::where('status', 1)
            ->whereDate('start_date', '<=', now())
            ->whereDate('end_date', '>=', now())
            ->value('id'); // returns null or id
    }
}

function loadSessionYear()
{
    $sessionYear = SessionYear::select(
        'id',
        'session_name',
        'slug',
        'start_date',
        'end_date'
    )->get();
    return $sessionYear;
}

function loadAccountType()
{
    $accountType = AccountType::select(
        'id',
        'name',
        'slug',
        'description',
        'status'
    )->get();
    return $accountType;
}
