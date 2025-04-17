<?php

namespace App\Jobs;

use App\Models\Upload;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessCsvFileJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $uploadId;

    public function __construct($uploadId)
    {
        $this->uploadId = $uploadId;
    }

    public function handle()
    {
        $upload = Upload::find($this->uploadId);
        if (!$upload) {
            return;
        }

        $filePath = storage_path("app/private/{$upload->file_name}");

        if (!file_exists($filePath)) {
            // Mark the upload as failed if the file is missing
            $upload->update(['status' => 'failed']);
            return;
        }

        // Mark the upload as "processing"
        $upload->update(['status' => 'processing']);

        // Count total rows (minus header) so we know how many row jobs to expect
        $totalRows = $this->countCsvRows($filePath) - 1;
        $upload->update(['total_rows' => max($totalRows, 0)]);

        // Open the CSV
        $handle = fopen($filePath, 'r');
        $header = fgetcsv($handle); // skip header row

        $rowNumber = 0;
        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;
            $rowData = array_combine($header, $row);

            // Dispatch a row-level job for each row
            ProcessCsvRowJob::dispatch($upload->id, $rowData, $rowNumber);
        }

        fclose($handle);
    }

    private function countCsvRows($filePath)
    {
        $count = 0;
        if (($fh = fopen($filePath, 'r')) !== false) {
            while (fgets($fh) !== false) {
                $count++;
            }
            fclose($fh);
        }
        return $count;
    }
}
