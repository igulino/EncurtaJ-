<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserLinks;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        \App\Models\User::create([
            'email' => 'teste@exemplo.com',
            'password' => \Illuminate\Support\Facades\Hash::make('123456'),
        ]);

        \App\Models\UserLinks::create([
            'user_id' => 1,
            'name' => 'Google',
            'link_generated' => 'https://www.google.com',
            'given_link' => 'https://www.google.com',
        ]);

    }
}
