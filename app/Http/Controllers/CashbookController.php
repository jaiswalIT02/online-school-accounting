<?php

namespace App\Http\Controllers;

use App\Models\Cashbook;
use App\Models\CashbookEntry;
use App\Models\ReceiptPaymentAccount;
use App\Models\ReceiptPaymentEntry;
use Illuminate\Http\Request;

class CashbookController extends Controller
{
    public function index(Request $request)
    {
        $query = Cashbook::query();

        if ($request->filled('session_id')) {
            $query->where('session_year_id', $request->session_id);
        }

        if ($request->filled('account_type')) {
            $query->where('account_type_id', $request->account_type);
        }

        // dd($query->toSql(), $query->getBindings());

        $cashbooks = $query->orderByDesc('period_year')
            ->orderBy('period_month')
            ->paginate(15)
            ->withQueryString();

        return view('cashbooks.index', compact('cashbooks'));
    }


    public function create()
    {
        return view('cashbooks.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'period_month' => ['required', 'string', 'max:20'],
            'period_year' => ['required', 'integer', 'min:1900', 'max:2100'],
            'opening_cash' => ['nullable', 'numeric', 'min:0'],
            'opening_bank' => ['nullable', 'numeric', 'min:0'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $data['opening_cash'] = $data['opening_cash'] ?? 0;
        $data['opening_bank'] = $data['opening_bank'] ?? 0;

        $cashbook = Cashbook::create($data);

        return redirect()
            ->route('cashbooks.show', $cashbook)
            ->with('status', 'Cashbook created.');
    }

    public function show(Cashbook $cashbook)
    {
        // Get cashbook period
        $year = (int) $cashbook->period_year;
        $monthNumber = $this->getMonthNumber($cashbook->period_month);

        // Get all R&P entries with relationships
        $rpeEntries = ReceiptPaymentEntry::with(['article', 'beneficiary'])
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();

        // Filter and transform R&P entries to match cashbook entry format
        $receipts = $rpeEntries->where('type', 'receipt')
            ->filter(function ($rpeEntry) use ($year, $monthNumber) {
                // Extract PPA date from remarks
                $ppaDate = $this->extractPpaDate($rpeEntry->remarks);
                if ($ppaDate) {
                    // Filter by PPA date matching cashbook period
                    return $ppaDate->year == $year && $ppaDate->month == $monthNumber;
                }
                // If no PPA date, use created_at as fallback
                return $rpeEntry->created_at->year == $year && $rpeEntry->created_at->month == $monthNumber;
            })
            ->map(function ($rpeEntry) {
                // Extract PPA date from remarks, fallback to created_at
                $entryDate = $this->extractPpaDate($rpeEntry->remarks) ?? $rpeEntry->created_at;

                return (object)[
                    'id' => $rpeEntry->id,
                    'entry_date' => $entryDate,
                    'particulars' => $rpeEntry->current_particular_name,
                    'narration' => $rpeEntry->remarks,
                    'cash_amount' => 0,
                    'bank_amount' => $rpeEntry->amount,
                    'tax_amount' => $rpeEntry->tax_amount,
                    'tax_for' => $rpeEntry->tax_for,
                    'tax_remark' => $rpeEntry->tax_remark,
                    'is_rpe_entry' => true,
                ];
            })
            ->sortBy(function ($entry) {
                return $entry->entry_date->format('Y-m-d');
            })
            ->values();

        $payments = $rpeEntries->where('type', 'payment')
            ->filter(function ($rpeEntry) use ($year, $monthNumber) {
                // Extract PPA date from remarks
                $ppaDate = $this->extractPpaDate($rpeEntry->remarks);
                if ($ppaDate) {
                    // Filter by PPA date matching cashbook period
                    return $ppaDate->year == $year && $ppaDate->month == $monthNumber;
                }
                // If no PPA date, use created_at as fallback
                return $rpeEntry->created_at->year == $year && $rpeEntry->created_at->month == $monthNumber;
            })
            ->map(function ($rpeEntry) {
                // Extract PPA date from remarks, fallback to created_at
                $entryDate = $this->extractPpaDate($rpeEntry->remarks) ?? $rpeEntry->created_at;

                return (object)[
                    'id' => $rpeEntry->id,
                    'entry_date' => $entryDate,
                    'particulars' => $rpeEntry->current_particular_name,
                    'narration' => $rpeEntry->remarks,
                    'cash_amount' => 0,
                    'bank_amount' => $rpeEntry->amount,
                    'tax_amount' => $rpeEntry->tax_amount,
                    'tax_for' => $rpeEntry->tax_for,
                    'tax_remark' => $rpeEntry->tax_remark,
                    'is_rpe_entry' => true,
                ];
            })
            ->sortBy(function ($entry) {
                return $entry->entry_date->format('Y-m-d');
            })
            ->values();

        $opening = [
            'cash' => $cashbook->opening_cash,
            'bank' => $cashbook->opening_bank,
            'total' => $cashbook->opening_cash + $cashbook->opening_bank,
        ];

        // Calculate totals from R&P entries (all amounts go to bank in R&P)
        $receiptTotals = [
            'cash' => 0,
            'bank' => $receipts->sum('bank_amount'),
        ];
        $receiptTotals['total'] = $receiptTotals['cash'] + $receiptTotals['bank'];

        $paymentTotals = [
            'cash' => 0,
            'bank' => $payments->sum('bank_amount'),
        ];
        $paymentTotals['total'] = $paymentTotals['cash'] + $paymentTotals['bank'];

        $closing = [
            'cash' => $opening['cash'] + $receiptTotals['cash'] - $paymentTotals['cash'],
            'bank' => $opening['bank'] + $receiptTotals['bank'] - $paymentTotals['bank'],
        ];
        $closing['total'] = $closing['cash'] + $closing['bank'];

        return view('cashbooks.show', compact(
            'cashbook',
            'receipts',
            'payments',
            'opening',
            'receiptTotals',
            'paymentTotals',
            'closing'
        ));
    }

    /**
     * Convert month name to number (1-12)
     */
    private function getMonthNumber($monthName)
    {
        $months = [
            'january' => 1,
            'jan' => 1,
            '1' => 1,
            '01' => 1,
            'february' => 2,
            'feb' => 2,
            '2' => 2,
            '02' => 2,
            'march' => 3,
            'mar' => 3,
            '3' => 3,
            '03' => 3,
            'april' => 4,
            'apr' => 4,
            '4' => 4,
            '04' => 4,
            'may' => 5,
            '5' => 5,
            '05' => 5,
            'june' => 6,
            'jun' => 6,
            '6' => 6,
            '06' => 6,
            'july' => 7,
            'jul' => 7,
            '7' => 7,
            '07' => 7,
            'august' => 8,
            'aug' => 8,
            '8' => 8,
            '08' => 8,
            'september' => 9,
            'sep' => 9,
            'sept' => 9,
            '9' => 9,
            '09' => 9,
            'october' => 10,
            'oct' => 10,
            '10' => 10,
            'november' => 11,
            'nov' => 11,
            '11' => 11,
            'december' => 12,
            'dec' => 12,
            '12' => 12,
        ];

        return $months[strtolower(trim($monthName))] ?? 1;
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
                return \Carbon\Carbon::create($year, $month, $day);
            } catch (\Exception $e) {
                return null;
            }
        }

        return null;
    }

