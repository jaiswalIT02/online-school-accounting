<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Services\StaffImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class StaffController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Staff::query()->where('status', 0); // Only show active records

        // Apply filters
        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }
        if ($request->filled('caste')) {
            $query->where('caste', $request->caste);
        }
        if ($request->filled('marital_status')) {
            $query->where('marital_status', $request->marital_status);
        }
        if ($request->filled('designation')) {
            $query->where('designation', $request->designation);
        }
        if ($request->filled('stream')) {
            $query->where('stream', $request->stream);
        }
        if ($request->filled('qualification')) {
            $query->where('qualification', $request->qualification);
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
        if ($request->filled('date_of_joining_from')) {
            try {
                // Convert dd-mm-yyyy to yyyy-mm-dd for comparison
                $dateFrom = Carbon::createFromFormat('d-m-Y', $request->date_of_joining_from);
                $query->whereRaw("STR_TO_DATE(date_of_joining, '%d-%m-%Y') >= ?", [$dateFrom->format('Y-m-d')]);
            } catch (\Exception $e) {
                // Invalid date format, skip this filter
            }
        }
        if ($request->filled('date_of_joining_to')) {
            try {
                // Convert dd-mm-yyyy to yyyy-mm-dd for comparison
                $dateTo = Carbon::createFromFormat('d-m-Y', $request->date_of_joining_to);
                $query->whereRaw("STR_TO_DATE(date_of_joining, '%d-%m-%Y') <= ?", [$dateTo->format('Y-m-d')]);
            } catch (\Exception $e) {
                // Invalid date format, skip this filter
            }
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('employee_id', 'like', "%{$search}%")
                  ->orWhere('contact_no', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $staff = $query->orderBy('id', 'desc')->paginate(20);

        return view('staff.index', compact('staff'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('staff.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $this->validateStaff($request);

        // Auto-increment Serial No if not provided
        if (empty($validated['serial_no'])) {
            $lastStaff = Staff::orderBy('serial_no', 'desc')->first();
            $validated['serial_no'] = $lastStaff && $lastStaff->serial_no ? $lastStaff->serial_no + 1 : 1;
        }

        // Auto-calculate age from DOB
        if ($request->filled('date_of_birth')) {
            $dob = Carbon::createFromFormat('d-m-Y', $request->date_of_birth);
            $now = Carbon::now();
            $age = $now->diff($dob);
            $validated['age'] = $age->y . ' Years, ' . $age->m . ' Months, ' . $age->d . ' Days';
        }

        // Combine address fields into format: Village,PO,Sub Div,Panchayat,Dist,State,Pincode
        $addressParts = array_filter([
            $request->village,
            $request->post_office,
            $request->sub_div,
            $request->panchayat,
            $request->dist,
            $request->state,
            $request->pincode,
        ]);
        if (!empty($addressParts)) {
            $validated['address'] = implode(', ', $addressParts);
        }

        // Store dates in dd-mm-yyyy format (string columns)
        if ($request->filled('date_of_birth')) {
            // DOB is stored as string, so keep in dd-mm-yyyy format
            $validated['date_of_birth'] = trim($request->date_of_birth);
        }
        if ($request->filled('date_of_joining')) {
            // Date of joining is stored as string, so keep in dd-mm-yyyy format
            $validated['date_of_joining'] = trim($request->date_of_joining);
        }

        Staff::create($validated);

        return redirect()->route('staff.index')->with('status', 'Staff created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Staff $staff)
    {
        return view('staff.show', compact('staff'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Staff $staff)
    {
        // Parse address to separate fields if it exists
        $addressParts = [];
        if ($staff->address) {
            $parts = explode(',', $staff->address);
            $addressParts = array_map('trim', $parts);
        }
        
        return view('staff.edit', compact('staff', 'addressParts'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Staff $staff)
    {
        $validated = $this->validateStaff($request, $staff->id);

        // Auto-calculate age from DOB
        if ($request->filled('date_of_birth')) {
            $dob = Carbon::createFromFormat('d-m-Y', $request->date_of_birth);
            $now = Carbon::now();
            $age = $now->diff($dob);
            $validated['age'] = $age->y . ' Years, ' . $age->m . ' Months, ' . $age->d . ' Days';
        }

        // Combine address fields into format: Village,PO,Sub Div,Panchayat,Dist,State,Pincode
        $addressParts = array_filter([
            $request->village,
            $request->post_office,
            $request->sub_div,
            $request->panchayat,
            $request->dist,
            $request->state,
            $request->pincode,
        ]);
        if (!empty($addressParts)) {
            $validated['address'] = implode(', ', $addressParts);
        }

        // Handle date formats - store as dd-mm-yyyy (string columns)
        if ($request->filled('date_of_birth')) {
            $validated['date_of_birth'] = trim($request->date_of_birth);
        }
        if ($request->filled('date_of_joining')) {
            $validated['date_of_joining'] = trim($request->date_of_joining);
        }

        $staff->update($validated);

        return redirect()->route('staff.index')->with('status', 'Staff updated successfully.');
    }

    /**
     * Show deleted staff (status = 1)
     */
    public function bin(Request $request)
    {
        $query = Staff::query()->where('status', 1); // Only show deleted records

        // Apply filters
        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }
        if ($request->filled('caste')) {
            $query->where('caste', $request->caste);
        }
        if ($request->filled('marital_status')) {
            $query->where('marital_status', $request->marital_status);
        }
        if ($request->filled('designation')) {
            $query->where('designation', $request->designation);
        }
        if ($request->filled('stream')) {
            $query->where('stream', $request->stream);
        }
        if ($request->filled('qualification')) {
            $query->where('qualification', $request->qualification);
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
        if ($request->filled('date_of_joining_from')) {
            try {
                $dateFrom = Carbon::createFromFormat('d-m-Y', $request->date_of_joining_from);
                $query->whereRaw("DATE(STR_TO_DATE(date_of_joining, '%d-%m-%Y')) >= DATE(?)", [$dateFrom->format('Y-m-d')]);
            } catch (\Exception $e) {
                // Invalid date format, skip this filter
            }
        }
        if ($request->filled('date_of_joining_to')) {
            try {
                $dateTo = Carbon::createFromFormat('d-m-Y', $request->date_of_joining_to);
                $query->whereRaw("DATE(STR_TO_DATE(date_of_joining, '%d-%m-%Y')) <= DATE(?)", [$dateTo->format('Y-m-d')]);
            } catch (\Exception $e) {
                // Invalid date format, skip this filter
            }
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('employee_id', 'like', "%{$search}%")
                  ->orWhere('contact_no', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $staff = $query->orderBy('updated_at', 'desc')->paginate(20)->appends($request->query());

        return view('staff.bin', compact('staff'));
    }

    /**
     * Restore a deleted staff member
     */
    public function restore($id)
    {
        $staff = Staff::findOrFail($id);
        
        if ($staff->status != 1) {
            return redirect()->route('staff.bin')->with('error', 'Staff member is not deleted.');
        }

        $staff->update([
            'status' => 0,
            'reason' => null
        ]);

        return redirect()->route('staff.bin')->with('status', 'Staff member restored successfully.');
    }

    /**
     * Remove the specified resource from storage (soft delete).
     */
    public function destroy(Staff $staff)
    {
        // Soft delete: Update status to 1 instead of actually deleting
        $staff->update([
            'status' => 1,
            'reason' => request()->input('reason', 'Deleted by user')
        ]);

        return redirect()->route('staff.index')->with('status', 'Staff deleted successfully.');
    }

    /**
     * Export staff to Excel
     */
    public function exportExcel(Request $request)
    {
        $query = $this->buildFilterQuery($request);
        $staff = $query->get();

        $filename = 'staff_' . date('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($staff) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for Excel UTF-8 support
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Headers
            fputcsv($file, [
                'Serial No', 'Employee ID', 'National ID', 'Full Name', 'Gender', 'Date of Birth',
                'Age', 'Caste', 'Marital Status', 'Contact No',
                'Email', 'Designation', 'Date of Joining', 'Bank Name', 'Account No', 'IFSC Code',
                'Component', 'Vendor Code', 'Aadhaar No', 'Voter ID', 'PAN No', 'Qualification',
                'Professional Qualification', 'Stream', 'Father Name', 'Mother Name', 'Blood Group',
                'Height (Feet)', 'Weight (Kg)', 'Address', 'Honors Major In'
            ]);

            // Data rows
            foreach ($staff as $s) {
                fputcsv($file, [
                    $s->serial_no,
                    $s->employee_id,
                    $s->national_id,
                    $s->full_name,
                    $s->gender,
                    $s->date_of_birth ?? '',
                    $s->age,
                    $s->caste,
                    $s->marital_status,
                    $s->contact_no,
                    $s->email,
                    $s->designation,
                    $s->date_of_joining ?? '',
                    $s->bank_name,
                    $s->account_no,
                    $s->ifsc_code,
                    $s->component_code,
                    $s->vendor_code,
                    $s->aadhaar_no,
                    $s->voter_id,
                    $s->pan_no,
                    $s->qualification,
                    $s->professional_qualification,
                    $s->stream,
                    $s->father_name,
                    $s->mother_name,
                    $s->blood_group,
                    $s->height,
                    $s->weight,
                    $s->address,
                    $s->honors_major_in,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export staff to PDF
     */
    public function exportPdf(Request $request)
    {
        $query = $this->buildFilterQuery($request);
        $staff = $query->get();

        $pdf = Pdf::loadView('staff.pdf', compact('staff'));
        return $pdf->download('staff_' . date('Y-m-d_His') . '.pdf');
    }

    /**
     * Build filter query
     */
    private function buildFilterQuery(Request $request)
    {
        $query = Staff::query()->where('status', 0); // Only export active records

        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }
        if ($request->filled('caste')) {
            $query->where('caste', $request->caste);
        }
        if ($request->filled('marital_status')) {
            $query->where('marital_status', $request->marital_status);
        }
        if ($request->filled('designation')) {
            $query->where('designation', $request->designation);
        }
        if ($request->filled('stream')) {
            $query->where('stream', $request->stream);
        }
        if ($request->filled('qualification')) {
            $query->where('qualification', $request->qualification);
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
        if ($request->filled('date_of_joining_from')) {
            try {
                // Convert dd-mm-yyyy to yyyy-mm-dd for comparison
                $dateFrom = Carbon::createFromFormat('d-m-Y', $request->date_of_joining_from);
                $query->whereRaw("STR_TO_DATE(date_of_joining, '%d-%m-%Y') >= ?", [$dateFrom->format('Y-m-d')]);
            } catch (\Exception $e) {
                // Invalid date format, skip this filter
            }
        }
        if ($request->filled('date_of_joining_to')) {
            try {
                // Convert dd-mm-yyyy to yyyy-mm-dd for comparison
                $dateTo = Carbon::createFromFormat('d-m-Y', $request->date_of_joining_to);
                $query->whereRaw("STR_TO_DATE(date_of_joining, '%d-%m-%Y') <= ?", [$dateTo->format('Y-m-d')]);
            } catch (\Exception $e) {
                // Invalid date format, skip this filter
            }
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('employee_id', 'like', "%{$search}%")
                  ->orWhere('contact_no', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('id', 'desc');
    }

    /**
     * Validate staff data
     */
    private function validateStaff(Request $request, $staffId = null)
    {
        $rules = [
            'serial_no' => 'nullable|integer',
            'employee_id' => 'nullable|string|max:255|unique:staff,employee_id,' . $staffId,
            'national_id' => 'nullable|string|max:255',
            'full_name' => 'nullable|string|max:255',
            'gender' => 'nullable|in:Male,Female',
            'date_of_birth' => 'nullable|date_format:d-m-Y',
            'caste' => 'nullable|in:General,SC,ST,OBC,MOBC',
            'marital_status' => 'nullable|in:Married,Unmarried',
            'contact_no' => 'nullable|string|regex:/^[0-9]{10}$/',
            'email' => 'nullable|email|max:255',
            'designation' => 'nullable|in:Warden Cum Superintendent,Head Teacher,Full Time Assistant Teacher,Part Time Assistant Teacher,Account Assistant Cum Caretaker,Peon Cum Matron,Chowkidar Cum Mali,Head Cook,Assistant Cook Cum Helper',
            'date_of_joining' => 'nullable|date_format:d-m-Y',
            'bank_name' => 'nullable|string|max:255',
            'account_no' => 'nullable|string|max:255',
            'ifsc_code' => 'nullable|string|max:11|regex:/^[A-Z]{4}[0-9A-Z]{7}$/',
            'component_code' => 'nullable|string|max:255',
            'vendor_code' => 'nullable|string|max:255',
            'aadhaar_no' => 'nullable|string|regex:/^[0-9]{12}$/',
            'voter_id' => 'nullable|string|max:255|regex:/^[A-Z]{3}[0-9]{7}$/',
            'pan_no' => 'nullable|string|regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/',
            'qualification' => 'nullable|in:Below HSLC,HSLC,HS,BA,Bsc,B.Com,MA,Msc,M.Com,Manual Enter',
            'professional_qualification' => 'nullable|in:D.El.Ed,B.Ed,TET,CTET',
            'stream' => 'nullable|in:Arts,Science,Commerce',
            'father_name' => 'nullable|string|max:255',
            'mother_name' => 'nullable|string|max:255',
            'blood_group' => 'nullable|string|max:10',
            'height' => 'nullable|numeric|min:0|max:10',
            'weight' => 'nullable|numeric|min:0|max:500',
            'village' => 'nullable|string|max:255',
            'post_office' => 'nullable|string|max:255',
            'sub_div' => 'nullable|string|max:255',
            'panchayat' => 'nullable|string|max:255',
            'dist' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'pincode' => 'nullable|string|max:10',
            'honors_major_in' => 'nullable|string|max:255',
        ];

        return $request->validate($rules);
    }

    /**
     * Show the import form
     */
    public function import()
    {
        return view('staff.import');
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
            $importService = new StaffImportService();
            $result = $importService->import($request->file('file'));

            return redirect()
                ->route('staff.index')
                ->with('status', "Import completed successfully! Total rows: {$result['total_rows']}, Inserted: {$result['inserted']}, Skipped: {$result['skipped']}")
                ->with('import_result', $result);
        } catch (\Exception $e) {
            return redirect()
                ->route('staff.import')
                ->with('error', 'Import failed: ' . $e->getMessage());
        }
    }
}
