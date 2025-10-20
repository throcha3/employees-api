<?php


use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

class EmployeeControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function authenticate(User $user)
    {
        Passport::actingAs($user);
    }

    public function test_index_should_show_only_owned_records(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        Employee::factory()->count(2)->create(['manager_id' => $owner->id]);
        Employee::factory()->count(3)->create(['manager_id' => $other->id]);

        $this->authenticate($owner);
        $response = $this->getJson('/api/employees');

        $response->assertOk();
        $this->assertCount(2, $response->json('data'));
    }
}