    public function edit(Cashbook $cashbook)
    {
        return view('cashbooks.edit', compact('cashbook'));
    }

    public function update(Request $request, Cashbook $cashbook)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'period_month' => ['required', 'string', 'max:20'],
            'period_year' => ['required', 'integer', 'min:1900', 'max:2100'],
            'opening_cash' => ['nullable', 'numeric', 'min:0'],
            'opening_bank' => ['nullable', 'numeric', 'min:0'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $data['opening_cash'] = $data['opening_cash'] ?? 0;
        $data['opening_bank'] = $data['opening_bank'] ?? 0;

        $cashbook->update($data);

        return redirect()
            ->route('cashbooks.show', $cashbook)
            ->with('status', 'Cashbook updated.');
    }

    public function destroy(Cashbook $cashbook)
    {
        $cashbook->delete();

        return redirect()
            ->route('cashbooks.index')
            ->with('status', 'Cashbook deleted.');
    }

    public function print(Cashbook $cashbook)
    {
        // Get cashbook period
        $year = (int) $cashbook->period_year;
        $monthNumber = $this->getMonthNumber($cashbook->period_month);

        // Get all R&P entries with relationships
        $rpeEntries = ReceiptPaymentEntry::with(['article', 'beneficiary'])
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();

        // Filter and transform R&P entries to match cashbook entry format
        $receipts = $rpeEntries->where('type', 'receipt')
            ->filter(function ($rpeEntry) use ($year, $monthNumber) {
                // Extract PPA date from remarks
                $ppaDate = $this->extractPpaDate($rpeEntry->remarks);
                if ($ppaDate) {
                    // Filter by PPA date matching cashbook period
                    return $ppaDate->year == $year && $ppaDate->month == $monthNumber;
                }
                // If no PPA date, use created_at as fallback
                return $rpeEntry->created_at->year == $year && $rpeEntry->created_at->month == $monthNumber;
            })
            ->map(function ($rpeEntry) {
                // Extract PPA date from remarks, fallback to created_at
                $entryDate = $this->extractPpaDate($rpeEntry->remarks) ?? $rpeEntry->created_at;

                return (object)[
                    'id' => $rpeEntry->id,
                    'entry_date' => $entryDate,
                    'particulars' => $rpeEntry->current_particular_name,
                    'narration' => $rpeEntry->remarks,
                    'cash_amount' => 0,
                    'bank_amount' => $rpeEntry->amount,
                    'tax_amount' => $rpeEntry->tax_amount,
                    'tax_for' => $rpeEntry->tax_for,
                    'tax_remark' => $rpeEntry->tax_remark,
                ];
            })
            ->sortBy(function ($entry) {
                return $entry->entry_date->format('Y-m-d');
            })
            ->values();

        $payments = $rpeEntries->where('type', 'payment')
            ->filter(function ($rpeEntry) use ($year, $monthNumber) {
                // Extract PPA date from remarks
                $ppaDate = $this->extractPpaDate($rpeEntry->remarks);
                if ($ppaDate) {
                    // Filter by PPA date matching cashbook period
                    return $ppaDate->year == $year && $ppaDate->month == $monthNumber;
                }
                // If no PPA date, use created_at as fallback
                return $rpeEntry->created_at->year == $year && $rpeEntry->created_at->month == $monthNumber;
            })
            ->map(function ($rpeEntry) {
                // Extract PPA date from remarks, fallback to created_at
                $entryDate = $this->extractPpaDate($rpeEntry->remarks) ?? $rpeEntry->created_at;

                return (object)[
                    'id' => $rpeEntry->id,
                    'entry_date' => $entryDate,
                    'particulars' => $rpeEntry->current_particular_name,
                    'narration' => $rpeEntry->remarks,
                    'cash_amount' => 0,
                    'bank_amount' => $rpeEntry->amount,
                    'tax_amount' => $rpeEntry->tax_amount,
                    'tax_for' => $rpeEntry->tax_for,
                    'tax_remark' => $rpeEntry->tax_remark,
                ];
            })
            ->sortBy(function ($entry) {
                return $entry->entry_date->format('Y-m-d');
            })
            ->values();

        $opening = [
            'cash' => $cashbook->opening_cash,
            'bank' => $cashbook->opening_bank,
            'total' => $cashbook->opening_cash + $cashbook->opening_bank,
        ];

        $receiptTotals = [
            'cash' => 0,
            'bank' => $receipts->sum('bank_amount'),
        ];
        $receiptTotals['total'] = $receiptTotals['cash'] + $receiptTotals['bank'];

        $paymentTotals = [
            'cash' => 0,
            'bank' => $payments->sum('bank_amount'),
        ];
        $paymentTotals['total'] = $paymentTotals['cash'] + $paymentTotals['bank'];

        $closing = [
            'cash' => $opening['cash'] + $receiptTotals['cash'] - $paymentTotals['cash'],
            'bank' => $opening['bank'] + $receiptTotals['bank'] - $paymentTotals['bank'],
        ];
        $closing['total'] = $closing['cash'] + $closing['bank'];

        // Calculate grand totals (including opening/closing balances)
        $receiptGrandTotal = $opening['bank'] + $receiptTotals['bank'];
        $paymentGrandTotal = $paymentTotals['bank'] + $closing['bank'];

        // Rows per printed page: tuned so ONE logical page = ONE A4 sheet when using normal print.
        // A4 landscape height 210mm, margins 10mm top/bottom → 190mm. Row height in print CSS = 55px (~14.5mm).
        // Header (column headers + opening row) ~3 rows, footer (closing + total) ~2 rows → ~5 rows fixed.
        // Data rows that fit: (190mm - 5*14.5mm) / 14.5mm ≈ 8. Override via config if your printer differs.
        $rowsPerPage = (int) config('cashbook.print_rows_per_page', 8);
        $rowsPerPage = $rowsPerPage >= 1 ? $rowsPerPage : 8;

        // Prepare paginated pages for receipts and payments
        $receiptPages = [];
        $paymentPages = [];

        // Get first month from receipts/payments for opening balance date
        $firstEntryDate = null;
        if ($receipts->count() > 0) {
            $firstEntryDate = $receipts->first()->entry_date;
        } elseif ($payments->count() > 0) {
            $firstEntryDate = $payments->first()->entry_date;
        }

        // Paginate receipts
        $receiptChunks = $receipts->chunk($rowsPerPage);
        $runningReceiptCash = $opening['cash'];
        $runningReceiptBank = $opening['bank'];
        $runningReceiptTotal = $opening['total'];
        $cumulativeReceiptAmounts = 0; // Cumulative sum of ONLY receipt transaction amounts (no opening balance)
        $pageNumber = 1;

        foreach ($receiptChunks as $chunk) {
            $pageReceipts = $chunk->values();

            // Calculate amounts for entries on THIS PAGE ONLY
            $pageReceiptCash = $pageReceipts->sum('cash_amount');
            $pageReceiptBank = $pageReceipts->sum('bank_amount');
            $pageReceiptTotal = $pageReceiptCash + $pageReceiptBank;

            // Opening balance for this page
            $pageOpening = [
                'cash' => $runningReceiptCash,
                'bank' => $runningReceiptBank,
                'total' => $runningReceiptTotal,
            ];

            if($receiptChunks->first()){

                // Update running totals (opening balance + transactions)
                // $runningReceiptCash += $pageReceiptCash;
                // $runningReceiptBank += $pageReceiptBank;
                // $runningReceiptTotal += $pageReceiptTotal;

                $pageReceiptCash += $runningReceiptCash;
                $pageReceiptBank += $runningReceiptBank;
                $pageReceiptTotal += $runningReceiptTotal;
            }


            // Closing balance for this page
            $pageClosing = [
                'cash' => $pageReceiptCash,
                'bank' => $pageReceiptBank,
                'total' => $pageReceiptTotal,
            ];

            // Add this page's transaction amounts to cumulative sum
            $cumulativeReceiptAmounts += $pageReceiptBank;

            $receiptPages[] = [
                'page_number' => $pageNumber,
                'entries' => $pageReceipts,
                'opening' => $pageOpening,
                'closing' => $pageClosing,
                'page_total' => $pageReceiptTotal,
                'cumulative_total' => $cumulativeReceiptAmounts, // Cumulative from page 1 to current page
                'is_first_page' => $pageNumber === 1,
                'is_last_page' => $pageNumber === $receiptChunks->count(),
            ];

            $pageNumber++;
        }

        // If no receipts, create one page with opening balance
        if (count($receiptPages) === 0) {
            $receiptPages[] = [
                'page_number' => 1,
                'entries' => collect(),
                'opening' => $opening,
                'closing' => $opening,
                'page_total' => 0,
                'cumulative_total' => 0,
                'is_first_page' => true,
                'is_last_page' => true,
            ];
        }

        // Paginate payments
        $paymentChunks = $payments->chunk($rowsPerPage);
        $runningPaymentCash = 0;
        $runningPaymentBank = 0;
        $runningPaymentTotal = 0;
        $cumulativePaymentAmounts = 0; // Cumulative sum of ONLY payment transaction amounts (no opening balance)
        $pageNumber = 1;

        foreach ($paymentChunks as $chunk) {
            $pagePayments = $chunk->values();

            // Calculate amounts for entries on THIS PAGE ONLY
            $pagePaymentCash = $pagePayments->sum('cash_amount');
            $pagePaymentBank = $pagePayments->sum('bank_amount');
            $pagePaymentTotal = $pagePaymentCash + $pagePaymentBank;

            // Opening balance for this page (carried forward from previous page)
            // $pageOpeningCash = $opening['cash'] + $receiptTotals['cash'] - $runningPaymentCash;
            // $pageOpeningBank = $opening['bank'] + $receiptTotals['bank'] - $runningPaymentBank;
            // $pageOpeningTotal = $pageOpeningCash + $pageOpeningBank;

            $pageOpening = [
                'cash' => $pagePaymentCash,
                'bank' => $pagePaymentBank,
                'total' => $pagePaymentTotal,
            ];

            // Update running totals
            $runningPaymentCash += $pagePaymentCash;
            $runningPaymentBank += $pagePaymentBank;
            $runningPaymentTotal += $pagePaymentTotal;

            // Closing balance for this page
            $pageClosingCash = $opening['cash'] + $receiptTotals['cash'] - $runningPaymentCash;
            $pageClosingBank = $opening['bank'] + $receiptTotals['bank'] - $runningPaymentBank;
            $pageClosingTotal = $pageClosingCash + $pageClosingBank;

            $pageClosing = [
                'cash' => $pageClosingCash,
                'bank' => $pageClosingBank,
                'total' => $pageClosingTotal,
            ];

            // Add this page's transaction amounts to cumulative sum
            $cumulativePaymentAmounts += $pagePaymentBank;

            $paymentPages[] = [
                'page_number' => $pageNumber,
                'entries' => $pagePayments,
                'opening' => $pageOpening,
                'closing' => $pageClosing,
                'page_total' => $pagePaymentTotal,
                'cumulative_total' => $cumulativePaymentAmounts, // Cumulative from page 1 to current page
                'is_first_page' => $pageNumber === 1,
                'is_last_page' => $pageNumber === $paymentChunks->count(),
            ];

            $pageNumber++;
        }

        // If no payments, create one page with opening balance
        if (count($paymentPages) === 0) {
            $paymentPages[] = [
                'page_number' => 1,
                'entries' => collect(),
                'opening' => [
                    'cash' => $opening['cash'] + $receiptTotals['cash'],
                    'bank' => $opening['bank'] + $receiptTotals['bank'],
                    'total' => $opening['total'] + $receiptTotals['total'],
                ],
                'closing' => $closing,
                'page_total' => 0,
                'cumulative_total' => 0,
                'is_first_page' => true,
                'is_last_page' => true,
            ];
        }
        // else {
        //     // Update first payment page opening balance to show after all receipts
        //     $paymentPages[0]['opening'] = [
        //         'cash' => $opening['cash'] + $receiptTotals['cash'],
        //         'bank' => $opening['bank'] + $receiptTotals['bank'],
        //         'total' => $opening['total'] + $receiptTotals['total'],
        //     ];
        // }

        // Determine total number of pages needed
        $totalPages = max(count($receiptPages), count($paymentPages));

        $firstDate = $firstEntryDate->copy()->startOfMonth(); // "2026-02-01"

        // Get last date of the month
        $lastDate = $firstEntryDate->copy()->endOfMonth(); // "2026-02-28"
        $prevReciept = $receiptPages[0];
        foreach ($receiptPages as $index  => $page) {

            // Pad payment pages to totalPages
            if($index == 0){
                $paymentPages[$index]['closing'] = [
                    'cash' => $page['closing']['cash'] + $page['opening']['cash'] - $paymentPages[$index]['opening']['cash'],
                    'bank' => $page['closing']['bank'] + $page['opening']['bank'] - $paymentPages[$index]['opening']['bank'],
                    'total' => $page['closing']['total'] + $page['opening']['total'] - $paymentPages[$index]['opening']['total'],
                ];
                continue;
            }

            $receiptClosing = unserialize(serialize($page['closing']));

            $receiptPages[$index]['opening'] = unserialize(serialize($paymentPages[$index-1]['closing']));

            $receiptPages[$index]['closing'] = [
                'cash' => $receiptClosing['cash'] + $paymentPages[$index-1]['closing']['cash'],
                'bank' => $receiptClosing['bank'] + $paymentPages[$index-1]['closing']['bank'],
                'total' => $receiptClosing['total'] + $paymentPages[$index-1]['closing']['total'],
            ];
            // Pad receipt pages to totalPages
            $paymentPages[$index]['closing'] = [
                'cash' => $receiptPages[$index]['closing']['cash'] - $paymentPages[$index]['opening']['cash'],
                'bank' => $receiptPages[$index]['closing']['bank'] - $paymentPages[$index]['opening']['bank'],
                'total' => $receiptPages[$index]['closing']['total'] - $paymentPages[$index]['opening']['total'],
            ];

        }

        // dd($receiptPages, $paymentPages);

        return view('cashbooks.print', compact(
            'cashbook',
            'receipts',
            'payments',
            'opening',
            'receiptTotals',
            'paymentTotals',
            'closing',
            'receiptGrandTotal',
            'paymentGrandTotal',
            'receiptPages',
            'paymentPages',
            'totalPages',
            'firstEntryDate',
            'firstDate',
            'lastDate'
        ));
    }

