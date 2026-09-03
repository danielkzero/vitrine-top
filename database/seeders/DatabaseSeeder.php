<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(PlanSeeder::class);

        User::query()->updateOrCreate(
            ['email' => 'danikzero@hotmail.com'],
            [
                'name' => 'Daniel',
                'email_verified_at' => now(),
                'password' => 'V1p@@2025Put',
                'is_admin' => true,
                'two_factor_secret' => null,
                'two_factor_recovery_codes' => null,
                'two_factor_confirmed_at' => null,
            ],
        );

        $this->call(MinhaLojinhaSeeder::class);
        $this->call(EcommerceDemoSeeder::class);
        $this->call(MinhaLojinhaContentSeeder::class);
        $this->call(AdminDemoSeeder::class);
    }
}
