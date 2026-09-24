<?php

namespace Database\Seeders;

use App\Models\NowPage;
use App\Models\Profile;
use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed what every install needs: the admin user and the singleton rows.
     * Demo content is opt-in: `php artisan db:seed --class=DemoContentSeeder`.
     */
    public function run(): void
    {
        $this->call(AdminUserSeeder::class);

        SiteSetting::current();
        Profile::current();
        NowPage::current();
    }
}
