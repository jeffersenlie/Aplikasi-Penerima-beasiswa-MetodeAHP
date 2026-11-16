<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Akun demo: login dengan admin@dss.test / password
        User::updateOrCreate(
            ['email' => 'admin@dss.test'],
            ['name' => 'Admin', 'password' => 'password']
        );
    }
}