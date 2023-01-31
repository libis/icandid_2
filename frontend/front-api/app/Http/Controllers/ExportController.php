<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use League\Flysystem\FileNotFoundException;


class ExportController extends Controller
{
    public function download(Request $request, $id) {
        $this->authorize('export');
        $filename = $id . ".zip";
        try {
            return Storage::download('export/'.$filename, "export.zip");
        } catch(FileNotFoundException $e) {
            abort(404);
        }
    }
}
