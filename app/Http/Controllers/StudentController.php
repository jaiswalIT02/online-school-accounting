<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Services\StudentImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Student::query()->where('status', 0); // Only show active records

        // Apply filters
        if ($request->filled('admission_date_from')) {
            try {
                // Admission dates are stored as dd-mm-yyyy format in database
                $dateFrom = Carbon::createFromFormat('d-m-Y', trim($request->admission_date_from));
                $query->whereRaw("DATE(STR_TO_DATE(admission_date, '%d-%m-%Y')) >= DATE(?)", [$dateFrom->format('Y-m-d')]);
            } catch (\Exception $e) {
                // Invalid date format, skip this filter
                \Log::warning('Invalid admission_date_from format: ' . $request->admission_date_from . ' - ' . $e->getMessage());
            }
        }
        if ($request->filled('admission_date_to')) {
            try {
                // Admission dates are stored as dd-mm-yyyy format in database
                $dateTo = Carbon::createFromFormat('d-m-Y', trim($request->admission_date_to));
                $query->whereRaw("DATE(STR_TO_DATE(admission_date, '%d-%m-%Y')) <= DATE(?)", [$dateTo->format('Y-m-d')]);
            } catch (\Exception $e) {
                // Invalid date format, skip this filter
                \Log::warning('Invalid admission_date_to format: ' . $request->admission_date_to . ' - ' . $e->getMessage());
            }
        }
        if ($request->filled('caste')) {
            $query->where('caste', $request->caste);
        }
        if ($request->filled('class')) {
            $query->where('class', $request->class);
        }
        if ($request->filled('blood_group')) {
            $query->where('blood_group', $request->blood_group);
        }
        // Age filtering - extract years from age string format "X Years, Y Months, Z Days"
        if ($request->filled('age_from')) {
            $query->whereRaw("CAST(SUBSTRING_INDEX(age, ' Years', 1) AS UNSIGNED) >= ?", [$request->age_from]);
        }
        if ($request->filled('age_to')) {
            $query->whereRaw("CAST(SUBSTRING_INDEX(age, ' Years', 1) AS UNSIGNED) <= ?", [$request->age_to]);
        }
        
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('student_name', 'like', "%{$search}%")
                  ->orWhere('student_id', 'like', "%{$search}%")
                  ->orWhere('admission_no', 'like', "%{$search}%")
                  ->orWhere('father_name', 'like', "%{$search}%")
                  ->orWhere('mobile_no', 'like', "%{$search}%");
            });
        }

        $students = $query->orderBy('id', 'desc')->paginate(20)->appends($request->query());

        return view('students.index', compact('students'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('students.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Convert IFSC to uppercase before validation
        if ($request->filled('ifsc')) {
            $request->merge(['ifsc' => strtoupper($request->ifsc)]);
        }
        
        $validated = $this->validateStudent($request);

        // Auto-increment SL No if not provided
        if (empty($validated['sl_no'])) {
            $lastStudent = Student::orderBy('sl_no', 'desc')->first();
            $validated['sl_no'] = $lastStudent && $lastStudent->sl_no ? $lastStudent->sl_no + 1 : 1;
        }

        // Auto-calculate age from DOB
        if ($request->filled('dob')) {
            $dob = Carbon::createFromFormat('d-m-Y', $request->dob);
            $now = Carbon::now();
            $age = $now->diff($dob);
            $validated['age'] = $age->y . ' Years, ' . $age->m . ' Months, ' . $age->d . ' Days';
        }

        // Combine address fields into format: Village,PO,Sub Div,Panchayat,Dist,State,Pincode
        $addressParts = array_filter([
            $request->village,
            $request->post_office,
            $request->subdiv,
            $request->panchayat,
            $request->dist,
            $request->state,
            $request->pincode,
        ]);
        if (!empty($addressParts)) {
            $validated['address'] = implode(', ', $addressParts);
        }

        // Store dates in dd-mm-yyyy format (no conversion needed - already in correct format)
        // Dates are validated as d-m-Y format and stored as-is
        if ($request->filled('admission_date')) {
            $validated['admission_date'] = trim($request->admission_date);
        }
        if ($request->filled('dob')) {
            // DOB is stored as date type, so convert to Y-m-d for database
            $validated['dob'] = Carbon::createFromFormat('d-m-Y', $request->dob)->format('Y-m-d');
        }
        if ($request->filled('dropout_date')) {
            $validated['dropout_date'] = trim($request->dropout_date);
        }

        Student::create($validated);

        return redirect()->route('students.index')->with('status', 'Student created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Student $student)
    {
        return view('students.show', compact('student'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Student $student)
    {
        // Parse address to separate fields if it exists
        $addressParts = [];
        if ($student->address) {
            $parts = explode(',', $student->address);
            $addressParts = array_map('trim', $parts);
        }
        
        return view('students.edit', compact('student', 'addressParts'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Student $student)
    {
        // Convert IFSC to uppercase before validation
        if ($request->filled('ifsc')) {
            $request->merge(['ifsc' => strtoupper($request->ifsc)]);
        }
        
        $validated = $this->validateStudent($request, $student->id);

        // Auto-calculate age from DOB
        if ($request->filled('dob')) {
            $dob = Carbon::createFromFormat('d-m-Y', $request->dob);
            $now = Carbon::now();
            $age = $now->diff($dob);
            $validated['age'] = $age->y . ' Years, ' . $age->m . ' Months, ' . $age->d . ' Days';
        }

        // Combine address fields into format: Village,PO,Sub Div,Panchayat,Dist,State,Pincode
        $addressParts = array_filter([
            $request->village,
            $request->post_office,
            $request->subdiv,
            $request->panchayat,
            $request->dist,
            $request->state,
            $request->pincode,
        ]);
        if (!empty($addressParts)) {
            $validated['address'] = implode(', ', $addressParts);
        }

        // Handle date formats:
        // - admission_date and dropout_date are strings, store as dd-mm-yyyy
        // - dob is a DATE column, must be YYYY-MM-DD format
        if ($request->filled('admission_date')) {
            $validated['admission_date'] = trim($request->admission_date);
        }
        if ($request->filled('dob')) {
            // DOB is stored as date type, so convert to Y-m-d for database
            $validated['dob'] = Carbon::createFromFormat('d-m-Y', $request->dob)->format('Y-m-d');
        }
        if ($request->filled('dropout_date')) {
            $validated['dropout_date'] = trim($request->dropout_date);
        }

        $student->update($validated);

        return redirect()->route('students.index')->with('status', 'Student updated successfully.');
    }

    /**
     * Show deleted students (status = 1)
     */
    public function bin(Request $request)
    {
        $query = Student::query()->where('status', 1); // Only show deleted records

        // Apply filters
        if ($request->filled('admission_date_from')) {
            try {
                $dateFrom = Carbon::createFromFormat('d-m-Y', trim($request->admission_date_from));
                $query->whereRaw("DATE(STR_TO_DATE(admission_date, '%d-%m-%Y')) >= DATE(?)", [$dateFrom->format('Y-m-d')]);
            } catch (\Exception $e) {
                \Log::warning('Invalid admission_date_from format: ' . $request->admission_date_from . ' - ' . $e->getMessage());
            }
        }
        if ($request->filled('admission_date_to')) {
            try {
                $dateTo = Carbon::createFromFormat('d-m-Y', trim($request->admission_date_to));
                $query->whereRaw("DATE(STR_TO_DATE(admission_date, '%d-%m-%Y')) <= DATE(?)", [$dateTo->format('Y-m-d')]);
            } catch (\Exception $e) {
                \Log::warning('Invalid admission_date_to format: ' . $request->admission_date_to . ' - ' . $e->getMessage());
            }
        }
        if ($request->filled('caste')) {
            $query->where('caste', $request->caste);
        }
        if ($request->filled('class')) {
            $query->where('class', $request->class);
        }
        if ($request->filled('blood_group')) {
            $query->where('blood_group', $request->blood_group);
        }
        if ($request->filled('age_from')) {
            $query->whereRaw("CAST(SUBSTRING_INDEX(age, ' Years', 1) AS UNSIGNED) >= ?", [$request->age_from]);
        }
        if ($request->filled('age_to')) {
            $query->whereRaw("CAST(SUBSTRING_INDEX(age, ' Years', 1) AS UNSIGNED) <= ?", [$request->age_to]);
        }
        
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('student_name', 'like', "%{$search}%")
                  ->orWhere('student_id', 'like', "%{$search}%")
                  ->orWhere('admission_no', 'like', "%{$search}%")
                  ->orWhere('father_name', 'like', "%{$search}%")
                  ->orWhere('mobile_no', 'like', "%{$search}%");
            });
        }

        $students = $query->orderBy('updated_at', 'desc')->paginate(20)->appends($request->query());

        return view('students.bin', compact('students'));
    }

    /**
     * Restore a deleted student
     */
    public function restore($id)
    {
        $student = Student::findOrFail($id);
        
        if ($student->status != 1) {
            return redirect()->route('students.bin')->with('error', 'Student is not deleted.');
        }

        $student->update([
            'status' => 0,
            'reason' => null
        ]);

        return redirect()->route('students.bin')->with('status', 'Student restored successfully.');
    }

    /**
     * Remove the specified resource from storage (soft delete).
     */
    public function destroy(Student $student)
    {
        // Soft delete: Update status to 1 instead of actually deleting
        $student->update([
            'status' => 1,
            'reason' => request()->input('reason', 'Deleted by user')
        ]);

        return redirect()->route('students.index')->with('status', 'Student deleted successfully.');
    }

    /**
     * Export students to Excel
     */
    public function exportExcel(Request $request)
    {
        $query = $this->buildFilterQuery($request);
        $students = $query->get();

        $filename = 'students_' . date('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($students) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for Excel UTF-8 support
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Headers
            fputcsv($file, [
                'SL No', 'Admission Date', 'Admission No', 'Class', 'Student ID', 'Student Name',
                'PEN Number', 'Aapaar ID', 'Father Name', 'Mother Name', 'Caste',
                'DOB', 'Age', 'Mobile No',
                'Address',
                'Dropout School', 'Dropout Date', 'Dropout Reason', 'Height (Feet)', 'Weight (Kg)',
                'Blood Group', 'Vendor Code', 'Bank Name', 'Branch Name', 'IFSC', 'Account No',
                'Aadhaar No', 'Father Aadhaar No', 'Father Voter ID No', 'Father PAN No'
            ]);

            // Data rows
            foreach ($students as $student) {
                fputcsv($file, [
                    $student->sl_no,
                    $student->admission_date ?? '',
                    $student->admission_no,
                    $student->class,
                    $student->student_id,
                    $student->student_name,
                    $student->pen_number,
                    $student->aapaar_id,
                    $student->father_name,
                    $student->mother_name,
                    $student->caste,
                    $student->dob ?? '',
                    $student->age,
                    $student->mobile_no,
                    $student->address,
                    $student->dropout_school,
                    $student->dropout_date ?? '',
                    $student->dropout_reason,
                    $student->height,
                    $student->weight,
                    $student->blood_group,
                    $student->vendor_code,
                    $student->bank_name,
                    $student->branch_name,
                    $student->ifsc,
                    $student->account_no,
                    $student->aadhar_number,
                    $student->father_aadhar_number,
                    $student->father_voter_id_no,
                    $student->father_pan_no,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export students to PDF
     */
    public function exportPdf(Request $request)
    {
        $query = $this->buildFilterQuery($request);
        $students = $query->get();

        $pdf = Pdf::loadView('students.pdf', compact('students'));
        return $pdf->download('students_' . date('Y-m-d_His') . '.pdf');
    }

    /**
     * Build filter query
     */
    private function buildFilterQuery(Request $request)
    {
        $query = Student::query()->where('status', 0); // Only export active records

        if ($request->filled('admission_date_from')) {
            try {
                // Admission dates are stored as dd-mm-yyyy format in database
                $dateFrom = Carbon::createFromFormat('d-m-Y', trim($request->admission_date_from));
                $query->whereRaw("DATE(STR_TO_DATE(admission_date, '%d-%m-%Y')) >= DATE(?)", [$dateFrom->format('Y-m-d')]);
            } catch (\Exception $e) {
                // Invalid date format, skip this filter
                \Log::warning('Invalid admission_date_from format: ' . $request->admission_date_from . ' - ' . $e->getMessage());
            }
        }
        if ($request->filled('admission_date_to')) {
            try {
                // Admission dates are stored as dd-mm-yyyy format in database
                $dateTo = Carbon::createFromFormat('d-m-Y', trim($request->admission_date_to));
                $query->whereRaw("DATE(STR_TO_DATE(admission_date, '%d-%m-%Y')) <= DATE(?)", [$dateTo->format('Y-m-d')]);
            } catch (\Exception $e) {
                // Invalid date format, skip this filter
                \Log::warning('Invalid admission_date_to format: ' . $request->admission_date_to . ' - ' . $e->getMessage());
            }
        }
        if ($request->filled('caste')) {
            $query->where('caste', $request->caste);
        }
        if ($request->filled('class')) {
            $query->where('class', $request->class);
        }
        if ($request->filled('blood_group')) {
            $query->where('blood_group', $request->blood_group);
        }
        // Age filtering - extract years from age string format "X Years, Y Months, Z Days"
        if ($request->filled('age_from')) {
            $query->whereRaw("CAST(SUBSTRING_INDEX(age, ' Years', 1) AS UNSIGNED) >= ?", [$request->age_from]);
        }
        if ($request->filled('age_to')) {
            $query->whereRaw("CAST(SUBSTRING_INDEX(age, ' Years', 1) AS UNSIGNED) <= ?", [$request->age_to]);
        }

        return $query->orderBy('id', 'desc');
    }

    /**
     * Validate student data
     */
    private function validateStudent(Request $request, $studentId = null)
    {
        $rules = [
            'sl_no' => 'nullable|integer',
            'admission_date' => 'nullable|date_format:d-m-Y',
            'admission_no' => 'nullable|string|max:255',
            'class' => 'nullable|in:Six,Seven,Eight,Nine,Ten,Eleven,Twelve',
            'student_id' => 'nullable|string|max:255|unique:students,student_id,' . $studentId,
            'student_name' => 'nullable|string|max:255',
            'pen_number' => 'nullable|string|max:255',
            'aapaar_id' => 'nullable|string|regex:/^[0-9]{12}$/',
            'aadhar_number' => 'nullable|string|regex:/^[0-9]{12}$/',
            'father_aadhar_number' => 'nullable|string|regex:/^[0-9]{12}$/',
            'father_voter_id_no' => 'nullable|string|max:255|regex:/^[A-Z]{3}[0-9]{7}$/',
            'father_pan_no' => 'nullable|string|regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/',
            'father_name' => 'nullable|string|max:255',
            'mother_name' => 'nullable|string|max:255',
            'caste' => 'nullable|in:SC,ST,OBC,MOBC,GEN',
            'dob' => 'nullable|date_format:d-m-Y',
            'mobile_no' => 'nullable|string|regex:/^[0-9]{10}$/',
            'village' => 'nullable|string|max:255',
            'post_office' => 'nullable|string|max:255',
            'subdiv' => 'nullable|string|max:255',
            'panchayat' => 'nullable|string|max:255',
            'dist' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'pincode' => 'nullable|string|max:10',
            'address' => 'nullable|string',
            'dropout_school' => 'nullable|string|max:255',
            'dropout_date' => 'nullable|date_format:d-m-Y',
            'dropout_reason' => 'nullable|string',
            'height' => 'nullable|numeric|min:0|max:10',
            'weight' => 'nullable|numeric|min:0|max:500',
            'blood_group' => 'nullable|string|max:10',
            'vendor_code' => 'nullable|string|max:255',
            'bank_name' => 'nullable|string|max:255',
            'branch_name' => 'nullable|string|max:255',
            'ifsc' => 'nullable|string|max:11|regex:/^[A-Z]{4}[0-9A-Z]{7}$/',
            'account_no' => 'nullable|string|max:255',
        ];

        return $request->validate($rules);
    }

    /**
     * Show the import form
     */
    public function import()
    {
        return view('students.import');
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
            $importService = new StudentImportService();
            $result = $importService->import($request->file('file'));

            return redirect()
                ->route('students.index')
                ->with('status', "Import completed successfully! Total rows: {$result['total_rows']}, Inserted: {$result['inserted']}, Skipped: {$result['skipped']}")
                ->with('import_result', $result);
        } catch (\Exception $e) {
            return redirect()
                ->route('students.import')
                ->with('error', 'Import failed: ' . $e->getMessage());
        }
    }
}
