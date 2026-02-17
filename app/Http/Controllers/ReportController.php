<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\ReceiptPaymentEntry;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Validate and normalize a date string. Accepts dd-mm-yyyy or dd/mm/yyyy.
     * Returns normalized dd/mm/yyyy string or null if invalid.
     */
    private function normalizeDateInput(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }
        $value = trim($value);
        // Must match dd-mm-yyyy or dd/mm/yyyy (e.g. 01-12-2025 or 01/12/2025)
        if (!preg_match('/^(\d{2})[\/\-](\d{2})[\/\-](\d{4})$/', $value, $matches)) {
            return null;
        }
        $day = (int) $matches[1];
        $month = (int) $matches[2];
        $year = (int) $matches[3];
        try {
            $date = Carbon::create($year, $month, $day);
            return $date->format('d/m/Y');
        } catch (\Exception $e) {
            return null;
        }
    }

    public function index(Request $request)
    {
        $session_filter = $request->get('session_id');
        $month = $request->input('month');
        $dateFromRaw = $request->input('date_from');
        $dateToRaw = $request->input('date_to');
        $articleId = $request->input('article_id');

        // Validate and normalize date inputs - only accept dd-mm-yyyy or dd/mm/yyyy
        $dateFrom = $this->normalizeDateInput($dateFromRaw);
        $dateTo = $this->normalizeDateInput($dateToRaw);

        // Derive year from month or date filters if needed
        $year = date('Y');
        if ($dateFrom) {
            try {
                // $dateFrom is dd/mm/yyyy
                $fromDate = Carbon::createFromFormat('d/m/Y', $dateFrom);
                $year = $fromDate->year;
            } catch (\Exception $e) {
                $year = date('Y');
            }
        } elseif ($month) {
            // For month filter, try to detect year from existing data
            // Check if there's data for this month in previous years
            $monthNum = (int) $month;
            $detectedYear = $this->detectYearForMonth($monthNum);
            $year = $detectedYear ?: date('Y');
        }

        // Get all articles for dropdown
        $articles = Article::orderBy('name')->get();

        // Tax Totals (P Tax and TDS)
        $taxTotals = $this->getTaxTotals($year, $month, $dateFrom, $dateTo, $session_filter);

        // Payment Totals by Article
        $paymentTotals = $this->getPaymentTotals($articleId, $year, $month, $dateFrom, $dateTo);

        return view('report', compact('month', 'dateFrom', 'dateTo', 'articleId', 'articles', 'taxTotals', 'paymentTotals', 'year'));
    }

    /**
     * Detect the most recent year that has data for a given month
     */
    private function detectYearForMonth($month)
    {
        // Check for entries with date column matching the month
        $entries = ReceiptPaymentEntry::whereNotNull('date')
            ->whereRaw("MONTH(STR_TO_DATE(date, '%d/%m/%Y')) = ?", [$month])
            ->get();

        if ($entries->isEmpty()) {
            return null;
        }

        $years = [];
        foreach ($entries as $entry) {
            try {
                if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $entry->date, $matches)) {
                    $years[] = (int) $matches[3];
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        if (empty($years)) {
            return null;
        }

        // Return the most recent year
        return max($years);
    }

    /**
     * Get tax totals (P Tax and TDS) with date/month filters
     */
    private function getTaxTotals($year, $month = null, $dateFrom = null, $dateTo = null, $session_filter)
    {
        $query = ReceiptPaymentEntry::where('type', 'payment')
            ->where('session_year_id', $session_filter)
            ->whereNotNull('tax_amount')
            ->where('tax_amount', '>', 0)
            ->whereNotNull('tax_for');

        // Apply date filters
        $this->applyDateFilters($query, $year, $month, $dateFrom, $dateTo);

        $entries = $query->get();

        $totalPTax = 0;
        $totalTDS = 0;

        foreach ($entries as $entry) {
            // Use date column if available, otherwise extract from remarks or use created_at
            $entryDate = $this->getEntryDate($entry);

            // Apply date filters to entry date
            if ($this->matchesDateFilters($entryDate, $year, $month, $dateFrom, $dateTo)) {
                if ($entry->tax_for === 'pTax') {
                    $totalPTax += $entry->tax_amount;
                } elseif ($entry->tax_for === 'tds') {
                    $totalTDS += $entry->tax_amount;
                }
            }
        }

        return [
            'pTax' => $totalPTax,
            'tds' => $totalTDS,
            'total' => $totalPTax + $totalTDS,
        ];
    }

    /**
     * Get payment totals by article with date/month filters
     */
    private function getPaymentTotals($articleId, $year, $month = null, $dateFrom = null, $dateTo = null)
    {
        $query = ReceiptPaymentEntry::with('article')
            ->where('type', 'payment');

        if ($articleId) {
            $query->where('article_id', $articleId);
        }

        // Apply date filters
        $this->applyDateFilters($query, $year, $month, $dateFrom, $dateTo);

        $entries = $query->get();

        $totals = [];

        foreach ($entries as $entry) {
            // Use date column if available, otherwise extract from remarks or use created_at
            $entryDate = $this->getEntryDate($entry);

            // Apply date filters to entry date
            if ($this->matchesDateFilters($entryDate, $year, $month, $dateFrom, $dateTo) && $entry->article_id) {
                $entryArticleId = $entry->article_id;

                if (!isset($totals[$entryArticleId])) {
                    $article = $entry->article;
                    $totals[$entryArticleId] = [
                        'article_id' => $entryArticleId,
                        'article_name' => $article ? $article->name : 'Unknown',
                        'article_acode' => $article ? $article->acode : '-',
                        'total_amount' => 0,
                        'count' => 0,
                    ];
                }

                $totals[$entryArticleId]['total_amount'] += $entry->amount;
                $totals[$entryArticleId]['count']++;
            }
        }

        // Sort by total amount descending
        usort($totals, function ($a, $b) {
            return $b['total_amount'] <=> $a['total_amount'];
        });

        return $totals;
    }

    /**
     * Get entry date from date column, remarks, or created_at
     */
    private function getEntryDate($entry)
    {
        // First try date column (dd/mm/yyyy format)
        if (!empty($entry->date)) {
            try {
                // Parse dd/mm/yyyy format
                if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $entry->date, $matches)) {
                    $day = (int) $matches[1];
                    $month = (int) $matches[2];
                    $year = (int) $matches[3];
                    return Carbon::create($year, $month, $day);
                }
            } catch (\Exception $e) {
                // If parsing fails, continue to next method
            }
        }

        // Fallback to extracting from remarks
        $ppaDate = $this->extractPpaDate($entry->remarks);
        if ($ppaDate) {
            return $ppaDate;
        }

        // Final fallback to created_at
        return $entry->created_at;
    }

    /**
     * Apply date filters to query (preliminary filter for performance)
     */
    private function applyDateFilters($query, $year, $month = null, $dateFrom = null, $dateTo = null)
    {
        // If specific date range is provided, use it (takes priority)
        if ($dateFrom && $dateTo) {
            // Normalize date formats - accept both dd-mm-yyyy and dd/mm/yyyy
            $dateFrom = str_replace('/', '-', $dateFrom);
            $dateTo = str_replace('/', '-', $dateTo);

            // Parse dd-mm-yyyy format
            try {
                $fromDate = Carbon::createFromFormat('d-m-Y', $dateFrom);
                $toDate = Carbon::createFromFormat('d-m-Y', $dateTo);

                // Filter by date column (dd/mm/yyyy) or created_at
                $query->where(function ($q) use ($fromDate, $toDate) {
                    $q->where(function ($subQ) use ($fromDate, $toDate) {
                        // Match date column (dd/mm/yyyy format)
                        $subQ->whereNotNull('date')
                            ->whereRaw("DATE(STR_TO_DATE(date, '%d/%m/%Y')) >= DATE(?)", [$fromDate->format('Y-m-d')])
                            ->whereRaw("DATE(STR_TO_DATE(date, '%d/%m/%Y')) <= DATE(?)", [$toDate->format('Y-m-d')]);
                    })->orWhere(function ($subQ) use ($fromDate, $toDate) {
                        // Fallback to created_at if date column is null
                        $subQ->whereNull('date')
                            ->whereBetween('created_at', [$fromDate->startOfDay(), $toDate->endOfDay()]);
                    });
                });
            } catch (\Exception $e) {
                // Invalid date format, skip filter
            }
        } elseif ($dateFrom) {
            // Single date filter - use same date for both from and to
            $dateFrom = str_replace('/', '-', $dateFrom);
            try {
                $fromDate = Carbon::createFromFormat('d-m-Y', $dateFrom);
                $toDate = $fromDate->copy()->endOfDay();

                // Filter by date column (dd/mm/yyyy) or created_at
                $query->where(function ($q) use ($fromDate, $toDate) {
                    $q->where(function ($subQ) use ($fromDate, $toDate) {
                        // Match date column (dd/mm/yyyy format) - exact date match
                        $subQ->whereNotNull('date')
                            ->whereRaw("DATE(STR_TO_DATE(date, '%d/%m/%Y')) = DATE(?)", [$fromDate->format('Y-m-d')]);
                    })->orWhere(function ($subQ) use ($fromDate, $toDate) {
                        // Fallback to created_at if date column is null
                        $subQ->whereNull('date')
                            ->whereBetween('created_at', [$fromDate->startOfDay(), $toDate]);
                    });
                });
            } catch (\Exception $e) {
                // Invalid date format, skip filter
            }
        } elseif ($month) {
            // Filter by month
            try {
                $monthNum = (int) $month;
                $startDate = Carbon::create($year, $monthNum, 1)->startOfDay();
                $endDate = Carbon::create($year, $monthNum, 1)->endOfMonth()->endOfDay();

                $query->where(function ($q) use ($startDate, $endDate, $monthNum, $year) {
                    $q->where(function ($subQ) use ($monthNum, $year) {
                        // Match date column (dd/mm/yyyy format) for the month
                        $subQ->whereNotNull('date')
                            ->whereRaw("MONTH(STR_TO_DATE(date, '%d/%m/%Y')) = ?", [$monthNum])
                            ->whereRaw("YEAR(STR_TO_DATE(date, '%d/%m/%Y')) = ?", [$year]);
                    })->orWhere(function ($subQ) use ($startDate, $endDate) {
                        // Fallback to created_at if date column is null
                        $subQ->whereNull('date')
                            ->whereBetween('created_at', [$startDate, $endDate]);
                    });
                });
            } catch (\Exception $e) {
                // Invalid month, skip filter
            }
        }
        // If no filters provided, show all data (no year filter applied)
    }

    /**
     * Check if entry date matches the filters
     */
    private function matchesDateFilters($entryDate, $year, $month = null, $dateFrom = null, $dateTo = null)
    {
        if ($dateFrom) {
            try {
                // Normalize date formats
                $dateFrom = str_replace('/', '-', $dateFrom);

                $fromDate = Carbon::createFromFormat('d-m-Y', $dateFrom);

                if ($dateTo) {
                    // Date range filter
                    $dateTo = str_replace('/', '-', $dateTo);
                    $toDate = Carbon::createFromFormat('d-m-Y', $dateTo);
                    return $entryDate->gte($fromDate->startOfDay()) && $entryDate->lte($toDate->endOfDay());
                } else {
                    // Single date filter - exact match for the day
                    return $entryDate->format('Y-m-d') === $fromDate->format('Y-m-d');
                }
            } catch (\Exception $e) {
                return false;
            }
        } elseif ($month) {
            return $entryDate->year == $year && $entryDate->month == (int)$month;
        } else {
            // No filters applied, show all entries
            return true;
        }
    }

    /**
     * Extract PPA Date from remarks
     * Format: "PPA Date: 03/08/2025" or "PPA Date: 03-08-2025"
     */
    private function extractPpaDate($remarks)
    {
        if (empty($remarks)) {
            return null;
        }

        // Try to find "PPA Date: DD/MM/YYYY" or "PPA Date: DD-MM-YYYY"
        if (preg_match('/PPA\s+Date:\s*(\d{2})[\/\-](\d{2})[\/\-](\d{4})/i', $remarks, $matches)) {
            $day = (int) $matches[1];
            $month = (int) $matches[2];
            $year = (int) $matches[3];

            try {
                return Carbon::create($year, $month, $day);
            } catch (\Exception $e) {
                return null;
            }
        }

        return null;
    }
}
