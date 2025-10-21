<?php

namespace App\Jobs;

use App\Http\Requests\EmployeeCreateRequest;
use App\Models\Employee;
use App\Models\User as AppUser;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class InsertEmployeesJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use Batchable;

    public array $rows;
    public AppUser $manager;
    public const LOG_KEY = 'insert_employees_job -> ';

    public function __construct(array $rows, AppUser $manager)
    {
        $this->rows = $rows;
        $this->manager = $manager;
    }

    public function handle(): void
    {
        foreach ($this->rows as $row) {
            try {
                $validator = Validator::make($row, (new EmployeeCreateRequest())->rules());
                if ($validator->fails()) {
                    Log::error(
                        self::LOG_KEY
                        . 'validation error inserting user: '
                        . json_encode($row) //line number would be better than entire line data
                        . ' with errors: ' . json_encode($validator->errors())
                    );
                    continue;
                }

                $data = $validator->validated();
                $data['manager_id'] = $this->manager->id;

                Employee::create($data);
            } catch (\Throwable $th) {
                Log::error(self::LOG_KEY . 'failed to insert user: ' . $th->getMessage() | $th->getFile() | $th->getLine());
            }
        }
    }
}
