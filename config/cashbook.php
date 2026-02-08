<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cashbook print – rows per A4 page
    |--------------------------------------------------------------------------
    |
    | Number of data rows per printed page. Tuned so one logical page fits
    | one A4 landscape sheet (with current print CSS: 55px row height,
    | 10mm margins). If your printer or margins differ, change this value.
    | Lower = fewer rows per page (more pages); higher = more rows per page.
    |
    */

    'print_rows_per_page' => (int) env('CASHBOOK_PRINT_ROWS_PER_PAGE', 6),

];
