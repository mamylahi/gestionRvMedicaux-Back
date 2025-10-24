<?php

namespace Database\Seeders;

use App\Models\RendezVous;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class RendezVousSeeder extends Seeder
{
    public function run()
    {
        $rendezvous = [
            [
                'patient_id' => 1,
                'medecin_id' => 1,
                'date_rendezvous' => Carbon::now()->addDays(1)->setHour(10)->setMinute(0),
                'heure_rendezvous' => '10:00',
                'motif' => 'Consultation cardiaque de routine',
                'statut' => 'confirme'
            ],
            [
                'patient_id' => 2,
                'medecin_id' => 2,
                'date_rendezvous' => Carbon::now()->addDays(2)->setHour(9)->setMinute(30),
                'heure_rendezvous' => '09:30',
                'motif' => 'Examen dermatologique',
                'statut' => 'en_attente'
            ],
            [
                'patient_id' => 3,
                'medecin_id' => 1,
                'date_rendezvous' => Carbon::now()->addDays(3)->setHour(15)->setMinute(0),
                'heure_rendezvous' => '15:00',
                'motif' => 'Suivi traitement cardiaque',
                'statut' => 'confirme'
            ],
            [
                'patient_id' => 4,
                'medecin_id' => 2,
                'date_rendezvous' => Carbon::now()->subDays(2)->setHour(11)->setMinute(0),
                'heure_rendezvous' => '11:00',
                'motif' => 'Problème de peau',
                'statut' => 'termine'
            ]
        ];

        foreach ($rendezvous as $rv) {
            RendezVous::create($rv);
        }
    }
}
