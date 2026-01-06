<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/**
 * Company Resource Controller with custom search filters
 */
class CompanyController extends BaseResourceController
{
    protected function getModelClass(): string
    {
        return Company::class;
    }

    protected function getViewPath(): string
    {
        return 'companies';
    }

    protected function getRouteName(): string
    {
        return 'companies';
    }

    protected function getPermissionPrefix(): ?string
    {
        return null; // No permission checks
    }

    protected function getStoreRules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:companies,name',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'gst_no' => 'nullable|string|max:50',
            'state_code' => 'nullable|string|max:50',
            'ein_cin_no' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'country' => 'nullable|string|max:100',
            'bank_name' => 'nullable|string|max:255',
            'account_no' => 'nullable|string|max:50',
            'ifsc_code' => 'nullable|string|max:50',
            'ad_code' => 'nullable|string|max:50',
            'sort_code' => 'nullable|string|max:50',
            'swift_code' => 'nullable|string|max:50',
            'iban' => 'nullable|string|max:50',
            'account_holder_name' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
        ];
    }

    protected function getUpdateRules($id): array
    {
        return [
            'name' => 'required|string|max:255|unique:companies,name,' . $id,
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'gst_no' => 'nullable|string|max:50',
            'state_code' => 'nullable|string|max:50',
            'ein_cin_no' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'country' => 'nullable|string|max:100',
            'bank_name' => 'nullable|string|max:255',
            'account_no' => 'nullable|string|max:50',
            'ifsc_code' => 'nullable|string|max:50',
            'ad_code' => 'nullable|string|max:50',
            'sort_code' => 'nullable|string|max:50',
            'swift_code' => 'nullable|string|max:50',
            'iban' => 'nullable|string|max:50',
            'account_holder_name' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
        ];
    }

    /**
     * Handle logo file upload for store
     */
    protected function prepareDataForStore(array $validated, Request $request): array
    {
        // Handle logo upload
        if ($request->hasFile('logo')) {
            $logoFile = $request->file('logo');
            $logoPath = $logoFile->store('companies/logos', 'public');
            $validated['logo'] = 'storage/' . $logoPath;
        }

        return parent::prepareDataForStore($validated, $request);
    }

    /**
     * Handle logo file upload for update
     */
    protected function prepareDataForUpdate(array $validated, Request $request, $item): array
    {
        // Handle logo upload
        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($item->logo && Storage::disk('public')->exists(str_replace('storage/', '', $item->logo))) {
                Storage::disk('public')->delete(str_replace('storage/', '', $item->logo));
            }

            // Upload new logo
            $logoFile = $request->file('logo');
            $logoPath = $logoFile->store('companies/logos', 'public');
            $validated['logo'] = 'storage/' . $logoPath;
        }

        return parent::prepareDataForUpdate($validated, $request, $item);
    }

    /**
     * Override index to add custom search/filter
     */
    public function index(Request $request)
    {
        $query = Company::query();

        // Multi-field search - includes commonly searched fields
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('gst_no', 'like', "%{$search}%")
                    ->orWhere('ein_cin_no', 'like', "%{$search}%")
                    ->orWhere('account_holder_name', 'like', "%{$search}%")
                    ->orWhere('bank_name', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        $items = $query->orderBy('id', 'desc')->paginate(10)->withQueryString();

        // Use 'companies' variable name for backward compatibility with views
        $companies = $items;

        return view('companies.index', compact('companies'));
    }

    /**
     * Override edit to use 'company' variable name in view
     */
    public function edit($id)
    {
        $this->checkPermission('edit');

        $company = Company::findOrFail($id);

        return view('companies.edit', compact('company'));
    }

    /**
     * Show company details as JSON for modal
     */
    public function show($id)
    {
        try {
            $company = Company::findOrFail($id);

            return response()->json([
                'id' => $company->id,
                'name' => $company->name,
                'email' => $company->email,
                'phone' => $company->phone,
                'logo' => $company->logo ? asset($company->logo) : null,
                'gst_no' => $company->gst_no,
                'ein_cin_no' => $company->ein_cin_no,
                'state_code' => $company->state_code,
                'address' => $company->address,
                'country' => $company->country,
                'bank_name' => $company->bank_name,
                'account_holder_name' => $company->account_holder_name,
                'account_no' => $company->account_no,
                'ifsc_code' => $company->ifsc_code,
                'iban' => $company->iban,
                'swift_code' => $company->swift_code,
                'sort_code' => $company->sort_code,
                'ad_code' => $company->ad_code,
                'status' => $company->status,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching company: ' . $e->getMessage());
            return response()->json(['error' => 'Company not found'], 404);
        }
    }
}

