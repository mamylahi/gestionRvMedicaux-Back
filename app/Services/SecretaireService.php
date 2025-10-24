<?php

namespace App\Services;

use App\Helpers\ApiResponse;
use App\Http\Resources\SecretaireResource;
use App\Http\Resources\UserResource;
use App\Models\Secretaire;
use App\Models\User;

class SecretaireService
{
    /**
     * Récupérer tous les secrétaires
     */
    public function index()
    {
        try {
            $secretaires = Secretaire::with('user')->get();
            return ApiResponse::success(SecretaireResource::collection($secretaires), 200, 'Liste des secrétaires récupérée');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    /**
     * Récupérer une secrétaire par ID
     */
    public function show(string $id)
    {
        try {
            $secretaire = Secretaire::with('user')->find($id);
            if (!$secretaire) {
                return ApiResponse::error('Secrétaire introuvable', 404);
            }
            return ApiResponse::success(new SecretaireResource($secretaire, 200, 'Secrétaire trouvée'));
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    /**
     * Mettre à jour une secrétaire
     */
    public function update(array $request, string $id)
    {
        try {
            $secretaire = Secretaire::find($id);
            if (!$secretaire) {
                return ApiResponse::error('Secrétaire introuvable', 404);
            }

            $user = $secretaire->user;
            if (!$user) {
                return ApiResponse::error('Utilisateur associé introuvable', 404);
            }

            // Mettre à jour l'utilisateur
            $user->update([
                'nom'       => $request['nom'] ?? $user->nom,
                'prenom'    => $request['prenom'] ?? $user->prenom,
                'adresse'   => $request['adresse'] ?? $user->adresse,
                'telephone' => $request['telephone'] ?? $user->telephone,
                'email'     => $request['email'] ?? $user->email,
            ]);

            if (!empty($request['password'])) {
                $user->password = bcrypt($request['password']);
                $user->save();
            }

            return ApiResponse::success(new SecretaireResource($secretaire->load('user'), 200, 'Secrétaire mise à jour avec succès'));
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    /**
     * Supprimer une secrétaire
     */
    public function destroy(int $id)
    {
        try {
            $secretaire = Secretaire::find($id);
            if (!$secretaire) {
                return ApiResponse::error('Secrétaire introuvable', 404);
            }

            $user = $secretaire->user;
            $secretaire->delete();

            if ($user) {
                $user->delete();
            }

            return ApiResponse::success([], 200, 'Secrétaire et utilisateur supprimés avec succès');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }


    public function getRendezVousAVenir()
    {
        try {
            // Vérifier l'authentification
            $user = Auth::user();
            if (!$user) {
                return ApiResponse::error('Non authentifié', 401);
            }

            $secretaire = Secretaire::where('user_id', $user->id)->first();
            if (!$secretaire) {
                return ApiResponse::error('Secrétaire introuvable', 404);
            }

            // Récupérer tous les rendez-vous à venir (date >= aujourd'hui)
            $rendezVousAVenir = RendezVous::with([
                'patient.user',
                'medecin.user',
                'medecin.specialite',
                'consultation'
            ])
                ->where('date_rendezvous', '>=', now()->toDateString())
                ->whereIn('statut', ['en_attente', 'confirme'])
                ->orderBy('date_rendezvous', 'asc')
                ->orderBy('heure_rendezvous', 'asc')
                ->get();

            // Grouper par date
            $rendezVousParDate = $rendezVousAVenir->groupBy(function($rdv) {
                return $rdv->date_rendezvous;
            });

            // Statistiques
            $statistiques = [
                'total_rendezvous_a_venir' => $rendezVousAVenir->count(),
                'aujourd_hui' => $rendezVousAVenir->filter(function($rdv) {
                    return $rdv->date_rendezvous == now()->toDateString();
                })->count(),
                'cette_semaine' => $rendezVousAVenir->filter(function($rdv) {
                    return $rdv->date_rendezvous >= now()->startOfWeek() &&
                        $rdv->date_rendezvous <= now()->endOfWeek();
                })->count(),
                'ce_mois' => $rendezVousAVenir->filter(function($rdv) {
                    return $rdv->date_rendezvous >= now()->startOfMonth() &&
                        $rdv->date_rendezvous <= now()->endOfMonth();
                })->count(),
                'en_attente' => $rendezVousAVenir->where('statut', 'en_attente')->count(),
                'confirmes' => $rendezVousAVenir->where('statut', 'confirme')->count(),
                'par_medecin' => $rendezVousAVenir->groupBy('medecin_id')->map(function($group) {
                    return [
                        'medecin' => $group->first()->medecin->user->nom . ' ' . $group->first()->medecin->user->prenom,
                        'count' => $group->count()
                    ];
                })->values(),
            ];

            return ApiResponse::success([
                'rendezvous_a_venir' => $rendezVousAVenir,
                'rendezvous_par_date' => $rendezVousParDate,
                'statistiques' => $statistiques
            ], 200, 'Rendez-vous à venir récupérés avec succès');

        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    public function getDossiersMedicaux()
    {
        try {
            // Vérifier l'authentification
            $user = Auth::user();
            if (!$user) {
                return ApiResponse::error('Non authentifié', 401);
            }

            $secretaire = Secretaire::where('user_id', $user->id)->first();
            if (!$secretaire) {
                return ApiResponse::error('Secrétaire introuvable', 404);
            }

            // Récupérer tous les dossiers médicaux
            $dossiersMedicaux = DossierMedical::with([
                'patient.user'
            ])
                ->orderBy('date_creation', 'desc')
                ->get();

            // Enrichir chaque dossier avec des informations supplémentaires
            $dossiersMedicaux->each(function($dossier) {
                // Nombre total de consultations du patient
                $dossier->total_consultations = Consultation::whereHas('rendezvous', function($query) use ($dossier) {
                    $query->where('patient_id', $dossier->patient_id);
                })->count();

                // Dernière consultation
                $dossier->derniere_consultation = Consultation::with(['rendezvous.medecin.user'])
                    ->whereHas('rendezvous', function($query) use ($dossier) {
                        $query->where('patient_id', $dossier->patient_id);
                    })
                    ->orderBy('date_consultation', 'desc')
                    ->first();

                // Prochain rendez-vous
                $dossier->prochain_rendezvous = RendezVous::with(['medecin.user'])
                    ->where('patient_id', $dossier->patient_id)
                    ->where('date_rendezvous', '>=', now()->toDateString())
                    ->whereIn('statut', ['en_attente', 'confirme'])
                    ->orderBy('date_rendezvous', 'asc')
                    ->first();

                // Informations patient formatées
                $dossier->patient_info = [
                    'nom_complet' => $dossier->patient->user->nom . ' ' . $dossier->patient->user->prenom,
                    'numero_patient' => $dossier->patient->numero_patient,
                    'telephone' => $dossier->patient->user->telephone,
                    'email' => $dossier->patient->user->email,
                    'adresse' => $dossier->patient->user->adresse,
                ];
            });

            // Statistiques
            $statistiques = [
                'total_dossiers' => $dossiersMedicaux->count(),
                'dossiers_ce_mois' => $dossiersMedicaux->filter(function($dossier) {
                    return $dossier->date_creation >= now()->startOfMonth();
                })->count(),
                'dossiers_cette_annee' => $dossiersMedicaux->filter(function($dossier) {
                    return $dossier->date_creation >= now()->startOfYear();
                })->count(),
                'par_groupe_sanguin' => $dossiersMedicaux->whereNotNull('groupe_sanguin')
                    ->groupBy('groupe_sanguin')
                    ->map(function($group) {
                        return $group->count();
                    }),
            ];

            return ApiResponse::success([
                'dossiers_medicaux' => $dossiersMedicaux,
                'statistiques' => $statistiques
            ], 200, 'Dossiers médicaux récupérés avec succès');

        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }


    public function getPaiementsNonPayes()
    {
        try {
            // Vérifier l'authentification
            $user = Auth::user();
            if (!$user) {
                return ApiResponse::error('Non authentifié', 401);
            }

            $secretaire = Secretaire::where('user_id', $user->id)->first();
            if (!$secretaire) {
                return ApiResponse::error('Secrétaire introuvable', 404);
            }

            // Récupérer tous les paiements en attente
            $paiementsNonPayes = Paiement::with([
                'consultation.rendezvous.patient.user',
                'consultation.rendezvous.medecin.user'
            ])
                ->where('statut', 'en_attente')
                ->orderBy('date_paiement', 'asc')
                ->get();

            // Enrichir les paiements avec des informations supplémentaires
            $paiementsNonPayes->each(function($paiement) {
                $rendezvous = $paiement->consultation->rendezvous ?? null;

                if ($rendezvous) {
                    // Informations patient
                    $paiement->patient_info = [
                        'id' => $rendezvous->patient->id,
                        'nom_complet' => $rendezvous->patient->user->nom . ' ' . $rendezvous->patient->user->prenom,
                        'numero_patient' => $rendezvous->patient->numero_patient,
                        'telephone' => $rendezvous->patient->user->telephone,
                        'email' => $rendezvous->patient->user->email,
                    ];

                    // Informations médecin
                    $paiement->medecin_info = [
                        'id' => $rendezvous->medecin->id,
                        'nom_complet' => $rendezvous->medecin->user->nom . ' ' . $rendezvous->medecin->user->prenom,
                    ];

                    // Informations consultation
                    $paiement->consultation_info = [
                        'id' => $paiement->consultation->id,
                        'date_consultation' => $paiement->consultation->date_consultation,
                        'motif' => $paiement->consultation->motif ?? 'Non spécifié',
                    ];

                    // Calcul du retard de paiement
                    $datePaiement = \Carbon\Carbon::parse($paiement->date_paiement);
                    $joursRetard = now()->diffInDays($datePaiement, false);
                    $paiement->jours_retard = $joursRetard < 0 ? abs($joursRetard) : 0;
                }
            });

            // Calculer le montant total non payé
            $montantTotalNonPaye = $paiementsNonPayes->sum('montant');

            // Grouper par ancienneté
            $paiementsParAnciennete = [
                'moins_de_7_jours' => $paiementsNonPayes->filter(function($p) {
                    return $p->jours_retard <= 7;
                })->count(),
                'entre_7_et_30_jours' => $paiementsNonPayes->filter(function($p) {
                    return $p->jours_retard > 7 && $p->jours_retard <= 30;
                })->count(),
                'plus_de_30_jours' => $paiementsNonPayes->filter(function($p) {
                    return $p->jours_retard > 30;
                })->count(),
            ];

            // Statistiques
            $statistiques = [
                'total_paiements_non_payes' => $paiementsNonPayes->count(),
                'montant_total_non_paye' => $montantTotalNonPaye,
                'paiements_par_anciennete' => $paiementsParAnciennete,
                'par_moyen_paiement' => $paiementsNonPayes->groupBy('moyen_paiement')->map(function($group) {
                    return [
                        'count' => $group->count(),
                        'montant' => $group->sum('montant')
                    ];
                }),
                'top_5_retards' => $paiementsNonPayes->sortByDesc('jours_retard')->take(5)->values(),
            ];

            return ApiResponse::success([
                'paiements_non_payes' => $paiementsNonPayes,
                'statistiques' => $statistiques
            ], 200, 'Paiements non payés récupérés avec succès');

        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

}
