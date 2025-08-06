<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'admin',
                'display_name' => 'Administrator',
                'description' => 'Full system access with all permissions including user management, role assignment, and system configuration.',
            ],
            [
                'name' => 'editor',
                'display_name' => 'Editor',
                'description' => 'Can create, edit, publish, and manage all articles. Can also manage authors and their content.',
            ],
            [
                'name' => 'author',
                'display_name' => 'Author',
                'description' => 'Can create and edit their own articles. Can submit articles for review but cannot publish directly.',
            ],
        ];

        foreach ($roles as $roleData) {
            Role::firstOrCreate(
                ['name' => $roleData['name']],
                [
                    'display_name' => $roleData['display_name'],
                    'description' => $roleData['description'],
                ]
            );
        }
    }
}
