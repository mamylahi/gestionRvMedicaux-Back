<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Administrateur
        User::create([
            'nom' => 'Admin',
            'prenom' => 'System',
            'email' => 'admin@clinique.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'adresse' => '123 Rue Admin, Ville',
            'telephone' => '0123456789'
        ]);

        // Médecins
        $medecins = [
            [
                'nom' => 'Dupont',
                'prenom' => 'Jean',
                'email' => 'jean.dupont@clinique.com',
                'password' => Hash::make('password'),
                'role' => 'medecin',
                'adresse' => '789 Rue des Médecins, Paris',
                'telephone' => '0123456791'
            ],
            [
                'nom' => 'Martin',
                'prenom' => 'Marie',
                'email' => 'marie.martin@clinique.com',
                'password' => Hash::make('password'),
                'role' => 'medecin',
                'adresse' => '101 Avenue de la Santé, Lyon',
                'telephone' => '0123456792'
            ],
            [
                'nom' => 'Bernard',
                'prenom' => 'Pierre',
                'email' => 'pierre.bernard@clinique.com',
                'password' => Hash::make('password'),
                'role' => 'medecin',
                'adresse' => '202 Boulevard Médical, Marseille',
                'telephone' => '0123456793'
            ]
        ];

        foreach ($medecins as $medecin) {
            User::create($medecin);
        }

        // Patients
        $patients = [
            [
                'nom' => 'Durand',
                'prenom' => 'Alice',
                'email' => 'alice.durand@email.com',
                'password' => Hash::make('password'),
                'role' => 'patient',
                'adresse' => '303 Rue des Patients, Lille',
                'telephone' => '0123456794'
            ],
            [
                'nom' => 'Moreau',
                'prenom' => 'Bob',
                'email' => 'bob.moreau@email.com',
                'password' => Hash::make('password'),
                'role' => 'patient',
                'adresse' => '404 Avenue de la Santé, Toulouse',
                'telephone' => '0123456795'
            ],
            [
                'nom' => 'Petit',
                'prenom' => 'Claire',
                'email' => 'claire.petit@email.com',
                'password' => Hash::make('password'),
                'role' => 'patient',
                'adresse' => '505 Boulevard Medical, Nice',
                'telephone' => '0123456796'
            ],
            [
                'nom' => 'Leroy',
                'prenom' => 'David',
                'email' => 'david.leroy@email.com',
                'password' => Hash::make('password'),
                'role' => 'patient',
                'adresse' => '606 Rue des Malades, Bordeaux',
                'telephone' => '0123456797'
            ]
        ];

        foreach ($patients as $patient) {
            User::create($patient);
        }

        // Secrétaire
        User::create([
            'nom' => 'Robert',
            'prenom' => 'Sophie',
            'email' => 'sophie.robert@clinique.com',
            'password' => Hash::make('password'),
            'role' => 'secretaire',
            'adresse' => '707 Avenue Secrétaire, Paris',
            'telephone' => '0123456798'
        ]);
    }
}
