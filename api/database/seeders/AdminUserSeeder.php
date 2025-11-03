<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
         
        if (!User::where('email', 'moe@gmail.com')->exists()) {
            User::create([
                'name' => 'Super Admin',
                'email' => 'moe@gmail.com',
                'password' => Hash::make('246810'),
                'role' => 'admin',
            ]);
        }
    }
}
 