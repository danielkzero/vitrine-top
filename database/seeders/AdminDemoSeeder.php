<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class AdminDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PlanSeeder::class,
            AdminDemoClientsSeeder::class,
            AdminDemoActivitySeeder::class,
        ]);
    }
}
