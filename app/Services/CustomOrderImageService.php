<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class CustomOrderImageService
{
    public function storeReferenceImage(UploadedFile $file): string
    {
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'jpg');
        $filename = now()->format('YmdHis') . '-' . Str::uuid()->toString() . '.' . $extension;

        $path = $file->storeAs('custom-orders', $filename, 's3');

        return Storage::disk('s3')->url($path);
    }
}
