<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Create (or update) the panel owner from ADMIN_NAME / ADMIN_EMAIL / ADMIN_PASSWORD.
     */
    public function run(): void
    {
        $email = config('portfolio.admin.email');
        $password = config('portfolio.admin.password');

        if (blank($email)) {
            $this->command->warn('ADMIN_EMAIL is not set — skipping the admin user. Use `php artisan make:filament-user` instead.');

            return;
        }

        if (blank($password)) {
            if (app()->isProduction()) {
                $this->command->warn('ADMIN_PASSWORD is not set — skipping the admin user in production.');

                return;
            }

            $password = 'password';
        }

        User::query()->updateOrCreate(
            ['email' => $email],
            ['name' => config('portfolio.admin.name'), 'password' => $password, 'email_verified_at' => now()],
        );
    }
}
