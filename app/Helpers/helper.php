<?php

use App\Models\SessionYear;

if (! function_exists('current_session_year_id')) {
    function current_session_year_id()
    {
        return SessionYear::where('status', 1)
            ->whereDate('start_date', '<=', now())
            ->whereDate('end_date', '>=', now())
            ->value('id'); // returns null or id
    }
}
