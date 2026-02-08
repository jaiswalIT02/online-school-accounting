<?php

namespace App\Http\Controllers;

use App\Models\Beneficiary;
use Illuminate\Http\Request;

class BeneficiaryController extends Controller
{
    public function index(Request $request)
    {
        $query = Beneficiary::query();
        
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('acode', 'like', "%{$search}%")
                  ->orWhere('vendor_code', 'like', "%{$search}%");
            });
        }
        
        $beneficiaries = $query->orderBy('name')->paginate(15)->withQueryString();
        $editingBeneficiary = null;

        if ($request->has('edit')) {
            $editingBeneficiary = Beneficiary::find($request->edit);
        }

        return view('beneficiaries.index', compact('beneficiaries', 'editingBeneficiary'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'acode' => ['nullable', 'string', 'max:50'],
            'vendor_code' => ['nullable', 'string', 'max:50', 'unique:beneficiaries,vendor_code'],
            // 'salary' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'integer', 'in:0,1'],
        ]);

        // Convert string fields to uppercase
        if (isset($data['name'])) $data['name'] = strtoupper($data['name']);
        if (isset($data['acode'])) $data['acode'] = strtoupper($data['acode']);
        if (isset($data['vendor_code'])) $data['vendor_code'] = strtoupper($data['vendor_code']);

        Beneficiary::create($data);

        return redirect()
            ->route('beneficiaries.index', $request->only('search'))
            ->with('status', 'Vendor created.');
    }

    public function update(Request $request, Beneficiary $beneficiary)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'acode' => ['nullable', 'string', 'max:50'],
            'vendor_code' => ['nullable', 'string', 'max:50', 'unique:beneficiaries,vendor_code,' . $beneficiary->id],
            // 'salary' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'integer', 'in:0,1'],
        ]);

        // Convert string fields to uppercase
        if (isset($data['name'])) $data['name'] = strtoupper($data['name']);
        if (isset($data['acode'])) $data['acode'] = strtoupper($data['acode']);
        if (isset($data['vendor_code'])) $data['vendor_code'] = strtoupper($data['vendor_code']);

        $beneficiary->update($data);

        return redirect()
            ->route('beneficiaries.index', $request->only('search'))
            ->with('status', 'Vendor updated.');
    }

    public function destroy(Beneficiary $beneficiary)
    {
        $beneficiary->delete();

        return redirect()
            ->route('beneficiaries.index')
            ->with('status', 'Vendor deleted.');
    }
}
