<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Beneficiary;
use App\Models\Cashbook;
use App\Models\ReceiptPaymentAccount;
use App\Models\ReceiptPaymentEntry;
use App\Services\ReceiptPaymentSyncService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReceiptPaymentAccountController extends Controller
{
    public function index(Request $request)
    {
        $session_filter = $request->get('session_id', 1);
        $account_type = $request->get('account_type', 1);
        $accounts = ReceiptPaymentAccount::where('session_year_id', $session_filter)->where('account_type_id', $account_type)->orderByDesc('period_from')->paginate(15);

        return view('receipt_payments.index', compact('accounts'));
    }

    public function create()
    {
        return view('receipt_payments.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'header_title' => ['nullable', 'string', 'max:200'],
            'header_subtitle' => ['nullable', 'string', 'max:200'],
            'period_from' => ['required', 'date'],
            'period_to' => ['required', 'date', 'after_or_equal:period_from'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $account = ReceiptPaymentAccount::create($data);

        return redirect()
            ->route('receipt_payments.show', $account)
            ->with('status', 'Receipt & payment account created.');
    }

    public function show(ReceiptPaymentAccount $receipt_payment)
    {

        $entries = $receipt_payment->entries()
            ->with(['article', 'beneficiary'])
            ->orderByDesc('id')
            ->get();

        $receipts = $entries->where('type', 'receipt');
        $payments = $entries->where('type', 'payment');

        $receiptTotal = $receipts->sum('amount');
        $paymentTotal = $payments->sum('amount');
        $closingBalance = $receiptTotal - $paymentTotal;

        return view('receipt_payments.show', [
            'account' => $receipt_payment,
            'receipts' => $receipts,
            'payments' => $payments,
            'receiptTotal' => $receiptTotal,
            'paymentTotal' => $paymentTotal,
            'closingBalance' => $closingBalance,
        ]);
    }

    public function print(Request $request, ReceiptPaymentAccount $receipt_payment)
    {
        $entries = $receipt_payment->entries()
            ->with(['article', 'beneficiary'])
            ->orderBy('id')
            ->get();

        // Apply month filters if provided
        $filteredEntries = $entries;

        if ($request->filled('filter_type')) {
            $filterType = $request->filter_type;

            if ($filterType === 'single_month' && $request->filled('month')) {
                // Single month filter: YYYY-MM format (e.g., 2025-04)
                $selectedMonth = $request->month;
                list($year, $month) = explode('-', $selectedMonth);
                $year = (int)$year;
                $month = (int)$month;

                $filteredEntries = $entries->filter(function ($entry) use ($year, $month) {
                    return $this->entryMatchesMonth($entry, $year, $month);
                });
            } elseif ($filterType === 'month_range' && $request->filled('from_month') && $request->filled('to_month')) {
                // Month range filter: From month to To month (both inclusive)
                $fromMonth = $request->from_month;
                $toMonth = $request->to_month;

                list($fromYear, $fromMonthNum) = explode('-', $fromMonth);
                list($toYear, $toMonthNum) = explode('-', $toMonth);
                $fromYear = (int)$fromYear;
                $fromMonthNum = (int)$fromMonthNum;
                $toYear = (int)$toYear;
                $toMonthNum = (int)$toMonthNum;

                $filteredEntries = $entries->filter(function ($entry) use ($fromYear, $fromMonthNum, $toYear, $toMonthNum) {
                    return $this->entryMatchesMonthRange($entry, $fromYear, $fromMonthNum, $toYear, $toMonthNum);
                });
            }
        }

        $receipts = $filteredEntries->where('type', 'receipt');
        $payments = $filteredEntries->where('type', 'payment');

        // Group receipts by component name and sum amounts
        $groupedReceipts = $receipts->groupBy(function ($entry) {
            return $entry->current_particular_name;
        })->map(function ($group) {
            $firstEntry = $group->first();
            return (object)[
                'current_particular_name' => $firstEntry->current_particular_name,
                'current_acode' => $firstEntry->current_acode,
                'amount' => $group->sum('amount'),
            ];
        })->values()->sortBy('current_particular_name')->values();

        // Group payments by component name and sum amounts
        $groupedPayments = $payments->groupBy(function ($entry) {
            return $entry->current_particular_name;
        })->map(function ($group) {
            $firstEntry = $group->first();
            return (object)[
                'current_particular_name' => $firstEntry->current_particular_name,
                'current_acode' => $firstEntry->current_acode,
                'amount' => $group->sum('amount'),
            ];
        })->values()->sortBy('current_particular_name')->values();

        $receiptTotal = $receipts->sum('amount');
        $paymentTotal = $payments->sum('amount');
        $closingBalance = $receiptTotal - $paymentTotal;

        return view('receipt_payments.print', [
            'account' => $receipt_payment,
            'receipts' => $groupedReceipts,
            'payments' => $groupedPayments,
            'receiptTotal' => $receiptTotal,
            'paymentTotal' => $paymentTotal,
            'closingBalance' => $closingBalance,
        ]);
    }

    /**
     * Check if entry date matches a specific month
     */
    private function entryMatchesMonth($entry, $year, $month)
    {
        $entryDate = $this->getEntryDate($entry);
        if (!$entryDate) {
            return false;
        }

        return $entryDate->year == $year && $entryDate->month == $month;
    }

    /**
     * Check if entry date falls within a month range (inclusive)
     */
    private function entryMatchesMonthRange($entry, $fromYear, $fromMonth, $toYear, $toMonth)
    {
        $entryDate = $this->getEntryDate($entry);
        if (!$entryDate) {
            return false;
        }

        $entryYear = $entryDate->year;
        $entryMonth = $entryDate->month;

        // Convert to comparable format (YYYYMM)
        $entryValue = (int)($entryYear . str_pad($entryMonth, 2, '0', STR_PAD_LEFT));
        $fromValue = (int)($fromYear . str_pad($fromMonth, 2, '0', STR_PAD_LEFT));
        $toValue = (int)($toYear . str_pad($toMonth, 2, '0', STR_PAD_LEFT));

        return $entryValue >= $fromValue && $entryValue <= $toValue;
    }

    /**
     * Get entry date from date column (dd/mm/yyyy) or fallback to created_at
     */
    private function getEntryDate($entry)
    {
        // Try date column first (dd/mm/yyyy format)
        if (!empty($entry->date)) {
            if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $entry->date, $matches)) {
                try {
                    return Carbon::create((int)$matches[3], (int)$matches[2], (int)$matches[1]);
                } catch (\Exception $e) {
                    // Fall through to next method
                }
            }
        }

        // Try to extract from remarks (PPA Date)
        if (!empty($entry->remarks)) {
            if (preg_match('/PPA\s+Date:\s*(\d{2})[\/\-](\d{2})[\/\-](\d{4})/i', $entry->remarks, $matches)) {
                try {
                    return Carbon::create((int)$matches[3], (int)$matches[2], (int)$matches[1]);
                } catch (\Exception $e) {
                    // Fall through to next method
                }
            }
        }

        // Fallback to created_at
        return $entry->created_at;
    }

    public function edit(ReceiptPaymentAccount $receipt_payment)
    {
        return view('receipt_payments.edit', ['account' => $receipt_payment]);
    }

    public function update(Request $request, ReceiptPaymentAccount $receipt_payment)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'header_title' => ['nullable', 'string', 'max:200'],
            'header_subtitle' => ['nullable', 'string', 'max:200'],
            'period_from' => ['required', 'date'],
            'period_to' => ['required', 'date', 'after_or_equal:period_from'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $receipt_payment->update($data);

        return redirect()
            ->route('receipt_payments.show', $receipt_payment)
            ->with('status', 'Receipt & payment account updated.');
    }

    public function destroy(ReceiptPaymentAccount $receipt_payment)
    {
        $receipt_payment->delete();

        return redirect()
            ->route('receipt_payments.index')
            ->with('status', 'Receipt & payment account deleted.');
    }

    public function createCashbook(Request $request, ReceiptPaymentAccount $receipt_payment)
    {
        $data = $request->validate([
            'period_month' => ['required', 'string', 'max:20'],
            'period_year' => ['required', 'integer', 'min:1900', 'max:2100'],
        ]);

        // Check if cashbook already exists for this month and year
        $existingCashbook = Cashbook::where('period_month', $data['period_month'])
            ->where('period_year', $data['period_year'])
            ->first();

        if ($existingCashbook) {
            return redirect()
                ->route('receipt_payments.show', $receipt_payment)
                ->with('cashbook_error', "Cashbook for {$data['period_month']} {$data['period_year']} already exists.");
        }

        // Create new cashbook
        $cashbook = Cashbook::create([
            'name' => 'Cash Book',
            'period_month' => $data['period_month'],
            'period_year' => $data['period_year'],
            'opening_cash' => 0,
            'opening_bank' => 0,
        ]);

        // Sync all ReceiptPaymentEntry records to this cashbook
        $syncService = app(ReceiptPaymentSyncService::class);
        $allEntries = ReceiptPaymentEntry::with(['article', 'beneficiary'])->get();

        foreach ($allEntries as $entry) {
            // Manually sync to the specific cashbook
            $syncService->syncEntryToCashbook($entry, $cashbook);
        }

        return redirect()
            ->route('receipt_payments.show', $receipt_payment)
            ->with('status', "Cashbook for {$data['period_month']} {$data['period_year']} created successfully with all activities.");
    }
}
