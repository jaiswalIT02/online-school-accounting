<?php

namespace App\Http\Controllers;

use App\Models\ReceiptPaymentAccount;
use App\Models\ReceiptPaymentEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser;

class PdfExtractController extends Controller
{
    public function index(Request $request)
    {
        $accounts = ReceiptPaymentAccount::orderByDesc('period_from')->get();
        $selectedAccountId = $request->get('account_id');
        return view('pdf_extract.index', compact('accounts', 'selectedAccountId'));
    }

    public function extract(Request $request)
    {
        $request->validate([
            'pdf_file' => ['required', 'file', 'mimes:pdf', 'max:10240'], // 10MB max
            'receipt_payment_account_id' => ['nullable', 'exists:receipt_payment_accounts,id'],
        ]);

        $file = $request->file('pdf_file');
        $filePath = $file->storeAs('pdfs', uniqid() . '_' . $file->getClientOriginalName(), 'local');

        try {
            $parser = new Parser();
            $pdf = $parser->parseFile(Storage::path($filePath));
            $text = $pdf->getText();

            // Extract metadata (Order ID, Generated On date)
            $metadata = $this->extractMetadata($text);

            // Extract beneficiary data from PDF text
            $extractedData = $this->parseBeneficiaryData($text, $metadata);

            // If account ID is provided, save to that account
            if ($request->receipt_payment_account_id) {
                $account = ReceiptPaymentAccount::findOrFail($request->receipt_payment_account_id);
                
                if (empty($extractedData)) {
                    return redirect()
                        ->route('receipt_payments.show', $account)
                        ->with('error', 'No beneficiary data could be extracted from the PDF. Please check if the PDF contains selectable text.');
                }
                
                $savedCount = $this->saveExtractedData($account, $extractedData);
                
                if ($savedCount > 0) {
                    $beneficiaryCount = count($extractedData);
                    return redirect()
                        ->route('receipt_payments.show', $account)
                        ->with('status', "PDF data extracted and saved successfully. {$beneficiaryCount} beneficiaries saved as {$savedCount} entries (payment and receipt).");
                } else {
                    return redirect()
                        ->route('receipt_payments.show', $account)
                        ->with('error', 'No valid beneficiary data found to save. Please check the PDF format.');
                }
            }

            // Return extracted data for review
            $accounts = ReceiptPaymentAccount::orderByDesc('period_from')->get();
            $selectedAccountId = $request->receipt_payment_account_id;
            return view('pdf_extract.result', [
                'extractedData' => $extractedData,
                'metadata' => $metadata,
                'rawText' => $text,
                'fileName' => $file->getClientOriginalName(),
                'accounts' => $accounts,
                'selectedAccountId' => $selectedAccountId,
            ]);

        } catch (\Exception $e) {
            Storage::delete($filePath);
            return back()
                ->withErrors(['pdf_file' => 'Failed to extract data from PDF: ' . $e->getMessage()])
                ->withInput();
        }
    }

    private function extractMetadata($text)
    {
        $metadata = [
            'order_id' => '',
            'generated_on' => '',
        ];

        // Extract Order ID
        if (preg_match('/Order\s*ID:\s*(\d+)/i', $text, $matches)) {
            $metadata['order_id'] = trim($matches[1]);
        }

        // Extract Generated On date (PPA date)
        // Format: "Generated On:03-08-2025 22:50:23"
        if (preg_match('/Generated\s*On:\s*(\d{2}-\d{2}-\d{4}\s+\d{2}:\d{2}:\d{2})/i', $text, $matches)) {
            $metadata['generated_on'] = trim($matches[1]);
        }

        return $metadata;
    }