    public function import(Cashbook $cashbook)
    {
        // Show selection page to choose Receipt & Payment account
        $accounts = ReceiptPaymentAccount::orderByDesc('period_from')->get();

        return view('cashbooks.import', compact('cashbook', 'accounts'));
    }

    public function processImport(Request $request, Cashbook $cashbook)
    {
        $request->validate([
            'receipt_payment_account_id' => ['required', 'exists:receipt_payment_accounts,id'],
        ]);

        $account = ReceiptPaymentAccount::findOrFail($request->receipt_payment_account_id);

        // Get all receipt_payment_entries from the selected account with relationships
        $rpeEntries = ReceiptPaymentEntry::with(['article', 'beneficiary'])
            ->where('receipt_payment_account_id', $account->id)
            ->with(['article', 'beneficiary'])
            ->get();

        if ($rpeEntries->isEmpty()) {
            return redirect()
                ->route('cashbooks.import', $cashbook)
                ->with('error', 'No entries found in the selected Receipt & Payment account.');
        }

        $importedCount = 0;
        $skippedCount = 0;

        foreach ($rpeEntries as $rpeEntry) {
            // Use ReceiptPaymentEntry's created_at for entry_date
            $entryDate = $rpeEntry->created_at->format('Y-m-d');

            // Check if entry already exists by receipt_payment_entry_id
            $currentParticulars = $rpeEntry->current_particular_name;
            $existingEntry = CashbookEntry::where('cashbook_id', $cashbook->id)
                ->where('receipt_payment_entry_id', $rpeEntry->id)
                ->first();

            if ($existingEntry) {
                // Update existing entry with latest data including tax information
                $existingEntry->update([
                    'entry_date' => $entryDate,
                    'particulars' => $currentParticulars,
                    'bank_amount' => $rpeEntry->amount,
                    'narration' => $rpeEntry->remarks,
                    'tax_amount' => $rpeEntry->tax_amount,
                    'tax_for' => $rpeEntry->tax_for,
                    'tax_remark' => $rpeEntry->tax_remark,
                    'receipt_payment_entry_id' => $rpeEntry->id,
                ]);
                $importedCount++; // Count as imported since we updated it
            } else {
                // Create new cashbook entry - amount goes to bank (assuming bank transactions)
                CashbookEntry::create([
                    'cashbook_id' => $cashbook->id,
                    'receipt_payment_entry_id' => $rpeEntry->id,
                    'entry_date' => $entryDate,
                    'particulars' => $currentParticulars,
                    'type' => $rpeEntry->type,
                    'cash_amount' => 0,
                    'bank_amount' => $rpeEntry->amount,
                    'narration' => $rpeEntry->remarks,
                    'tax_amount' => $rpeEntry->tax_amount,
                    'tax_for' => $rpeEntry->tax_for,
                    'tax_remark' => $rpeEntry->tax_remark,
                ]);

                $importedCount++;
            }
        }

        $message = "Import completed. {$importedCount} entries imported/updated from Receipt & Payment account: " . ($account->header_title ?? $account->name) . ".";

        return redirect()
            ->route('cashbooks.show', $cashbook)
            ->with('status', $message);
    }

