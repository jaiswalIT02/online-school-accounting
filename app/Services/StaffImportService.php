<?php

namespace App\Services;

use App\Models\Staff;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class StaffImportService
{
    protected $totalRows = 0;
    protected $insertedCount = 0;
    protected $skippedCount = 0;
    protected $skippedRows = [];

    /**
     * Get all fillable columns for Staff model
     */
    protected function getFillableColumns()
    {
        return (new Staff)->getFillable();
    }

    /**
     * Get comprehensive column mapping
     */
    protected function getColumnMapping()
    {
        return [
            // Name variations
            'name' => 'full_name',
            'full name' => 'full_name',
            'fullname' => 'full_name',
            'full_name' => 'full_name',
            'staff name' => 'full_name',
            'staffname' => 'full_name',
            'employee name' => 'full_name',
            'employeename' => 'full_name',
            
            // Employee ID / Roll No variations
            'roll no' => 'employee_id',
            'rollno' => 'employee_id',
            'roll_no' => 'employee_id',
            'roll number' => 'employee_id',
            'rollnumber' => 'employee_id',
            'employee id' => 'employee_id',
            'employeeid' => 'employee_id',
            'employee_id' => 'employee_id',
            'emp id' => 'employee_id',
            'empid' => 'employee_id',
            'id' => 'employee_id',
            
            // Phone/Contact variations
            'phone' => 'contact_no',
            'phone number' => 'contact_no',
            'phonenumber' => 'contact_no',
            'mobile' => 'contact_no',
            'mobile no' => 'contact_no',
            'mobileno' => 'contact_no',
            'mobile_no' => 'contact_no',
            'mobile number' => 'contact_no',
            'mobilenumber' => 'contact_no',
            'contact' => 'contact_no',
            'contact no' => 'contact_no',
            'contactno' => 'contact_no',
            'contact_no' => 'contact_no',
            'contact number' => 'contact_no',
            'contactnumber' => 'contact_no',
            
            // Email
            'email' => 'email',
            'email address' => 'email',
            'emailaddress' => 'email',
            'e mail' => 'email',
            'emailid' => 'email',
            
            // Serial No variations
            'serial no' => 'serial_no',
            'serialno' => 'serial_no',
            'serial_no' => 'serial_no',
            'serial number' => 'serial_no',
            'serialnumber' => 'serial_no',
            'sno' => 'serial_no',
            
            // National ID
            'national id' => 'national_id',
            'nationalid' => 'national_id',
            'national_id' => 'national_id',
            
            // Gender
            'gender' => 'gender',
            'sex' => 'gender',
            
            // Date of Birth variations
            'dob' => 'date_of_birth',
            'date of birth' => 'date_of_birth',
            'dateofbirth' => 'date_of_birth',
            'date_of_birth' => 'date_of_birth',
            'birth date' => 'date_of_birth',
            'birthdate' => 'date_of_birth',
            'birthday' => 'date_of_birth',
            
            // Age
            'age' => 'age',
            
            // Caste
            'caste' => 'caste',
            'category' => 'caste',
            
            // Marital Status
            'marital status' => 'marital_status',
            'maritalstatus' => 'marital_status',
            'marital_status' => 'marital_status',
            'marriage status' => 'marital_status',
            'marriagestatus' => 'marital_status',
            
            // Designation
            'designation' => 'designation',
            'post' => 'designation',
            'position' => 'designation',
            'job title' => 'designation',
            'jobtitle' => 'designation',
            
            // Date of Joining variations
            'date of joining' => 'date_of_joining',
            'dateofjoining' => 'date_of_joining',
            'date_of_joining' => 'date_of_joining',
            'joining date' => 'date_of_joining',
            'joiningdate' => 'date_of_joining',
            'doj' => 'date_of_joining',
            
            // Bank details
            'bank name' => 'bank_name',
            'bankname' => 'bank_name',
            'bank_name' => 'bank_name',
            'bank' => 'bank_name',
            
            'account no' => 'account_no',
            'accountno' => 'account_no',
            'account_no' => 'account_no',
            'account number' => 'account_no',
            'accountnumber' => 'account_no',
            'account' => 'account_no',
            
            'ifsc code' => 'ifsc_code',
            'ifsccode' => 'ifsc_code',
            'ifsc_code' => 'ifsc_code',
            'ifsc' => 'ifsc_code',
            
            // Component and Vendor
            'component code' => 'component_code',
            'componentcode' => 'component_code',
            'component_code' => 'component_code',
            'component' => 'component_code',
            
            'vendor code' => 'vendor_code',
            'vendorcode' => 'vendor_code',
            'vendor_code' => 'vendor_code',
            'vendor' => 'vendor_code',
            
            // Aadhaar variations
            'aadhaar no' => 'aadhaar_no',
            'aadhaarno' => 'aadhaar_no',
            'aadhaar_no' => 'aadhaar_no',
            'aadhaar number' => 'aadhaar_no',
            'aadhaarnumber' => 'aadhaar_no',
            'aadhar no' => 'aadhaar_no',
            'aadharno' => 'aadhaar_no',
            'aadhar number' => 'aadhaar_no',
            'aadharnumber' => 'aadhaar_no',
            
            // Voter ID
            'voter id' => 'voter_id',
            'voterid' => 'voter_id',
            'voter_id' => 'voter_id',
            'voter id no' => 'voter_id',
            'voteridno' => 'voter_id',
            
            // PAN No
            'pan no' => 'pan_no',
            'panno' => 'pan_no',
            'pan_no' => 'pan_no',
            'pan' => 'pan_no',
            'pan number' => 'pan_no',
            'pannumber' => 'pan_no',
            
            // Qualification
            'qualification' => 'qualification',
            'qual' => 'qualification',
            'education' => 'qualification',
            
            // Professional Qualification
            'professional qualification' => 'professional_qualification',
            'professionalqualification' => 'professional_qualification',
            'professional_qualification' => 'professional_qualification',
            'prof qualification' => 'professional_qualification',
            'profqualification' => 'professional_qualification',
            
            // Stream
            'stream' => 'stream',
            'subject' => 'stream',
            
            // Father Name
            'father name' => 'father_name',
            'fathername' => 'father_name',
            'father_name' => 'father_name',
            'father' => 'father_name',
            'fathers name' => 'father_name',
            'fathersname' => 'father_name',
            
            // Mother Name
            'mother name' => 'mother_name',
            'mothername' => 'mother_name',
            'mother_name' => 'mother_name',
            'mother' => 'mother_name',
            'mothers name' => 'mother_name',
            'mothersname' => 'mother_name',
            
            // Blood Group
            'blood group' => 'blood_group',
            'bloodgroup' => 'blood_group',
            'blood_group' => 'blood_group',
            'blood' => 'blood_group',
            
            // Height and Weight
            'height' => 'height',
            'weight' => 'weight',
            
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
            
            'sub div' => 'sub_div',
            'subdiv' => 'sub_div',
            'sub division' => 'sub_div',
            'subdivision' => 'sub_div',
            'sub_div' => 'sub_div',
            'address sub div' => 'sub_div',
            'addresssubdiv' => 'sub_div',
            'address_sub_div' => 'sub_div', // Legacy support
            'address subdiv' => 'sub_div',
            
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
            
            // Honors Major In
            'honors major in' => 'honors_major_in',
            'honorsmajorin' => 'honors_major_in',
            'honors_major_in' => 'honors_major_in',
            'honors' => 'honors_major_in',
            'major' => 'honors_major_in',
            
            // Combined Address field
            'address' => 'address',
            'complete address' => 'address',
            'full address' => 'address',
            'address_in_the_format' => 'address', // Legacy support
        ];
    }

    /**
     * Import staff from Excel/CSV file
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
            $headerKeywords = ['name', 'roll', 'employee', 'phone', 'email', 'contact', 'id', 'designation'];
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

                // Check for duplicate employee_id
                if (!empty($mappedData['employee_id'])) {
                    $existing = Staff::where('employee_id', $mappedData['employee_id'])->first();
                    if ($existing) {
                        $this->skippedCount++;
                        $this->skippedRows[] = [
                            'row' => $rowNumber,
                            'data' => $associativeRow,
                            'reason' => 'Duplicate employee_id (roll_no): ' . $mappedData['employee_id'] . ' already exists'
                        ];
                        continue;
                    }
                }

                // Parse combined address field if provided
                if (isset($mappedData['address']) && !empty($mappedData['address'])) {
                    $this->parseCombinedAddress($mappedData);
                }
                
                // Prepare data for insertion
                $staffData = $this->prepareStaffData($mappedData);

                // Insert staff
                Staff::create($staffData);
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
        if (empty($data['full_name'])) {
            $errors[] = 'Name (full_name) is required';
        }

        // Employee ID (roll_no equivalent) is required
        if (empty($data['employee_id'])) {
            $errors[] = 'Employee ID (roll_no) is required';
        }

        // Email validation if provided
        if (isset($data['email']) && !empty($data['email'])) {
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Invalid email format: ' . $data['email'];
            }
        }

        // Contact number validation if provided
        if (isset($data['contact_no']) && !empty($data['contact_no'])) {
            $contactNo = preg_replace('/[^0-9]/', '', (string)$data['contact_no']);
            if (strlen($contactNo) !== 10 && strlen($contactNo) > 0) {
                $errors[] = 'Contact number must be 10 digits';
            } else {
                $data['contact_no'] = $contactNo;
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => implode('; ', $errors)
        ];
    }

    /**
     * Prepare staff data for insertion
     */
    protected function prepareStaffData($data)
    {
        $prepared = [];
        $fillable = $this->getFillableColumns();
        
        // Only include fillable fields
        foreach ($fillable as $field) {
            if (isset($data[$field])) {
                $prepared[$field] = $data[$field];
            }
        }

        // Auto-increment Serial No if not provided
        if (empty($prepared['serial_no'])) {
            $lastStaff = Staff::orderBy('serial_no', 'desc')->first();
            $prepared['serial_no'] = $lastStaff && $lastStaff->serial_no ? $lastStaff->serial_no + 1 : 1;
        }

        // Auto-calculate age from DOB if provided
        if (!empty($prepared['date_of_birth']) && empty($prepared['age'])) {
            try {
                $dob = $this->parseDate($prepared['date_of_birth']);
                if ($dob) {
                    $now = Carbon::now();
                    $age = $now->diff($dob);
                    $prepared['age'] = $age->y . ' Years, ' . $age->m . ' Months, ' . $age->d . ' Days';
                    // Store date_of_birth as dd-mm-yyyy format (string column)
                    $prepared['date_of_birth'] = $dob->format('d-m-Y');
                }
            } catch (\Exception $e) {
                // If date parsing fails, try to format as date
                try {
                    $parsedDate = $this->parseDate($prepared['date_of_birth']);
                    if ($parsedDate) {
                        $prepared['date_of_birth'] = $parsedDate->format('d-m-Y');
                    }
                } catch (\Exception $e2) {
                    // Leave as is if parsing fails
                }
            }
        } elseif (!empty($prepared['date_of_birth'])) {
            // Just format the date - store as dd-mm-yyyy format
            try {
                $date = $this->parseDate($prepared['date_of_birth']);
                if ($date) {
                    $prepared['date_of_birth'] = $date->format('d-m-Y');
                }
            } catch (\Exception $e) {
                // Leave as is
            }
        }

        // Format dates
        if (!empty($prepared['date_of_joining'])) {
            try {
                $date = $this->parseDate($prepared['date_of_joining']);
                if ($date) {
                    $prepared['date_of_joining'] = $date->format('d-m-Y');
                }
            } catch (\Exception $e) {
                // Leave as is
            }
        }

        // Combine address fields into format: Village,PO,Sub Div,Panchayat,Dist,State,Pincode
        // Only combine if address is not already set (e.g., from combined address import)
        if (empty($prepared['address'])) {
            $addressParts = array_filter([
                $prepared['village'] ?? null,
                $prepared['post_office'] ?? null,
                $prepared['sub_div'] ?? null,
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
        if (isset($parts[2]) && !empty($parts[2]) && empty($data['sub_div'])) {
            $data['sub_div'] = $parts[2];
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
        
        // Remove the temporary 'address' field as it's not in fillable
        unset($data['address']);
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
