<?php

namespace App\Services;

use Cloudinary\Cloudinary;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

/**
 * Shared Cloudinary upload/delete service.
 *
 * Replaces duplicated Cloudinary initialization and upload logic across
 * OrderController, ExpenseController, CompanyController, ChatController,
 * PurchaseController, JewelleryStockController, and PackageController.
 */
class CloudinaryUploadService
{
    private ?Cloudinary $cloudinary = null;

    /**
     * Get lazily-initialized Cloudinary SDK instance.
     */
    private function client(): Cloudinary
    {
        if ($this->cloudinary === null) {
            $this->cloudinary = new Cloudinary([
                'cloud' => [
                    'cloud_name' => config('cloudinary.cloud_name'),
                    'api_key'    => config('cloudinary.api_key'),
                    'api_secret' => config('cloudinary.api_secret'),
                ],
                'url' => [
                    'secure' => true,
                ],
            ]);
        }

        return $this->cloudinary;
    }

    /**
     * Upload files from a request field to Cloudinary.
     *
     * Works with both single-file and multi-file inputs.
     *
     * @param  Request  $request   The HTTP request
     * @param  string   $field     The form field name
     * @param  string   $folder    Cloudinary folder path
     * @param  int      $maxFiles  Maximum number of files to upload
     * @param  bool     $isPdf     Whether to treat files as raw PDFs
     * @return array  Array of uploaded file metadata
     */
    public function uploadFromRequest(Request $request, string $field, string $folder, int $maxFiles = 10, bool $isPdf = false): array
    {
        $uploadedFiles = [];

        if (!$request->hasFile($field)) {
            return $uploadedFiles;
        }

        $files = $request->file($field);

        // Handle both single and multiple file inputs
        if (!is_array($files)) {
            $files = [$files];
        }

        foreach ($files as $index => $file) {
            if ($index >= $maxFiles) {
                Log::warning("Max files limit reached for {$field}");
                break;
            }

            $result = $this->uploadFile($file, $folder, $isPdf);
            if ($result !== null) {
                $uploadedFiles[] = $result;
            }
        }

        return $uploadedFiles;
    }

    /**
     * Upload a single file to Cloudinary.
     *
     * @param  UploadedFile  $file    The file to upload
     * @param  string        $folder  Cloudinary folder path
     * @param  bool          $isPdf   Whether to treat as raw PDF
     * @return array|null  File metadata on success, null on failure
     */
    public function uploadFile(UploadedFile $file, string $folder, bool $isPdf = false): ?array
    {
        try {
            if (!$file->isValid()) {
                Log::error("Invalid file upload: {$file->getClientOriginalName()}");
                return null;
            }

            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension();
            $detectedPdf = $isPdf || strtolower($extension) === 'pdf';
            $timestamp = time();
            $uniqueId = uniqid();

            $publicId = "{$folder}/{$timestamp}_{$uniqueId}";
            $uploadOptions = [
                'public_id' => $publicId,
                'folder'    => $folder,
            ];

            Log::info("Uploading to Cloudinary", [
                'file' => $file->getClientOriginalName(),
                'type' => $detectedPdf ? 'PDF' : 'Image',
                'size' => $file->getSize(),
            ]);

            $uploadApi = $this->client()->uploadApi();

            if ($detectedPdf) {
                $uploadOptions['resource_type'] = 'raw';
                $result = $uploadApi->upload($file->getRealPath(), $uploadOptions);
            } else {
                $uploadOptions['transformation'] = [
                    'quality'      => 'auto:good',
                    'fetch_format' => 'auto',
                ];
                $result = $uploadApi->upload($file->getRealPath(), $uploadOptions);
            }

            $fileInfo = [
                'url'           => $result['secure_url'],
                'public_id'     => $result['public_id'],
                'name'          => $originalName . '.' . $extension,
                'original_name' => $originalName . '.' . $extension,
                'format'        => $extension,
                'size'          => $file->getSize(),
                'resource_type' => $detectedPdf ? 'raw' : 'image',
                'uploaded_at'   => now()->toDateTimeString(),
            ];

            Log::info("Successfully uploaded to Cloudinary", [
                'file'      => $originalName,
                'url'       => $fileInfo['url'],
                'public_id' => $result['public_id'],
            ]);

            return $fileInfo;
        } catch (\Exception $e) {
            Log::error('Cloudinary upload failed', [
                'file'  => $file->getClientOriginalName(),
                'error' => $e->getMessage(),
                'line'  => $e->getLine(),
            ]);
            return null;
        }
    }

    /**
     * Delete a single file from Cloudinary by its public ID.
     *
     * @param  string  $publicId      The Cloudinary public ID
     * @param  string  $resourceType  The resource type (image, raw, video)
     * @return bool  True on success
     */
    public function delete(string $publicId, string $resourceType = 'image'): bool
    {
        try {
            $uploadApi = $this->client()->uploadApi();
            $uploadApi->destroy($publicId, ['resource_type' => $resourceType]);

            Log::info("File deleted from Cloudinary", [
                'public_id'     => $publicId,
                'resource_type' => $resourceType,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to delete from Cloudinary', [
                'public_id' => $publicId,
                'error'     => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Extract the public_id from a Cloudinary URL.
     *
     * URL format: https://res.cloudinary.com/cloud_name/image/upload/v123/folder/file.jpg
     *
     * @param  string  $url  The Cloudinary URL
     * @return string|null  The public ID, or null if not extractable
     */
    public function extractPublicId(string $url): ?string
    {
        $pattern = '/\/upload\/(?:v\d+\/)?(.+?)(?:\.[^.]+)?$/';
        if (preg_match($pattern, $url, $matches)) {
            return $matches[1];
        }
        return null;
    }

    /**
     * Delete a file from Cloudinary using its URL.
     *
     * @param  string  $url           The Cloudinary URL
     * @param  string  $resourceType  The resource type
     * @return bool
     */
    public function deleteByUrl(string $url, string $resourceType = 'image'): bool
    {
        $publicId = $this->extractPublicId($url);
        if ($publicId === null) {
            Log::warning('Could not extract public_id from Cloudinary URL', ['url' => $url]);
            return false;
        }
        return $this->delete($publicId, $resourceType);
    }
}
