<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['name' => 'Admin', 'email' => 'admin@barangay.gov', 'password' => env('SEED_ADMIN_PASSWORD')],
            ['name' => 'Secretary', 'email' => 'secretary@barangay.gov', 'password' => env('SEED_SECRETARY_PASSWORD')],
            ['name' => 'Kagawad', 'email' => 'kagawad@barangay.gov', 'password' => env('SEED_KAGAWAD_PASSWORD')],
        ] as $account) {
            User::updateOrCreate(
                ['email' => $account['email']],
                [
                    'name' => $account['name'],
                    'password' => Hash::make($account['password'] ?: Str::random(40)),
                    'role' => strtolower($account['name']),
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
