<?php

namespace Database\Seeders;

use App\Models\Patient;
use Illuminate\Database\Seeder;

class PatientSeeder extends Seeder
{
    public function run()
    {
        $patients = [
            [
                'numero_patient' => 'PAT-' . date('Y') . '-001',
                'user_id' => 5 // Alice Durand (le premier patient, après l'admin et les 3 médecins et avant la secrétaire)
            ],
            [
                'numero_patient' => 'PAT-' . date('Y') . '-002',
                'user_id' => 6 // Bob Moreau
            ],
            [
                'numero_patient' => 'PAT-' . date('Y') . '-003',
                'user_id' => 7 // Claire Petit
            ],
            [
                'numero_patient' => 'PAT-' . date('Y') . '-004',
                'user_id' => 8 // David Leroy
            ]
        ];

        foreach ($patients as $patient) {
            Patient::create($patient);
        }
    }
}
