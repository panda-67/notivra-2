<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@notivra.id'],
            [
                'name' => 'Admin',
                'role' => UserRole::ADMIN_STAFF,
                'password' => Hash::make('admin123'),
            ]
        );

        $this->call([
            ServiceSeeder::class,
        ]);
    }
}
