<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Ledger;
use App\Models\LedgerEntry;
use App\Models\ReceiptPaymentAccount;
use App\Models\ReceiptPaymentEntry;
use Illuminate\Http\Request;

class LedgerController extends Controller
{
    public function index(Request $request)
    {
        $session_filter = $request->get('session_id', 1);
        $account_type = $request->get('account_type', 1);

        $ledgers = Ledger::where('session_year_id', $session_filter)
            ->where('account_type_id', $account_type)
            ->paginate(15)
            ->withQueryString();

        return view('ledgers.index', compact('ledgers', 'session_filter'));
    }

    public function create()
    {
        $articles = Article::orderBy('name')->get();
        return view('ledgers.create', compact('articles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120', 'unique:ledgers,name'],
            'opening_balance' => ['nullable', 'numeric', 'min:0'],
            'opening_balance_type' => ['required', 'in:Dr,Cr'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $data['opening_balance'] = $data['opening_balance'] ?? 0;

        // Convert string fields to uppercase
        if (isset($data['name'])) $data['name'] = strtoupper($data['name']);
        if (isset($data['opening_balance_type'])) $data['opening_balance_type'] = strtoupper($data['opening_balance_type']);
        if (isset($data['description'])) $data['description'] = strtoupper($data['description']);

        $ledger = Ledger::create($data);

        return redirect()
            ->route('ledgers.show', $ledger)
            ->with('status', 'Ledger created.');
    }

    public function show(Ledger $ledger)
    {
        // Get only normal R&P entries (exclude tax entries)
        // Normal entries: have amount > 0 and particular_name doesn't start with tax identifiers
        // Order by ID to preserve insertion order (receipt, payment, receipt, payment...)
        $rpeEntries = ReceiptPaymentEntry::with(['article', 'beneficiary'])
            ->where(function ($query) use ($ledger) {
                $query->whereHas('article', function ($q) use ($ledger) {
                    $q->where('name', $ledger->name);
                })
                    ->orWhereHas('beneficiary', function ($q) use ($ledger) {
                        $q->where('name', $ledger->name);
                    });
            })
            ->where('amount', '>', 0) // Only entries with actual amount
            ->where(function ($query) {
                // Exclude tax entries by particular_name
                $query->where('particular_name', 'NOT LIKE', 'PTAX%')
                    ->where('particular_name', 'NOT LIKE', 'TDS%');
            })
            ->orderBy('id', 'asc') // Preserve insertion order (receipt, payment, receipt, payment...)
            ->get();

        $runningBalance = $ledger->opening_balance_type === 'Cr'
            ? -1 * $ledger->opening_balance
            : $ledger->opening_balance;

        $rows = [];
        $totalDebit = 0;
        $totalCredit = 0;

        // Process main entries in stored order (receipt, payment, receipt, payment...)
        foreach ($rpeEntries as $rpeEntry) {
            // Extract PPA date from date column, then remarks, fallback to created_at
            $entryDate = $this->getEntryDate($rpeEntry);

            // Receipt entries: amount on credit side, balance type Cr
            // Payment entries: amount on debit side, balance type Dr
            $credit = $rpeEntry->type === 'receipt' ? $rpeEntry->amount : 0;
            $debit = $rpeEntry->type === 'payment' ? $rpeEntry->amount : 0;
            $balanceType = $rpeEntry->type === 'receipt' ? 'Cr' : 'Dr';

            // Calculate running balance
            $runningBalance += $debit;
            $runningBalance -= $credit;
            $totalDebit += $debit;
            $totalCredit += $credit;

            $rows[] = [
                'entry' => (object)[
                    'id' => $rpeEntry->id,
                    'entry_date' => $entryDate,
                    'particulars' => $rpeEntry->current_particular_name,
                    'debit' => $debit,
                    'credit' => $credit,
                    'narration' => $rpeEntry->remarks,
                    'is_rpe_entry' => true,
                ],
                'balance' => abs($runningBalance),
                'balance_type' => $balanceType, // Transaction type: Cr for receipt, Dr for payment
            ];
        }

        $closingBalance = abs($runningBalance);
        $closingBalanceType = $runningBalance < 0 ? 'Cr' : 'Dr';

        return view('ledgers.show', compact('ledger', 'rows', 'totalDebit', 'totalCredit', 'closingBalance', 'closingBalanceType'));
    }

    public function edit(Ledger $ledger)
    {
        $articles = Article::orderBy('name')->get();
        return view('ledgers.edit', compact('ledger', 'articles'));
    }

    public function update(Request $request, Ledger $ledger)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120', 'unique:ledgers,name,' . $ledger->id],
            'opening_balance' => ['nullable', 'numeric', 'min:0'],
            'opening_balance_type' => ['required', 'in:Dr,Cr'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $data['opening_balance'] = $data['opening_balance'] ?? 0;

        $ledger->update($data);

        return redirect()
            ->route('ledgers.show', $ledger)
            ->with('status', 'Ledger updated.');
    }

    public function destroy(Ledger $ledger)
    {
        $ledger->delete();

        return redirect()
            ->route('ledgers.index')
            ->with('status', 'Ledger deleted.');
    }

    public function print(Ledger $ledger)
    {
        // Get only normal R&P entries (exclude tax entries)
        // Normal entries: have amount > 0
        // Order by ID to preserve insertion order (receipt, payment, receipt, payment...)
        $rpeEntries = ReceiptPaymentEntry::with(['article', 'beneficiary'])
            ->where(function($query) use ($ledger) {
                $query->whereHas('article', function($q) use ($ledger) {
                    $q->where('name', $ledger->name);
                })
                ->orWhereHas('beneficiary', function($q) use ($ledger) {
                    $q->where('name', $ledger->name);
                });
            })
            ->where('amount', '>', 0) // Only entries with actual amount
            ->orderBy('id', 'asc') // Preserve insertion order (receipt, payment, receipt, payment...)
            ->get();

        $runningBalance = $ledger->opening_balance_type === 'Cr'
            ? -1 * $ledger->opening_balance
            : $ledger->opening_balance;

        $rows = [];
        $totalDebit = 0;
        $totalCredit = 0;

        // Add opening balance row if exists
        if ($ledger->opening_balance > 0) {
            $rows[] = [
                'entry' => null,
                'is_opening' => true,
                'balance' => abs($runningBalance),
                'balance_type' => $ledger->opening_balance_type,
            ];
        }

        // Process main entries in stored order (receipt, payment, receipt, payment...)
        foreach ($rpeEntries as $rpeEntry) {
            // Extract PPA date from date column, then remarks, fallback to created_at
            $entryDate = $this->getEntryDate($rpeEntry);
            
            // Receipt entries: amount on credit side, balance type Cr
            // Payment entries: amount on debit side, balance type Dr
            $credit = $rpeEntry->type === 'receipt' ? $rpeEntry->amount : 0;
            $debit = $rpeEntry->type === 'payment' ? $rpeEntry->amount : 0;
            $balanceType = $rpeEntry->type === 'receipt' ? 'Cr' : 'Dr';
            
            // Calculate running balance
            $runningBalance += $debit;
            $runningBalance -= $credit;
            $totalDebit += $debit;
            $totalCredit += $credit;

            $rows[] = [
                'entry' => (object)[
                    'id' => $rpeEntry->id,
                    'entry_date' => $entryDate,
                    'particulars' => $rpeEntry->current_particular_name,
                    'debit' => $debit,
                    'credit' => $credit,
                    'narration' => $rpeEntry->remarks,
                    'is_rpe_entry' => true,
                    'balance' => abs($runningBalance),
                ],
                'is_opening' => false,
                'balance' => abs($runningBalance),
                'balance_type' => $balanceType, // Transaction type: Cr for receipt, Dr for payment
            ];
        }



        $closingBalance = abs($runningBalance);
        $closingBalanceType = $runningBalance < 0 ? 'Cr' : 'Dr';
        $pages = collect($rows)->chunk(10);

        $pages = collect($pages); // assuming $pages is already a collection or array of chunks

        $previousClosingBalance = 0;

        $pages = $pages->map(function ($page, $index) use (&$previousClosingBalance) {
            $entries = $page; // original collection of rows

            $totalDebit = $entries->sum(fn($item) => (float)($item['entry']->debit ?? 0));
            $totalCredit = $entries->sum(fn($item) => (float)($item['entry']->credit ?? 0));

            $totalDebit += $previousClosingBalance < 0 ? abs($previousClosingBalance) : $previousClosingBalance;

            $closingBalance = $totalDebit - $totalCredit;

            if ($index === 0) {
                $openingBalance = 0;
            } else {
                $openingBalance = $previousClosingBalance;
            }

            $previousClosingBalance = $closingBalance;

            return [
                'entries' => $entries, // ✅ keep your rows here
                'total_debit' => $totalDebit,
                'total_credit' => $totalCredit,
                'opening_date' => $entries->first()['entry']->entry_date ?? null,
                'closing_date' => $entries->last()['entry']->entry_date ?? null,
                'closing_balance' => $closingBalance,
                'opening_balance' => $openingBalance,
                'page_number' => $index + 1,
            ];
        });


        // dd($rows);

        // dd($pages);

        return view('ledgers.print', compact('pages','ledger', 'rows', 'totalDebit', 'totalCredit', 'closingBalance', 'closingBalanceType'));
    }

    public function import(Ledger $ledger)
    {
        // Show selection page to choose Receipt & Payment account
        $accounts = ReceiptPaymentAccount::orderByDesc('period_from')->get();

        return view('ledgers.import', compact('ledger', 'accounts'));
    }

    public function processImport(Request $request, Ledger $ledger)
    {
        $request->validate([
            'receipt_payment_account_id' => ['required', 'exists:receipt_payment_accounts,id'],
        ]);

        $account = ReceiptPaymentAccount::findOrFail($request->receipt_payment_account_id);

        // Find all receipt_payment_entries by article_id or beneficiary_id matching ledger name
        $rpeEntries = ReceiptPaymentEntry::with(['article', 'beneficiary'])
            ->where('receipt_payment_account_id', $account->id)
            ->where(function ($query) use ($ledger) {
                $query->whereHas('article', function ($q) use ($ledger) {
                    $q->where('name', $ledger->name);
                })
                    ->orWhereHas('beneficiary', function ($q) use ($ledger) {
                        $q->where('name', $ledger->name);
                    });
            })
            ->get();

        if ($rpeEntries->isEmpty()) {
            return redirect()
                ->route('ledgers.import', $ledger)
                ->with('error', 'No matching entries found in the selected Receipt & Payment account.');
        }

        $importedCount = 0;
        $skippedCount = 0;

        foreach ($rpeEntries as $rpeEntry) {
            // Use ReceiptPaymentEntry's created_at for entry_date
            $entryDate = $rpeEntry->created_at->format('Y-m-d');
            $debit = $rpeEntry->type === 'receipt' ? $rpeEntry->amount : 0;
            $credit = $rpeEntry->type === 'payment' ? $rpeEntry->amount : 0;

            // Check if entry already exists by receipt_payment_entry_id (excluding tax entries)
            $existingEntry = LedgerEntry::where('ledger_id', $ledger->id)
                ->where('receipt_payment_entry_id', $rpeEntry->id)
                ->where(function ($query) {
                    $query->whereNull('narration')
                        ->orWhere('narration', 'NOT LIKE', 'TAX_ENTRY:%');
                })
                ->first();

            if ($existingEntry) {
                // Update existing entry with latest data
                $existingEntry->update([
                    'entry_date' => $entryDate,
                    'particulars' => $rpeEntry->current_particular_name,
                    'debit' => $debit,
                    'credit' => $credit,
                    'narration' => $rpeEntry->remarks,
                    'receipt_payment_entry_id' => $rpeEntry->id,
                ]);
                $importedCount++; // Count as imported since we updated it
            } else {
                // Create ledger entry using ReceiptPaymentEntry data
                LedgerEntry::create([
                    'ledger_id' => $ledger->id,
                    'receipt_payment_entry_id' => $rpeEntry->id,
                    'entry_date' => $entryDate,
                    'particulars' => $rpeEntry->current_particular_name,
                    'folio_no' => null,
                    'debit' => $debit,
                    'credit' => $credit,
                    'narration' => $rpeEntry->remarks,
                ]);

                $importedCount++;
            }

            // If there's a tax deduction, create/update tax entry separately
            if ($rpeEntry->tax_amount && $rpeEntry->tax_amount > 0 && $rpeEntry->tax_for) {
                $syncService = app(\App\Services\ReceiptPaymentSyncService::class);
                $syncService->syncTaxEntryToLedger($rpeEntry, $ledger);
            }
        }

        $message = "Import completed. {$importedCount} entries imported/updated from Receipt & Payment account: " . ($account->header_title ?? $account->name) . ".";

        return redirect()
            ->route('ledgers.show', $ledger)
            ->with('status', $message);
    }

    /**
     * Get entry date from receipt_payment_entry
     * Priority: date column -> extract from remarks -> created_at
     */
    private function getEntryDate($entry)
    {
        // First, try to use the date column (dd/mm/yyyy format)
        if (!empty($entry->date)) {
            try {
                // Parse dd/mm/yyyy format
                if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $entry->date, $matches)) {
                    $day = (int) $matches[1];
                    $month = (int) $matches[2];
                    $year = (int) $matches[3];
                    return \Carbon\Carbon::create($year, $month, $day);
                }
            } catch (\Exception $e) {
                // Fall through to next method
            }
        }

        // Second, try to extract from remarks
        $ppaDate = $this->extractPpaDate($entry->remarks);
        if ($ppaDate) {
            return $ppaDate;
        }

        // Finally, fall back to created_at
        return $entry->created_at;
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

    public function createAllFromActivities()
    {
        // Get all Articles (activities)
        $articles = Article::where('status', 1)->orderBy('name')->get();

        if ($articles->isEmpty()) {
            return redirect()
                ->route('ledgers.index')
                ->with('ledger_error', 'No active activities found.');
        }

        $createdCount = 0;
        $skippedCount = 0;

        foreach ($articles as $article) {
            // Check if ledger already exists for this article
            $existingLedger = Ledger::where('name', $article->name)->first();

            if ($existingLedger) {
                $skippedCount++;
                continue;
            }

            // Create new ledger from article
            Ledger::create([
                'name' => $article->name,
                'opening_balance' => 0,
                'opening_balance_type' => 'Dr',
                'description' => 'Auto-created from Activity: ' . $article->acode,
            ]);

            $createdCount++;
        }

        $message = "Created {$createdCount} new ledger(s) from activities. {$skippedCount} ledger(s) already existed and were skipped.";

        return redirect()
            ->route('ledgers.index')
            ->with('ledger_success', $message);
    }
}
