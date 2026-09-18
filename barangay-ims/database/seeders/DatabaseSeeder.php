<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['name' => 'Admin', 'email' => 'admin@barangay.gov', 'password' => env('SEED_ADMIN_PASSWORD'), 'role' => User::ROLE_ADMIN],
            ['name' => 'Secretary', 'email' => 'secretary@barangay.gov', 'password' => env('SEED_SECRETARY_PASSWORD'), 'role' => User::ROLE_SECRETARY],
            ['name' => 'Captain', 'email' => 'captain@barangay.gov', 'password' => env('SEED_CAPTAIN_PASSWORD'), 'role' => User::ROLE_CAPTAIN],
            ['name' => 'Kagawad', 'email' => 'kagawad@barangay.gov', 'password' => env('SEED_KAGAWAD_PASSWORD'), 'role' => User::ROLE_KAGAWAD],
            ['name' => 'Staff', 'email' => 'staff@barangay.gov', 'password' => env('SEED_STAFF_PASSWORD'), 'role' => User::ROLE_STAFF],
        ] as $account) {
            $password = $account['password'] ?: Str::random(40);

            if (empty($account['password']) && $this->command) {
                $this->command->warn("No seed password set for {$account['email']}; generated a random password (account will need a reset).");
            }

            User::updateOrCreate(
                ['email' => $account['email']],
                [
                    'name' => $account['name'],
                    'password' => $password,
                    'role' => $account['role'],
                    'is_active' => true,
                ],
            );
        }

        $this->call([
            DocumentTypeSeeder::class,
        ]);

        if (filter_var(env('SEED_MOCK_DATA', false), FILTER_VALIDATE_BOOL)) {
            $this->call(MockDataSeeder::class);
        }
    }
}
