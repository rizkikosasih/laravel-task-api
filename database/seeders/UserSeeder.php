<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $rizki = User::factory()->create([
            'name' => 'Rizki',
            'email' => 'rizki@mail.com',
            'password' => 'rahasia123',
        ]);

        $rizki->assignRole('admin');

        $admin = User::factory()->create([
            'email' => 'admin@mail.com',
        ]);

        $admin->assignRole('admin');

        $members = User::factory(5)->create();

        foreach ($members as $member) {
            $member->assignRole('member');
        }
    }
}
