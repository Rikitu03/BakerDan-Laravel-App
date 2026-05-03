<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class CustomOrderImageService
{
    public function storeReferenceImage(UploadedFile $file): string
    {
        $directory = public_path('images/custom-orders');

        if (! File::isDirectory($directory)) {
            File::ensureDirectoryExists($directory);
        }

        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'jpg');
        $filename = now()->format('YmdHis') . '-' . Str::uuid()->toString() . '.' . $extension;

        $file->move($directory, $filename);

        return '/images/custom-orders/' . $filename;
    }
}
