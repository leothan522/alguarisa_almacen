<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Usuario Root
        $root = User::factory()->create([
            'name' => config('app.root_name') ? config('app.root_name') : 'Administrador',
            'email' => config('app.root_email') ? config('app.root_email') : 'admin@alguarisa.com',
            'email_verified_at' => now(),
            'password' => Hash::make(config('app.root_password') ? config('app.root_password') : 'admin1234'),
            'access_panel' => 1,
            'is_root' => 1,
        ]);

        // Usuario Administrador
        if ($root->email != 'admin@alguarisa.com') {
            $admin = User::factory()->create([
                'name' => 'Administrador',
                'email' => 'admin@alguarisa.com',
                'email_verified_at' => now(),
                'password' => Hash::make('admin1234'),
                'access_panel' => 1,
            ]);
            $admin->assignRole('admin');
        }
    }
}
