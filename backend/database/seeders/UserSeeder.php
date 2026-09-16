<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Department;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $dept = Department::first();

        User::create([
            'name' => 'Administrator',
            'email' => 'admin@filemanagement.com',
            'password' => Hash::make('password123'),
            'department_id' => $dept->id,
            'role' => 'administrator'
        ]);
    }
}