    public function createAllMonthsEntries()
    {
        // Financial year: April 2025 to March 2026
        $months = [
            ['name' => 'April', 'year' => 2025],
            ['name' => 'May', 'year' => 2025],
            ['name' => 'June', 'year' => 2025],
            ['name' => 'July', 'year' => 2025],
            ['name' => 'August', 'year' => 2025],
            ['name' => 'September', 'year' => 2025],
            ['name' => 'October', 'year' => 2025],
            ['name' => 'November', 'year' => 2025],
            ['name' => 'December', 'year' => 2025],
            ['name' => 'January', 'year' => 2026],
            ['name' => 'February', 'year' => 2026],
            ['name' => 'March', 'year' => 2026],
        ];

        $createdCount = 0;
        $skippedCount = 0;

        // Create cashbook for each month
        foreach ($months as $monthData) {
            $monthName = $monthData['name'];
            $year = $monthData['year'];

            // Check if cashbook already exists for this month/year
            $existingCashbook = Cashbook::where('period_year', $year)
                ->where('period_month', $monthName)
                ->first();

            if (!$existingCashbook) {
                // Create new cashbook
                Cashbook::create([
                    'name' => "Cashbook - {$monthName} {$year}",
                    'period_month' => $monthName,
                    'period_year' => $year,
                    'opening_cash' => 0,
                    'opening_bank' => 0,
                    'description' => "Cashbook for {$monthName} {$year}",
                ]);
                $createdCount++;
            } else {
                $skippedCount++;
            }
        }

        if ($createdCount > 0) {
            $message = "Successfully created {$createdCount} cashbook(s) for financial year April 2025 - March 2026.";
            if ($skippedCount > 0) {
                $message .= " {$skippedCount} cashbook(s) already existed and were skipped.";
            }
        } else {
            $message = "All 12 cashbooks for financial year April 2025 - March 2026 already exist. No new cashbooks created.";
        }

        return redirect()
            ->route('cashbooks.index')
            ->with('status', $message);
    }
}
