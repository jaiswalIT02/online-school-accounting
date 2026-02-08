<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\BeneficiaryController;
use App\Http\Controllers\CashbookController;
use App\Http\Controllers\CashbookEntryController;
use App\Http\Controllers\FundController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\LedgerController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\StockLedgerController;
use App\Http\Controllers\LedgerEntryController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TaxLedgerController;
use App\Http\Controllers\PdfExtractController;
use App\Http\Controllers\ReceiptPaymentAccountController;
use App\Http\Controllers\ReceiptPaymentEntryController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('articles', ArticleController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('beneficiaries', BeneficiaryController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('items', ItemController::class);
    Route::resource('stocks', StockController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::post('stock-ledgers/create-all-from-items', [StockLedgerController::class, 'createAllFromItems'])->name('stock_ledgers.create_all_from_items');
    Route::get('stock-ledgers/{stock_ledger}/print', [StockLedgerController::class, 'print'])->name('stock_ledgers.print');
    Route::resource('stock-ledgers', StockLedgerController::class)->only(['index', 'create', 'store', 'show'])->names('stock_ledgers');
    // Funds routes - Import and Export routes must come BEFORE resource route
    Route::get('funds/import', [FundController::class, 'import'])->name('funds.import');
    Route::post('funds/import', [FundController::class, 'processImport'])->name('funds.processImport');
    Route::get('funds/export/excel', [FundController::class, 'exportExcel'])->name('funds.export.excel');
    Route::resource('funds', FundController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::get('funds/view-all', [FundController::class, 'show'])->name('funds.view_all');
    Route::resource('receipt-payments', ReceiptPaymentAccountController::class)
        ->names('receipt_payments');
    Route::get('receipt-payments/{receipt_payment}/print', [ReceiptPaymentAccountController::class, 'print'])
        ->name('receipt_payments.print');
    Route::post('receipt-payments/{receipt_payment}/create-cashbook', [ReceiptPaymentAccountController::class, 'createCashbook'])
        ->name('receipt_payments.create_cashbook');
    Route::resource('receipt-payments.entries', ReceiptPaymentEntryController::class)
        ->only(['create', 'store'])
        ->names([
            'create' => 'receipt_payment_entries.create',
            'store' => 'receipt_payment_entries.store',
        ]);
    
    // Bulk routes must come before {entry} routes so "bulk-edit" / "bulk-update" are not matched as entry IDs
    Route::post('receipt-payment-entries/bulk-destroy', [ReceiptPaymentEntryController::class, 'bulkDestroy'])
        ->name('receipt_payment_entries.bulk_destroy');
    Route::get('receipt-payment-entries/bulk-edit', [ReceiptPaymentEntryController::class, 'bulkEdit'])
        ->name('receipt_payment_entries.bulk_edit');
    Route::put('receipt-payment-entries/bulk-update', [ReceiptPaymentEntryController::class, 'bulkUpdate'])
        ->name('receipt_payment_entries.bulk_update');
    // Shallow routes for edit, update, destroy (not nested)
    Route::get('receipt-payment-entries/{entry}/edit', [ReceiptPaymentEntryController::class, 'edit'])
        ->name('receipt_payment_entries.edit');
    Route::put('receipt-payment-entries/{entry}', [ReceiptPaymentEntryController::class, 'update'])
        ->name('receipt_payment_entries.update');
    Route::delete('receipt-payment-entries/{entry}', [ReceiptPaymentEntryController::class, 'destroy'])
        ->name('receipt_payment_entries.destroy');
    Route::post('cashbooks/create-all-months', [CashbookController::class, 'createAllMonthsEntries'])
        ->name('cashbooks.create_all_months');
    Route::resource('cashbooks', CashbookController::class);
    Route::get('cashbooks/{cashbook}/print', [CashbookController::class, 'print'])
        ->name('cashbooks.print');
    Route::get('cashbooks/{cashbook}/import', [CashbookController::class, 'import'])
        ->name('cashbooks.import');
    Route::post('cashbooks/{cashbook}/import', [CashbookController::class, 'processImport'])
        ->name('cashbooks.processImport');
    Route::resource('cashbooks.entries', CashbookEntryController::class)
        ->only(['create', 'store'])
        ->names([
            'create' => 'cashbook_entries.create',
            'store' => 'cashbook_entries.store',
        ]);
    
    // Shallow routes for cashbook entries edit, update, destroy (not nested)
    Route::get('cashbook-entries/{entry}/edit', [CashbookEntryController::class, 'edit'])
        ->name('cashbook_entries.edit');
    Route::put('cashbook-entries/{entry}', [CashbookEntryController::class, 'update'])
        ->name('cashbook_entries.update');
    Route::delete('cashbook-entries/{entry}', [CashbookEntryController::class, 'destroy'])
        ->name('cashbook_entries.destroy');
    
    Route::resource('ledgers', LedgerController::class);
    Route::get('ledgers/{ledger}/print', [LedgerController::class, 'print'])
        ->name('ledgers.print');
    Route::get('ledgers/{ledger}/import', [LedgerController::class, 'import'])
        ->name('ledgers.import');
    Route::post('ledgers/{ledger}/import', [LedgerController::class, 'processImport'])
        ->name('ledgers.processImport');
    Route::post('ledgers/create-all-from-activities', [LedgerController::class, 'createAllFromActivities'])
        ->name('ledgers.create_all_from_activities');
    Route::resource('ledgers.entries', LedgerEntryController::class)
        ->shallow()
        ->only(['create', 'store', 'edit', 'update', 'destroy'])
        ->names('ledger_entries');
    
    // Tax Ledger routes
    Route::get('tax-ledgers', [TaxLedgerController::class, 'index'])
        ->name('tax_ledgers.index');
    Route::get('tax-ledgers/{article}', [TaxLedgerController::class, 'show'])
        ->name('tax_ledgers.show');
    Route::get('tax-ledgers/{article}/print', [TaxLedgerController::class, 'print'])
        ->name('tax_ledgers.print');
    
    // PDF Extract routes
    Route::get('pdf-extract', [PdfExtractController::class, 'index'])->name('pdf_extract.index');
    Route::post('pdf-extract/extract', [PdfExtractController::class, 'extract'])->name('pdf_extract.extract');
    Route::post('pdf-extract/save', [PdfExtractController::class, 'save'])->name('pdf_extract.save');
    
    // Reports routes
    Route::get('reports', [App\Http\Controllers\ReportController::class, 'index'])->name('reports.index');
    
    // Students routes - Import and Export routes must come BEFORE resource route
    Route::get('students/import', [StudentController::class, 'import'])->name('students.import');
    Route::post('students/import', [StudentController::class, 'processImport'])->name('students.processImport');
    Route::get('students/export/excel', [StudentController::class, 'exportExcel'])->name('students.export.excel');
    Route::get('students/export/pdf', [StudentController::class, 'exportPdf'])->name('students.export.pdf');
    Route::get('students/bin', [StudentController::class, 'bin'])->name('students.bin');
    Route::post('students/{id}/restore', [StudentController::class, 'restore'])->name('students.restore');
    Route::resource('students', StudentController::class);
    
    // Staff routes - Import and Export routes must come BEFORE resource route
    Route::get('staff/import', [StaffController::class, 'import'])->name('staff.import');
    Route::post('staff/import', [StaffController::class, 'processImport'])->name('staff.processImport');
    Route::get('staff/export/excel', [StaffController::class, 'exportExcel'])->name('staff.export.excel');
    Route::get('staff/export/pdf', [StaffController::class, 'exportPdf'])->name('staff.export.pdf');
    Route::get('staff/bin', [StaffController::class, 'bin'])->name('staff.bin');
    Route::post('staff/{id}/restore', [StaffController::class, 'restore'])->name('staff.restore');
    Route::resource('staff', StaffController::class);
});

require __DIR__.'/auth.php';

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
