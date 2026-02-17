<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\ReceiptPaymentEntry;
use Illuminate\Http\Request;

class TaxLedgerController extends Controller
{
    public function index()
    {
        // Get all articles that have tax entries (receipt or payment)
        $articleIds = ReceiptPaymentEntry::whereNotNull('tax_amount')
            ->where('tax_amount', '>', 0)
            ->whereNotNull('tax_for')
            ->whereNotNull('article_id')
            ->distinct()
            ->pluck('article_id');
        
        $articles = Article::whereIn('id', $articleIds)
            ->orderBy('name')
            ->get();

        return view('tax_ledgers.index', compact('articles'));
    }

    public function show($articleId)
    {
        $article = Article::findOrFail($articleId);

        // Get R&P entries with tax deductions for this article (both receipt and payment)
        $rpeEntries = ReceiptPaymentEntry::with(['article', 'beneficiary'])
            ->where('article_id', $articleId)
            ->whereNotNull('tax_amount')
            ->where('tax_amount', '>', 0)
            ->whereNotNull('tax_for')
            ->orderBy('id')
            ->get();

        $runningBalance = 0;
        $rows = [];
        $totalDebit = 0;
        $totalCredit = 0;

        // Process: receipt = Credit side, payment = Debit side (ignore tax_type)
        $processedEntries = [];
        foreach ($rpeEntries as $rpeEntry) {
            $entryDate = $this->getEntryDate($rpeEntry);

            if ($rpeEntry->type === 'receipt') {
                $debit = 0;
                $credit = $rpeEntry->tax_amount;
            } else {
                $debit = $rpeEntry->tax_amount;
                $credit = 0;
            }

            $taxParticulars = strtoupper($rpeEntry->tax_for) . ' - ' . $rpeEntry->current_particular_name;
            if ($rpeEntry->tax_remark) {
                $taxParticulars .= ' (' . $rpeEntry->tax_remark . ')';
            }

            $processedEntries[] = [
                'entry_date' => $entryDate,
                'entry' => (object)[
                    'id' => $rpeEntry->id,
                    'entry_date' => $entryDate,
                    'particulars' => $taxParticulars,
                    'debit' => $debit,
                    'credit' => $credit,
                    'narration' => $rpeEntry->remarks,
                    'tax_for' => $rpeEntry->tax_for,
                    'tax_amount' => $rpeEntry->tax_amount,
                    'tax_remark' => $rpeEntry->tax_remark,
                    'is_rpe_entry' => true,
                ],
            ];
        }

        // Show data in same order as stored in database (orderBy id)
        foreach ($processedEntries as $item) {
            $entry = $item['entry'];
            $runningBalance += $entry->debit;
            $runningBalance -= $entry->credit;
            $totalDebit += $entry->debit;
            $totalCredit += $entry->credit;

            $rows[] = [
                'entry' => $entry,
                'balance' => abs($runningBalance),
                'balance_type' => $runningBalance < 0 ? 'Cr' : 'Dr',
            ];
        }

        $closingBalance = abs($runningBalance);
        $closingBalanceType = $runningBalance < 0 ? 'Cr' : 'Dr';

        return view('tax_ledgers.show', compact('article', 'rows', 'totalDebit', 'totalCredit', 'closingBalance', 'closingBalanceType'));
    }

    public function print($articleId)
    {
        $article = Article::findOrFail($articleId);

        // Get R&P entries with tax deductions for this article (both receipt and payment)
        $rpeEntries = ReceiptPaymentEntry::with(['article', 'beneficiary'])
            ->where('article_id', $articleId)
            ->whereNotNull('tax_amount')
            ->where('tax_amount', '>', 0)
            ->whereNotNull('tax_for')
            ->orderBy('id')
            ->get();

        $runningBalance = 0;
        $rows = [];
        $totalDebit = 0;
        $totalCredit = 0;

        // Process: receipt = Credit side, payment = Debit side (ignore tax_type)
        $processedEntries = [];
        foreach ($rpeEntries as $rpeEntry) {
            $entryDate = $this->getEntryDate($rpeEntry);

            if ($rpeEntry->type === 'receipt') {
                $debit = 0;
                $credit = $rpeEntry->tax_amount;
            } else {
                $debit = $rpeEntry->tax_amount;
                $credit = 0;
            }

            $taxParticulars = strtoupper($rpeEntry->tax_for) . ' - ' . $rpeEntry->current_particular_name;
            if ($rpeEntry->tax_remark) {
                $taxParticulars .= ' (' . $rpeEntry->tax_remark . ')';
            }

            $processedEntries[] = [
                'entry_date' => $entryDate,
                'entry' => (object)[
                    'id' => $rpeEntry->id,
                    'entry_date' => $entryDate,
                    'particulars' => $taxParticulars,
                    'debit' => $debit,
                    'credit' => $credit,
                    'narration' => $rpeEntry->remarks,
                    'tax_for' => $rpeEntry->tax_for,
                    'tax_amount' => $rpeEntry->tax_amount,
                    'tax_remark' => $rpeEntry->tax_remark,
                    'is_rpe_entry' => true,
                ],
            ];
        }

        // Show data in same order as stored in database (orderBy id)
        foreach ($processedEntries as $item) {
            $entry = $item['entry'];
            $runningBalance += $entry->debit;
            $runningBalance -= $entry->credit;
            $totalDebit += $entry->debit;
            $totalCredit += $entry->credit;

            $rows[] = [
                'entry' => $entry,
                'is_opening' => false,
                'balance' => abs($runningBalance),
                'balance_type' => $runningBalance < 0 ? 'Cr' : 'Dr',
            ];
        }

        $closingBalance = abs($runningBalance);
        $closingBalanceType = $runningBalance < 0 ? 'Cr' : 'Dr';

        $pages = collect($rows)->chunk(2);

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
                'closing_balance' => $closingBalance,
                'opening_balance' => $openingBalance,
                'page_number' => $index + 1,
            ];
        });
        // dd($pages);

        return view('tax_ledgers.print', compact('pages','article', 'rows', 'totalDebit', 'totalCredit', 'closingBalance', 'closingBalanceType'));
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
}
