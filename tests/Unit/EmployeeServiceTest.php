<?php

namespace Tests\Unit;

use App\Jobs\InsertEmployeesJob;
use App\Mail\CsvProcessedNotification;
use App\Models\User as AppUser;
use App\Services\EmployeeService;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EmployeeServiceTest extends TestCase
{
    use RefreshDatabase;

    private EmployeeService $employeeService;
    private AppUser $manager;
    private string $csvContent;
    private int $batchSize = 2;

    protected function setUp(): void
    {
        parent::setUp();
        $this->employeeService = new EmployeeService();

        $this->manager = AppUser::factory()->make([
            'email' => 'manager@example.com',
        ]);

        $this->csvContent =
            "name,email,cpf,city,state\n" .
            "John Doe,john@example.com,123.456.789-00,New York,NY\n" .
            "Jane Smith,jane@example.com,123.456.789-01,Los Angeles,CA\n" .
            "Mark Test,mark@example.com,123.456.789-02,Chicago,IL\n" .
            "Alice Beta,alice@example.com,123.456.789-03,Houston,TX\n" .
            "Bob Gamma,bob@example.com,123.456.789-04,Phoenix,AZ";

        Bus::fake();
        Mail::fake();
        Log::fake();
    }

    public function test_should_process_csv_and_dispatche_jobs_in_batches()
    {
        $file = UploadedFile::fake()->createWithContent('employees.csv', $this->csvContent);
        $totalRows = 5;
        $expectedJobs = ceil($totalRows / $this->batchSize);

        $this->employeeService->createEmployeesByCsv($file, $this->manager);

        Bus::assertBatched(function ($batch) use ($expectedJobs) {
            $this->assertCount($expectedJobs, $batch->jobs);
            return $batch->jobs->every(fn ($job) => $job instanceof InsertEmployeesJob);
        });

        Bus::assertDispatchedTimes(InsertEmployeesJob::class, $expectedJobs);

        Bus::assertBatched(function ($batch) {
            return count($batch->thenCallbacks) === 1;
        });
    }

    public function test_should_send_email_notification_on_batch_completion()
    {
        $file = UploadedFile::fake()->createWithContent('employees.csv', $this->csvContent);

        $this->employeeService->createEmployeesByCsv($file, $this->manager);

        Mail::assertSent(CsvProcessedNotification::class, function ($mail) {
            return $mail->hasTo($this->manager->email);
        });
    }

    public function test_should_throw_file_not_found_exception_if_stream_fails_to_open()
    {
        $file = $this->getMockBuilder(UploadedFile::class)
            ->disableOriginalConstructor()
            ->getMock();

        $file->method('getRealPath')->willReturn('/nonexistent/path/to/fail.csv');
        $file->method('getClientOriginalName')->willReturn('fail.csv');

        $this->expectException(FileNotFoundException::class);

        $this->employeeService->createEmployeesByCsv($file, $this->manager);
    }
}
