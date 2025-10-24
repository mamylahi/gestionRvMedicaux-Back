<?php

namespace Database\Seeders;

use App\Models\Specialite;
use Illuminate\Database\Seeder;

class SpecialiteSeeder extends Seeder
{
    public function run()
    {
        $specialites = [
            ['nom' => 'Cardiologue', 'departement_id' => 1],
            ['nom' => 'Dermatologue', 'departement_id' => 2],
            ['nom' => 'Gynécologue', 'departement_id' => 3],
            ['nom' => 'Pédiatre', 'departement_id' => 4],
            ['nom' => 'Radiologue', 'departement_id' => 5],
        ];

        foreach ($specialites as $specialite) {
            Specialite::create($specialite);
        }
    }
}
