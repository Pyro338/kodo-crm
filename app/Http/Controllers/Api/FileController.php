<?php

namespace App\Http\Controllers\Api;

use App\Models\File;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class FileController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function store(Request $request)
    {
        $new_file = File::createFile($request, $request->file()['attachments']);

        if ($new_file) {
            return ['success' => $new_file];
        } else {
            return ['fail' => $request];
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  File $file
     *
     * @return array
     */
    public function destroy(File $file)
    {
        $file->is_visible = 0;

        if ($file->save()) {
            return ['success' => $file];
        } else {
            return ['fail' => 'Error occurred'];
        }
    }
}
