<?php

namespace App\Console\Commands;

use App\Mail\RappelRendezVousMail;
use App\Models\Rendezvous;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class EnvoyerRappelRendezVous extends Command
{
    protected $signature = 'rendezvous:rappel';
    protected $description = 'Envoie des rappels par email pour les rendez-vous du lendemain';

    public function handle()
    {
        $demain = Carbon::tomorrow()->format('Y-m-d');

        $rendezVous = Rendezvous::with(['patient.user', 'medecin.user', 'medecin.specialite'])
            ->whereDate('date_rendezvous', $demain)
            ->where('statut', '!=', 'annule')
            ->get();

        $count = 0;

        foreach ($rendezVous as $rdv) {
            if ($rdv->patient && $rdv->patient->user && $rdv->patient->user->email) {
                try {
                    Mail::to($rdv->patient->user->email)
                        ->send(new RappelRendezVousMail($rdv));

                    $count++;
                    $this->info("✓ Rappel envoyé à {$rdv->patient->user->email}");
                } catch (\Exception $e) {
                    $this->error("✗ Erreur pour {$rdv->patient->user->email}: {$e->getMessage()}");
                }
            }
        }

        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->info("Total: {$count} rappel(s) envoyé(s) sur " . $rendezVous->count() . " rendez-vous");

        return Command::SUCCESS;
    }
}
