<?php

namespace Database\Seeders;

use App\Models\Disponibilite;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DisponibiliteSeeder extends Seeder
{
    public function run()
    {
        $disponibilites = [
            [
                'medecin_id' => 1, // Jean Dupont
                'date_debut' => Carbon::now()->addDays(1)->setHour(9)->setMinute(0),
                'date_fin' => Carbon::now()->addDays(1)->setHour(12)->setMinute(0),
                'recurrent' => false
            ],
            [
                'medecin_id' => 1,
                'date_debut' => Carbon::now()->addDays(3)->setHour(14)->setMinute(0),
                'date_fin' => Carbon::now()->addDays(3)->setHour(18)->setMinute(0),
                'recurrent' => false
            ],
            [
                'medecin_id' => 2, // Marie Martin
                'date_debut' => Carbon::now()->addDays(2)->setHour(8)->setMinute(0),
                'date_fin' => Carbon::now()->addDays(2)->setHour(16)->setMinute(0),
                'recurrent' => true
            ]
        ];

        foreach ($disponibilites as $disponibilite) {
            Disponibilite::create($disponibilite);
        }
    }
}
