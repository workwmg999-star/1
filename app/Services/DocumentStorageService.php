<?php

namespace App\Services;

use App\Models\Company;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentStorageService
{
    /**
     * Store an uploaded file and return its path.
     */
    public function store(UploadedFile $file, Company $company, string $subfolder = 'documents'): string
    {
        $year = now()->format('Y');
        $month = now()->format('m');
        $ext = $file->getClientOriginalExtension();
        $name = Str::uuid().'.'.$ext;

        $path = "companies/{$company->id}/{$subfolder}/{$year}/{$month}/{$name}";

        Storage::put($path, file_get_contents($file->getRealPath()));

        return $path;
    }

    /**
     * Store raw content (e.g., generated PDF) and return its path.
     */
    public function storeContent(string $content, Company $company, string $filename, string $subfolder = 'documents'): string
    {
        $year = now()->format('Y');
        $month = now()->format('m');
        $name = Str::uuid().'_'.$filename;

        $path = "companies/{$company->id}/{$subfolder}/{$year}/{$month}/{$name}";

        Storage::put($path, $content);

        return $path;
    }

    /**
     * Delete a file from storage.
     */
    public function delete(string $path): bool
    {
        if (Storage::exists($path)) {
            return Storage::delete($path);
        }

        return true;
    }

    /**
     * Delete multiple files from storage.
     */
    public function deleteMany(array $paths): void
    {
        foreach ($paths as $path) {
            $this->delete($path);
        }
    }

    /**
     * Get a temporary (signed) URL for the given path.
     *
     * Works for S3 and for the local disk when `serve` is enabled in the
     * filesystems config (signed `/storage/...` URLs). Falls back to a plain
     * URL for any other setup.
     */
    public function getUrl(string $path, int $expirationMinutes = 60): string
    {
        try {
            return Storage::temporaryUrl($path, now()->addMinutes($expirationMinutes));
        } catch (\RuntimeException $e) {
            return Storage::url($path);
        }
    }

    /**
     * Get file size in bytes.
     */
    public function getSize(string $path): int
    {
        if (Storage::exists($path)) {
            return Storage::size($path);
        }

        return 0;
    }

    /**
     * Check if a file exists in storage.
     */
    public function exists(string $path): bool
    {
        return Storage::exists($path);
    }
}
