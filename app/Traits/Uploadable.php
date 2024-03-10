<?php
namespace App\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

trait Uploadable{
    /**
     * Upload a file to the specified path.
     *
     * @param UploadedFile $file
     * @param string $path
     * @param string|null $fileName
     * @param string|null $disk
     * @return string|false
     */
    public static function uploadFile(UploadedFile $file, string $path='', string $fileName = null, string $disk = null)
    {
        $fileName = $fileName ?: $file->getClientOriginalName();
        $disk = $disk ?: config('filesystems.default');

        return 'uploads/'.$file->storeAs($path, $fileName, ['disk' => $disk]);
    }
    /**
     * Upload multiple files to the specified path.
     *
     * @param array $files
     * @param string $path
     * @param string|null $disk
     * @param array $extraData
     * @return array
     */
    public static function uploadMultipleFiles(array $files, string $path ='', string $disk = null, array $extraData = [])
    {
        $paths = [];

        foreach ($files as $file) {
            $fileName = self::generateRandomName($file);
            $uploadedPath = self::uploadFile($file, $path, $fileName, $disk);
            if ($uploadedPath) {
                $paths[] = array_merge(['path' => $uploadedPath],$extraData);
            }
        }

        return $paths;
    }
    /**
     * Generate a random name for a file.
     *
     * @param UploadedFile $file
     * @return string
     */
    public static function generateRandomName(UploadedFile $file)
    {
        $timestamp = now()->timestamp;
        $extension = $file->getClientOriginalExtension();
        return $timestamp . '_' . \Str::random(10) . '.' . $extension;
    }

    /**
     * Delete a file from the specified path.
     *
     * @param string $path
     * @param string|null $disk
     * @return bool
     */
    public static function deleteFile(string $path, string $disk = null)
    {
        $disk = $disk ?: config('filesystems.default');

        return Storage::disk($disk)->delete($path);
    }
}
