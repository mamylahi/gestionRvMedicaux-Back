<?php

namespace App\Services;

use App\Models\Patient;
use App\Models\Medecin;
use App\Models\Rendezvous;
use App\Models\Consultation;
use App\Models\Paiement;
use App\Models\Departement;
use App\Models\Specialite;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StatistiqueService
{
    /**
     * Récupère toutes les statistiques pour l'administrateur
     */
    public function getStatistiquesAdmin(): array
    {
        return [
            'general' => $this->getStatistiquesGenerales(),
            'rendezvous' => $this->getStatistiquesRendezVous(),
            'consultations' => $this->getStatistiquesConsultations(),
            'financier' => $this->getStatistiquesFinancieres(),
            'medecins' => $this->getStatistiquesMedecins(),
            'patients' => $this->getStatistiquesPatients(),
            'departements' => $this->getStatistiquesDepartements(),
        ];
    }

    /**
     * Statistiques générales
     */
    private function getStatistiquesGenerales(): array
    {
        return [
            'total_patients' => Patient::count(),
            'total_medecins' => Medecin::count(),
            'total_departements' => Departement::count(),
            'total_specialites' => Specialite::count(),
            'medecins_disponibles' => Medecin::where('disponible', true)->count(),
            'patients_ce_mois' => Patient::whereMonth('created_at', Carbon::now()->month)->count(),
        ];
    }

    /**
     * Statistiques des rendez-vous
     */
    private function getStatistiquesRendezVous(): array
    {
        $aujourdhui = Carbon::today();
        $debutMois = Carbon::now()->startOfMonth();

        return [
            'total' => Rendezvous::count(),
            'aujourdhui' => Rendezvous::whereDate('date_rendezvous', $aujourdhui)->count(),
            'ce_mois' => Rendezvous::whereBetween('date_rendezvous', [$debutMois, Carbon::now()])->count(),
            'par_statut' => Rendezvous::select('statut', DB::raw('count(*) as total'))
                ->groupBy('statut')
                ->get()
                ->pluck('total', 'statut'),
            'en_attente' => Rendezvous::where('statut', 'en_attente')->count(),
            'confirmes' => Rendezvous::where('statut', 'confirme')->count(),
            'annules' => Rendezvous::where('statut', 'annule')->count(),
            'termines' => Rendezvous::where('statut', 'termine')->count(),
            'prochains_7_jours' => Rendezvous::whereBetween('date_rendezvous', [
                Carbon::now(),
                Carbon::now()->addDays(7)
            ])->count(),
        ];
    }

    /**
     * Statistiques des consultations - CORRIGÉ pour PostgreSQL
     */
    private function getStatistiquesConsultations(): array
    {
        $debutMois = Carbon::now()->startOfMonth();
        $debutAnnee = Carbon::now()->startOfYear();

        // Correction pour PostgreSQL
        $consultationsParMois = Consultation::select(
            DB::raw('EXTRACT(MONTH FROM date_consultation) as mois'),
            DB::raw('count(*) as total')
        )
            ->whereYear('date_consultation', Carbon::now()->year)
            ->groupBy('mois')
            ->orderBy('mois')
            ->get()
            ->pluck('total', 'mois');

        return [
            'total' => Consultation::count(),
            'ce_mois' => Consultation::whereBetween('date_consultation', [$debutMois, Carbon::now()])->count(),
            'cette_annee' => Consultation::whereBetween('date_consultation', [$debutAnnee, Carbon::now()])->count(),
            'aujourdhui' => Consultation::whereDate('date_consultation', Carbon::today())->count(),
            'par_mois' => $consultationsParMois,
        ];
    }

    /**
     * Statistiques financières - CORRIGÉ pour PostgreSQL
     */
    private function getStatistiquesFinancieres(): array
    {
        $debutMois = Carbon::now()->startOfMonth();
        $debutAnnee = Carbon::now()->startOfYear();

        // Correction pour PostgreSQL
        $revenuParMois = Paiement::where('statut', 'valide')
            ->select(
                DB::raw('EXTRACT(MONTH FROM date_paiement) as mois'),
                DB::raw('sum(montant) as total')
            )
            ->whereYear('date_paiement', Carbon::now()->year)
            ->groupBy('mois')
            ->orderBy('mois')
            ->get()
            ->pluck('total', 'mois');

        return [
            'revenu_total' => Paiement::where('statut', 'valide')->sum('montant'),
            'revenu_ce_mois' => Paiement::where('statut', 'valide')
                ->whereBetween('date_paiement', [$debutMois, Carbon::now()])
                ->sum('montant'),
            'revenu_cette_annee' => Paiement::where('statut', 'valide')
                ->whereBetween('date_paiement', [$debutAnnee, Carbon::now()])
                ->sum('montant'),
            'revenu_aujourdhui' => Paiement::where('statut', 'valide')
                ->whereDate('date_paiement', Carbon::today())
                ->sum('montant'),
            'paiements_en_attente' => Paiement::where('statut', 'en_attente')->count(),
            'montant_en_attente' => Paiement::where('statut', 'en_attente')->sum('montant'),
            'par_moyen_paiement' => Paiement::where('statut', 'valide')
                ->select('moyen_paiement', DB::raw('sum(montant) as total'))
                ->groupBy('moyen_paiement')
                ->get()
                ->pluck('total', 'moyen_paiement'),
            'revenu_par_mois' => $revenuParMois,
        ];
    }

    /**
     * Statistiques des médecins
     */
    private function getStatistiquesMedecins(): array
    {
        return [
            'total' => Medecin::count(),
            'disponibles' => Medecin::where('disponible', true)->count(),
            'indisponibles' => Medecin::where('disponible', false)->count(),
            'par_specialite' => Medecin::select('specialites.nom', DB::raw('count(*) as total'))
                ->join('specialites', 'medecins.specialite_id', '=', 'specialites.id')
                ->groupBy('specialites.nom')
                ->get()
                ->pluck('total', 'nom'),
            'top_medecins' => Medecin::select(
                'medecins.id',
                'users.nom',
                'users.prenom',
                DB::raw('count(rendezvous.id) as total_rendezvous')
            )
                ->join('users', 'medecins.user_id', '=', 'users.id')
                ->leftJoin('rendezvous', 'medecins.id', '=', 'rendezvous.medecin_id')
                ->groupBy('medecins.id', 'users.nom', 'users.prenom')
                ->orderBy('total_rendezvous', 'desc')
                ->limit(10)
                ->get(),
        ];
    }

    /**
     * Statistiques des patients
     */
    private function getStatistiquesPatients(): array
    {
        $debutMois = Carbon::now()->startOfMonth();
        $debutAnnee = Carbon::now()->startOfYear();

        return [
            'total' => Patient::count(),
            'nouveaux_ce_mois' => Patient::whereBetween('created_at', [$debutMois, Carbon::now()])->count(),
            'nouveaux_cette_annee' => Patient::whereBetween('created_at', [$debutAnnee, Carbon::now()])->count(),
            'avec_dossier_medical' => Patient::has('dossierMedical')->count(),
            'sans_dossier_medical' => Patient::doesntHave('dossierMedical')->count(),
            'patients_actifs' => Patient::whereHas('rendezVous', function($query) {
                $query->where('date_rendezvous', '>=', Carbon::now()->subMonths(3));
            })->count(),
        ];
    }

    /**
     * Statistiques des départements
     */
    private function getStatistiquesDepartements(): array
    {
        return [
            'total' => Departement::count(),
            'avec_specialites' => Departement::has('specialites')->count(),
            'specialites_par_departement' => Departement::select('departements.nom', DB::raw('count(specialites.id) as total'))
                ->leftJoin('specialites', 'departements.id', '=', 'specialites.departement_id')
                ->groupBy('departements.id', 'departements.nom')
                ->get()
                ->pluck('total', 'nom'),
            'medecins_par_departement' => Departement::select('departements.nom', DB::raw('count(medecins.id) as total'))
                ->leftJoin('specialites', 'departements.id', '=', 'specialites.departement_id')
                ->leftJoin('medecins', 'specialites.id', '=', 'medecins.specialite_id')
                ->groupBy('departements.id', 'departements.nom')
                ->get()
                ->pluck('total', 'nom'),
        ];
    }
}
