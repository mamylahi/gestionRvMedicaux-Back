<?php

namespace App\Services;

use App\Helpers\ApiResponse;
use App\Http\Resources\MedecinResource;
use App\Http\Resources\PatientResource;
use App\Http\Resources\UserResource;
use App\Models\CompteRendu;
use App\Models\Consultation;
use App\Models\DossierMedical;
use App\Models\Medecin;
use App\Models\RendezVous;
use App\Models\Secretaire;
use App\Models\User;

class MedecinService
{
    /**
     * Récupérer tous les médecins
     */
    public function index()
    {
        try {
            $medecins = Medecin::with('user')->get();
            return ApiResponse::success(MedecinResource::collection($medecins), 200, 'Liste des médecins récupérée');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }


    /**
     * Récupérer un médecin par ID
     */
    public function show(string $id)
    {
        try {
            $medecin = Medecin::with('user')->find($id);
            if (!$medecin) {
                return ApiResponse::error('Médecin introuvable', 404);
            }
            return ApiResponse::success(new MedecinResource($medecin, 200, 'Médecin trouvé'));
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    /**
     * Mettre à jour un médecin
     */
    public function update(array $request, string $id)
    {
        try {
            $medecin = Medecin::find($id);
            if (!$medecin) {
                return ApiResponse::error('Médecin introuvable', 404);
            }

            $user = $medecin->user;
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

            // Mettre à jour le médecin
            $medecin->update([
                'specialite' => $request['specialite'] ?? $medecin->specialite,
                'departement' => $request['departement'] ?? $medecin->departement,
                'disponible' => $request['disponible'] ?? $medecin->disponible,
            ]);

            return ApiResponse::success(new MedecinResource($medecin->load('user'), 200, 'Médecin mis à jour avec succès'));
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    /**
     * Supprimer un médecin
     */
    public function destroy(int $id)
    {
        try {
            $medecin = Medecin::find($id);
            if (!$medecin) {
                return ApiResponse::error('Médecin introuvable', 404);
            }

            $user = $medecin->user;
            $medecin->delete();

            if ($user) {
                $user->delete();
            }

            return ApiResponse::success([], 200, 'Médecin et utilisateur supprimés avec succès');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }


    public function getDisponibles()
    {
        try {
            $medecins = Medecin::with(['user', 'specialite', 'departement'])
                ->where('disponible', true)
                ->get();

            return ApiResponse::success(MedecinResource::collection($medecins), 200, 'Médecins disponibles récupérés');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    public function getBySpecialite(string $specialiteId)
    {
        try {
            $medecins = Medecin::with(['user', 'specialite', 'departement'])
                ->where('specialite_id', $specialiteId)
                ->get();

            return ApiResponse::success(MedecinResource::collection($medecins), 200, 'Médecins de la spécialité récupérés');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    public function getDashboard(string $medecinId)
    {
        try {
            $medecin = Medecin::with(['user', 'specialite', 'departement'])->find($medecinId);

            if (!$medecin) {
                return ApiResponse::error('Médecin introuvable', 404);
            }

            // Statistiques
            $totalRendezVous = RendezVous::where('medecin_id', $medecinId)->count();
            $rendezVousEnAttente = RendezVous::where('medecin_id', $medecinId)
                ->where('statut', 'en_attente')
                ->count();
            $rendezVousConfirmes = RendezVous::where('medecin_id', $medecinId)
                ->where('statut', 'confirme')
                ->count();
            $totalConsultations = Consultation::whereHas('rendezvous', function($query) use ($medecinId) {
                $query->where('medecin_id', $medecinId);
            })->count();

            // Rendez-vous d'aujourd'hui
            $today = now()->toDateString();
            $rendezVousAujourdhui = RendezVous::with(['patient.user'])
                ->where('medecin_id', $medecinId)
                ->whereDate('date_rendezvous', $today)
                ->orderBy('heure_rendezvous')
                ->get();

            // Prochains rendez-vous
            $prochainsRendezVous = RendezVous::with(['patient.user'])
                ->where('medecin_id', $medecinId)
                ->where('date_rendezvous', '>=', now())
                ->whereIn('statut', ['en_attente', 'confirme'])
                ->orderBy('date_rendezvous')
                ->orderBy('heure_rendezvous')
                ->limit(10)
                ->get();

            $dashboard = [
                'medecin' => new MedecinResource($medecin),
                'statistiques' => [
                    'total_rendezvous' => $totalRendezVous,
                    'rendezvous_en_attente' => $rendezVousEnAttente,
                    'rendezvous_confirmes' => $rendezVousConfirmes,
                    'total_consultations' => $totalConsultations,
                ],
                'rendezvous_aujourdhui' => $rendezVousAujourdhui,
                'prochains_rendezvous' => $prochainsRendezVous,
            ];

            return ApiResponse::success($dashboard, 200, 'Dashboard médecin récupéré');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    public function getMesRendezVous()
    {
        try {
            // Récupérer l'utilisateur connecté
            $user = Auth::user();

            if (!$user) {
                return ApiResponse::error('Non authentifié', 401);
            }

            // Récupérer le médecin associé à l'utilisateur
            $medecin = Medecin::where('user_id', $user->id)->first();

            if (!$medecin) {
                return ApiResponse::error('Médecin introuvable', 404);
            }

            // Récupérer tous les rendez-vous du médecin
            $rendezVous = RendezVous::with([
                'patient.user',
                'consultation'
            ])
                ->where('medecin_id', $medecin->id)
                ->orderBy('date_rendezvous', 'desc')
                ->orderBy('heure_rendezvous', 'desc')
                ->get();

            // Statistiques des rendez-vous
            $statistiquesRendezVous = [
                'total_rendezvous' => $rendezVous->count(),
                'en_attente' => $rendezVous->where('statut', 'en_attente')->count(),
                'confirmes' => $rendezVous->where('statut', 'confirme')->count(),
                'termines' => $rendezVous->where('statut', 'termine')->count(),
                'annules' => $rendezVous->where('statut', 'annule')->count(),
                'aujourdhui' => $rendezVous->filter(function($rdv) {
                    return $rdv->date_rendezvous == now()->toDateString();
                })->count(),
            ];

            return ApiResponse::success([
                'rendezvous' => $rendezVous,
                'statistiques' => $statistiquesRendezVous
            ], 200, 'Rendez-vous récupérés avec succès');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }


    public function getMesPatients()
    {
        try {
            // Récupérer l'utilisateur connecté
            $user = Auth::user();

            if (!$user) {
                return ApiResponse::error('Non authentifié', 401);
            }

            // Récupérer le médecin associé à l'utilisateur
            $medecin = Medecin::where('user_id', $user->id)->first();

            if (!$medecin) {
                return ApiResponse::error('Médecin introuvable', 404);
            }

            // Récupérer tous les patients uniques qui ont eu des rendez-vous avec ce médecin
            $patients = Patient::with(['user', 'dossierMedical'])
                ->whereHas('rendezVous', function($query) use ($medecin) {
                    $query->where('medecin_id', $medecin->id);
                })
                ->withCount(['rendezVous' => function($query) use ($medecin) {
                    $query->where('medecin_id', $medecin->id);
                }])
                ->get();

            // Pour chaque patient, récupérer la dernière consultation
            $patients->each(function($patient) use ($medecin) {
                $patient->derniere_consultation = Consultation::with('rendezvous')
                    ->whereHas('rendezvous', function($query) use ($patient, $medecin) {
                        $query->where('patient_id', $patient->id)
                            ->where('medecin_id', $medecin->id);
                    })
                    ->orderBy('date_consultation', 'desc')
                    ->first();
            });

            return ApiResponse::success([
                'patients' => PatientResource::collection($patients),
                'total_patients' => $patients->count()
            ], 200, 'Patients récupérés avec succès');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }


    public function getMesConsultations()
    {
        try {
            // Récupérer l'utilisateur connecté
            $user = Auth::user();

            if (!$user) {
                return ApiResponse::error('Non authentifié', 401);
            }

            // Récupérer le médecin associé à l'utilisateur
            $medecin = Medecin::where('user_id', $user->id)->first();

            if (!$medecin) {
                return ApiResponse::error('Médecin introuvable', 404);
            }

            // Récupérer toutes les consultations du médecin
            $consultations = Consultation::with([
                'rendezvous.patient.user',
                'compteRendu',
                'paiement'
            ])
                ->whereHas('rendezvous', function($query) use ($medecin) {
                    $query->where('medecin_id', $medecin->id);
                })
                ->orderBy('date_consultation', 'desc')
                ->get();

            // Statistiques des consultations
            $statistiquesConsultations = [
                'total_consultations' => $consultations->count(),
                'consultations_ce_mois' => $consultations->filter(function($consultation) {
                    return $consultation->date_consultation >= now()->startOfMonth();
                })->count(),
                'consultations_cette_annee' => $consultations->filter(function($consultation) {
                    return $consultation->date_consultation >= now()->startOfYear();
                })->count(),
                'avec_compte_rendu' => $consultations->whereNotNull('compteRendu')->count(),
                'avec_paiement' => $consultations->whereNotNull('paiement')->count(),
            ];

            return ApiResponse::success([
                'consultations' => $consultations,
                'statistiques' => $statistiquesConsultations
            ], 200, 'Consultations récupérées avec succès');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    public function getDossiersMedicaux()
    {
        try {
            // Récupérer l'utilisateur connecté
            $user = Auth::user();

            if (!$user) {
                return ApiResponse::error('Non authentifié', 401);
            }

            // Récupérer le médecin associé à l'utilisateur
            $medecin = Medecin::where('user_id', $user->id)->first();

            if (!$medecin) {
                return ApiResponse::error('Médecin introuvable', 404);
            }

            // Récupérer les dossiers médicaux des patients qui ont consulté ce médecin
            $dossiersMedicaux = DossierMedical::with(['patient.user'])
                ->whereHas('patient.rendezVous', function($query) use ($medecin) {
                    $query->where('medecin_id', $medecin->id);
                })
                ->get();

            // Pour chaque dossier, ajouter des infos supplémentaires
            $dossiersMedicaux->each(function($dossier) use ($medecin) {
                // Nombre de consultations avec ce médecin
                $dossier->nombre_consultations_medecin = Consultation::whereHas('rendezvous', function($query) use ($dossier, $medecin) {
                    $query->where('patient_id', $dossier->patient_id)
                        ->where('medecin_id', $medecin->id);
                })->count();

                // Dernière consultation avec ce médecin
                $dossier->derniere_consultation_medecin = Consultation::with(['rendezvous', 'compteRendu'])
                    ->whereHas('rendezvous', function($query) use ($dossier, $medecin) {
                        $query->where('patient_id', $dossier->patient_id)
                            ->where('medecin_id', $medecin->id);
                    })
                    ->orderBy('date_consultation', 'desc')
                    ->first();
            });

            return ApiResponse::success([
                'dossiers_medicaux' => $dossiersMedicaux,
                'total_dossiers' => $dossiersMedicaux->count()
            ], 200, 'Dossiers médicaux récupérés avec succès');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    public function getCompteRenduPatients()
    {
        try {
            // Récupérer l'utilisateur connecté
            $user = Auth::user();

            if (!$user) {
                return ApiResponse::error('Non authentifié', 401);
            }

            // Récupérer le médecin associé à l'utilisateur
            $medecin = Medecin::where('user_id', $user->id)->first();

            if (!$medecin) {
                return ApiResponse::error('Médecin introuvable', 404);
            }

            // Récupérer tous les comptes rendus des consultations du médecin
            $comptesRendus = CompteRendu::with([
                'consultation.rendezvous.patient.user',
                'consultation.rendezvous.medecin.user'
            ])
                ->whereHas('consultation.rendezvous', function($query) use ($medecin) {
                    $query->where('medecin_id', $medecin->id);
                })
                ->orderBy('date_creation', 'desc')
                ->get();

            // Enrichir chaque compte rendu avec des informations supplémentaires
            $comptesRendus->each(function($compteRendu) {
                // Informations patient
                $patient = $compteRendu->consultation->rendezvous->patient ?? null;
                if ($patient) {
                    $compteRendu->patient_info = [
                        'id' => $patient->id,
                        'numero_patient' => $patient->numero_patient,
                        'nom_complet' => $patient->user->nom . ' ' . $patient->user->prenom,
                        'telephone' => $patient->user->telephone,
                        'email' => $patient->user->email,
                    ];
                }

                // Informations consultation
                $consultation = $compteRendu->consultation;
                if ($consultation) {
                    $compteRendu->consultation_info = [
                        'id' => $consultation->id,
                        'date_consultation' => $consultation->date_consultation,
                        'heure_consultation' => $consultation->heure_consultation,
                        'motif' => $consultation->motif ?? 'Non spécifié',
                    ];
                }
            });

            // Statistiques des comptes rendus
            $statistiquesComptesRendus = [
                'total_comptes_rendus' => $comptesRendus->count(),
                'ce_mois' => $comptesRendus->filter(function($cr) {
                    return $cr->date_creation >= now()->startOfMonth();
                })->count(),
                'cette_semaine' => $comptesRendus->filter(function($cr) {
                    return $cr->date_creation >= now()->startOfWeek();
                })->count(),
                'aujourd_hui' => $comptesRendus->filter(function($cr) {
                    return $cr->date_creation >= now()->startOfDay();
                })->count(),
                'par_mois' => $comptesRendus->groupBy(function($cr) {
                    return $cr->date_creation->format('Y-m');
                })->map(function($group) {
                    return $group->count();
                }),
            ];

            return ApiResponse::success([
                'comptes_rendus' => $comptesRendus,
                'statistiques' => $statistiquesComptesRendus
            ], 200, 'Comptes rendus récupérés avec succès');

        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }
}
