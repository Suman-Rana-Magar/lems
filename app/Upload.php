<?php

namespace App;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait Upload
{
    public function uploadFile(UploadedFile $file, $folder = null, $disk = 'public', $filename = null)
    {
        $FileName = !is_null($filename) ? $filename : Str::random(50);

        return [
            'path' => $file->storeAs(
                $folder,
                $FileName . "." . $file->getClientOriginalExtension(),
                $disk
            ),
            'filename' => !is_null($filename) ? $filename : $file->getClientOriginalName()
        ];
    }

    public function deleteFile($path, $disk = 'public')
    {
        Storage::disk($disk)->delete($path);
    }
}
