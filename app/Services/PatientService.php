<?php

namespace App\Services;

use App\Helpers\ApiResponse;
use App\Http\Resources\PatientResource;
use App\Models\CompteRendu;
use App\Models\Consultation;
use App\Models\DossierMedical;
use App\Models\Paiement;
use App\Models\Patient;
use App\Models\Rendezvous;
use Illuminate\Support\Facades\Auth;

class PatientService
{
    /**
     * Récupérer tous les patients
     */
    public function index()
    {
        try {
            $patients = Patient::with('user')->get();
            return ApiResponse::success(PatientResource::collection($patients), 200, 'Liste des patients récupérée');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    /**
     * Récupérer un patient par ID
     */
    public function show(string $id)
    {
        try {
            $patient = Patient::with('user')->find($id);
            if (!$patient) {
                return ApiResponse::error('Patient introuvable', 404);
            }
            return ApiResponse::success(new PatientResource($patient), 200, 'Patient trouvé');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    /**
     * Mettre à jour un patient
     */
    public function update(array $request, string $id)
    {
        try {
            $patient = Patient::find($id);
            if (!$patient) {
                return ApiResponse::error('Patient introuvable', 404);
            }

            $user = $patient->user;
            if (!$user) {
                return ApiResponse::error('Utilisateur associé introuvable', 404);
            }

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

            return ApiResponse::success(new PatientResource($patient->load('user')), 200, 'Patient mis à jour avec succès');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    /**
     * Supprimer un patient
     */
    public function destroy(int $id)
    {
        try {
            $patient = Patient::find($id);
            if (!$patient) {
                return ApiResponse::error('Patient introuvable', 404);
            }

            $user = $patient->user;
            $patient->delete();

            if ($user) {
                $user->delete();
            }

            return ApiResponse::success([], 200, 'Patient et utilisateur supprimés avec succès');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    /**
     * Rechercher des patients
     */
    public function search(string $query)
    {
        try {
            $patients = Patient::with('user')
                ->where('numero_patient', 'LIKE', "%{$query}%")
                ->orWhereHas('user', function($q) use ($query) {
                    $q->where('nom', 'LIKE', "%{$query}%")
                        ->orWhere('prenom', 'LIKE', "%{$query}%")
                        ->orWhere('email', 'LIKE', "%{$query}%");
                })
                ->get();

            return ApiResponse::success(PatientResource::collection($patients), 200, 'Résultats de recherche');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    /**
     * Récupérer le dashboard d'un patient spécifique
     */
    public function getDashboard(string $patientId)
    {
        try {
            $patient = Patient::with('user')->find($patientId);

            if (!$patient) {
                return ApiResponse::error('Patient introuvable', 404);
            }

            $totalRendezVous = Rendezvous::where('patient_id', $patientId)->count();
            $totalConsultations = Consultation::whereHas('rendezvous', function($query) use ($patientId) {
                $query->where('patient_id', $patientId);
            })->count();

            // Compter les ordonnances
            $totalOrdonnances = Consultation::whereHas('rendezvous', function($query) use ($patientId) {
                $query->where('patient_id', $patientId);
            })->whereNotNull('ordonnance')->count();

            // Compter les paiements
            $totalPaiements = Paiement::whereHas('consultation.rendezvous', function($query) use ($patientId) {
                $query->where('patient_id', $patientId);
            })->count();

            $prochainRendezVous = Rendezvous::with(['medecin.user', 'medecin.specialite'])
                ->where('patient_id', $patientId)
                ->where('date_rendezvous', '>=', now())
                ->whereIn('statut', ['en_attente', 'confirme'])
                ->orderBy('date_rendezvous')
                ->orderBy('heure_rendezvous')
                ->first();

            // Formatage du prochain rendez-vous pour le frontend
            $prochainRdvFormatted = null;
            if ($prochainRendezVous) {
                $prochainRdvFormatted = [
                    'id' => $prochainRendezVous->id,
                    'date' => $prochainRendezVous->date_rendezvous,
                    'heure' => $prochainRendezVous->heure_rendezvous,
                    'medecin' => $prochainRendezVous->medecin->user->nom . ' ' . $prochainRendezVous->medecin->user->prenom,
                    'specialite' => $prochainRendezVous->medecin->specialite->nom ?? 'Non spécifiée',
                    'statut' => $prochainRendezVous->statut
                ];
            }

            $dashboard = [
                'patient' => new PatientResource($patient),
                'statistiques' => [
                    'mes_rendez_vous' => $totalRendezVous,
                    'mes_consultations' => $totalConsultations,
                    'ordonnances' => $totalOrdonnances,
                    'mes_paiements' => $totalPaiements,
                ],
                'prochain_rendez_vous' => $prochainRdvFormatted,
            ];

            return ApiResponse::success($dashboard, 200, 'Dashboard patient récupéré');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    /**
     * Récupérer tous les rendez-vous du patient connecté
     */
    public function getMesRendezVous()
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return ApiResponse::error('Non authentifié', 401);
            }

            $patient = $user->patient;

            if (!$patient) {
                return ApiResponse::error('Patient introuvable', 404);
            }

            // Récupérer tous les rendez-vous du patient
            $rendezVous = Rendezvous::with([
                'medecin.user',
                'medecin.specialite',
                'consultation'
            ])
                ->where('patient_id', $patient->id)
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
     * Récupérer tous les paiements du patient connecté
     */
    public function getMesPaiements()
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return ApiResponse::error('Non authentifié', 401);
            }

            $patient = $user->patient;

            if (!$patient) {
                return ApiResponse::error('Patient introuvable', 404);
            }

            // Récupérer tous les paiements du patient
            $paiements = Paiement::with([
                'consultation.rendezvous.medecin.user',
                'consultation.rendezvous.medecin.specialite'
            ])
                ->whereHas('consultation.rendezvous', function($query) use ($patient) {
                    $query->where('patient_id', $patient->id);
                })
                ->orderBy('date_paiement', 'desc')
                ->get();

            // Statistiques des paiements
            $statistiquesPaiements = [
                'total_paiements' => $paiements->count(),
                'montant_total' => $paiements->where('statut', 'valide')->sum('montant'),
                'montant_en_attente' => $paiements->where('statut', 'en_attente')->sum('montant'),
                'paiements_valides' => $paiements->where('statut', 'valide')->count(),
                'paiements_en_attente' => $paiements->where('statut', 'en_attente')->count(),
                'paiements_annules' => $paiements->where('statut', 'annule')->count(),
                'par_mois' => $paiements->where('statut', 'valide')->groupBy(function($paiement) {
                    return $paiement->date_paiement->format('Y-m');
                })->map(function($group) {
                    return [
                        'count' => $group->count(),
                        'montant' => $group->sum('montant')
                    ];
                }),
            ];

            return ApiResponse::success([
                'paiements' => $paiements,
                'statistiques' => $statistiquesPaiements
            ], 200, 'Paiements récupérés avec succès');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    /**
     * Récupérer toutes les consultations du patient connecté
     */
    public function getMesConsultations()
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return ApiResponse::error('Non authentifié', 401);
            }

            $patient = $user->patient;

            if (!$patient) {
                return ApiResponse::error('Patient introuvable', 404);
            }

            // Récupérer toutes les consultations du patient
            $consultations = Consultation::with([
                'rendezvous.medecin.user',
                'rendezvous.medecin.specialite',
                'compteRendu',
                'paiement'
            ])
                ->whereHas('rendezvous', function($query) use ($patient) {
                    $query->where('patient_id', $patient->id);
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
     * Récupérer le dossier médical du patient connecté
     */
    public function getMonDossierMedical()
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return ApiResponse::error('Non authentifié', 401);
            }

            $patient = $user->patient;

            if (!$patient) {
                return ApiResponse::error('Patient introuvable', 404);
            }

            // Récupérer le dossier médical
            $dossierMedical = DossierMedical::where('patient_id', $patient->id)->first();

            if (!$dossierMedical) {
                return ApiResponse::error('Dossier médical introuvable', 404);
            }

            // Enrichir avec des informations supplémentaires
            $nombreConsultations = Consultation::whereHas('rendezvous', function($query) use ($patient) {
                $query->where('patient_id', $patient->id);
            })->count();

            $derniereConsultation = Consultation::with([
                'rendezvous.medecin.user',
                'rendezvous.medecin.specialite',
                'compteRendu'
            ])
                ->whereHas('rendezvous', function($query) use ($patient) {
                    $query->where('patient_id', $patient->id);
                })
                ->orderBy('date_consultation', 'desc')
                ->first();

            $dossierComplet = [
                'dossier_medical' => $dossierMedical,
                'patient' => new PatientResource($patient->load('user')),
                'nombre_consultations' => $nombreConsultations,
                'derniere_consultation' => $derniereConsultation,
            ];

            return ApiResponse::success($dossierComplet, 200, 'Dossier médical récupéré avec succès');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    /**
     * Récupérer tous les comptes rendus accessibles au patient connecté
     */
    public function getMesComptesRendus()
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return ApiResponse::error('Non authentifié', 401);
            }

            $patient = $user->patient;

            if (!$patient) {
                return ApiResponse::error('Patient introuvable', 404);
            }

            // Récupérer tous les comptes rendus
            $comptesRendus = CompteRendu::with([
                'consultation.rendezvous.medecin.user',
                'consultation.rendezvous.medecin.specialite',
                'consultation.rendezvous.patient.user'
            ])
                ->whereHas('consultation.rendezvous', function($query) use ($patient) {
                    $query->where('patient_id', $patient->id);
                })
                ->orderBy('date_creation', 'desc')
                ->get();

            // Enrichir avec des informations supplémentaires
            $comptesRendus->each(function($compteRendu) {
                $medecin = $compteRendu->consultation->rendezvous->medecin ?? null;
                if ($medecin) {
                    $compteRendu->medecin_info = [
                        'id' => $medecin->id,
                        'nom_complet' => $medecin->user->nom . ' ' . $medecin->user->prenom,
                        'specialite' => $medecin->specialite->nom ?? 'Non spécifiée',
                        'telephone' => $medecin->user->telephone,
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
     * Obtenir le profil complet du patient connecté
     */
    public function getMonProfil()
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return ApiResponse::error('Non authentifié', 401);
            }

            $patient = $user->patient()->with(['user', 'dossierMedical'])->first();

            if (!$patient) {
                return ApiResponse::error('Patient introuvable', 404);
            }

            // Statistiques globales
            $statistiques = [
                'total_rendezvous' => Rendezvous::where('patient_id', $patient->id)->count(),

                'total_consultations' => Consultation::whereHas('rendezvous', function($query) use ($patient) {
                    $query->where('patient_id', $patient->id);
                })->count(),

                'total_paiements' => Paiement::whereHas('consultation.rendezvous', function($query) use ($patient) {
                    $query->where('patient_id', $patient->id);
                })->count(),

                'total_comptes_rendus' => CompteRendu::whereHas('consultation.rendezvous', function($query) use ($patient) {
                    $query->where('patient_id', $patient->id);
                })->count(),

                'montant_total_paye' => Paiement::whereHas('consultation.rendezvous', function($query) use ($patient) {
                    $query->where('patient_id', $patient->id);
                })->where('statut', 'valide')->sum('montant'),
            ];

            return ApiResponse::success([
                'patient' => $patient,
                'statistiques' => $statistiques
            ], 200, 'Profil récupéré avec succès');

        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }


}
