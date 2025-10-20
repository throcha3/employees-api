<?php


use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Passport\Passport;
use Tests\TestCase;

class EmployeeControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected function authenticate(User $user): void
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
        $response = $this->getJson(route('employee.index'));

        $response->assertOk();
        $this->assertCount(2, $response->json('data'));
    }

    public function test_store_should_save_and_set_manager_id_to_authenticated_user(): void
    {
        $user = User::factory()->create();
        $this->authenticate($user);
        $payload = [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'cpf' => $this->faker->unique()->numerify('###########'),
            'city' => $this->faker->city(),
            'state' => $this->faker->randomElement(['SP', 'RJ', 'PR', 'CE', 'BA']),
        ];
        $response = $this->postJson(route('employee.store'), $payload);
        $response->assertCreated();
        $this->assertDatabaseHas('employees', array_merge($payload, ['manager_id' => $user->id]));
    }

    public function test_store_should_not_save_when_required_fields_are_not_present(): void
    {
        $user = User::factory()->create();
        $this->authenticate($user);

        $response = $this->postJson(route('employee.store'), []);
        $response->assertUnprocessable();
        $response->assertInvalid(['name', 'email', 'cpf', 'city', 'state']);
    }

    public function test_should_update_own_employee()
    {
        $user = User::factory()->create();
        $employee = Employee::factory()->create(['manager_id' => $user->id]);
        $this->authenticate($user);
        $fakeEmail = $this->faker->unique()->safeEmail();
        $this->patchJson('/api/employees/'.$employee->id, ['email' => $fakeEmail])->assertOk();
        $this->assertDatabaseHas('employees', ['id' => $employee->id, 'email' => $fakeEmail]);
    }

    public function test_should_not_update_employee_of_another_manager()
    {
        $user = User::factory()->create();
        $this->authenticate($user);
        $employee = Employee::factory()->create();
        $this->patchJson('/api/employees/'.$employee->id, ['name' => $this->faker()->name])->assertNotFound();
    }

    public function test_should_not_update_when_invalid_input()
    {
        $user = User::factory()->create();
        $employee = Employee::factory()->create(['manager_id' => $user->id]);
        $this->authenticate($user);
        $this->patchJson('/api/employees/'.$employee->id, ['email' => 'bad'])->assertUnprocessable();
    }

    public function test_should_not_update_or_create_duplicated_email()
    {
        $user = User::factory()->create();
        $this->authenticate($user);

        $emailA = $this->faker->unique()->safeEmail();
        $emailB = $this->faker->unique()->safeEmail();

        $a = Employee::factory()->create(['manager_id' => $user->id, 'email' => $emailA]);
        $b = Employee::factory()->create(['manager_id' => $user->id, 'email' => $emailB]);

        $this->postJson('/api/employees', [
            'name' => $this->faker->name,
            'email' => $emailA,
        ])->assertUnprocessable();

        $this->patchJson('/api/employees/'.$b->id, [
            'email' => $emailA
        ])->assertUnprocessable();
    }

    public function test_should_update_when_same_email_and_different_name()
    {
        $user = User::factory()->create();
        $this->authenticate($user);

        $emailA = $this->faker->unique()->safeEmail();
        $a = Employee::factory()->create(['manager_id' => $user->id, 'email' => $emailA]);
        $differentName = $this->faker->name;

        $this->patchJson('/api/employees/'.$a->id, [
            'email' => $emailA,
            'name' => $differentName
        ])->assertOk();

        $this->assertDatabaseHas('employees', ['id' => $a->id, 'name' => $differentName]);
    }
}
