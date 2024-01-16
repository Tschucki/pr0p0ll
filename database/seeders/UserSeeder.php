<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if(config('app.env') === 'production') {
            return;
        }

        User::create([
            'name' => 'Admin',
            'email' => 'admin@locahost',
            'password' => \Hash::make('password'),
            'pr0gramm_identifier' => 'admin',
        ]);
    }
}
