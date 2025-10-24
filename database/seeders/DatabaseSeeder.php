<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            DepartementSeeder::class,
            SpecialiteSeeder::class,
            UserSeeder::class,
            MedecinSeeder::class,
            PatientSeeder::class,
            SecretaireSeeder::class,
            DisponibiliteSeeder::class,
            RendezVousSeeder::class,
            ConsultationSeeder::class,
            CompteRenduSeeder::class,
            DossierMedicalSeeder::class,
            PaiementSeeder::class,
        ]);

        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
