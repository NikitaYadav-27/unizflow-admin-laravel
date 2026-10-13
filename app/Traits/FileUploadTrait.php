<?php

namespace App\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;

trait FileUploadTrait
{
    
    public function uploadBase64File(?string $base64Data, string $directory = 'assets', string $prefix = 'file-'): ?string
    {
        if (empty($base64Data) || strlen($base64Data) < 100) {
            return null;
        }

        $extension = 'png';
        if (preg_match('/^data:image\/(\w+);base64,/', $base64Data, $matches)) {
            $extension = strtolower($matches[1]);
            if ($extension === 'jpeg') {
                $extension = 'jpg';
            }
            $base64Data = substr($base64Data, strpos($base64Data, ',') + 1);
        } elseif (strpos($base64Data, ',') !== false) {
            $base64Data = explode(',', $base64Data, 2)[1];
        }

        $binary = base64_decode($base64Data, true);
        if ($binary === false) {
            return null;
        }

        $targetDir = public_path($directory);
        if (!File::isDirectory($targetDir)) {
            File::makeDirectory($targetDir, 0755, true, true);
        }

        $fileName = $prefix . rand(11111, 99999) . '-' . time() . '.' . $extension;
        $filePath = $targetDir . DIRECTORY_SEPARATOR . $fileName;

        file_put_contents($filePath, $binary);

        return '/' . trim($directory, '/') . '/' . $fileName;
    }

     
    public function uploadFile(?UploadedFile $file, string $directory = 'uploads', string $prefix = 'file-'): ?string
    {
        if (!$file || !$file->isValid()) {
            return null;
        }

        $targetDir = public_path($directory);
        if (!File::isDirectory($targetDir)) {
            File::makeDirectory($targetDir, 0755, true, true);
        }

        $extension = $file->getClientOriginalExtension() ?: 'png';
        $fileName = $prefix . rand(11111, 99999) . '-' . time() . '.' . $extension;

        $file->move($targetDir, $fileName);

        return '/' . trim($directory, '/') . '/' . $fileName;
    }

    
    public function deleteFile(?string $path): bool
    {
        if (empty($path)) {
            return false;
        }

        if (preg_match('/^https?:\/\//i', $path)) {
            return false;
        }

        $cleanPath = ltrim(parse_url($path, PHP_URL_PATH) ?? $path, '/');
        $fullPath = public_path($cleanPath);

        if (File::exists($fullPath) && File::isFile($fullPath)) {
            return File::delete($fullPath);
        }

        return false;
    }

     
    public function handleBase64Upload(?string $newBase64, ?string $oldPath = null, string $directory = 'assets', string $prefix = 'file-'): ?string
    {
        if (!empty($newBase64) && strlen($newBase64) > 100) {
            $newPath = $this->uploadBase64File($newBase64, $directory, $prefix);
            if ($newPath) {
                if (!empty($oldPath)) {
                    $this->deleteFile($oldPath);
                }
                return $newPath;
            }
        }

        return $oldPath;
    }
}
