<?php

namespace Tests\Feature;

use App\Models\Resident;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportFiltersTest extends TestCase
{
    use RefreshDatabase;

    private function activeUser(string $role): User
    {
        return User::create([
            'name' => ucfirst($role) . ' User',
            'email' => $role . uniqid() . '@example.com',
            'password' => bcrypt('password123'),
            'role' => $role,
            'is_active' => true,
        ]);
    }

    private function resident(string $lastName, string $birthDate): Resident
    {
        return Resident::create([
            'first_name' => 'Filter',
            'last_name' => $lastName,
            'birth_date' => $birthDate,
            'gender' => 'male',
            'civil_status' => 'single',
        ]);
    }

    public function test_age_to_filter_is_accepted_without_age_from(): void
    {
        $admin = $this->activeUser('admin');
        $this->resident('Youngster', now()->subYears(30)->toDateString());
        $this->resident('Elderly', now()->subYears(70)->toDateString());

        $this->actingAs($admin)
            ->get('/reports/residents?age_to=50')
            ->assertOk()
            ->assertSee('Youngster')
            ->assertDontSee('Elderly');
    }

    public function test_age_range_is_still_validated_when_both_bounds_are_present(): void
    {
        $admin = $this->activeUser('admin');

        $this->actingAs($admin)
            ->get('/reports/residents?age_from=60&age_to=50')
            ->assertRedirect()
            ->assertSessionHasErrors('age_to');
    }

    public function test_resident_excel_export_accepts_age_to_without_age_from(): void
    {
        $admin = $this->activeUser('admin');
        $this->resident('Youngster', now()->subYears(30)->toDateString());

        $this->actingAs($admin)->get('/exports/residents/excel?age_to=50')->assertOk();
    }

    public function test_resident_pdf_export_accepts_age_to_without_age_from(): void
    {
        $admin = $this->activeUser('admin');
        $this->resident('Youngster', now()->subYears(30)->toDateString());

        $this->actingAs($admin)->get('/exports/residents/pdf?age_to=50')->assertOk();
    }
}
