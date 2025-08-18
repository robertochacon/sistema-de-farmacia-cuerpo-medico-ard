<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User as ModelsUser;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Usuarios base del sistema
        ModelsUser::create([
            'name' => 'Administrador',
            'email' => 'admin@admin.com',
            'password' => bcrypt('admin'),
            'remember_token' => null,
            'role' => 'admin',
            'created_at' => now()
        ]);

        ModelsUser::create([
            'name' => 'Supervisor',
            'email' => 'supervisor@supervisor.com',
            'password' => bcrypt('supervisor'),
            'remember_token' => null,
            'role' => 'supervisor',
            'created_at' => now()
        ]);

        ModelsUser::create([
            'name' => 'Empleado',
            'email' => 'empleado@empleado.com',
            'password' => bcrypt('empleado'),
            'remember_token' => null,
            'role' => 'employee',
            'created_at' => now()
        ]);

        ModelsUser::create([
            'name' => 'Aristides',
            'email' => 'aristides2476@gmail.com',
            'password' => bcrypt('marinamv'),
            'remember_token' => null,
            'role' => 'admin',
            'created_at' => date("Y-m-d H:i:s")
        ]);
        // === Usuarios de la lista (todos como "employee") ===
        $users = [
            ['name' => 'MARTHA BRIOSO', 'email' => 'martha.brioso@app.com'],
            ['name' => 'YOCASTA NUÑE VITINI', 'email' => 'yocasta.vitini@app.com'],
            ['name' => 'SAGRARIO SANCHEZ UREÑA ARD', 'email' => 'sagrario.sanchez@app.com'],
            ['name' => 'DANIURIS ALTAGRACIA RODRIGUEZ', 'email' => 'daniuris.rodriguez@app.com'],
            ['name' => 'GRECIA MARIA MARTINEZ LIRANZO', 'email' => 'grecia.martinez@app.com'],
            ['name' => 'WINIVEL M MENDEZ', 'email' => 'winivel.mendez@app.com'],
            ['name' => 'LIDIA CHALAS MORENO ARD', 'email' => 'lidia.chalas@app.com'],
            ['name' => 'ANGEL MANUEL RODRIGUEZ MATOS', 'email' => 'angel.rodriguez@app.com'],
            ['name' => 'HEIDY VALUZ VASQUEZ PAULINO', 'email' => 'heidy.vasquez@app.com'],
            ['name' => 'DIANA VALDEZ FIGUEREO', 'email' => 'diana.valdez@app.com'],
            ['name' => 'ALEXANDER OVIEDO DE AZA NIN', 'email' => 'alexander.oviedo@app.com'],
        ];

        foreach ($users as $user) {
            ModelsUser::create([
                'name' => $user['name'],
                'email' => $user['email'],
                'password' => bcrypt('123456'), // contraseña genérica
                'remember_token' => null,
                'role' => 'employee', // 🔹 todos como employee
                'created_at' => now()
            ]);
        }
    }
}