    private function parseBeneficiaryData($text, $metadata = [])
    {
        $beneficiaries = [];
        $lines = explode("\n", $text);
        
        // Find Annexure-I section
        $foundAnnexure = false;
        $skippingHeader = false;
        $inTable = false;
        $currentEntry = null;
        $headerLinesSkipped = 0;
        
        foreach ($lines as $index => $line) {
            $line = trim($line);
            
            // Look for Annexure-I marker
            if (!$foundAnnexure && (preg_match('/Annexure[-\s]*I/i', $line) || preg_match('/Benificiar\s*y\s*List/i', $line))) {
                $foundAnnexure = true;
                $skippingHeader = true;
                continue;
            }
            
            // After finding Annexure-I, skip header lines
            if ($foundAnnexure && $skippingHeader) {
                // Look for column headers: Sl No, Txn ID, Name of Beneficiary, etc.
                if (preg_match('/(Sl\s*No|SI\s*No|Serial|Txn\s*ID|Transaction\s*ID|Name\s*of\s*Beneficiary|Benificiary|Account\s*No|IFSC|Amount)/i', $line)) {
                    $headerLinesSkipped++;
                    // Continue skipping header lines until we see a transaction row
                    continue;
                }
                
                // Skip "page to be stamped" line
                if (preg_match('/page to be stamped/i', $line)) {
                    continue;
                }
                
                // If we see a transaction row (starts with digit + 8-9 digits), stop skipping header
                if (preg_match('/^(\d)(\d{8,9})/', $line)) {
                    $skippingHeader = false;
                    $inTable = true;
                    // Don't continue, process this line below
                } else {
                    // Still in header, skip this line
                    continue;
                }
            }
            
            // Extract transaction rows (only after header is skipped)
            if ($inTable && !$skippingHeader) {
                // Stop parsing if we hit end markers
                if (preg_match('/^\/\/|^--|^===/', $line)) {
                    // Save current entry if exists
                    if ($currentEntry !== null) {
                        $this->saveCurrentEntry($currentEntry, $beneficiaries, $metadata);
                        $currentEntry = null;
                    }
                    break;
                }
                
                // Skip other non-data lines
                if (preg_match('/^(Order|Party|Debit|Total|Count|Generated|Expiry|Narration|Branch|Bank|authorise|account|amount|words|Sign|Information|official|process|print|submit|dully|signed|authorised|stamped|initial|last|page|Annexure|Sl\s*No|SI\s*No|Serial|Txn\s*ID|Transaction|Name\s*of|Beneficiary|Benificiary|Account\s*No|IFSC|Amount)/i', $line)) {
                    // Save current entry if exists before skipping
                    if ($currentEntry !== null) {
                        $this->saveCurrentEntry($currentEntry, $beneficiaries, $metadata);
                        $currentEntry = null;
                    }
                    continue;
                }
                
                // Check if line starts with SlNo (1 digit) followed immediately by TxnID (8-9 digits)
                // Pattern: "132937165" or "232937166HEMANTA"
                if (preg_match('/^(\d)(\d{8,9})(.*)$/', $line, $slMatches)) {
                    // Save previous entry if exists
                    if ($currentEntry !== null) {
                        $this->saveCurrentEntry($currentEntry, $beneficiaries, $metadata);
                    }
                    
                    // Start new entry
                    $slNo = $slMatches[1];
                    $txnId = $slMatches[2];
                    $remaining = trim($slMatches[3]);
                    
                    $currentEntry = [
                        'sl_no' => $slNo,
                        'txn_id' => $txnId,
                        'name' => '',
                        'name_in_pfms' => '',
                        'account_no' => '',
                        'ifsc' => '',
                        'amount' => 0,
                    ];
                    
                    // Check if this line also contains account/IFSC/amount (single line entry)
                    // Pattern: account (with x's) + IFSC + amount (all concatenated)
                    if (preg_match('/([xX\d]{10,20})([A-Z]{4}0[A-Z0-9]{6})(\d+(?:\.\d{2})?)/', $remaining, $dataMatches)) {
                        // Extract name before account number
                        $accountPos = strpos($remaining, $dataMatches[1]);
                        $nameSection = trim(substr($remaining, 0, $accountPos));
                        
                        if (!empty($nameSection)) {
                            $currentEntry['name'] = $nameSection;
                        }
                        
                        $currentEntry['account_no'] = preg_replace('/[^0-9]/', '', $dataMatches[1]);
                        $currentEntry['ifsc'] = trim($dataMatches[2]);
                        $currentEntry['amount'] = floatval(str_replace(',', '', $dataMatches[3]));
                        
                        // Try to split name and PFMS name
                        $this->splitNameAndPFMS($currentEntry);
                        
                        // Save this entry immediately
                        $this->saveCurrentEntry($currentEntry, $beneficiaries, $metadata);
                        $currentEntry = null;
                    } else if (!empty($remaining)) {
                        // This line has name text, add it
                        $currentEntry['name'] = $remaining;
                    }
                    continue;
                }
                
                // If we have a current entry, try to extract data from this line
                if ($currentEntry !== null) {
                    // Skip empty lines when building entry
                    if (empty($line)) {
                        continue;
                    }
                    
                    // Try to find account number, IFSC, and amount in this line
                    // Pattern: account (with x's) + IFSC + amount (may be concatenated)
                    if (preg_match('/([xX\d]{10,20})([A-Z]{4}0[A-Z0-9]{6})(\d+(?:\.\d{2})?)/', $line, $dataMatches)) {
                        $currentEntry['account_no'] = preg_replace('/[^0-9]/', '', $dataMatches[1]);
                        $currentEntry['ifsc'] = trim($dataMatches[2]);
                        $currentEntry['amount'] = floatval(str_replace(',', '', $dataMatches[3]));
                        
                        // Extract name section (everything before account number)
                        $accountPos = strpos($line, $dataMatches[1]);
                        $nameSection = trim(substr($line, 0, $accountPos));
                        
                        // Append to name if not empty
                        if (!empty($nameSection)) {
                            if (empty($currentEntry['name'])) {
                                $currentEntry['name'] = $nameSection;
                            } else {
                                $currentEntry['name'] .= ' ' . $nameSection;
                            }
                        }
                        
                        // Try to split name and PFMS name
                        $this->splitNameAndPFMS($currentEntry);
                        
                        // Save this entry
                        $this->saveCurrentEntry($currentEntry, $beneficiaries, $metadata);
                        $currentEntry = null;
                        continue;
                    }
                    
                    // If no account/IFSC found, this might be continuation of name
                    // Only add if line looks like name text (starts with uppercase letter, not just numbers or x's)
                    if (preg_match('/^[A-Z]/', $line) && !preg_match('/^\d+$/', $line) && !preg_match('/^[xX]+$/', $line)) {
                        if (empty($currentEntry['name'])) {
                            $currentEntry['name'] = $line;
                        } else {
                            $currentEntry['name'] .= ' ' . $line;
                        }
                    }
                }
            }
        }
        
        // Save last entry if exists
        if ($currentEntry !== null) {
            $this->saveCurrentEntry($currentEntry, $beneficiaries, $metadata);
        }

        // If no structured data found, try fallback extraction
        if (empty($beneficiaries)) {
            $beneficiaries = $this->extractUnstructuredData($text);
        }

        // Add metadata to all entries
        foreach ($beneficiaries as &$item) {
            $item['order_id'] = $metadata['order_id'] ?? '';
            $item['generated_on'] = $metadata['generated_on'] ?? '';
        }

        // Clean and validate extracted data
        return array_filter($beneficiaries, function($item) {
            return !empty($item['txn_id']) && 
                   !empty($item['name']) && 
                   !empty($item['ifsc']) && 
                   isset($item['amount']) && 
                   $item['amount'] > 0;
        });
    }
    
