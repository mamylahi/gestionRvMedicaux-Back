<?php

namespace App\Services;

use App\Helpers\ApiResponse;
use App\Http\Resources\MedecinResource;
use App\Http\Resources\PatientResource;
use App\Models\CompteRendu;
use App\Models\Consultation;
use App\Models\DossierMedical;
use App\Models\Medecin;
use App\Models\Patient;
use App\Models\Rendezvous;
use Illuminate\Support\Facades\Auth;

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
            return ApiResponse::success(new MedecinResource($medecin), 200, 'Médecin trouvé');
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

            // Mise à jour des données utilisateur
            $user->update([
                'nom'       => $request['nom'] ?? $user->nom,
                'prenom'    => $request['prenom'] ?? $user->prenom,
                'adresse'   => $request['adresse'] ?? $user->adresse,
                'telephone' => $request['telephone'] ?? $user->telephone,
                'email'     => $request['email'] ?? $user->email,
            ]);

            // Mise à jour du mot de passe si fourni
            if (!empty($request['password'])) {
                $user->password = bcrypt($request['password']);
                $user->save();
            }

            // ✅ CORRECTION : Utiliser specialite_id au lieu de specialite
            $medecin->update([
                'numero_medecin' => $request['numero_medecin'] ?? $medecin->numero_medecin,
                'specialite_id'  => $request['specialite_id'] ?? $medecin->specialite_id,
                'disponible'     => $request['disponible'] ?? $medecin->disponible,
            ]);

            return ApiResponse::success(
                new MedecinResource($medecin->load('user', 'specialite')),
                200,
                'Médecin mis à jour avec succès'
            );
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
            $medecins = Medecin::with(['user', 'specialite'])
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
            $medecins = Medecin::with(['user', 'specialite'])
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
            $medecin = Medecin::with(['user', 'specialite'])->find($medecinId);

            if (!$medecin) {
                return ApiResponse::error('Médecin introuvable', 404);
            }

            $totalRendezVous = Rendezvous::where('medecin_id', $medecinId)->count();
            $rendezVousEnAttente = Rendezvous::where('medecin_id', $medecinId)
                ->where('statut', 'en_attente')
                ->count();
            $rendezVousConfirmes = Rendezvous::where('medecin_id', $medecinId)
                ->where('statut', 'confirme')
                ->count();
            $totalConsultations = Consultation::whereHas('rendezvous', function($query) use ($medecinId) {
                $query->where('medecin_id', $medecinId);
            })->count();

            $today = now()->toDateString();
            $rendezVousAujourdhui = Rendezvous::with(['patient.user'])
                ->where('medecin_id', $medecinId)
                ->whereDate('date_rendezvous', $today)
                ->orderBy('heure_rendezvous')
                ->get();

            $prochainsRendezVous = Rendezvous::with(['patient.user'])
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

    /**
     * Récupérer tous les rendez-vous du médecin connecté
     */
    public function getMesRendezVous()
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return ApiResponse::error('Non authentifié', 401);
            }

            $medecin = $user->medecin;

            if (!$medecin) {
                return ApiResponse::error('Médecin introuvable', 404);
            }

            // Récupérer tous les rendez-vous du médecin
            $rendezVous = Rendezvous::with([
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

    /**
     * Récupérer tous les patients du médecin connecté
     */
    public function getMesPatients()
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return ApiResponse::error('Non authentifié', 401);
            }

            $medecin = $user->medecin;

            if (!$medecin) {
                return ApiResponse::error('Médecin introuvable', 404);
            }

            // Récupérer tous les patients uniques
            $patients = Patient::with(['user', 'dossierMedical'])
                ->whereHas('rendezvous', function($query) use ($medecin) {
                    $query->where('medecin_id', $medecin->id);
                })
                ->withCount(['rendezvous' => function($query) use ($medecin) {
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

    /**
     * Récupérer toutes les consultations du médecin connecté
     */
    public function getMesConsultations()
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return ApiResponse::error('Non authentifié', 401);
            }

            $medecin = $user->medecin;

            if (!$medecin) {
                return ApiResponse::error('Médecin introuvable', 404);
            }

            // Récupérer toutes les consultations
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

            // Statistiques
            $statistiquesConsultations = [
                'total_consultations' => $consultations->count(),
                'consultations_ce_mois' => $consultations->filter(function($consultation) {
                    return $consultation->date_consultation >= now()->startOfMonth();
                })->count(),
                'consultations_cette_annee' => $consultations->filter(function($consultation) {
                    return $consultation->date_consultation >= now()->startOfYear();
                })->count(),
                'avec_compte_rendu' => $consultations->filter(function($consultation) {
                    return $consultation->compteRendu !== null;
                })->count(),
                'avec_paiement' => $consultations->filter(function($consultation) {
                    return $consultation->paiement !== null;
                })->count(),
            ];

            return ApiResponse::success([
                'consultations' => $consultations,
                'statistiques' => $statistiquesConsultations
            ], 200, 'Consultations récupérées avec succès');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    /**
     * Récupérer tous les dossiers médicaux accessibles au médecin connecté
     */
    public function getMesDossiersMedicaux()
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return ApiResponse::error('Non authentifié', 401);
            }

            $medecin = $user->medecin;

            if (!$medecin) {
                return ApiResponse::error('Médecin introuvable', 404);
            }

            // Récupérer les dossiers médicaux
            $dossiersMedicaux = DossierMedical::with(['patient.user'])
                ->whereHas('patient.rendezvous', function($query) use ($medecin) {
                    $query->where('medecin_id', $medecin->id);
                })
                ->get();

            // Enrichir avec des infos supplémentaires
            $dossiersMedicaux->each(function($dossier) use ($medecin) {
                $dossier->nombre_consultations_medecin = Consultation::whereHas('rendezvous', function($query) use ($dossier, $medecin) {
                    $query->where('patient_id', $dossier->patient_id)
                        ->where('medecin_id', $medecin->id);
                })->count();

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

    /**
     * Récupérer tous les comptes rendus du médecin connecté
     */
    public function getMesComptesRendus()
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return ApiResponse::error('Non authentifié', 401);
            }

            $medecin = $user->medecin;

            if (!$medecin) {
                return ApiResponse::error('Médecin introuvable', 404);
            }

            // Récupérer tous les comptes rendus
            $comptesRendus = CompteRendu::with([
                'consultation.rendezvous.patient.user',
                'consultation.rendezvous.medecin.user'
            ])
                ->whereHas('consultation.rendezvous', function($query) use ($medecin) {
                    $query->where('medecin_id', $medecin->id);
                })
                ->orderBy('date_creation', 'desc')
                ->get();

            // Enrichir avec des informations supplémentaires
            $comptesRendus->each(function($compteRendu) {
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

            // Statistiques
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

    /**
     * Obtenir le profil complet du médecin connecté
     */
    public function getMonProfil()
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return ApiResponse::error('Non authentifié', 401);
            }

            $medecin = $user->medecin()->with(['specialite', 'user'])->first();

            if (!$medecin) {
                return ApiResponse::error('Médecin introuvable', 404);
            }

            // Statistiques globales
            $statistiques = [
                'total_patients' => Patient::whereHas('rendezvous', function($query) use ($medecin) {
                    $query->where('medecin_id', $medecin->id);
                })->count(),

                'total_rendezvous' => Rendezvous::where('medecin_id', $medecin->id)->count(),

                'total_consultations' => Consultation::whereHas('rendezvous', function($query) use ($medecin) {
                    $query->where('medecin_id', $medecin->id);
                })->count(),

                'total_comptes_rendus' => CompteRendu::whereHas('consultation.rendezvous', function($query) use ($medecin) {
                    $query->where('medecin_id', $medecin->id);
                })->count(),
            ];

            return ApiResponse::success([
                'medecin' => $medecin,
                'statistiques' => $statistiques
            ], 200, 'Profil récupéré avec succès');

        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }
}
