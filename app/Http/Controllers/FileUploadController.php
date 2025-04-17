<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Upload;
use App\Jobs\ProcessCsvFileJob;

class FileUploadController extends Controller
{
    public function showUploadForm()
    {
        return view('upload'); // your Blade with the Bootstrap form
    }

    public function uploadCsv(Request $request)
    {
        $request->validate(
            [
                'csv_file' => 'required|file|mimes:csv,txt'
            ]
        );

        $file = $request->file('csv_file');
        $originalName = $file->getClientOriginalName();

        // Store file in storage/app/csv_uploads/
        $path = $file->store('csv_uploads');

        // Create an Upload record
        $upload = Upload::create(
            [
                'file_name' => $path,
                'original_name' => $originalName,
                'status' => 'pending',
                'total_rows' => 0, // We'll update this later
                'processed_rows' => 0
            ]
        );

        // Dispatch a job to read the file and create row-level jobs
        ProcessCsvFileJob::dispatch($upload->id);

        return redirect()
            ->route('dashboard')
            ->with('success', 'File uploaded. Under processing.');
    }
}