    private function saveCurrentEntry($entry, &$beneficiaries, $metadata = [])
    {
        // Clean up name (remove extra spaces)
        $name = preg_replace('/\s+/', ' ', trim($entry['name']));
        $pfmsName = preg_replace('/\s+/', ' ', trim($entry['name_in_pfms']));
        
        // Validate we have essential data
        if (!empty($entry['txn_id']) && !empty($name) && !empty($entry['ifsc']) && $entry['amount'] > 0) {
            $beneficiaries[] = [
                'txn_id' => $entry['txn_id'],
                'name' => $name,
                'name_in_pfms' => $pfmsName,
                'account_no' => $entry['account_no'],
                'ifsc' => $entry['ifsc'],
                'amount' => $entry['amount'],
                'order_id' => $metadata['order_id'] ?? '',
                'generated_on' => $metadata['generated_on'] ?? '',
            ];
        }
    }
    
    private function splitNameAndPFMS(&$entry)
    {
        $nameSection = trim($entry['name']);
        
        if (empty($nameSection)) {
            return;
        }
        
        // Try to detect PFMS name pattern
        // PFMS name often appears after the main name and might be similar
        // Look for patterns like: "NAME NAME" or "NAME NAME NAME" followed by account
        
        // Pattern 1: Look for repeated name patterns
        $words = preg_split('/\s+/', $nameSection);
        if (count($words) > 4) {
            // Try splitting at midpoint
            $midPoint = ceil(count($words) / 2);
            $firstHalf = implode(' ', array_slice($words, 0, $midPoint));
            $secondHalf = implode(' ', array_slice($words, $midPoint));
            
            // If both halves look like names (start with uppercase, have multiple words)
            if (preg_match('/^[A-Z]/', $firstHalf) && preg_match('/^[A-Z]/', $secondHalf) && 
                count(explode(' ', $firstHalf)) >= 2 && count(explode(' ', $secondHalf)) >= 2) {
                $entry['name'] = $firstHalf;
                $entry['name_in_pfms'] = $secondHalf;
                return;
            }
        }
        
        // Pattern 2: Look for common separators or patterns
        // Some entries have "KRD" or similar codes between name and PFMS name
        if (preg_match('/^(.+?)\s+([A-Z]{2,4})\s+(.+)$/', $nameSection, $matches)) {
            $entry['name'] = trim($matches[1]);
            $entry['name_in_pfms'] = trim($matches[3]);
            return;
        }
        
        // If no clear split found, use full name as name, PFMS empty
        $entry['name'] = $nameSection;
    }

