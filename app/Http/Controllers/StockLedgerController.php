<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Stock;
use App\Models\StockLedger;
use Carbon\Carbon;
use Illuminate\Http\Request;

class StockLedgerController extends Controller
{
    /**
     * Session period for stock ledgers: Year 2025–26 (From 01/04/2025 to 31/03/2026).
     */
    private function getSessionPeriod(): array
    {
        $dateFrom = Carbon::create(2025, 4, 1)->startOfDay();
        $dateTo = Carbon::create(2026, 3, 31)->endOfDay();

        return [
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ];
    }
    public function index(Request $request)
    {
        $query = StockLedger::query()->with('item');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('ledger_name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('item', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('item_code', 'like', "%{$search}%");
                    });
            });
        }

        $stockLedgers = $query->orderByDesc('date_from')->paginate(15)->withQueryString();

        return view('stock_ledgers.index', compact('stockLedgers'));
    }

    /**
     * Create stock ledgers only for items that do not have a stock ledger yet.
     */
    public function createAllFromItems()
    {
        $itemsWithoutLedger = Item::whereDoesntHave('stockLedgers')->orderBy('name')->get();

        if ($itemsWithoutLedger->isEmpty()) {
            return redirect()
                ->route('stock_ledgers.index')
                ->with('status', 'All items already have a stock ledger. No new ledgers created.');
        }

        $period = $this->getSessionPeriod();
        $dateFrom = $period['date_from'];
        $dateTo = $period['date_to'];

        $created = 0;
        foreach ($itemsWithoutLedger as $item) {
            StockLedger::create([
                'ledger_name' => strtoupper('Stock Ledger - ' . $item->name),
                'item_id' => $item->id,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'opening_balance' => 0,
                'opening_type' => strtoupper('Dr'),
                'description' => null,
            ]);
            $created++;
        }

        return redirect()
            ->route('stock_ledgers.index')
            ->with('status', "Created {$created} stock ledger(s) for new item(s).");
    }

    public function create()
    {
        $items = Item::orderBy('name')->get();
        $period = $this->getSessionPeriod();
        return view('stock_ledgers.create', compact('items', 'period'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'ledger_name' => ['required', 'string', 'max:255'],
            'item_id' => ['required', 'exists:items,id'],
            'date_from' => ['required', 'date'],
            'date_to' => ['required', 'date', 'after_or_equal:date_from'],
            'opening_balance' => ['nullable', 'numeric', 'min:0'],
            'opening_type' => ['required', 'in:Dr,Cr'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        // Convert string fields to uppercase
        if (isset($data['ledger_name'])) $data['ledger_name'] = strtoupper($data['ledger_name']);
        if (isset($data['opening_type'])) $data['opening_type'] = strtoupper($data['opening_type']);
        if (isset($data['description'])) $data['description'] = strtoupper($data['description']);

        StockLedger::create($data);

        return redirect()
            ->route('stock_ledgers.index')
            ->with('status', 'Stock ledger created.');
    }

    /**
     * Build show/print data for a stock ledger (stocks, rows with running balance, totals).
     */
    private function buildStockLedgerShowData(StockLedger $stock_ledger): array
    {
        $stock_ledger->load('item');

        $stocks = Stock::where('item_id', $stock_ledger->item_id)
            ->whereDate('date', '>=', $stock_ledger->date_from)
            ->whereDate('date', '<=', $stock_ledger->date_to)
            ->orderBy('date')
            ->orderBy('id')
            ->get();

        $runningBalance = (float) $stock_ledger->opening_balance;
        $displayUnit = $stock_ledger->item->unit ?? 'kg';

        $rows = [];
        foreach ($stocks as $stock) {
            $qty = (float) ($stock->stock_amount ?? $stock->number ?? 0);
            if ($stock->stock_type === 'Receipt') {
                $runningBalance += $qty;
            } else {
                $runningBalance -= $qty;
            }
            $rows[] = [
                'stock' => $stock,
                'running_balance' => $runningBalance,
                'unit' => $displayUnit,
            ];
        }

        $totalReceipt = $stocks->where('stock_type', 'Receipt')->sum(fn ($s) => $s->stock_amount ?? $s->number);
        $totalIssued = $stocks->where('stock_type', 'Issued')->sum(fn ($s) => $s->stock_amount ?? $s->number);
        $closingBalance = $runningBalance;

        return [
            'stock_ledger' => $stock_ledger,
            'stocks' => $stocks,
            'rows' => $rows,
            'displayUnit' => $displayUnit,
            'totalReceipt' => $totalReceipt,
            'totalIssued' => $totalIssued,
            'closingBalance' => $closingBalance,
        ];
    }

    public function show(StockLedger $stock_ledger)
    {
        $data = $this->buildStockLedgerShowData($stock_ledger);
        return view('stock_ledgers.show', $data);
    }

    public function print(StockLedger $stock_ledger)
    {
        $data = $this->buildStockLedgerShowData($stock_ledger);
        return view('stock_ledgers.print', $data);
    }
}
