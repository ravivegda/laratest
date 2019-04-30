<?php

use Illuminate\Database\Seeder;
use App\User;
use App\UserRoles;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(UsersTableSeeder::class);
        // Create Default Admin User        
        $adminUser = new User();
        $adminUser->full_name = 'Admin';
        $adminUser->email = 'admin@mymail.com';
        $adminUser->password = bcrypt('admin');
        $adminUser->role = 'admin';
        $adminUser->save();
    }
}
