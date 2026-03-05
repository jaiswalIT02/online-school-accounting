<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\ReceiptPaymentAccount;
use App\Models\SessionYear;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $accounts = Account::paginate(15);
        return view('accounts.index', compact('accounts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('accounts.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:200'],
            'slug' => ['required', 'string', 'max:200'],
            // 'status' => ['in', 'in:active,in-active'],
        ]);

        $accountId = Account::create($data);

        $schoolId      = auth()->user()->school_id;
        $sessionFilter = session('session_id', current_session_year_id());
        $accountType   = session('account_type', current_account_type_id());

        $sessions = loadSessionYear();

        /*
        |--------------------------------------------------------------------------
        | Get Current Session Data From Array
        |--------------------------------------------------------------------------
        */
        $currentSession = collect($sessions)->firstWhere('id', $sessionFilter);

        if (!$currentSession) {
            return; // or throw exception
        }

        $start = Carbon::parse($currentSession['start_date']);
        $end   = Carbon::parse($currentSession['end_date']);

        /*
        |--------------------------------------------------------------------------
        | 1️⃣ Annual Entry (Dynamic)
        |--------------------------------------------------------------------------
        */
        ReceiptPaymentAccount::create([
            'account_id'    => $accountId,
            'name' => 'Annual Receipt & Payment',
            'header_title' => 'Annual Report',
            'header_subtitle' => $currentSession['session_name'],
            'period_from' => $start,
            'period_to' => $end,
            'date' => now(),
            'description' => 'Annual receipt and payment account',
            'account_type_id' => $accountType,
            'session_year_id' => $sessionFilter,
            'school_id' => $schoolId,
        ]);

        /*
        |--------------------------------------------------------------------------
        | 2️⃣ Half Year Entries (Dynamic Split)
        |--------------------------------------------------------------------------
        */
        $midPoint = $start->copy()->addMonths(6)->subDay();

        $halves = [
            [
                'name' => 'Half Year 1',
                'from' => $start,
                'to'   => $midPoint,
            ],
            [
                'name' => 'Half Year 2',
                'from' => $midPoint->copy()->addDay(),
                'to'   => $end,
            ]
        ];

        foreach ($halves as $half) {
            ReceiptPaymentAccount::create([
                'account_id'    => $accountId,
                'name' => $half['name'],
                'header_title' => 'Half Year Report',
                'header_subtitle' => $half['name'],
                'period_from' => $half['from'],
                'period_to' => $half['to'],
                'date' => now(),
                'description' => 'Half yearly receipt and payment account',
                'account_type_id' => $accountType,
                'session_year_id' => $sessionFilter,
                'school_id' => $schoolId,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | 4️⃣ Quarter Entries (Fully Dynamic)
        |--------------------------------------------------------------------------
        */
        $quarterStart = $start->copy();

        for ($i = 1; $i <= 4; $i++) {

            $quarterEnd = $quarterStart->copy()->addMonths(3)->subDay();

            ReceiptPaymentAccount::create([
                'account_id'    => $accountId,
                'name' => 'Q' . $i,
                'header_title' => 'Quarterly Report',
                'header_subtitle' => 'Q' . $i,
                'period_from' => $quarterStart,
                'period_to' => $quarterEnd,
                'date' => now(),
                'description' => 'Quarterly receipt and payment account',
                'account_type_id' => $accountType,
                'session_year_id' => $sessionFilter,
                'school_id' => $schoolId,
            ]);

            $quarterStart = $quarterEnd->copy()->addDay();
        }

        return redirect()
            ->route('accounts.index')
            ->with('status', 'Account created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Get only normal R&P entries (exclude tax entries)
        // Normal entries: have amount > 0 and particular_name doesn't start with tax identifiers
        // Order by ID to preserve insertion order (receipt, payment, receipt, payment...)
        $rpeEntries = ReceiptPaymentAccount::where('account_id', $id)
            ->where(function ($query) {

                $query->where('particular_name', 'NOT LIKE', 'PTAX%')
                    ->where('particular_name', 'NOT LIKE', 'TDS%');
            })
            ->orderBy('id', 'asc')
            ->get();

        // $runningBalance = $ledger->opening_balance_type === 'Cr'
        //     ? -1 * $ledger->opening_balance
        //     : $ledger->opening_balance;

        // $rows = [];
        // $totalDebit = 0;
        // $totalCredit = 0;

        // // Process main entries in stored order (receipt, payment, receipt, payment...)
        // foreach ($rpeEntries as $rpeEntry) {
        //     // Extract PPA date from date column, then remarks, fallback to created_at
        //     $entryDate = $this->getEntryDate($rpeEntry);

        //     // Receipt entries: amount on credit side, balance type Cr
        //     // Payment entries: amount on debit side, balance type Dr
        //     $credit = $rpeEntry->type === 'receipt' ? $rpeEntry->amount : 0;
        //     $debit = $rpeEntry->type === 'payment' ? $rpeEntry->amount : 0;
        //     $balanceType = $rpeEntry->type === 'receipt' ? 'Cr' : 'Dr';

        //     // Calculate running balance
        //     $runningBalance += $debit;
        //     $runningBalance -= $credit;
        //     $totalDebit += $debit;
        //     $totalCredit += $credit;

        //     $rows[] = [
        //         'entry' => (object)[
        //             'id' => $rpeEntry->id,
        //             'entry_date' => $entryDate,
        //             'particulars' => $rpeEntry->current_particular_name,
        //             'debit' => $debit,
        //             'credit' => $credit,
        //             'narration' => $rpeEntry->remarks,
        //             'is_rpe_entry' => true,
        //         ],
        //         'balance' => abs($runningBalance),
        //         'balance_type' => $balanceType, // Transaction type: Cr for receipt, Dr for payment
        //     ];
        // }

        // $closingBalance = abs($runningBalance);
        // $closingBalanceType = $runningBalance < 0 ? 'Cr' : 'Dr';

        return view('receipt_payments.index', compact('ledger', 'rows', 'totalDebit', 'totalCredit', 'closingBalance', 'closingBalanceType'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
