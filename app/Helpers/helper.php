<?php

use App\Models\SessionYear;
use App\Models\AccountType;

if (! function_exists('current_session_year_id')) {
    function current_session_year_id()
    {
        return SessionYear::where('status', 1)
            ->where('school_id', auth()->user()?->school_id)
            ->whereDate('start_date', '<=', now())
            ->whereDate('end_date', '>=', now())
            ->value('id');
    }
}

if (! function_exists('current_account_type_id')) {
    function current_account_type_id()
    {
        return AccountType::where('slug', 'type-1')
            ->value('id');
    }
}

function loadSessionYear()
{
    $today = now()->toDateString();

    $sessionYear = SessionYear::select(
        'id',
        'session_name',
        'slug',
        'start_date',
        'end_date'
    )
        ->where('school_id', auth()->user()?->school_id)
        ->where(function ($query) use ($today) {
            $query->where(function ($q) use ($today) {
                $q->where('start_date', '<=', $today)
                    ->where('end_date', '>=', $today);
            })
                ->orWhere('end_date', '<', $today);
        })
        ->orderBy('start_date', 'desc')
        ->get();

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
    )->where('school_id', auth()->user()?->school_id)->get();

    return $accountType;
}


if (!function_exists('parseDateOrNull')) {
    /**
     * Parse a date string into Carbon object if valid.
     *
     * @param string|null $dateString
     * @param string $format
     * @return Carbon|null
     */
    function parseDateOrNull(?string $dateString, string $format = 'd/m/Y'): ?Carbon
    {
        if (!$dateString) {
            return null;
        }

        try {
            $date = Carbon::createFromFormat($format, $dateString);

            // Ensure strict match (to catch invalid dates like 32/01/2024)
            return $date->format($format) === $dateString ? $date : null;

        } catch (\Exception $e) {
            return null;
        }
    }
}