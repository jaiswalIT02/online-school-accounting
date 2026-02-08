<?php

namespace App\Http\Controllers;

use App\Models\Ledger;
use App\Models\LedgerEntry;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class LedgerEntryController extends Controller
{
    public function create(Ledger $ledger)
    {
        return view('ledger_entries.create', compact('ledger'));
    }

    public function store(Request $request, Ledger $ledger)
    {
        $data = $this->validateEntry($request);
        $data['ledger_id'] = $ledger->id;

        $ledger->entries()->create($data);

        return redirect()
            ->route('ledgers.show', $ledger)
            ->with('status', 'Entry added.');
    }

    public function edit(LedgerEntry $entry)
    {
        $ledger = $entry->ledger;

        return view('ledger_entries.edit', compact('ledger', 'entry'));
    }

    public function update(Request $request, LedgerEntry $entry)
    {
        $data = $this->validateEntry($request);

        $entry->update($data);

        return redirect()
            ->route('ledgers.show', $entry->ledger)
            ->with('status', 'Entry updated.');
    }

    public function destroy(LedgerEntry $entry)
    {
        $ledger = $entry->ledger;

        $entry->delete();

        return redirect()
            ->route('ledgers.show', $ledger)
            ->with('status', 'Entry deleted.');
    }

    private function validateEntry(Request $request): array
    {
        $data = $request->validate([
            'entry_date' => ['required', 'string'],
            'particulars' => ['required', 'string', 'max:255'],
            'folio_no' => ['nullable', 'string', 'max:50'],
            'debit' => ['nullable', 'numeric', 'min:0'],
            'credit' => ['nullable', 'numeric', 'min:0'],
            'narration' => ['nullable', 'string', 'max:1000'],
        ]);

        // Convert dd/mm/yy format to Y-m-d for database
        $data['entry_date'] = $this->convertDateFormat($data['entry_date']);

        $debit = (float) ($data['debit'] ?? 0);
        $credit = (float) ($data['credit'] ?? 0);

        if (($debit <= 0 && $credit <= 0) || ($debit > 0 && $credit > 0)) {
            throw ValidationException::withMessages([
                'debit' => 'Enter either a debit or a credit amount.',
                'credit' => 'Enter either a debit or a credit amount.',
            ]);
        }

        $data['debit'] = $debit;
        $data['credit'] = $credit;

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
