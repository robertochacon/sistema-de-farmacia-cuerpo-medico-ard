<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            ['name' => 'Emergencias', 'code' => 'EMG', 'description' => 'Atención inmediata a pacientes en estado crítico.'],
            ['name' => 'Medicina Interna', 'code' => 'MED', 'description' => 'Diagnóstico y manejo integral de adultos.'],
            ['name' => 'Cirugía General', 'code' => 'CIR', 'description' => 'Procedimientos quirúrgicos generales.'],
            ['name' => 'Pediatría', 'code' => 'PED', 'description' => 'Atención médica a pacientes pediátricos.'],
            ['name' => 'Ginecología y Obstetricia', 'code' => 'GYO', 'description' => 'Salud de la mujer y atención obstétrica.'],
            ['name' => 'Cardiología', 'code' => 'CAR', 'description' => 'Diagnóstico y tratamiento de enfermedades del corazón.'],
            ['name' => 'Ortopedia y Traumatología', 'code' => 'ORT', 'description' => 'Sistema musculoesquelético y traumas.'],
            ['name' => 'Neurología', 'code' => 'NEU', 'description' => 'Trastornos del sistema nervioso.'],
            ['name' => 'Dermatología', 'code' => 'DER', 'description' => 'Enfermedades de la piel.'],
            ['name' => 'Otorrinolaringología', 'code' => 'ORL', 'description' => 'Oído, nariz y garganta.'],
            ['name' => 'Oftalmología', 'code' => 'OFT', 'description' => 'Salud visual.'],
            ['name' => 'Urología', 'code' => 'URO', 'description' => 'Sistema urinario y reproductor masculino.'],
            ['name' => 'Nefrología', 'code' => 'NEF', 'description' => 'Enfermedades renales.'],
            ['name' => 'Gastroenterología', 'code' => 'GAS', 'description' => 'Sistema digestivo.'],
            ['name' => 'Anestesiología', 'code' => 'ANE', 'description' => 'Manejo anestésico perioperatorio.'],
            ['name' => 'Imagenología / Radiología', 'code' => 'IMG', 'description' => 'Rayos X, tomografía, ecografía.'],
            ['name' => 'Laboratorio Clínico', 'code' => 'LAB', 'description' => 'Análisis clínicos.'],
            ['name' => 'Farmacia', 'code' => 'FAR', 'description' => 'Dispensación y control de medicamentos.'],
            ['name' => 'Odontología', 'code' => 'ODO', 'description' => 'Salud bucal.'],
            ['name' => 'Psicología', 'code' => 'PSI', 'description' => 'Atención psicológica.'],
            ['name' => 'Psiquiatría', 'code' => 'PSQ', 'description' => 'Salud mental.'],
            ['name' => 'Fisiatría y Rehabilitación', 'code' => 'FIS', 'description' => 'Rehabilitación física.'],
            ['name' => 'Enfermería', 'code' => 'ENF', 'description' => 'Cuidado integral de pacientes.'],
            ['name' => 'Admisión', 'code' => 'ADM', 'description' => 'Ingreso y registro de pacientes.'],
            ['name' => 'Archivo Clínico', 'code' => 'ARC', 'description' => 'Gestión de historias clínicas.'],
            ['name' => 'Epidemiología', 'code' => 'EPI', 'description' => 'Vigilancia epidemiológica.'],
            ['name' => 'Trabajo Social', 'code' => 'TSO', 'description' => 'Apoyo y gestión social.'],
            ['name' => 'Auditoría Médica', 'code' => 'AUD', 'description' => 'Control de calidad y auditoría.'],
            ['name' => 'Mantenimiento', 'code' => 'MAN', 'description' => 'Infraestructura y equipos.'],
            ['name' => 'CEYE / Esterilización', 'code' => 'CEY', 'description' => 'Centro de esterilización.'],
            ['name' => 'Banco de Sangre / Hematología', 'code' => 'BSA', 'description' => 'Donación y manejo de sangre.'],
        ];

        foreach ($departments as $data) {
            Department::updateOrCreate(
                ['code' => $data['code']],
                [
                    'name' => $data['name'],
                    'description' => $data['description'] ?? null,
                ]
            );
        }
    }
} 