<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\ReceiptPaymentEntry;
use Illuminate\Http\Request;

class TaxLedgerController extends Controller
{
    public function index()
    {
        // Get all articles that have tax entries from PAYMENT type only
        $articleIds = ReceiptPaymentEntry::where('type', 'payment')
            ->whereNotNull('tax_amount')
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

        // Get R&P entries with tax deductions for this article - ONLY PAYMENT type
        // Order by id so we preserve storage order (receipt, payment, receipt, ...)
        $rpeEntries = ReceiptPaymentEntry::with(['article', 'beneficiary'])
            ->where('type', 'payment')
            ->where('article_id', $articleId)
            ->whereNotNull('tax_amount')
            ->where('tax_amount', '>', 0)
            ->whereNotNull('tax_for')
            ->orderBy('id')
            ->get();

        $runningBalance = 0; // Tax ledger starts with 0 balance
        $rows = [];
        $totalDebit = 0;
        $totalCredit = 0;

        // Process tax entries (keep storage order: one by one receipt, payment, receipt...)
        $processedEntries = [];
        foreach ($rpeEntries as $rpeEntry) {
            // Extract PPA date from date column, then remarks, fallback to created_at
            $entryDate = $this->getEntryDate($rpeEntry);
            
            // Check tax_type to determine debit or credit
            // tax_type 'dr' = debit, 'cr' = credit
            if ($rpeEntry->tax_type === 'dr') {
                $debit = $rpeEntry->tax_amount;
                $credit = 0;
            } else {
                // Default to credit if tax_type is 'cr' or null
                $debit = 0;
                $credit = $rpeEntry->tax_amount;
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

        // Sort by entry_date (PPA date), then by id
        usort($processedEntries, function ($a, $b) {
            $dateCompare = $a['entry_date']->timestamp <=> $b['entry_date']->timestamp;
            if ($dateCompare !== 0) {
                return $dateCompare;
            }
            return $a['entry']->id <=> $b['entry']->id;
        });

        // Within each date, show Credit then Debit then Credit then Debit (alternating)
        $processedEntries = $this->interleaveCreditsAndDebits($processedEntries);

        // Calculate running balance
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

        // Get R&P entries with tax deductions for this article - ONLY PAYMENT type
        // Order by id so we preserve storage order (receipt, payment, receipt, ...)
        $rpeEntries = ReceiptPaymentEntry::with(['article', 'beneficiary'])
            ->where('type', 'payment')
            ->where('article_id', $articleId)
            ->whereNotNull('tax_amount')
            ->where('tax_amount', '>', 0)
            ->whereNotNull('tax_for')
            ->orderBy('id')
            ->get();

        $runningBalance = 0; // Tax ledger starts with 0 balance
        $rows = [];
        $totalDebit = 0;
        $totalCredit = 0;

        // Process tax entries (keep storage order: one by one receipt, payment, receipt...)
        $processedEntries = [];
        foreach ($rpeEntries as $rpeEntry) {
            // Extract PPA date from date column, then remarks, fallback to created_at
            $entryDate = $this->getEntryDate($rpeEntry);
            
            // Check tax_type to determine debit or credit
            // tax_type 'dr' = debit, 'cr' = credit
            if ($rpeEntry->tax_type === 'dr') {
                $debit = $rpeEntry->tax_amount;
                $credit = 0;
            } else {
                // Default to credit if tax_type is 'cr' or null
                $debit = 0;
                $credit = $rpeEntry->tax_amount;
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

        // Sort by entry_date (PPA date), then by id
        usort($processedEntries, function ($a, $b) {
            $dateCompare = $a['entry_date']->timestamp <=> $b['entry_date']->timestamp;
            if ($dateCompare !== 0) {
                return $dateCompare;
            }
            return $a['entry']->id <=> $b['entry']->id;
        });

        // Within each date, show Credit then Debit then Credit then Debit (alternating)
        $processedEntries = $this->interleaveCreditsAndDebits($processedEntries);

        // Calculate running balance
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

        return view('tax_ledgers.print', compact('article', 'rows', 'totalDebit', 'totalCredit', 'closingBalance', 'closingBalanceType'));
    }

    /**
     * Within each date, reorder entries to alternate: Credit, Debit, Credit, Debit, ...
     */
    private function interleaveCreditsAndDebits(array $processedEntries): array
    {
        $byDate = [];
        foreach ($processedEntries as $item) {
            $ts = $item['entry_date']->timestamp;
            if (!isset($byDate[$ts])) {
                $byDate[$ts] = [];
            }
            $byDate[$ts][] = $item;
        }
        ksort($byDate);

        $result = [];
        foreach ($byDate as $dateEntries) {
            $credits = [];
            $debits = [];
            foreach ($dateEntries as $item) {
                if ($item['entry']->credit > 0) {
                    $credits[] = $item;
                } else {
                    $debits[] = $item;
                }
            }
            usort($credits, fn ($a, $b) => $a['entry']->id <=> $b['entry']->id);
            usort($debits, fn ($a, $b) => $a['entry']->id <=> $b['entry']->id);
            $max = max(count($credits), count($debits));
            for ($i = 0; $i < $max; $i++) {
                if (isset($credits[$i])) {
                    $result[] = $credits[$i];
                }
                if (isset($debits[$i])) {
                    $result[] = $debits[$i];
                }
            }
        }
        return $result;
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
