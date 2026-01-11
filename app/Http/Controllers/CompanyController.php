<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Cloudinary\Cloudinary;
use Cloudinary\Api\Upload\UploadApi;

/**
 * Company Resource Controller with custom search filters
 */
class CompanyController extends BaseResourceController
{
    private $cloudinary = null;

    /**
     * Get Cloudinary instance (lazy initialization)
     */
    private function getCloudinary(): Cloudinary
    {
        if ($this->cloudinary === null) {
            $this->cloudinary = new Cloudinary([
                'cloud' => [
                    'cloud_name' => config('cloudinary.cloud_name'),
                    'api_key' => config('cloudinary.api_key'),
                    'api_secret' => config('cloudinary.api_secret'),
                ],
                'url' => [
                    'secure' => true
                ]
            ]);
        }
        return $this->cloudinary;
    }

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
            // US Bank Details
            'beneficiary_name' => 'nullable|string|max:255',
            'aba_routing_number' => 'nullable|string|max:9',
            'us_account_no' => 'nullable|string|max:50',
            'account_type' => 'nullable|in:checking,savings',
            'beneficiary_address' => 'nullable|string|max:500',
            'currency' => 'nullable|string|in:USD,GBP,INR,EUR',
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
            // US Bank Details
            'beneficiary_name' => 'nullable|string|max:255',
            'aba_routing_number' => 'nullable|string|max:9',
            'us_account_no' => 'nullable|string|max:50',
            'account_type' => 'nullable|in:checking,savings',
            'beneficiary_address' => 'nullable|string|max:500',
            'currency' => 'nullable|string|in:USD,GBP,INR,EUR',
            'status' => 'required|in:active,inactive',
        ];
    }

    /**
     * Handle logo file upload for store - uploads to Cloudinary
     */
    protected function prepareDataForStore(array $validated, Request $request): array
    {
        // Handle logo upload to Cloudinary
        if ($request->hasFile('logo')) {
            $logoUrl = $this->uploadLogoToCloudinary($request->file('logo'));
            if ($logoUrl) {
                $validated['logo'] = $logoUrl;
            }
        }

        return parent::prepareDataForStore($validated, $request);
    }

    /**
     * Handle logo file upload for update - uploads to Cloudinary
     */
    protected function prepareDataForUpdate(array $validated, Request $request, $item): array
    {
        // Handle logo upload to Cloudinary
        if ($request->hasFile('logo')) {
            // Delete old logo from Cloudinary if exists
            if ($item->logo && str_contains($item->logo, 'cloudinary.com')) {
                $this->deleteLogoFromCloudinary($item->logo);
            }

            // Upload new logo
            $logoUrl = $this->uploadLogoToCloudinary($request->file('logo'));
            if ($logoUrl) {
                $validated['logo'] = $logoUrl;
            }
        }

        return parent::prepareDataForUpdate($validated, $request, $item);
    }

    /**
     * Upload logo to Cloudinary
     */
    private function uploadLogoToCloudinary($file): ?string
    {
        try {
            $timestamp = time();
            $uniqueId = uniqid();
            $publicId = "companies/logos/{$timestamp}_{$uniqueId}";

            $uploadOptions = [
                'public_id' => $publicId,
                'folder' => 'companies/logos',
                'transformation' => [
                    'quality' => 'auto:good',
                    'fetch_format' => 'auto'
                ]
            ];

            Log::info("Uploading company logo to Cloudinary", [
                'file' => $file->getClientOriginalName(),
                'size' => $file->getSize()
            ]);

            $uploadApi = $this->getCloudinary()->uploadApi();
            $result = $uploadApi->upload($file->getRealPath(), $uploadOptions);

            Log::info("Successfully uploaded company logo to Cloudinary", [
                'url' => $result['secure_url'],
                'public_id' => $result['public_id']
            ]);

            return $result['secure_url'];

        } catch (\Exception $e) {
            Log::error('Cloudinary logo upload failed', [
                'file' => $file->getClientOriginalName(),
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Delete logo from Cloudinary
     */
    private function deleteLogoFromCloudinary(string $url): bool
    {
        try {
            // Extract public_id from URL
            // URL format: https://res.cloudinary.com/cloud_name/image/upload/v123/companies/logos/xxx.jpg
            $pattern = '/\/upload\/(?:v\d+\/)?(.+?)(?:\.[^.]+)?$/';
            if (preg_match($pattern, $url, $matches)) {
                $publicId = $matches[1];

                $uploadApi = $this->getCloudinary()->uploadApi();
                $uploadApi->destroy($publicId, ['resource_type' => 'image']);

                Log::info('Deleted company logo from Cloudinary', ['public_id' => $publicId]);
                return true;
            }
        } catch (\Exception $e) {
            Log::error('Failed to delete company logo from Cloudinary', [
                'url' => $url,
                'error' => $e->getMessage()
            ]);
        }
        return false;
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
                'logo' => $company->logo ? (str_starts_with($company->logo, 'http') ? $company->logo : asset($company->logo)) : null,
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
                // US Bank Details
                'beneficiary_name' => $company->beneficiary_name,
                'aba_routing_number' => $company->aba_routing_number,
                'us_account_no' => $company->us_account_no,
                'account_type' => $company->account_type,
                'beneficiary_address' => $company->beneficiary_address,
                'currency' => $company->currency,
                'currency_symbol' => $company->currency_symbol,
                'status' => $company->status,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching company: ' . $e->getMessage());
            return response()->json(['error' => 'Company not found'], 404);
        }
    }
}

