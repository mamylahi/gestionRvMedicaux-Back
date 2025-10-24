<?php

namespace Database\Seeders;

use App\Models\Paiement;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PaiementSeeder extends Seeder
{
    public function run()
    {
        $paiements = [
            [
                'consultation_id' => 2,
                'montant' => 45.00,
                'date_paiement' => Carbon::now()->subDays(1),
                'moyen_paiement' => 'carte',
                'statut' => 'valide'
            ],
            [
                'consultation_id' => 1,
                'montant' => 60.00,
                'date_paiement' => Carbon::now()->addDays(2),
                'moyen_paiement' => 'espece',
                'statut' => 'en_attente'
            ]
        ];

        foreach ($paiements as $paiement) {
            Paiement::create($paiement);
        }
    }
}
