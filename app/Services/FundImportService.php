<?php

namespace App\Services;

use App\Models\Fund;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class FundImportService
{
    protected $totalRows = 0;
    protected $insertedCount = 0;
    protected $skippedCount = 0;
    protected $skippedRows = [];

    /**
     * Get all fillable columns for Fund model
     */
    protected function getFillableColumns()
    {
        return (new Fund)->getFillable();
    }

    /**
     * Get comprehensive column mapping
     */
    protected function getColumnMapping()
    {
        return [
            // Date variations
            'date' => 'fund_date',
            'fund date' => 'fund_date',
            'funddate' => 'fund_date',
            'fund_date' => 'fund_date',
            'transaction date' => 'fund_date',
            'transactiondate' => 'fund_date',
            
            // Component Name variations
            'component name' => 'component_name',
            'componentname' => 'component_name',
            'component_name' => 'component_name',
            'fund for' => 'component_name',
            'fundfor' => 'component_name',
            'article name' => 'component_name',
            'articlename' => 'component_name',
            'article_name' => 'component_name',
            'name' => 'component_name',
            
            // Component Code variations
            'component code' => 'component_code',
            'componentcode' => 'component_code',
            'component_code' => 'component_code',
            'code' => 'component_code',
            'acode' => 'component_code',
            'article code' => 'component_code',
            'articlecode' => 'component_code',
            'article_code' => 'component_code',
            
            // Amount variations
            'amount' => 'amount',
            'total' => 'amount',
            'value' => 'amount',
            'fund amount' => 'amount',
            'fundamount' => 'amount',
            
            // Remark variations
            'remark' => 'remark',
            'remarks' => 'remark',
            'note' => 'remark',
            'notes' => 'remark',
            'description' => 'remark',
            'comment' => 'remark',
            'comments' => 'remark',
        ];
    }

    /**
     * Normalize column name for matching
     */
    protected function normalizeColumnName($columnName)
    {
        if (empty($columnName)) {
            return '';
        }
        
        // Convert to lowercase and remove special characters
        $normalized = strtolower(trim((string)$columnName));
        $normalized = preg_replace('/[^a-z0-9]/', '', $normalized);
        
        return $normalized;
    }

    /**
     * Import funds from Excel/CSV file
     */
    public function import($file)
    {
        try {
            $data = Excel::toArray([], $file);
        } catch (\Exception $e) {
            throw new \Exception('Error reading file: ' . $e->getMessage());
        }

        if (empty($data) || empty($data[0])) {
            throw new \Exception('The file is empty or invalid.');
        }

        $rows = $data[0];
        $this->totalRows = count($rows);

        // Get headers from first row
        $headers = [];
        if (!empty($rows[0])) {
            $firstRow = $rows[0];
            // Check if first row looks like headers
            $headerKeywords = ['date', 'component', 'amount', 'remark', 'fund', 'code', 'name'];
            $isHeaderRow = false;
            foreach ($firstRow as $cell) {
                $cellLower = strtolower(trim((string)$cell));
                foreach ($headerKeywords as $keyword) {
                    if (strpos($cellLower, $keyword) !== false) {
                        $isHeaderRow = true;
                        break 2;
                    }
                }
            }
            
            if ($isHeaderRow) {
                $headers = array_map('trim', array_map('strval', $firstRow));
                $rows = array_slice($rows, 1);
                $this->totalRows = count($rows);
            }
        }

        $columnMapping = $this->getColumnMapping();
        $fillableColumns = $this->getFillableColumns();

        DB::beginTransaction();
        try {
            foreach ($rows as $rowIndex => $row) {
                $rowNumber = $rowIndex + 2; // +2 because Excel rows start at 1 and we skip header

                // Convert numeric array to associative array using headers
                $associativeRow = [];
                if (!empty($headers)) {
                    foreach ($headers as $colIndex => $header) {
                        $associativeRow[$header] = isset($row[$colIndex]) ? $row[$colIndex] : null;
                    }
                } else {
                    // If no headers, try to use first row as headers (fallback)
                    if ($rowIndex === 0 && !empty($row)) {
                        $headers = array_map('trim', array_map('strval', $row));
                        continue;
                    }
                    // Use numeric indices
                    $associativeRow = $row;
                }

                // Map all columns from Excel to database columns
                $mappedData = [];
                
                foreach ($associativeRow as $excelColumn => $value) {
                    if ($value === null || $value === '') {
                        continue;
                    }
                    
                    // Normalize Excel column name
                    $normalizedExcelColumn = $this->normalizeColumnName($excelColumn);
                    
                    // Check if this column maps to a database column
                    if (isset($columnMapping[$normalizedExcelColumn])) {
                        $dbColumn = $columnMapping[$normalizedExcelColumn];
                        
                        // Only include if it's a fillable column
                        if (in_array($dbColumn, $fillableColumns)) {
                            $mappedData[$dbColumn] = trim((string)$value);
                        }
                    }
                }

                // Validate required fields (all are nullable, so skip if empty)
                if (empty($mappedData)) {
                    $this->skippedCount++;
                    $this->skippedRows[] = [
                        'row' => $rowNumber,
                        'reason' => 'No valid data found',
                        'data' => $associativeRow
                    ];
                    continue;
                }

                // Prepare data for insertion
                $preparedData = $this->prepareFundData($mappedData, $rowNumber);

                if ($preparedData === null) {
                    $this->skippedCount++;
                    continue;
                }

                // Insert into database
                try {
                    Fund::create($preparedData);
                    $this->insertedCount++;
                } catch (\Exception $e) {
                    $this->skippedCount++;
                    $this->skippedRows[] = [
                        'row' => $rowNumber,
                        'reason' => 'Database error: ' . $e->getMessage(),
                        'data' => $associativeRow
                    ];
                }
            }

            DB::commit();

            return [
                'total_rows' => $this->totalRows,
                'inserted' => $this->insertedCount,
                'skipped' => $this->skippedCount,
                'skipped_rows' => $this->skippedRows,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Prepare fund data for insertion
     */
    protected function prepareFundData($mappedData, $rowNumber)
    {
        $prepared = [];

        // Process fund_date - should be in dd/mm/yyyy format
        if (isset($mappedData['fund_date'])) {
            $dateValue = trim($mappedData['fund_date']);
            if (!empty($dateValue)) {
                // Try to parse various date formats
                try {
                    // If it's already in dd/mm/yyyy format, use as is
                    if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $dateValue)) {
                        $prepared['fund_date'] = $dateValue;
                    } 
                    // Try to parse other formats and convert to dd/mm/yyyy
                    else {
                        // Try dd-mm-yyyy
                        if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $dateValue)) {
                            $prepared['fund_date'] = str_replace('-', '/', $dateValue);
                        }
                        // Try yyyy-mm-dd
                        elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateValue)) {
                            $date = Carbon::createFromFormat('Y-m-d', $dateValue);
                            $prepared['fund_date'] = $date->format('d/m/Y');
                        }
                        // Try Excel date serial number (if PhpSpreadsheet is available)
                        elseif (is_numeric($dateValue) && class_exists('\PhpOffice\PhpSpreadsheet\Shared\Date')) {
                            try {
                                $date = Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($dateValue));
                                $prepared['fund_date'] = $date->format('d/m/Y');
                            } catch (\Exception $e) {
                                // Fall through to Carbon parse
                                $date = Carbon::parse($dateValue);
                                $prepared['fund_date'] = $date->format('d/m/Y');
                            }
                        }
                        // Try Carbon parse
                        else {
                            $date = Carbon::parse($dateValue);
                            $prepared['fund_date'] = $date->format('d/m/Y');
                        }
                    }
                } catch (\Exception $e) {
                    $this->skippedRows[] = [
                        'row' => $rowNumber,
                        'reason' => 'Invalid date format: ' . $dateValue,
                        'data' => $mappedData
                    ];
                    return null;
                }
            }
        }

        // Process component_name
        if (isset($mappedData['component_name'])) {
            $prepared['component_name'] = trim($mappedData['component_name']);
        }

        // Process component_code
        if (isset($mappedData['component_code'])) {
            $prepared['component_code'] = trim($mappedData['component_code']);
        }

        // Process amount
        if (isset($mappedData['amount'])) {
            $amount = trim($mappedData['amount']);
            if (!empty($amount)) {
                // Remove currency symbols and commas
                $amount = preg_replace('/[₹,\s]/', '', $amount);
                if (is_numeric($amount)) {
                    $prepared['amount'] = (float)$amount;
                }
            }
        }

        // Process remark
        if (isset($mappedData['remark'])) {
            $prepared['remark'] = trim($mappedData['remark']);
        }

        return $prepared;
    }
}
