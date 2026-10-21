<?php

namespace Modules\User\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\User\Entities\Role;
use Modules\User\Entities\User;
use Cartalyst\Sentinel\Laravel\Facades\Activation;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $adminEmail = env('INITIAL_ADMIN_EMAIL');
        $adminPassword = env('INITIAL_ADMIN_PASSWORD');

        if (!$adminEmail || !$adminPassword) {
            throw new \RuntimeException('Set INITIAL_ADMIN_EMAIL and INITIAL_ADMIN_PASSWORD before seeding the initial admin user.');
        }

        $adminRole = Role::find(1);

        $envaySoft = User::create([
            'first_name' => 'Store',
            'last_name' => 'Admin',
            'email' => $adminEmail,
            'password' => app('sentinel.hasher')->hash($adminPassword),
        ]);

        $activation = Activation::create($envaySoft);
        Activation::complete($envaySoft, $activation->code);

        $adminRole->users()->attach($envaySoft);
    }
}
