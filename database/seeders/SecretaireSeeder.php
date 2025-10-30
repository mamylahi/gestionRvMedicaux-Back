<?php

namespace Database\Seeders;

use App\Models\Secretaire;
use Illuminate\Database\Seeder;

class SecretaireSeeder extends Seeder
{
    public function run()
    {
        Secretaire::create([
            'numero_employe' => 'SEC-' . date('Y') . '-001',
            'user_id' => 9 // Sophie Ndiaye (la secrétaire, après les patients)
        ]);
    }
}
