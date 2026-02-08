<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Fund;
use App\Services\FundImportService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class FundController extends Controller
{
    public function index(Request $request)
    {
        $query = Fund::query();

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('component_name', 'like', "%{$search}%")
                  ->orWhere('component_code', 'like', "%{$search}%")
                  ->orWhere('remark', 'like', "%{$search}%");
            });
        }

        if ($request->filled('component_name')) {
            $query->where('component_name', $request->component_name);
        }
        
        if ($request->filled('component_code')) {
            $query->where('component_code', $request->component_code);
        }

        if ($request->filled('date_from')) {
            $query->where('fund_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('fund_date', '<=', $request->date_to);
        }

        if ($request->filled('amount_from')) {
            $query->where('amount', '>=', $request->amount_from);
        }

        if ($request->filled('amount_to')) {
            $query->where('amount', '<=', $request->amount_to);
        }

        // Calculate totals before pagination
        $totalAmount = (clone $query)->sum('amount');
        $totalCount = (clone $query)->count();

        // Order by date - recent first (default) or old first
        $sortOrder = $request->get('sort_order', 'recent');
        if ($sortOrder === 'old') {
            $funds = $query->orderBy('fund_date')->orderBy('id')->paginate(20);
        } else {
            // Default: recent first (newest)
            $funds = $query->orderByDesc('fund_date')->orderByDesc('id')->paginate(20);
        }

        // Get fund for editing if edit parameter is provided
        $editFund = null;
        if ($request->filled('edit')) {
            $editFund = Fund::find($request->edit);
        }

        // Get all articles for dropdown
        $articles = Article::orderBy('name')->get();

        return view('funds.index', compact('funds', 'totalAmount', 'totalCount', 'editFund', 'articles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'fund_date' => ['nullable', 'string', 'max:10'],
            'component_name' => ['nullable', 'string', 'max:200'],
            'component_code' => ['nullable', 'string', 'max:100'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'remark' => ['nullable', 'string', 'max:1000'],
        ]);

        // Store date in dd/mm/yyyy format (no conversion needed)
        if ($request->filled('fund_date')) {
            $data['fund_date'] = trim($request->fund_date);
        }

        Fund::create($data);

        return redirect()
            ->route('funds.index')
            ->with('status', 'Fund record created successfully.');
    }

    public function update(Request $request, Fund $fund)
    {
        $data = $request->validate([
            'fund_date' => ['nullable', 'string', 'max:10'],
            'component_name' => ['nullable', 'string', 'max:200'],
            'component_code' => ['nullable', 'string', 'max:100'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'remark' => ['nullable', 'string', 'max:1000'],
        ]);

        // Store date in dd/mm/yyyy format (no conversion needed)
        if ($request->filled('fund_date')) {
            $data['fund_date'] = trim($request->fund_date);
        }

        $fund->update($data);

        return redirect()
            ->route('funds.index')
            ->with('status', 'Fund record updated successfully.');
    }

    public function show(Request $request)
    {
        $query = Fund::query();

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('component_name', 'like', "%{$search}%")
                  ->orWhere('component_code', 'like', "%{$search}%")
                  ->orWhere('remark', 'like', "%{$search}%");
            });
        }

        if ($request->filled('component_name')) {
            $query->where('component_name', $request->component_name);
        }
        
        if ($request->filled('component_code')) {
            $query->where('component_code', $request->component_code);
        }

        // Month-wise filter (by month only, across years)
        if ($request->filled('month_only')) {
            $month = str_pad($request->month_only, 2, '0', STR_PAD_LEFT);
            // fund_date format is dd/mm/yyyy, extract the month part and compare
            $query->whereRaw(
                "LPAD(SUBSTRING_INDEX(SUBSTRING_INDEX(fund_date, '/', 2), '/', -1), 2, '0') = ?",
                [$month]
            );
        }

        // Date filtering - both input and stored format are dd/mm/yyyy
        // Convert to YYYYMMDD format for proper date comparison (string comparison works correctly)
        if ($request->filled('date_from')) {
            $dateFrom = trim($request->date_from);
            // Parse dd/mm/yyyy input format
            if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $dateFrom, $matches)) {
                $day = $matches[1];
                $month = $matches[2];
                $year = $matches[3];
                $dateFromFormatted = $year . $month . $day; // YYYYMMDD format for comparison
                
                // Convert stored dd/mm/yyyy to YYYYMMDD and compare
                // fund_date format: dd/mm/yyyy -> extract year, month, day and convert to YYYYMMDD
                $query->whereRaw("CONCAT(
                    SUBSTRING_INDEX(fund_date, '/', -1),  -- Year (last part)
                    LPAD(SUBSTRING_INDEX(SUBSTRING_INDEX(fund_date, '/', 2), '/', -1), 2, '0'),  -- Month (middle part)
                    LPAD(SUBSTRING_INDEX(fund_date, '/', 1), 2, '0')  -- Day (first part)
                ) >= ?", [$dateFromFormatted]);
            }
        }

        if ($request->filled('date_to')) {
            $dateTo = trim($request->date_to);
            // Parse dd/mm/yyyy input format
            if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $dateTo, $matches)) {
                $day = $matches[1];
                $month = $matches[2];
                $year = $matches[3];
                $dateToFormatted = $year . $month . $day; // YYYYMMDD format for comparison
                
                // Convert stored dd/mm/yyyy to YYYYMMDD and compare
                $query->whereRaw("CONCAT(
                    SUBSTRING_INDEX(fund_date, '/', -1),  -- Year (last part)
                    LPAD(SUBSTRING_INDEX(SUBSTRING_INDEX(fund_date, '/', 2), '/', -1), 2, '0'),  -- Month (middle part)
                    LPAD(SUBSTRING_INDEX(fund_date, '/', 1), 2, '0')  -- Day (first part)
                ) <= ?", [$dateToFormatted]);
            }
        }

        if ($request->filled('amount_from')) {
            $query->where('amount', '>=', $request->amount_from);
        }

        if ($request->filled('amount_to')) {
            $query->where('amount', '<=', $request->amount_to);
        }

        // Calculate totals before pagination
        $totalAmount = (clone $query)->sum('amount');
        $totalCount = (clone $query)->count();
        $averageAmount = $totalCount > 0 ? ($totalAmount / $totalCount) : 0;

        // Get funds with pagination (recent first)
        $funds = $query->orderByDesc('fund_date')->orderByDesc('id')->paginate(20);

        // Get all articles for dropdown
        $articles = Article::orderBy('name')->get();

        // Get unique component names for filter
        $uniqueComponents = Fund::distinct()->orderBy('component_name')->pluck('component_name');

        return view('funds.show', compact('funds', 'totalAmount', 'totalCount', 'averageAmount', 'articles', 'uniqueComponents'));
    }

    public function destroy(Fund $fund)
    {
        $fund->delete();

        return redirect()
            ->route('funds.index')
            ->with('status', 'Fund record deleted successfully.');
    }

    /**
     * Show import form
     */
    public function import()
    {
        return view('funds.import');
    }

    /**
     * Process the import
     */
    public function processImport(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240', // 10MB max
        ]);

        try {
            $importService = new FundImportService();
            $result = $importService->import($request->file('file'));

            return redirect()
                ->route('funds.index')
                ->with('status', "Import completed successfully! Total rows: {$result['total_rows']}, Inserted: {$result['inserted']}, Skipped: {$result['skipped']}")
                ->with('import_result', $result);
        } catch (\Exception $e) {
            return redirect()
                ->route('funds.import')
                ->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    /**
     * Export funds to Excel (CSV format)
     */
    public function exportExcel(Request $request)
    {
        $query = Fund::query();

        // Apply filters from request
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('component_name', 'like', "%{$search}%")
                  ->orWhere('component_code', 'like', "%{$search}%")
                  ->orWhere('remark', 'like', "%{$search}%");
            });
        }

        if ($request->filled('component_name')) {
            $query->where('component_name', $request->component_name);
        }
        
        if ($request->filled('component_code')) {
            $query->where('component_code', $request->component_code);
        }

        if ($request->filled('date_from')) {
            $query->where('fund_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('fund_date', '<=', $request->date_to);
        }

        if ($request->filled('amount_from')) {
            $query->where('amount', '>=', $request->amount_from);
        }

        if ($request->filled('amount_to')) {
            $query->where('amount', '<=', $request->amount_to);
        }

        $funds = $query->orderByDesc('fund_date')->orderByDesc('id')->get();

        $filename = 'funds_' . date('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($funds) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for Excel UTF-8 support
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Headers
            fputcsv($file, [
                'SL No', 'Date', 'Component Name', 'Component Code', 'Amount', 'Remark'
            ]);

            // Data rows
            $slNo = 1;
            foreach ($funds as $fund) {
                fputcsv($file, [
                    $slNo++,
                    $fund->fund_date ?? '',
                    $fund->component_name ?? '',
                    $fund->component_code ?? '',
                    $fund->amount ?? 0,
                    $fund->remark ?? '',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

}
