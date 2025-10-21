<?php

namespace App\Services;

use App\Jobs\InsertEmployeesJob;
use App\Mail\CsvProcessedNotification;
use App\Models\User as AppUser;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class EmployeeService
{
    public const LOG_KEY = 'employee_service -> ';

    public function __construct(private int $batchSize = 2)
    {
        $this->batchSize = (int) env('IMPORT_CSV_BATCH_SIZE', 2);
    }

    public function createEmployeesByCsv(UploadedFile $file, AppUser $manager): void
    {
        \Log::info(self::LOG_KEY . 'started');
        $stream = fopen($file->getRealPath(), 'rb');
        if ($stream === false) {
            throw new FileNotFoundException($file->getClientOriginalName());
        }

        $batchData = [];
        $rowIndex = 0;
        $fileName = $file->getClientOriginalName();
        $batchId = Str::uuid();
        $batchName = 'employees csv import #'. $batchId . now()->toDateString();

        $batch = Bus::batch([])
            ->then(function () use ($manager, $fileName, $batchId) {
                Mail::to($manager->email)->send(new CsvProcessedNotification($manager->email, $fileName));
                Log::info(self::LOG_KEY . "finished batch #$batchId");
            })
            ->name($batchName);

        while (($row = fgetcsv($stream)) !== false) {
            if ($rowIndex === 0) {
                $rowIndex++;
                continue;
            }
            $batchData[] = [
                'name' => $row[0] ?? null,
                'email' => $row[1] ?? null,
                'cpf' => $row[2] ?? null,
                'city' => $row[3] ?? null,
                'state' => $row[4] ?? null,
            ];
            if (count($batchData) === $this->batchSize) {
                $batch->add(new InsertEmployeesJob($batchData, $manager));
                $batchData = [];
            }
            $rowIndex++;
        }
        if (count($batchData) > 0) {
            $batch->add(new InsertEmployeesJob($batchData, $manager));
        }
        fclose($stream);

        $batch->dispatch();

        \Log::info(self::LOG_KEY . 'finished');
    }
}
