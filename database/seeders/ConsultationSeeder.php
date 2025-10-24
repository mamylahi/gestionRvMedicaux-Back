<?php

namespace Database\Seeders;

use App\Models\Consultation;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ConsultationSeeder extends Seeder
{
    public function run()
    {
        $consultations = [
            [
                'rendezvous_id' => 1,
                'date_consultation' => Carbon::now()->addDays(1)->toDateString()
            ],
            [
                'rendezvous_id' => 4,
                'date_consultation' => Carbon::now()->subDays(2)->toDateString()
            ]
        ];

        foreach ($consultations as $consultation) {
            Consultation::create($consultation);
        }
    }
}
