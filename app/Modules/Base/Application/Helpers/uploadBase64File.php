<?php
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;


function uploadBase64File($base64, $path)
{
    $fileData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64));
    $fileName = uniqid() . '.png';
    $filePath = $path . '/' . $fileName;
    Storage::disk('public')->put($filePath, $fileData);
    return 'storage/' . $filePath;
}

