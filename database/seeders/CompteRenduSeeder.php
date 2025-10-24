<?php

namespace Database\Seeders;

use App\Models\CompteRendu;
use Illuminate\Database\Seeder;

class CompteRenduSeeder extends Seeder
{
    public function run()
    {
        $compteRendus = [
            [
                'consultation_id' => 2, // La consultation avec rendezvous_id 4 (qui est le deuxième dans ConsultationSeeder)
                'traitement' => 'Crème hydratante 2 fois par jour pendant 15 jours',
                'diagnostic' => 'Dermatite séborrhéique légère',
                'observation' => 'Patient en bonne santé générale, problème cutané bénin',
                'date_creation' => now()
            ]
        ];

        foreach ($compteRendus as $compteRendu) {
            CompteRendu::create($compteRendu);
        }
    }
}
