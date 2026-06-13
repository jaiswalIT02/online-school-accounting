<?php

namespace App\Services;

use App\Models\Article;
use App\Models\Beneficiary;
use App\Models\Cashbook;
use App\Models\CashbookEntry;
use App\Models\Ledger;
use App\Models\LedgerEntry;
use App\Models\ReceiptPaymentEntry;
use Carbon\Carbon;

class ReceiptPaymentSyncService
{
    /**
     * Sync a ReceiptPaymentEntry to Cashbooks
     */
    public function syncToCashbooks(ReceiptPaymentEntry $rpeEntry): void
    {
        $entryDate = $rpeEntry->created_at;
        $year = (int) $entryDate->format('Y');
        $monthNumber = (int) $entryDate->format('n'); // 1-12
        
        // Generate all possible month name variations
        $monthVariations = [
            $entryDate->format('F'),           // January
            $entryDate->format('M'),           // Jan
            $entryDate->format('F'),           // January (full)
            ucfirst(strtolower($entryDate->format('F'))), // january
            ucfirst(strtolower($entryDate->format('M'))), // jan
            $entryDate->format('m'),           // 01-12
            (string) $monthNumber,             // 1-12
        ];

        // Find cashbooks that match the period
        $cashbooks = Cashbook::where('period_year', $year)
            ->where(function($query) use ($monthVariations) {
                foreach ($monthVariations as $month) {
                    $query->orWhere('period_month', $month);
                }
            })
            ->get();

        foreach ($cashbooks as $cashbook) {
            $this->syncEntryToCashbook($rpeEntry, $cashbook);
        }
    }

    /**
     * Sync a ReceiptPaymentEntry to a specific Cashbook
     */
    public function syncEntryToCashbook(ReceiptPaymentEntry $rpeEntry, Cashbook $cashbook): void
    {
        $entryDate = $rpeEntry->created_at->format('Y-m-d');
        $currentParticulars = $rpeEntry->current_particular_name;
        
        // Find existing entry by receipt_payment_entry_id
        $existingEntry = CashbookEntry::where('cashbook_id', $cashbook->id)
            ->where('receipt_payment_entry_id', $rpeEntry->id)
            ->first();

        if ($existingEntry) {
            // Update existing entry
            $existingEntry->update([
                'entry_date' => $entryDate,
                'particulars' => $currentParticulars,
                'bank_amount' => $rpeEntry->amount,
                'narration' => $rpeEntry->remarks,
                'tax_amount' => $rpeEntry->tax_amount,
                'tax_for' => $rpeEntry->tax_for,
                'tax_remark' => $rpeEntry->tax_remark,
            ]);
        } else {
            // Create new entry
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
        }
    }

    /**
     * Sync a ReceiptPaymentEntry to Ledgers
     */
    public function syncToLedgers(ReceiptPaymentEntry $rpeEntry): void
    {
        // Find ledgers by article_id or beneficiary_id - get the name from relationship
        $ledgers = collect();
        
        if ($rpeEntry->article_id && $rpeEntry->article) {
            $ledgers = Ledger::where('name', $rpeEntry->article->name)->get();
        } elseif ($rpeEntry->beneficiary_id && $rpeEntry->beneficiary) {
            $ledgers = Ledger::where('name', $rpeEntry->beneficiary->name)->get();
        }

        foreach ($ledgers as $ledger) {
            $this->syncEntryToLedger($rpeEntry, $ledger);
            
            // If there's a tax deduction, create a separate tax entry
            if ($rpeEntry->tax_amount && $rpeEntry->tax_amount > 0 && $rpeEntry->tax_for) {
                $this->syncTaxEntryToLedger($rpeEntry, $ledger);
            }
        }
    }

    /**
     * Sync a ReceiptPaymentEntry to a specific Ledger
     */
    private function syncEntryToLedger(ReceiptPaymentEntry $rpeEntry, Ledger $ledger): void
    {
        $entryDate = $rpeEntry->created_at->format('Y-m-d');
        $debit = $rpeEntry->type === 'receipt' ? $rpeEntry->amount : 0;
        $credit = $rpeEntry->type === 'payment' ? $rpeEntry->amount : 0;
        $currentParticulars = $rpeEntry->current_particular_name;
        
        // Find existing entry by receipt_payment_entry_id (excluding tax entries)
        $existingEntry = LedgerEntry::where('ledger_id', $ledger->id)
            ->where('receipt_payment_entry_id', $rpeEntry->id)
            ->where(function($query) {
                $query->whereNull('narration')
                      ->orWhere('narration', 'NOT LIKE', 'TAX_ENTRY:%');
            })
            ->first();

        if ($existingEntry) {
            // Update existing entry
            $existingEntry->update([
                'entry_date' => $entryDate,
                'particulars' => $currentParticulars,
                'debit' => $debit,
                'credit' => $credit,
                'narration' => $rpeEntry->remarks,
            ]);
        } else {
            // Create new entry
            LedgerEntry::create([
                'ledger_id' => $ledger->id,
                'receipt_payment_entry_id' => $rpeEntry->id,
                'entry_date' => $entryDate,
                'particulars' => $currentParticulars,
                'folio_no' => null,
                'debit' => $debit,
                'credit' => $credit,
                'narration' => $rpeEntry->remarks,
            ]);
        }
    }

