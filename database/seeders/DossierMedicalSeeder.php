<?php

namespace Database\Seeders;

use App\Models\DossierMedical;
use Illuminate\Database\Seeder;

class DossierMedicalSeeder extends Seeder
{
    public function run()
    {
        $dossiers = [
            [
                'patient_id' => 1,
                'groupe_sanguin' => 'A+',
                'date_creation' => now()->subMonths(6)
            ],
            [
                'patient_id' => 2,
                'groupe_sanguin' => 'O-',
                'date_creation' => now()->subMonths(3)
            ],
            [
                'patient_id' => 3,
                'groupe_sanguin' => 'B+',
                'date_creation' => now()->subMonths(8)
            ],
            [
                'patient_id' => 4,
                'groupe_sanguin' => 'AB+',
                'date_creation' => now()->subMonths(1)
            ]
        ];

        foreach ($dossiers as $dossier) {
            DossierMedical::create($dossier);
        }
    }
}
