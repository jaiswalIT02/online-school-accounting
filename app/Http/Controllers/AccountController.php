<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\ReceiptPaymentAccount;
use App\Models\Schools;
use App\Models\SessionYear;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

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
            'name' => ['required', 'string', 'max:200', 'unique:accounts,name'],
            'slug' => ['required', 'string', 'max:200', 'unique:accounts,slug'],
        ]);
        try {

            DB::beginTransaction();
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

            $school = Schools::select('name')->where('id', $schoolId)->first();

            $schoolName = $school->name;

            $receptPaymentName = "RECEIPT AND PAYMENT ACCOUNT OF " . $schoolName;


            /*
        |--------------------------------------------------------------------------
        | 1️⃣ Annual Entry (Dynamic)
        |--------------------------------------------------------------------------
        */

            ReceiptPaymentAccount::create([
                'account_id'    => $accountId->id,
                'name' => $receptPaymentName, //'Annual Receipt & Payment',
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
                    'name' => $receptPaymentName, // 'Half Year 1',
                    'from' => $start,
                    'to'   => $midPoint,
                ],
                [
                    'name' => $receptPaymentName, //'Half Year 2',
                    'from' => $midPoint->copy()->addDay(),
                    'to'   => $end,
                ]
            ];

            foreach ($halves as $half) {
                ReceiptPaymentAccount::create([
                    'account_id'    => $accountId->id,
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
                    'account_id'    => $accountId->id,
                    'name' => $receptPaymentName, //'Q' . $i,
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
            DB::commit();

            return redirect()
                ->route('accounts.index')
                ->with('status', 'Account created successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;

            // ->route('accounts.index')
            // ->with('status', 'Something error, please try again!' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $sessionFilter = session('session_id', current_session_year_id());
        $accountType   = session('account_type', current_account_type_id());

        $rpAccounts = ReceiptPaymentAccount::where('account_id', $id)
            ->where('session_year_id', $sessionFilter)
            ->where('account_type_id', $accountType)
            ->orderByRaw("
        CASE
            -- Quarterly (3 months)
            WHEN TIMESTAMPDIFF(MONTH, period_from, period_to) BETWEEN 2 AND 3 THEN 1

            -- Half-Yearly (6 months)
            WHEN TIMESTAMPDIFF(MONTH, period_from, period_to) BETWEEN 5 AND 6 THEN 2

            -- Yearly (12 months or more)
            ELSE 3
        END
    ")
            ->orderBy('period_from')
            ->get();
        return view('accounts.show', compact('rpAccounts'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $account = Account::where('id', $id)
            ->orderBy('id', 'asc')
            ->first();
        // dd($account);
        return view('accounts.edit', compact('account'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Account $account)
    {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:200',
                Rule::unique('accounts', 'name')->ignore($account->id),
            ],
            'slug' => [
                'required',
                'string',
                'max:200',
                Rule::unique('accounts', 'slug')->ignore($account->id),
            ],
        ]);

        $account->update($data);

        return redirect()
            ->route('accounts.index')
            ->with('status', 'Account updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
