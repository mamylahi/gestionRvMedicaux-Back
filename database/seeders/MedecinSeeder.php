<?php

namespace Database\Seeders;

use App\Models\Medecin;
use Illuminate\Database\Seeder;

class MedecinSeeder extends Seeder
{
    public function run()
    {
        $medecins = [
            [
                'numero_medecin' => 'MED-' . date('Y') . '-001',
                'disponible' => true,
                'user_id' => 2, // Jean Dupont (le premier médecin, note: l'admin a l'id 1, donc les médecins commencent à l'id 2)
                'specialite_id' => 1 // Cardiologue
            ],
            [
                'numero_medecin' => 'MED-' . date('Y') . '-002',
                'disponible' => true,
                'user_id' => 3, // Marie Martin
                'specialite_id' => 2 // Dermatologue
            ],
            [
                'numero_medecin' => 'MED-' . date('Y') . '-003',
                'disponible' => false,
                'user_id' => 4, // Pierre Bernard
                'specialite_id' => 3 // Pédiatre
            ]
        ];

        foreach ($medecins as $medecin) {
            Medecin::create($medecin);
        }
    }
}
