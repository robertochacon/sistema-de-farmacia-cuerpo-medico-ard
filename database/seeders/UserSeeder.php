<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User as ModelsUser;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        ModelsUser::create([
            'name' => 'Administrador',
            'email' => 'admin@admin.com',
            'password' => bcrypt('admin'),
            'remember_token' => null,
            'role' => 'admin',
            'created_at' => date("Y-m-d H:i:s")
        ]);

        ModelsUser::create([
            'name' => 'Supervisor',
            'email' => 'supervisor@supervisor.com',
            'password' => bcrypt('supervisor'),
            'remember_token' => null,
            'role' => 'supervisor',
            'created_at' => date("Y-m-d H:i:s")
        ]);

        ModelsUser::create([
            'name' => 'Empleado',
            'email' => 'empleado@empleado.com',
            'password' => bcrypt('empleado'),
            'remember_token' => null,
            'role' => 'employee',
            'created_at' => date("Y-m-d H:i:s")
        ]);

        ModelsUser::create([
            'name' => 'Aristides',
            'email' => 'aristides2476@gmail.com',
            'password' => bcrypt('marinamv'),
            'remember_token' => null,
            'role' => 'admin',
            'created_at' => date("Y-m-d H:i:s")
        ]);
    }
}
