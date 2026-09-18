<?php

namespace Tests\Feature;

use App\Models\Household;
use App\Models\Resident;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_from_dashboard(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }

    public function test_login_page_is_accessible_to_guests(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_seeder_creates_role_users(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseHas('users', ['email' => 'admin@barangay.gov', 'role' => 'admin', 'is_active' => true]);
        $this->assertDatabaseHas('users', ['email' => 'secretary@barangay.gov', 'role' => 'secretary']);
        $this->assertDatabaseHas('users', ['email' => 'captain@barangay.gov', 'role' => 'captain']);
        $this->assertDatabaseHas('users', ['email' => 'kagawad@barangay.gov', 'role' => 'kagawad']);
        $this->assertDatabaseHas('users', ['email' => 'staff@barangay.gov', 'role' => 'staff']);
    }

    public function test_database_seeder_can_be_run_more_than_once(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseCount('users', 5);
        $this->assertDatabaseCount('document_types', 7);
    }

    public function test_household_cannot_have_two_heads(): void
    {
        $user = User::create([
            'name' => 'Secretary',
            'email' => 'secretary@example.com',
            'password' => bcrypt('password123'),
            'role' => 'secretary',
            'is_active' => true,
        ]);
        $household = Household::create(['household_number' => 'HH-001']);
        Resident::create([
            'first_name' => 'First',
            'last_name' => 'Head',
            'birth_date' => '1980-01-01',
            'gender' => 'male',
            'household_id' => $household->id,
            'is_household_head' => true,
        ]);

        $this->actingAs($user)->post('/residents', [
            'first_name' => 'Second',
            'last_name' => 'Head',
            'birth_date' => '1985-01-01',
            'gender' => 'female',
            'civil_status' => 'single',
            'household_id' => $household->id,
            'is_household_head' => '1',
        ])->assertSessionHasErrors('is_household_head');

        $this->assertDatabaseCount('residents', 1);
    }

    public function test_updating_a_resident_clears_unchecked_classifications(): void
    {
        $user = User::create([
            'name' => 'Secretary',
            'email' => 'secretary-update@example.com',
            'password' => bcrypt('password123'),
            'role' => 'secretary',
            'is_active' => true,
        ]);
        $resident = Resident::create([
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
            'birth_date' => '1980-01-01',
            'gender' => 'male',
            'civil_status' => 'single',
            'is_voter' => true,
            'is_pwd' => true,
            'is_senior' => true,
            'is_4ps' => true,
            'is_household_head' => true,
        ]);

        $this->actingAs($user)->put("/residents/{$resident->id}", [
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
            'birth_date' => '1980-01-01',
            'gender' => 'male',
            'civil_status' => 'single',
        ])->assertRedirect('/residents');

        $this->assertDatabaseHas('residents', [
            'id' => $resident->id,
            'is_voter' => false,
            'is_pwd' => false,
            'is_senior' => false,
            'is_4ps' => false,
            'is_household_head' => false,
        ]);
    }

    public function test_self_registered_staff_is_inactive(): void
    {
        $response = $this->post('/register', [
            'name' => 'New Staff',
            'email' => 'staff@example.com',
            'password' => 'ValidPass1!',
            'password_confirmation' => 'ValidPass1!',
        ]);

        $response->assertRedirect('/login');
        $this->assertDatabaseHas('users', ['email' => 'staff@example.com', 'role' => 'staff', 'is_active' => false]);
        $this->assertGuest();
    }

    public function test_inactive_user_cannot_authenticate(): void
    {
        User::create([
            'name' => 'Inactive User',
            'email' => 'inactive@example.com',
            'password' => bcrypt('password123'),
            'role' => 'staff',
            'is_active' => false,
        ]);

        $this->post('/login', [
            'email' => 'inactive@example.com',
            'password' => 'password123',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_authenticated_user_can_open_dashboard_with_sqlite(): void
    {
        $user = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk();
    }
}
