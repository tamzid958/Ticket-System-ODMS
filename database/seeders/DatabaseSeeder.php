<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed roles & permissions
        $this->call(ShieldSeeder::class);

        // Seed Packages
        $this->call(PackageSeeder::class);

        if (config('APP_ENV', 'dev') !== 'production') {
            $this->call(UsersSeeder::class);
        }
    }
}
