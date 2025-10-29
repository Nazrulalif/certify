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
        // Create or update root user
        User::updateOrCreate(
            ['email' => 'root@certify.com'],
            [
                'name' => 'Root Admin',
                'password' => Hash::make('password'),
                'role' => User::ROLE_ROOT,
                'status' => true,
            ]
        );

        // Create or update regular user
        User::updateOrCreate(
            ['email' => 'user@certify.com'],
            [
                'name' => 'Regular User',
                'password' => Hash::make('password'),
                'role' => User::ROLE_USER,
                'status' => true,
            ]
        );

        // Create an inactive user for testing
        User::updateOrCreate(
            ['email' => 'inactive@certify.com'],
            [
                'name' => 'Inactive User',
                'password' => Hash::make('password'),
                'role' => User::ROLE_USER,
                'status' => false,
            ]
        );

        $this->command->info('Users seeded successfully!');
        $this->command->info('');
        $this->command->info('Root Account:');
        $this->command->info('  Email: root@certify.com');
        $this->command->info('  Password: password');
        $this->command->info('  Role: Root');
        $this->command->info('');
        $this->command->info('Regular User Account:');
        $this->command->info('  Email: user@certify.com');
        $this->command->info('  Password: password');
        $this->command->info('  Role: User');
        $this->command->info('');
        $this->command->info('Inactive User Account:');
        $this->command->info('  Email: inactive@certify.com');
        $this->command->info('  Password: password');
        $this->command->info('  Role: User (Inactive)');
    }
}
