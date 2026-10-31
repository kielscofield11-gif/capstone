<?php

namespace Tests\Feature;

use App\Models\Resident;
use App\Models\User;
use App\Services\ChartDataService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardChartsTest extends TestCase
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

    private function resident(string $firstName, string $gender, string $purok): Resident
    {
        return Resident::create([
            'first_name' => $firstName,
            'last_name' => 'Sample',
            'birth_date' => '1990-01-01',
            'gender' => $gender,
            'civil_status' => 'single',
            'purok' => $purok,
        ]);
    }

    public function test_grouped_returns_real_column_values_and_counts(): void
    {
        $this->resident('Ana', 'female', 'Purok 1');
        $this->resident('Bea', 'female', 'Purok 1');
        $this->resident('Carlo', 'male', 'Purok 2');

        $charts = app(ChartDataService::class);

        $gender = $charts->grouped(Resident::query(), 'gender')->pluck('total', 'gender');
        $this->assertSame(2, (int) $gender['female']);
        $this->assertSame(1, (int) $gender['male']);

        $purok = $charts->grouped(Resident::query(), 'purok')->pluck('total', 'purok');
        $this->assertSame(2, (int) $purok['Purok 1']);
        $this->assertSame(1, (int) $purok['Purok 2']);
    }

    public function test_monthly_counts_records_created_in_the_current_month(): void
    {
        $this->resident('Ana', 'female', 'Purok 1');

        $current = app(ChartDataService::class)
            ->monthly(Resident::query())
            ->firstWhere('period', now()->format('Y-m'));

        $this->assertNotNull($current);
        $this->assertSame(1, $current->total);
    }

    public function test_dashboard_exposes_working_breakdowns(): void
    {
        $admin = $this->activeUser('admin');
        $this->resident('Ana', 'female', 'Purok 1');

        $response = $this->actingAs($admin)->get('/dashboard')->assertOk();

        $this->assertSame(1, (int) ($response->viewData('genderDistribution')['female'] ?? 0));
        $this->assertSame('Purok 1', (string) ($response->viewData('residentByPurok')->first()->purok ?? ''));
        $this->assertSame(
            1,
            (int) ($response->viewData('residentsByMonth')->firstWhere('period', now()->format('Y-m'))->total ?? 0),
        );
    }
}
