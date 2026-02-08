<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Beneficiary;
use App\Models\ReceiptPaymentAccount;
use App\Models\ReceiptPaymentEntry;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ReceiptPaymentEntryController extends Controller
{
    public function create(ReceiptPaymentAccount $receipt_payment)
    {
        $type = request('type', 'both');
        $articles = Article::orderBy('name')->get();
        $beneficiaries = Beneficiary::orderBy('name')->get();

        return view('receipt_payment_entries.create', compact(
            'receipt_payment',
            'type',
            'articles',
            'beneficiaries'
        ));
    }

    public function store(Request $request, ReceiptPaymentAccount $receipt_payment)
    {
        $data = $this->validateEntry($request);
        $data['receipt_payment_account_id'] = $receipt_payment->id;

        // Extract transaction ID from remarks (format: "Txn ID: 32937166")
        $txnId = null;
        if ($data['remarks'] && preg_match('/Txn ID:\s*(\d+)/i', $data['remarks'], $matches)) {
            $txnId = $matches[1];
        }
        
        // Use date from input if provided, otherwise extract PPA date from remarks
        $ppaDate = !empty($data['date']) ? $data['date'] : $this->extractPpaDateFromRemarks($data['remarks'] ?? null);

        // If type is 'both', create both payment and receipt entries
        if ($data['type'] === 'both') {
            $entryData = [
                'receipt_payment_account_id' => $receipt_payment->id,
                'article_id' => $data['article_id'] ?? null,
                'beneficiary_id' => $data['beneficiary_id'] ?? null,
                'particular_name' => $data['particular_name'],
                'acode' => $data['acode'],
                'amount' => $data['amount'],
                'remarks' => $data['remarks'] ?? null,
                'date' => $ppaDate, // Store date in dd/mm/yyyy format
                'tax_amount' => $data['tax_amount'] ?? null,
                'tax_for' => $data['tax_for'] ?? null,
                'tax_type' => $data['tax_type'] ?? null,
                'tax_remark' => $data['tax_remark'] ?? null,
                'pair_id' => $txnId, // Store transaction ID in pair_id
            ];

            // Create receipt entry first
            $receiptEntry = $receipt_payment->entries()->create(array_merge($entryData, [
                'type' => 'receipt',
            ]));
            
            // Create payment entry with same transaction ID in pair_id
            $paymentEntry = $receipt_payment->entries()->create(array_merge($entryData, ['type' => 'payment']));

            return redirect()
                ->route('receipt_payments.show', $receipt_payment)
                ->with('status', 'Entry added as both payment and receipt.');
        } else {
            // Single entry (payment or receipt) - store transaction ID in pair_id if available
            if ($txnId) {
                $data['pair_id'] = $txnId;
            }
            // Add date to data (from input or extracted from remarks)
            $data['date'] = $ppaDate;
            $receipt_payment->entries()->create($data);

            return redirect()
                ->route('receipt_payments.show', $receipt_payment)
                ->with('status', 'Entry added.');
        }
    }

    public function edit(ReceiptPaymentEntry $entry)
    {
        $receipt_payment = $entry->account;
        $articles = Article::orderBy('name')->get();
        $beneficiaries = Beneficiary::orderBy('name')->get();
        
        // Load relationships to ensure acode is available
        $entry->load(['article', 'beneficiary']);

        return view('receipt_payment_entries.edit', compact(
            'receipt_payment',
            'entry',
            'articles',
            'beneficiaries'
        ));
    }

    public function update(Request $request, ReceiptPaymentEntry $entry)
    {
        $data = $this->validateEntry($request);
        $account = $entry->account;

        // Store current entry details BEFORE updating
        $currentType = $entry->type;
        $otherType = $currentType === 'payment' ? 'receipt' : 'payment';

        // Extract transaction ID from remarks (format: "Txn ID: 32937166")
        $txnId = null;
        if ($data['remarks'] && preg_match('/Txn ID:\s*(\d+)/i', $data['remarks'], $matches)) {
            $txnId = $matches[1];
        }
        
        // Use date from input if provided, otherwise extract PPA date from remarks
        $ppaDate = !empty($data['date']) ? $data['date'] : $this->extractPpaDateFromRemarks($data['remarks'] ?? null);

        // Find corresponding entry FIRST, before updating current entry
        $correspondingEntry = null;
        
        if ($data['type'] === 'both') {
            // Find by pair_id (transaction ID) - entries with same pair_id and opposite type
            if ($txnId) {
                $correspondingEntry = $account->entries()
                    ->where('type', $otherType)
                    ->where('pair_id', $txnId)
                    ->where('id', '!=', $entry->id)
                    ->first();
            }
            
            // Fallback: If no transaction ID in new remarks, try using existing pair_id
            if (!$correspondingEntry && $entry->pair_id) {
                $correspondingEntry = $account->entries()
                    ->where('type', $otherType)
                    ->where('pair_id', $entry->pair_id)
                    ->where('id', '!=', $entry->id)
                    ->first();
            }
        }

        // Update current entry
        $updateData = [
            'article_id' => $data['article_id'] ?? null,
            'beneficiary_id' => $data['beneficiary_id'] ?? null,
            'particular_name' => $data['particular_name'],
            'acode' => $data['acode'],
            'amount' => $data['amount'],
            'remarks' => $data['remarks'] ?? null,
            'date' => $ppaDate, // Store PPA date in dd/mm/yyyy format
            'tax_amount' => $data['tax_amount'] ?? null,
            'tax_for' => $data['tax_for'] ?? null,
            'tax_type' => $data['tax_type'] ?? null,
            'tax_remark' => $data['tax_remark'] ?? null,
        ];

        // Store transaction ID in pair_id if available
        if ($txnId) {
            $updateData['pair_id'] = $txnId;
        }

        // Handle type changes based on user selection
        if ($data['type'] === 'both') {
            // If user selects 'both', keep current type but ensure corresponding entry exists
            // Don't change the type of current entry
        } else {
            // If user selects 'receipt' or 'payment', update the type
            $updateData['type'] = $data['type'];
            // Clear pair_id if converting from 'both' to single entry
            if (!$txnId) {
                $updateData['pair_id'] = null;
            }
        }

        $entry->update($updateData);

        // Only update corresponding entry if user selected 'both'
        if ($data['type'] === 'both') {
            if ($correspondingEntry) {
                // Update existing corresponding entry (DO NOT CREATE NEW)
                $correspondingEntry->update([
                    'article_id' => $data['article_id'] ?? null,
                    'beneficiary_id' => $data['beneficiary_id'] ?? null,
                    'particular_name' => $data['particular_name'],
                    'acode' => $data['acode'],
                    'amount' => $data['amount'],
                    'remarks' => $data['remarks'] ?? null,
                    'date' => $ppaDate, // Store date in dd/mm/yyyy format
                    'tax_amount' => $data['tax_amount'] ?? null,
                    'tax_for' => $data['tax_for'] ?? null,
                    'tax_type' => $data['tax_type'] ?? null,
                    'tax_remark' => $data['tax_remark'] ?? null,
                    'pair_id' => $txnId, // Update pair_id with transaction ID
                ]);
                
                $message = 'Entry updated. Corresponding entry on the other side also updated.';
            } else {
                // No corresponding entry found - only update current entry
                // Do not create new entry when editing
                $message = 'Entry updated. No corresponding entry found to update.';
            }
        } else {
            $message = 'Entry updated.';
        }

        return redirect()
            ->route('receipt_payments.show', $account)
            ->with('status', $message);
    }

    public function destroy(ReceiptPaymentEntry $entry)
    {
        $account = $entry->account;

        // pair_id now stores transaction ID, not entry ID, so no need to update other entries
        $entry->delete();

        return redirect()
            ->route('receipt_payments.show', $account)
            ->with('status', 'Entry deleted.');
    }

    public function bulkDestroy(Request $request)
    {
        $ids = $request->input('ids', []);
        if (! is_array($ids)) {
            $ids = array_filter([$ids]);
        }
        $ids = array_values(array_filter(array_map('intval', $ids)));

        if (empty($ids)) {
            return redirect()->back()->with('status', 'No entries selected.');
        }

        $entries = ReceiptPaymentEntry::whereIn('id', $ids)->get();
        $account = $entries->first()->account ?? null;
        if (! $account) {
            return redirect()->back()->with('status', 'Invalid selection.');
        }
        foreach ($entries as $entry) {
            if ((int) $entry->receipt_payment_account_id === (int) $account->id) {
                $entry->delete();
            }
        }

        $count = $entries->count();
        return redirect()
            ->route('receipt_payments.show', $account)
            ->with('status', $count === 1 ? 'Entry deleted.' : "{$count} entries deleted.");
    }

    public function bulkEdit(Request $request)
    {
        $ids = $request->input('ids', []);
        if (! is_array($ids)) {
            $ids = array_filter([$ids]);
        }
        $ids = array_values(array_filter(array_map('intval', $ids)));

        if (empty($ids)) {
            return redirect()->back()->with('status', 'No entries selected.');
        }

        $entries = ReceiptPaymentEntry::with(['article', 'beneficiary'])
            ->whereIn('id', $ids)
            ->orderBy('id')
            ->get();
        $account = $entries->first()->account ?? null;
        if (! $account) {
            return redirect()->back()->with('status', 'Invalid selection.');
        }
        $entries = $entries->filter(fn ($e) => (int) $e->receipt_payment_account_id === (int) $account->id)->values();
        if ($entries->isEmpty()) {
            return redirect()->back()->with('status', 'Invalid selection.');
        }

        $entry = $entries->first();
        $articles = Article::orderBy('name')->get();
        $beneficiaries = Beneficiary::orderBy('name')->get();

        return view('receipt_payment_entries.bulk_edit', [
            'receipt_payment' => $account,
            'entries' => $entries,
            'entry' => $entry,
            'articles' => $articles,
            'beneficiaries' => $beneficiaries,
        ]);
    }

    public function bulkUpdate(Request $request)
    {
        $ids = $request->input('ids', []);
        if (! is_array($ids)) {
            $ids = array_filter([$ids]);
        }
        $ids = array_values(array_filter(array_map('intval', $ids)));

        if (empty($ids)) {
            return redirect()->back()->with('status', 'No entries selected.');
        }

        $entries = ReceiptPaymentEntry::whereIn('id', $ids)->get();
        $account = $entries->first()->account ?? null;
        if (! $account) {
            return redirect()->back()->with('status', 'Invalid selection.');
        }
        $entries = $entries->filter(fn ($e) => (int) $e->receipt_payment_account_id === (int) $account->id)->values();
        if ($entries->isEmpty()) {
            return redirect()->back()->with('status', 'Invalid selection.');
        }

        // Bulk update: only Component, Vendor, Amount, Tax fields (no remarks/date/pair_id so transaction ID is preserved)
        $data = $this->validateBulkUpdateEntry($request);

        $updateData = [
            'article_id' => $data['article_id'] ?? null,
            'beneficiary_id' => $data['beneficiary_id'] ?? null,
            'particular_name' => $data['particular_name'],
            'acode' => $data['acode'],
            'amount' => $data['amount'],
            'tax_amount' => $data['tax_amount'] ?? null,
            'tax_for' => $data['tax_for'] ?? null,
            'tax_type' => $data['tax_type'] ?? null,
            'tax_remark' => $data['tax_remark'] ?? null,
        ];
        // Do NOT update: remarks, date, pair_id (transaction ID stays unchanged)

        $selectedIds = $entries->pluck('id')->all();
        foreach ($entries as $entry) {
            $entry->update($updateData);
        }

        // Update paired entries on the other side (same pair_id, opposite type)
        $pairedIds = [];
        foreach ($entries as $entry) {
            if (empty($entry->pair_id)) {
                continue;
            }
            $otherType = $entry->type === 'receipt' ? 'payment' : 'receipt';
            $paired = $account->entries()
                ->where('type', $otherType)
                ->where('pair_id', $entry->pair_id)
                ->whereNotIn('id', $selectedIds)
                ->first();
            if ($paired && ! in_array($paired->id, $pairedIds, true)) {
                $pairedIds[] = $paired->id;
                $paired->update($updateData);
            }
        }

        $totalUpdated = $entries->count() + count($pairedIds);
        $message = $totalUpdated === 1
            ? 'Entry updated.'
            : "{$totalUpdated} entries updated (including paired on the other side).";
        return redirect()
            ->route('receipt_payments.show', $account)
            ->with('status', $message);
    }

    /**
     * Validate only fields allowed in bulk update: Component, Vendor, Amount, Tax fields.
     * Does not validate or use type, date, remarks (transaction ID is preserved).
     */
    private function validateBulkUpdateEntry(Request $request): array
    {
        $data = $request->validate([
            'article_ref' => ['nullable', 'string', 'max:255'],
            'beneficiary_ref' => ['nullable', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'tax_for' => ['nullable', 'in:tds,pTax'],
            'tax_type' => ['nullable', 'in:dr,cr'],
            'tax_remark' => ['nullable', 'string', 'max:1000'],
        ]);

        $articleRef = $data['article_ref'] ?? null;
        $beneficiaryRef = $data['beneficiary_ref'] ?? null;

        if (! $articleRef) {
            throw ValidationException::withMessages([
                'article_ref' => 'Component is required.',
            ]);
        }

        $articleParsed = $this->parseReference($articleRef, 'article');
        if (! $articleParsed) {
            throw ValidationException::withMessages([
                'article_ref' => 'Choose a valid component.',
            ]);
        }

        $beneficiaryParsed = null;
        if ($beneficiaryRef) {
            $beneficiaryParsed = $this->parseReference($beneficiaryRef, 'beneficiary');
            if (! $beneficiaryParsed) {
                throw ValidationException::withMessages([
                    'beneficiary_ref' => 'Choose a valid vendor.',
                ]);
            }
        }

        $data['article_id'] = $articleParsed['id'];
        $data['particular_name'] = $articleParsed['name'];
        $data['acode'] = $articleParsed['acode'];
        $data['beneficiary_id'] = $beneficiaryParsed ? $beneficiaryParsed['id'] : null;
        unset($data['article_ref'], $data['beneficiary_ref']);

        return $data;
    }

    private function validateEntry(Request $request): array
    {
        $data = $request->validate([
            'type' => ['required', 'in:receipt,payment,both'],
            'article_ref' => ['nullable', 'string', 'max:255'],
            'beneficiary_ref' => ['nullable', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'date' => ['nullable', 'string', 'regex:/^\d{2}\/\d{2}\/\d{4}$/'],
            'remarks' => ['nullable', 'string', 'max:1000'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'tax_for' => ['nullable', 'in:tds,pTax'],
            'tax_type' => ['nullable', 'in:dr,cr'],
            'tax_remark' => ['nullable', 'string', 'max:1000'],
        ]);

        $articleRef = $data['article_ref'] ?? null;
        $beneficiaryRef = $data['beneficiary_ref'] ?? null;

        // Component is required, vendor is optional
        if (!$articleRef) {
            throw ValidationException::withMessages([
                'article_ref' => 'Component is required.',
            ]);
        }

        // Parse component (required)
        $articleParsed = $this->parseReference($articleRef, 'article');
        if (! $articleParsed) {
            throw ValidationException::withMessages([
                'article_ref' => 'Choose a valid component.',
            ]);
        }

        // Parse vendor (optional)
        $beneficiaryParsed = null;
        if ($beneficiaryRef) {
            $beneficiaryParsed = $this->parseReference($beneficiaryRef, 'beneficiary');
            if (! $beneficiaryParsed) {
                throw ValidationException::withMessages([
                    'beneficiary_ref' => 'Choose a valid vendor.',
                ]);
            }
        }

        // Save IDs and name/acode
        // Component is always saved
        $data['article_id'] = $articleParsed['id'];
        $data['particular_name'] = $articleParsed['name'];
        $data['acode'] = $articleParsed['acode'];
        
        // Vendor is optional
        if ($beneficiaryParsed) {
            $data['beneficiary_id'] = $beneficiaryParsed['id'];
        } else {
            $data['beneficiary_id'] = null;
        }
        unset($data['article_ref'], $data['beneficiary_ref']);

        return $data;
    }

    private function parseReference(?string $value, string $type = 'article'): ?array
    {
        if (! $value) {
            return null;
        }

        $parts = explode('||', $value, 2);
        $name = trim($parts[0] ?? '');
        $acode = trim($parts[1] ?? '');

        // Component (article) requires both name and acode
        if ($type === 'article') {
            if ($name === '' || $acode === '') {
                return null;
            }
            $item = Article::where('name', $name)->where('acode', $acode)->first();
        } else {
            // Vendor (beneficiary): name is required, acode can be null/empty
            if ($name === '') {
                return null;
            }
            if ($acode === '') {
                $item = Beneficiary::where('name', $name)
                    ->where(function ($q) {
                        $q->whereNull('acode')->orWhere('acode', '');
                    })
                    ->first();
            } else {
                $item = Beneficiary::where('name', $name)->where('acode', $acode)->first();
            }
        }

        if (! $item) {
            return null;
        }

        return [
            'id' => $item->id,
            'name' => $item->name,
            'acode' => $item->acode ?? '',
        ];
    }
    
    /**
     * Extract PPA Date from remarks and format as dd/mm/yyyy
     * Format: "PPA Date: 03/08/2025" or "PPA Date: 03-08-2025"
     */
    private function extractPpaDateFromRemarks($remarks)
    {
        if (empty($remarks)) {
            return null;
        }

        // Try to find "PPA Date: DD/MM/YYYY" or "PPA Date: DD-MM-YYYY"
        if (preg_match('/PPA\s+Date:\s*(\d{2})[\/\-](\d{2})[\/\-](\d{4})/i', $remarks, $matches)) {
            $day = $matches[1];
            $month = $matches[2];
            $year = $matches[3];
            
            // Return in dd/mm/yyyy format
            return $day . '/' . $month . '/' . $year;
        }

        return null;
    }
}
