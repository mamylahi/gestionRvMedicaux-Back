<?php

namespace Database\Seeders;

use App\Models\Departement;
use Illuminate\Database\Seeder;

class DepartementSeeder extends Seeder
{
    public function run()
    {
        $departements = [
            ['nom' => 'Cardiologie', 'description' => 'Spécialité médicale concernant les maladies du cœur et des vaisseaux sanguins.'],
            ['nom' => 'Dermatologie', 'description' => 'Spécialité médicale concernant la peau, les cheveux, les ongles et les muqueuses.'],
            ['nom' => 'Gynécologie', 'description' => 'Spécialité médicale concernant la santé de l\'appareil reproducteur féminin.'],
            ['nom' => 'Pédiatrie', 'description' => 'Spécialité médicale concernant les enfants et les adolescents.'],
            ['nom' => 'Radiologie', 'description' => 'Spécialité médicale concernant l\'imagerie médicale.'],
        ];

        foreach ($departements as $departement) {
            Departement::create($departement);
        }
    }
}
