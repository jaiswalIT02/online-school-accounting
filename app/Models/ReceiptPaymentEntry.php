<?php

namespace App\Models;

use App\Models\Traits\AutoAssignSessionYear;
use App\Models\Traits\HasSessionYear;
use App\Services\ReceiptPaymentSyncService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReceiptPaymentEntry extends Model
{
    use HasFactory, HasSessionYear, AutoAssignSessionYear;

    protected $fillable = [
        'receipt_payment_account_id',
        'type',
        'particular_name',
        'acode',
        'article_id',
        'beneficiary_id',
        'amount',
        'remarks',
        'date',
        'tax_amount',
        'tax_for',
        'tax_type',
        'tax_remark',
        'pair_id',
    ];

    protected static function booted()
    {
        // Sync particular_name and acode from relationships before saving
        static::saving(function ($entry) {
            $entry->syncNameFromRelationship();
        });

        // Sync to cashbook and ledger when entry is created
        static::created(function ($entry) {
            $syncService = app(ReceiptPaymentSyncService::class);
            $syncService->syncToCashbooks($entry);
            $syncService->syncToLedgers($entry);
        });

        // Sync to cashbook and ledger when entry is updated
        static::updated(function ($entry) {
            $syncService = app(ReceiptPaymentSyncService::class);
            $syncService->syncToCashbooks($entry);
            $syncService->syncToLedgers($entry);
        });

        // Remove from cashbook and ledger when entry is deleted
        static::deleted(function ($entry) {
            $syncService = app(ReceiptPaymentSyncService::class);
            $syncService->removeFromCashbooks($entry);
            $syncService->removeFromLedgers($entry);
        });
    }

    /**
     * Sync particular_name and acode from article or beneficiary relationship
     */
    public function syncNameFromRelationship()
    {
        // Only sync if we have an ID and the name/acode might be outdated
        if ($this->article_id) {
            $article = $this->relationLoaded('article') ? $this->article : Article::find($this->article_id);
            if ($article) {
                $this->particular_name = $article->name;
                $this->acode = $article->acode;
            }
        } elseif ($this->beneficiary_id) {
            $beneficiary = $this->relationLoaded('beneficiary') ? $this->beneficiary : Beneficiary::find($this->beneficiary_id);
            if ($beneficiary) {
                $this->particular_name = $beneficiary->name;
                $this->acode = $beneficiary->acode;
            }
        }
    }

    public function account()
    {
        return $this->belongsTo(ReceiptPaymentAccount::class, 'receipt_payment_account_id');
    }

    /**
     * Get paired entries by transaction ID (pair_id)
     * Returns entries with the same pair_id (transaction ID) and opposite type
     */
    public function getPairedEntries()
    {
        if (!$this->pair_id) {
            return collect([]);
        }
        
        $otherType = $this->type === 'payment' ? 'receipt' : 'payment';
        
        return self::where('pair_id', $this->pair_id)
            ->where('type', $otherType)
            ->where('id', '!=', $this->id)
            ->where('receipt_payment_account_id', $this->receipt_payment_account_id)
            ->get();
    }

    public function article()
    {
        return $this->belongsTo(Article::class);
    }

    public function beneficiary()
    {
        return $this->belongsTo(Beneficiary::class);
    }

    /**
     * Get the current particular name from relationship if available
     * Always uses relationship when article_id or beneficiary_id exists
     */
    public function getCurrentParticularNameAttribute()
    {
        if ($this->article_id) {
            // Load relationship if not already loaded
            if (!$this->relationLoaded('article')) {
                $this->load('article');
            }
            if ($this->article) {
                return $this->article->name;
            }
        } elseif ($this->beneficiary_id) {
            // Load relationship if not already loaded
            if (!$this->relationLoaded('beneficiary')) {
                $this->load('beneficiary');
            }
            if ($this->beneficiary) {
                return $this->beneficiary->name;
            }
        }
        // Fallback to stored name only if no relationship exists
        return $this->particular_name;
    }

    /**
     * Get the current acode from relationship if available
     * Always uses relationship when article_id or beneficiary_id exists
     */
    public function getCurrentAcodeAttribute()
    {
        if ($this->article_id) {
            // Load relationship if not already loaded
            if (!$this->relationLoaded('article')) {
                $this->load('article');
            }
            if ($this->article) {
                return $this->article->acode;
            }
        } elseif ($this->beneficiary_id) {
            // Load relationship if not already loaded
            if (!$this->relationLoaded('beneficiary')) {
                $this->load('beneficiary');
            }
            if ($this->beneficiary) {
                return $this->beneficiary->acode;
            }
        }
        // Fallback to stored acode only if no relationship exists
        return $this->acode;
    }

}
