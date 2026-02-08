<?php

namespace App\Services;

use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class StudentImportService
{
    protected $totalRows = 0;
    protected $insertedCount = 0;
    protected $skippedCount = 0;
    protected $skippedRows = [];

    /**
     * Get all fillable columns for Student model
     */
    protected function getFillableColumns()
    {
        return (new Student)->getFillable();
    }

    /**
     * Get comprehensive column mapping
     */
    protected function getColumnMapping()
    {
        return [
            // Name variations
            'name' => 'student_name',
            'student name' => 'student_name',
            'studentname' => 'student_name',
            'student_name' => 'student_name',
            'full name' => 'student_name',
            'fullname' => 'student_name',
            
            // Roll No / Student ID variations
            'roll no' => 'student_id',
            'rollno' => 'student_id',
            'roll_no' => 'student_id',
            'roll number' => 'student_id',
            'rollnumber' => 'student_id',
            'student id' => 'student_id',
            'studentid' => 'student_id',
            'student_id' => 'student_id',
            'id' => 'student_id',
            
            // Phone/Mobile variations
            'phone' => 'mobile_no',
            'phone number' => 'mobile_no',
            'phonenumber' => 'mobile_no',
            'mobile' => 'mobile_no',
            'mobile no' => 'mobile_no',
            'mobileno' => 'mobile_no',
            'mobile_no' => 'mobile_no',
            'mobile number' => 'mobile_no',
            'mobilenumber' => 'mobile_no',
            'contact' => 'mobile_no',
            'contact no' => 'mobile_no',
            'contactno' => 'mobile_no',
            'contact number' => 'mobile_no',
            'contactnumber' => 'mobile_no',
            
            // SL No variations
            'sl no' => 'sl_no',
            'slno' => 'sl_no',
            'sl_no' => 'sl_no',
            'serial no' => 'sl_no',
            'serialno' => 'sl_no',
            'serial number' => 'sl_no',
            'serialnumber' => 'sl_no',
            'sno' => 'sl_no',
            
            // Admission Date variations
            'admission date' => 'admission_date',
            'admissiondate' => 'admission_date',
            'admission_date' => 'admission_date',
            'admit date' => 'admission_date',
            'admitdate' => 'admission_date',
            
            // Admission No variations
            'admission no' => 'admission_no',
            'admissionno' => 'admission_no',
            'admission_no' => 'admission_no',
            'admission number' => 'admission_no',
            'admissionnumber' => 'admission_no',
            'admit no' => 'admission_no',
            'admitno' => 'admission_no',
            
            // Class variations
            'class' => 'class',
            'grade' => 'class',
            'standard' => 'class',
            
            // DOB variations
            'dob' => 'dob',
            'date of birth' => 'dob',
            'dateofbirth' => 'dob',
            'birth date' => 'dob',
            'birthdate' => 'dob',
            'birthday' => 'dob',
            
            // Age
            'age' => 'age',
            
            // Father Name variations
            'father name' => 'father_name',
            'fathername' => 'father_name',
            'father_name' => 'father_name',
            'father' => 'father_name',
            'fathers name' => 'father_name',
            'fathersname' => 'father_name',
            
            // Mother Name variations
            'mother name' => 'mother_name',
            'mothername' => 'mother_name',
            'mother_name' => 'mother_name',
            'mother' => 'mother_name',
            'mothers name' => 'mother_name',
            'mothersname' => 'mother_name',
            
            // Caste
            'caste' => 'caste',
            'category' => 'caste',
            
            // Address variations
            'village' => 'village',
            'address village' => 'village',
            'addressvillage' => 'village',
            'address_village' => 'village', // Legacy support
            
            'po' => 'post_office',
            'post office' => 'post_office',
            'postoffice' => 'post_office',
            'post_office' => 'post_office',
            'address po' => 'post_office',
            'addresspo' => 'post_office',
            'address_po' => 'post_office', // Legacy support
            
            'subdiv' => 'subdiv',
            'sub div' => 'subdiv',
            'sub division' => 'subdiv',
            'subdivision' => 'subdiv',
            'address subdiv' => 'subdiv',
            'addresssubdiv' => 'subdiv',
            'address_subdiv' => 'subdiv', // Legacy support
            
            'panchayat' => 'panchayat',
            'address panchayat' => 'panchayat',
            'addresspanchayat' => 'panchayat',
            'address_panchayat' => 'panchayat', // Legacy support
            
            'district' => 'dist',
            'dist' => 'dist',
            'address district' => 'dist',
            'addressdistrict' => 'dist',
            'address_dist' => 'dist', // Legacy support
            
            'state' => 'state',
            'address state' => 'state',
            'addressstate' => 'state',
            'address_state' => 'state', // Legacy support
            
            'pincode' => 'pincode',
            'pin code' => 'pincode',
            'pin' => 'pincode',
            'address pincode' => 'pincode',
            'addresspincode' => 'pincode',
            'address_pincode' => 'pincode', // Legacy support
            
            // Combined Address field
            'address' => 'address',
            'complete address' => 'address',
            'full address' => 'address',
            
            // Other fields
            'pen number' => 'pen_number',
            'pennumber' => 'pen_number',
            'pen_number' => 'pen_number',
            'pen no' => 'pen_number',
            'penno' => 'pen_number',
            
            'aapaar id' => 'aapaar_id',
            'aapaarid' => 'aapaar_id',
            'aapaar_id' => 'aapaar_id',
            
            'aadhar number' => 'aadhar_number',
            'aadharnumber' => 'aadhar_number',
            'aadhar_number' => 'aadhar_number',
            'aadhar no' => 'aadhar_number',
            'aadharno' => 'aadhar_number',
            'aadhaar number' => 'aadhar_number',
            'aadhaarnumber' => 'aadhar_number',
            'aadhaar no' => 'aadhar_number',
            'aadhaarno' => 'aadhar_number',
            
            'father aadhar number' => 'father_aadhar_number',
            'fatheraadharnumber' => 'father_aadhar_number',
            'father_aadhar_number' => 'father_aadhar_number',
            'father aadhar no' => 'father_aadhar_number',
            'fatheraadharno' => 'father_aadhar_number',
            
            'father voter id no' => 'father_voter_id_no',
            'fathervoteridno' => 'father_voter_id_no',
            'father_voter_id_no' => 'father_voter_id_no',
            'father voter id' => 'father_voter_id_no',
            'fathervoterid' => 'father_voter_id_no',
            
            'father pan no' => 'father_pan_no',
            'fatherpanno' => 'father_pan_no',
            'father_pan_no' => 'father_pan_no',
            'father pan' => 'father_pan_no',
            'fatherpan' => 'father_pan_no',
            
            'dropout school' => 'dropout_school',
            'dropoutschool' => 'dropout_school',
            'dropout_school' => 'dropout_school',
            
            'dropout date' => 'dropout_date',
            'dropoutdate' => 'dropout_date',
            'dropout_date' => 'dropout_date',
            
            'dropout reason' => 'dropout_reason',
            'dropoutreason' => 'dropout_reason',
            'dropout_reason' => 'dropout_reason',
            
            'height' => 'height',
            
            'weight' => 'weight',
            
            'blood group' => 'blood_group',
            'bloodgroup' => 'blood_group',
            'blood_group' => 'blood_group',
            'blood' => 'blood_group',
            
            'vendor code' => 'vendor_code',
            'vendorcode' => 'vendor_code',
            'vendor_code' => 'vendor_code',
            
            'bank name' => 'bank_name',
            'bankname' => 'bank_name',
            'bank_name' => 'bank_name',
            'bank' => 'bank_name',
            
            'branch name' => 'branch_name',
            'branchname' => 'branch_name',
            'branch_name' => 'branch_name',
            'branch' => 'branch_name',
            
            'ifsc' => 'ifsc',
            'ifsc code' => 'ifsc',
            'ifsccode' => 'ifsc',
            'ifsc_code' => 'ifsc',
            
            'account no' => 'account_no',
            'accountno' => 'account_no',
            'account_no' => 'account_no',
            'account number' => 'account_no',
            'accountnumber' => 'account_no',
            'account' => 'account_no',
        ];
    }

    /**
     * Import students from Excel/CSV file
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
            $headerKeywords = ['name', 'roll', 'student', 'phone', 'email', 'mobile', 'id', 'admission', 'class'];
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
                        continue; // Skip empty values
                    }

                    // Normalize Excel column name
                    $normalizedExcelColumn = $this->normalizeColumnName($excelColumn);
                    
                    // Check if we have a mapping for this column
                    if (isset($columnMapping[$normalizedExcelColumn])) {
                        $dbColumn = $columnMapping[$normalizedExcelColumn];
                        if (in_array($dbColumn, $fillableColumns)) {
                            $mappedData[$dbColumn] = $value;
                        }
                    } else {
                        // Try direct match (case-insensitive, handle spaces/underscores)
                        $directMatch = $this->findDirectColumnMatch($excelColumn, $fillableColumns);
                        if ($directMatch) {
                            $mappedData[$directMatch] = $value;
                        }
                        // If no match found, just skip this column (don't error)
                    }
                }

                // Validate required fields
                $validationResult = $this->validateRow($mappedData, $rowNumber);
                
                if (!$validationResult['valid']) {
                    $this->skippedCount++;
                    $this->skippedRows[] = [
                        'row' => $rowNumber,
                        'data' => $associativeRow,
                        'reason' => $validationResult['errors']
                    ];
                    continue;
                }

                // Check for duplicate student_id
                if (!empty($mappedData['student_id'])) {
                    $existing = Student::where('student_id', $mappedData['student_id'])->first();
                    if ($existing) {
                        $this->skippedCount++;
                        $this->skippedRows[] = [
                            'row' => $rowNumber,
                            'data' => $associativeRow,
                            'reason' => 'Duplicate roll_no (student_id): ' . $mappedData['student_id'] . ' already exists'
                        ];
                        continue;
                    }
                }

                // Parse combined address field if provided
                if (isset($mappedData['address']) && !empty($mappedData['address'])) {
                    $this->parseCombinedAddress($mappedData);
                }
                
                // Prepare data for insertion
                $studentData = $this->prepareStudentData($mappedData);

                // Insert student
                Student::create($studentData);
                $this->insertedCount++;
            }

            DB::commit();

            return [
                'total_rows' => $this->totalRows,
                'inserted' => $this->insertedCount,
                'skipped' => $this->skippedCount,
                'skipped_rows' => $this->skippedRows
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Find direct column match in fillable columns
     */
    protected function findDirectColumnMatch($excelColumn, $fillableColumns)
    {
        $normalizedExcel = $this->normalizeColumnName($excelColumn);
        
        foreach ($fillableColumns as $dbColumn) {
            $normalizedDb = $this->normalizeColumnName($dbColumn);
            if ($normalizedExcel === $normalizedDb) {
                return $dbColumn;
            }
        }
        
        return null;
    }

    /**
     * Normalize column name for matching
     */
    protected function normalizeColumnName($columnName)
    {
        // Convert to lowercase, remove spaces, underscores, and special characters
        $normalized = strtolower(trim((string)$columnName));
        $normalized = preg_replace('/[^a-z0-9]/', '', $normalized);
        return $normalized;
    }

    /**
     * Validate a single row
     */
    protected function validateRow($data, $rowNumber)
    {
        $errors = [];

        // Required fields
        if (empty($data['student_name'])) {
            $errors[] = 'Name (student_name) is required';
        }

        // Roll no (student_id) is required
        if (empty($data['student_id'])) {
            $errors[] = 'Roll No (student_id) is required';
        }

        // Mobile number validation if provided
        if (isset($data['mobile_no']) && !empty($data['mobile_no'])) {
            $mobileNo = preg_replace('/[^0-9]/', '', (string)$data['mobile_no']);
            if (strlen($mobileNo) !== 10 && strlen($mobileNo) > 0) {
                $errors[] = 'Mobile number must be 10 digits';
            } else {
                $data['mobile_no'] = $mobileNo;
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => implode('; ', $errors)
        ];
    }

    /**
     * Prepare student data for insertion
     */
    protected function prepareStudentData($data)
    {
        $prepared = [];
        $fillable = $this->getFillableColumns();
        
        // Only include fillable fields
        foreach ($fillable as $field) {
            if (isset($data[$field])) {
                $prepared[$field] = $data[$field];
            }
        }

        // Auto-increment SL No if not provided
        if (empty($prepared['sl_no'])) {
            $lastStudent = Student::orderBy('sl_no', 'desc')->first();
            $prepared['sl_no'] = $lastStudent && $lastStudent->sl_no ? $lastStudent->sl_no + 1 : 1;
        }

        // Auto-calculate age from DOB if provided
        if (!empty($prepared['dob']) && empty($prepared['age'])) {
            try {
                $dob = $this->parseDate($prepared['dob']);
                if ($dob) {
                    $now = Carbon::now();
                    $age = $now->diff($dob);
                    $prepared['age'] = $age->y . ' Years, ' . $age->m . ' Months, ' . $age->d . ' Days';
                    // DOB is stored as date type, so convert to Y-m-d for database
                    $prepared['dob'] = $dob->format('Y-m-d');
                }
            } catch (\Exception $e) {
                // If date parsing fails, try to format as date
                try {
                    $prepared['dob'] = $this->parseDate($prepared['dob'])->format('Y-m-d');
                } catch (\Exception $e2) {
                    // Leave as is if parsing fails
                }
            }
        } elseif (!empty($prepared['dob'])) {
            // Just format the date - DOB must be Y-m-d format for DATE column
            try {
                $date = $this->parseDate($prepared['dob']);
                if ($date) {
                    $prepared['dob'] = $date->format('Y-m-d');
                }
            } catch (\Exception $e) {
                // Leave as is
            }
        }

        // Format dates - store in dd-mm-yyyy format
        if (!empty($prepared['admission_date'])) {
            try {
                $date = $this->parseDate($prepared['admission_date']);
                if ($date) {
                    $prepared['admission_date'] = $date->format('d-m-Y');
                }
            } catch (\Exception $e) {
                // Leave as is
            }
        }

        if (!empty($prepared['dropout_date'])) {
            try {
                $date = $this->parseDate($prepared['dropout_date']);
                if ($date) {
                    $prepared['dropout_date'] = $date->format('d-m-Y');
                }
            } catch (\Exception $e) {
                // Leave as is
            }
        }

        // Only combine if address is not already set (e.g., from combined address import)
        if (empty($prepared['address'])) {
            $addressParts = array_filter([
                $prepared['village'] ?? null,
                $prepared['post_office'] ?? null,
                $prepared['subdiv'] ?? null,
                $prepared['panchayat'] ?? null,
                $prepared['dist'] ?? null,
                $prepared['state'] ?? null,
                $prepared['pincode'] ?? null,
            ]);
            if (!empty($addressParts)) {
                $prepared['address'] = implode(', ', $addressParts);
            }
        }

        // Clean numeric fields
        if (isset($prepared['height']) && $prepared['height'] !== null) {
            $prepared['height'] = is_numeric($prepared['height']) ? (float)$prepared['height'] : null;
        }

        if (isset($prepared['weight']) && $prepared['weight'] !== null) {
            $prepared['weight'] = is_numeric($prepared['weight']) ? (float)$prepared['weight'] : null;
        }

        return $prepared;
    }

    /**
     * Parse combined address into individual fields
     */
    protected function parseCombinedAddress(&$data)
    {
        if (empty($data['address'])) {
            return;
        }
        
        $address = trim($data['address']);
        
        // Set address directly from the combined address
        $data['address'] = $address;
        
        // Also split into individual fields for better data structure
        // Split by comma
        $parts = array_map('trim', explode(',', $address));
        
        // Map parts to individual address fields (only if not already set)
        if (isset($parts[0]) && !empty($parts[0]) && empty($data['village'])) {
            $data['village'] = $parts[0];
        }
        if (isset($parts[1]) && !empty($parts[1]) && empty($data['post_office'])) {
            $data['post_office'] = $parts[1];
        }
        if (isset($parts[2]) && !empty($parts[2]) && empty($data['subdiv'])) {
            $data['subdiv'] = $parts[2];
        }
        if (isset($parts[3]) && !empty($parts[3]) && empty($data['panchayat'])) {
            $data['panchayat'] = $parts[3];
        }
        if (isset($parts[4]) && !empty($parts[4]) && empty($data['dist'])) {
            $data['dist'] = $parts[4];
        }
        if (isset($parts[5]) && !empty($parts[5]) && empty($data['state'])) {
            $data['state'] = $parts[5];
        }
        if (isset($parts[6]) && !empty($parts[6]) && empty($data['pincode'])) {
            $data['pincode'] = $parts[6];
        }
    }

    /**
     * Parse date from various formats
     */
    protected function parseDate($dateString)
    {
        if (empty($dateString)) {
            return null;
        }

        // Try common date formats
        $formats = ['Y-m-d', 'd-m-Y', 'd/m/Y', 'Y/m/d', 'm-d-Y', 'm/d/Y', 'd.m.Y', 'Y.m.d'];
        
        foreach ($formats as $format) {
            try {
                $date = Carbon::createFromFormat($format, trim((string)$dateString));
                if ($date) {
                    return $date;
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        // Try Carbon's parse as fallback
        try {
            return Carbon::parse(trim((string)$dateString));
        } catch (\Exception $e) {
            return null;
        }
    }
}
