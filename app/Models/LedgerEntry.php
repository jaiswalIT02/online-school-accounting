<?php

namespace App\Models;

use App\Models\Traits\AutoAssignSessionYear;
use App\Models\Traits\HasSessionYear;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LedgerEntry extends Model
{
    use HasFactory, HasSessionYear, AutoAssignSessionYear;

    protected $fillable = [
        'ledger_id',
        'entry_date',
        'particulars',
        'folio_no',
        'debit',
        'credit',
        'narration',
        'receipt_payment_entry_id',
    ];

    protected $casts = [
        'entry_date' => 'date',
    ];

    public function ledger()
    {
        return $this->belongsTo(Ledger::class);
    }

    public function receiptPaymentEntry()
    {
        return $this->belongsTo(ReceiptPaymentEntry::class, 'receipt_payment_entry_id');
    }

    /**
     * Get the current particulars from relationship if available
     * Always uses relationship when receipt_payment_entry_id exists
     */
    public function getCurrentParticularsAttribute()
    {
        if ($this->receipt_payment_entry_id) {
            // Load relationship if not already loaded
            if (!$this->relationLoaded('receiptPaymentEntry')) {
                $this->load('receiptPaymentEntry.article', 'receiptPaymentEntry.beneficiary');
            }
            if ($this->receiptPaymentEntry) {
                return $this->receiptPaymentEntry->current_particular_name;
            }
        }
        // Fallback to stored particulars only if no relationship exists
        return $this->particulars;
    }
}