    private function extractUnstructuredData($text)
    {
        $beneficiaries = [];
        
        // Extract account numbers (typically 10-16 digits, may have xxxx masking)
        preg_match_all('/(?:Account\s*No|Account\s*Number|A\/c\s*No)[\s:]*([\d\sxX]{10,20})/i', $text, $accountMatches);
        
        // Extract IFSC codes (typically 11 characters)
        preg_match_all('/(?:IFSC|IFSC\s*Code)[\s:]*([A-Z]{4}0[A-Z0-9]{6})/i', $text, $ifscMatches);
        
        // Extract amounts (numbers with decimal points)
        preg_match_all('/(\d{1,3}(?:,\d{2,3})*(?:\.\d{2})?)/', $text, $amountMatches);
        
        // Extract names (look for capitalized words)
        preg_match_all('/([A-Z][a-z]+(?:\s+[A-Z][a-z]+)+)/', $text, $nameMatches);
        
        // Try to match patterns that look like beneficiary entries
        $lines = explode("\n", $text);
        foreach ($lines as $line) {
            $line = trim($line);
            
            // Look for lines with name, account number, IFSC, and amount
            if (preg_match('/([A-Z][a-z]+(?:\s+[A-Z][a-z]+)+)\s+([\d\sxX]{10,20})\s+([A-Z]{4}0[A-Z0-9]{6})\s+(\d{1,3}(?:,\d{2,3})*(?:\.\d{2})?)/', $line, $matches)) {
                $beneficiaries[] = [
                    'txn_id' => '',
                    'name' => trim($matches[1] ?? ''),
                    'name_in_pfms' => '',
                    'account_no' => preg_replace('/[^0-9]/', '', $matches[2] ?? ''),
                    'ifsc' => $matches[3] ?? '',
                    'amount' => floatval(str_replace(',', '', $matches[4] ?? 0)),
                ];
            }
        }
        
        return $beneficiaries;
    }

