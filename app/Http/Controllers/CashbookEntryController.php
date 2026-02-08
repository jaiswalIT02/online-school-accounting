<?php

namespace App\Http\Controllers;

use App\Models\Cashbook;
use App\Models\CashbookEntry;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CashbookEntryController extends Controller
{
    public function create(Cashbook $cashbook)
    {
        $type = request('type', 'both');

        return view('cashbook_entries.create', compact('cashbook', 'type'));
    }

    public function store(Request $request, Cashbook $cashbook)
    {
        $data = $this->validateEntry($request);
        $data['cashbook_id'] = $cashbook->id;

        // If type is 'both', create both receipt and payment entries
        if ($data['type'] === 'both') {
            $entryData = [
                'cashbook_id' => $cashbook->id,
                'entry_date' => $data['entry_date'],
                'particulars' => $data['particulars'],
                'cash_amount' => $data['cash_amount'] ?? 0,
                'bank_amount' => $data['bank_amount'] ?? 0,
                'narration' => $data['narration'] ?? null,
            ];

            // Create receipt entry
            $cashbook->entries()->create(array_merge($entryData, ['type' => 'receipt']));
            
            // Create payment entry
            $cashbook->entries()->create(array_merge($entryData, ['type' => 'payment']));

            return redirect()
                ->route('cashbooks.show', $cashbook)
                ->with('status', 'Entry added as both receipt and payment.');
        } else {
            // Single entry (receipt or payment)
            $cashbook->entries()->create($data);

            return redirect()
                ->route('cashbooks.show', $cashbook)
                ->with('status', 'Entry added.');
        }
    }

    public function edit(CashbookEntry $entry)
    {
        $cashbook = $entry->cashbook;
        $type = $entry->type;

        return view('cashbook_entries.edit', compact('cashbook', 'entry', 'type'));
    }

    public function update(Request $request, CashbookEntry $entry)
    {
        $data = $this->validateEntry($request);
        $cashbook = $entry->cashbook;

        // Store old values to find corresponding entry
        $oldParticulars = $entry->particulars;
        $oldCashAmount = $entry->cash_amount;
        $oldBankAmount = $entry->bank_amount;
        $currentType = $entry->type;
        $otherType = $currentType === 'payment' ? 'receipt' : 'payment';

        // Update current entry
        $updateData = [
            'entry_date' => $data['entry_date'],
            'particulars' => $data['particulars'],
            'cash_amount' => $data['cash_amount'] ?? 0,
            'bank_amount' => $data['bank_amount'] ?? 0,
            'narration' => $data['narration'] ?? null,
        ];

        // If type is 'both', also update the type
        if ($data['type'] === 'both') {
            // Keep current type, but ensure corresponding entry exists
        } else {
            // Update type if changed
            if ($data['type'] !== $currentType) {
                $updateData['type'] = $data['type'];
            }
        }

        $entry->update($updateData);

        // Always check for and update corresponding entry on the other side
        $correspondingEntry = $cashbook->entries()
            ->where('id', '!=', $entry->id)
            ->where('type', $otherType)
            ->where(function($query) use ($oldParticulars, $oldCashAmount, $oldBankAmount, $data) {
                // Match by old values OR new values
                $query->where(function($q) use ($oldParticulars, $oldCashAmount, $oldBankAmount) {
                    $q->where('particulars', $oldParticulars)
                      ->where('cash_amount', $oldCashAmount)
                      ->where('bank_amount', $oldBankAmount);
                })->orWhere(function($q) use ($data) {
                    $q->where('particulars', $data['particulars'])
                      ->where('cash_amount', $data['cash_amount'] ?? 0)
                      ->where('bank_amount', $data['bank_amount'] ?? 0);
                });
            })
            ->first();

        if ($correspondingEntry) {
            // Update corresponding entry with new data
            $correspondingEntry->update([
                'entry_date' => $data['entry_date'],
                'particulars' => $data['particulars'],
                'cash_amount' => $data['cash_amount'] ?? 0,
                'bank_amount' => $data['bank_amount'] ?? 0,
                'narration' => $data['narration'] ?? null,
            ]);
        } elseif ($data['type'] === 'both') {
            // If type is 'both' and no corresponding entry found, create one
            $cashbook->entries()->create([
                'type' => $otherType,
                'entry_date' => $data['entry_date'],
                'particulars' => $data['particulars'],
                'cash_amount' => $data['cash_amount'] ?? 0,
                'bank_amount' => $data['bank_amount'] ?? 0,
                'narration' => $data['narration'] ?? null,
            ]);
        }

        $message = $data['type'] === 'both' || $correspondingEntry 
            ? 'Entry updated. Corresponding entry on the other side also updated.' 
            : 'Entry updated.';

        return redirect()
            ->route('cashbooks.show', $cashbook)
            ->with('status', $message);
    }

    public function destroy(CashbookEntry $entry)
    {
        $cashbook = $entry->cashbook;

        $entry->delete();

        return redirect()
            ->route('cashbooks.show', $cashbook)
            ->with('status', 'Entry deleted.');
    }

    private function validateEntry(Request $request): array
    {
        $data = $request->validate([
            'entry_date' => ['required', 'string'],
            'voucher_no' => ['nullable', 'string', 'max:50'],
            'particulars' => ['required', 'string', 'max:255'],
            'folio_no' => ['nullable', 'string', 'max:50'],
            'type' => ['required', 'in:receipt,payment,both'],
            'cash_amount' => ['nullable', 'numeric', 'min:0'],
            'bank_amount' => ['nullable', 'numeric', 'min:0'],
            'narration' => ['nullable', 'string', 'max:1000'],
        ]);

        // Convert dd/mm/yy format to Y-m-d for database
        $data['entry_date'] = $this->convertDateFormat($data['entry_date']);

        $cashAmount = (float) ($data['cash_amount'] ?? 0);
        $bankAmount = (float) ($data['bank_amount'] ?? 0);

        if ($cashAmount <= 0 && $bankAmount <= 0) {
            throw ValidationException::withMessages([
                'cash_amount' => 'Enter a cash or bank amount.',
                'bank_amount' => 'Enter a cash or bank amount.',
            ]);
        }

        $data['cash_amount'] = $cashAmount;
        $data['bank_amount'] = $bankAmount;

        return $data;
    }

    private function convertDateFormat($dateString): string
    {
        // Handle dd/mm/yy format
        if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{2})$/', $dateString, $matches)) {
            $day = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
            $month = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
            $year = $matches[3];
            
            // Convert 2-digit year to 4-digit (assuming 2000-2099)
            $fullYear = (int)$year < 50 ? '20' . $year : '19' . $year;
            
            return $fullYear . '-' . $month . '-' . $day;
        }
        
        // If already in Y-m-d format, return as is
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateString)) {
            return $dateString;
        }
        
        // Try to parse as date and convert
        try {
            $date = \Carbon\Carbon::createFromFormat('d/m/y', $dateString);
            return $date->format('Y-m-d');
        } catch (\Exception $e) {
            throw ValidationException::withMessages([
                'entry_date' => 'Invalid date format. Please use dd/mm/yy format.',
            ]);
        }
    }
}
