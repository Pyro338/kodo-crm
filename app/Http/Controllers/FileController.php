<?php

namespace App\Http\Controllers;

use App\Models\File;

class FileController extends Controller
{
    public function download($id)
    {
        $path        = File::where('id', $id)->first()->path;
        $file_exists = file_exists(storage_path('app/' . $path));
        if ($file_exists) {
            $file = response()->download(storage_path('app/' . $path));
        } else {
            $file = response()->download(storage_path('app/no-photo.png'));
        }

        return $file;
    }
}