    /**
     * Sync tax deduction as a separate entry to Ledger
     */
    public function syncTaxEntryToLedger(ReceiptPaymentEntry $rpeEntry, Ledger $ledger): void
    {
        $entryDate = $rpeEntry->created_at->format('Y-m-d');
        $taxAmount = $rpeEntry->tax_amount;
        $taxType = strtoupper($rpeEntry->tax_for); // TDS or PTAX
        
        // For tax entries:
        // - If it's a receipt: tax is deducted, so create credit entry (liability/payable)
        // - If it's a payment: tax is deducted, so create debit entry (asset/receivable)
        $debit = $rpeEntry->type === 'payment' ? $taxAmount : 0;
        $credit = $rpeEntry->type === 'receipt' ? $taxAmount : 0;
        
        // Create tax particulars name
        $taxParticulars = $taxType . ' - ' . $rpeEntry->current_particular_name;
        if ($rpeEntry->tax_remark) {
            $taxParticulars .= ' (' . $rpeEntry->tax_remark . ')';
        }
        
        // Create tax narration identifier
        $taxNarration = 'TAX_ENTRY:' . $rpeEntry->id . ':' . $taxType;
        if ($rpeEntry->tax_remark) {
            $taxNarration .= ' - ' . $rpeEntry->tax_remark;
        }

        // Try to find existing tax entry
        $existingTaxEntry = LedgerEntry::where('ledger_id', $ledger->id)
            ->where('entry_date', $entryDate)
            ->where('particulars', $taxParticulars)
            ->where('debit', $debit)
            ->where('credit', $credit)
            ->where('narration', 'LIKE', 'TAX_ENTRY:%')
            ->first();

        // If not found, try matching by tax identifier in narration
        if (!$existingTaxEntry) {
            $existingTaxEntry = LedgerEntry::where('ledger_id', $ledger->id)
                ->where('entry_date', $entryDate)
                ->where('narration', 'LIKE', 'TAX_ENTRY:' . $rpeEntry->id . ':%')
                ->first();
        }

        if ($existingTaxEntry) {
            // Update existing tax entry
            $existingTaxEntry->update([
                'entry_date' => $entryDate,
                'particulars' => $taxParticulars,
                'debit' => $debit,
                'credit' => $credit,
                'narration' => $taxNarration,
            ]);
        } else {
            // Create new tax entry
            LedgerEntry::create([
                'ledger_id' => $ledger->id,
                'entry_date' => $entryDate,
                'particulars' => $taxParticulars,
                'folio_no' => null,
                'debit' => $debit,
                'credit' => $credit,
                'narration' => $taxNarration,
            ]);
        }
    }

    /**
     * Remove a ReceiptPaymentEntry from Cashbooks
     */
    public function removeFromCashbooks(ReceiptPaymentEntry $rpeEntry): void
    {
        // Delete entries by receipt_payment_entry_id
        CashbookEntry::where('receipt_payment_entry_id', $rpeEntry->id)->delete();
    }

    /**
     * Remove a ReceiptPaymentEntry from Ledgers
     */
    public function removeFromLedgers(ReceiptPaymentEntry $rpeEntry): void
    {
        // Delete main entries by receipt_payment_entry_id (excluding tax entries)
        LedgerEntry::where('receipt_payment_entry_id', $rpeEntry->id)
            ->where(function($query) {
                $query->whereNull('narration')
                      ->orWhere('narration', 'NOT LIKE', 'TAX_ENTRY:%');
            })
            ->delete();

        // Also remove tax entries if they exist
        if ($rpeEntry->tax_amount && $rpeEntry->tax_amount > 0) {
            LedgerEntry::where('narration', 'LIKE', 'TAX_ENTRY:' . $rpeEntry->id . ':%')
                ->delete();
        }
    }
}