    private function saveExtractedData(ReceiptPaymentAccount $account, $extractedData)
    {
        $savedCount = 0;
        foreach ($extractedData as $data) {
            if (!empty($data['name']) && isset($data['amount']) && $data['amount'] > 0) {
                try {
                    // Build remarks with all extracted information
                    $remarksParts = [];
                    
                    if (!empty($data['order_id'])) {
                        $remarksParts[] = 'Order ID: ' . $data['order_id'];
                    }
                    
                    $ppaDate = null;
                    if (!empty($data['generated_on'])) {
                        // Format date: convert "03-08-2025 22:50:23" to "03/08/2025"
                        $dateStr = $data['generated_on'];
                        // Extract date part (before space) and convert dashes to slashes
                        if (preg_match('/^(\d{2})-(\d{2})-(\d{4})/', $dateStr, $dateMatches)) {
                            $formattedDate = $dateMatches[1] . '/' . $dateMatches[2] . '/' . $dateMatches[3];
                            $remarksParts[] = 'PPA Date: ' . $formattedDate;
                            $ppaDate = $formattedDate; // Store in dd/mm/yyyy format
                        } else {
                            $remarksParts[] = 'PPA Date: ' . $dateStr;
                            $ppaDate = $dateStr;
                        }
                    }
                    
                    $txnId = $data['txn_id'] ?? null;
                    $remarksParts[] = 'Txn ID: ' . ($txnId ?? 'N/A');
                    
                    $remarks = implode(' - ', $remarksParts);
                    
                    // Create both RECEIPT and PAYMENT entries with transaction ID in pair_id
                    $entryData = [
                        'receipt_payment_account_id' => $account->id,
                        'particular_name' => $data['name'],
                        'acode' => $data['ifsc'] ?? '',
                        'amount' => $data['amount'],
                        'remarks' => $remarks,
                        'date' => $ppaDate, // Store PPA date in dd/mm/yyyy format
                        'pair_id' => $txnId, // Store transaction ID in pair_id
                    ];
                    
                    // Create receipt entry first
                    $receiptEntry = ReceiptPaymentEntry::create(array_merge($entryData, [
                        'type' => 'receipt',
                    ]));
                    $savedCount++;
                    
                    // Create payment entry with same transaction ID in pair_id
                    $paymentEntry = ReceiptPaymentEntry::create(array_merge($entryData, ['type' => 'payment']));
                    $savedCount++;
                } catch (\Exception $e) {
                    // Log error with full details but continue with other entries
                    Log::error('Failed to save PDF extracted entry: ' . $e->getMessage());
                    Log::error('Entry data: ' . json_encode($entryData));
                    Log::error('Stack trace: ' . $e->getTraceAsString());
                }
            }
        }
        return $savedCount;
    }

    public function save(Request $request)
    {
        $request->validate([
            'receipt_payment_account_id' => ['required', 'exists:receipt_payment_accounts,id'],
            'extracted_data' => ['required', 'json'],
        ]);

        $account = ReceiptPaymentAccount::findOrFail($request->receipt_payment_account_id);
        $extractedData = json_decode($request->extracted_data, true);

        if (!is_array($extractedData)) {
            return back()->withErrors(['extracted_data' => 'Invalid data format.']);
        }

        if (empty($extractedData)) {
            return back()->withErrors(['extracted_data' => 'No data to save.']);
        }

        $savedCount = $this->saveExtractedData($account, $extractedData);

        if ($savedCount > 0) {
            $beneficiaryCount = count($extractedData);
            return redirect()
                ->route('receipt_payments.show', $account)
                ->with('status', "PDF data saved successfully. {$beneficiaryCount} beneficiaries saved as {$savedCount} entries (payment and receipt).");
        } else {
            return redirect()
                ->route('receipt_payments.show', $account)
                ->with('error', 'No valid beneficiary data found to save. Please check the PDF format.');
        }
    }
}
