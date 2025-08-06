<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Admin User
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'System Administrator',
                'password' => Hash::make('password123'),
            ]
        );
        $admin->assignRole('admin');

        // Create Editor User
        $editor = User::firstOrCreate(
            ['email' => 'editor@example.com'],
            [
                'name' => 'Content Editor',
                'password' => Hash::make('password123'),
            ]
        );
        $editor->assignRole('editor');

        // Create Author User
        $author = User::firstOrCreate(
            ['email' => 'author@example.com'],
            [
                'name' => 'Article Author',
                'password' => Hash::make('password123'),
            ]
        );
        $author->assignRole('author');

        // Create a user with multiple roles (Admin + Editor)
        $superUser = User::firstOrCreate(
            ['email' => 'super@example.com'],
            [
                'name' => 'Super User',
                'password' => Hash::make('password123'),
            ]
        );
        $superUser->assignRoles(['admin', 'editor']);
    }
}
