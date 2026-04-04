<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => config('constants.admin_email')],
            [
                'name' => 'Admin',
                'password' => bcrypt('admin123'),
                'is_admin' => true,
                'role' => 'Administrator',
                'phone' => '0000000000',
            ]
        );
    }
}
