<?php

namespace App\Services;

use App\Helpers\ApiResponse;
use App\Http\Resources\PatientResource;
use App\Http\Resources\UserResource;
use App\Models\Consultation;
use App\Models\DossierMedical;
use App\Models\Paiement;
use App\Models\Patient;
use App\Models\RendezVous;
use App\Models\User;
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
            return ApiResponse::success(new PatientResource($patient, 200, 'Patient trouvé'));
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

            // Récupérer l'utilisateur associé
            $user = $patient->user;
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


            return ApiResponse::success(new PatientResource($patient->load('user'), 200, 'Patient mis à jour avec succès'));
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

            // Récupérer l'utilisateur
            $user = $patient->user;

            // Supprimer le patient d'abord
            $patient->delete();

            // Supprimer l'utilisateur
            if ($user) {
                $user->delete();
            }

            return ApiResponse::success([], 200, 'Patient et utilisateur supprimés avec succès');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

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

    public function getDashboard(string $patientId)
    {
        try {
            $patient = Patient::with('user')->find($patientId);

            if (!$patient) {
                return ApiResponse::error('Patient introuvable', 404);
            }

            // Statistiques
            $totalRendezVous = RendezVous::where('patient_id', $patientId)->count();
            $rendezVousEnAttente = RendezVous::where('patient_id', $patientId)
                ->where('statut', 'en_attente')
                ->count();
            $rendezVousConfirmes = RendezVous::where('patient_id', $patientId)
                ->where('statut', 'confirme')
                ->count();
            $totalConsultations = Consultation::whereHas('rendezvous', function($query) use ($patientId) {
                $query->where('patient_id', $patientId);
            })->count();

            // Dossier médical
            $dossierMedical = DossierMedical::where('patient_id', $patientId)->first();

            // Prochain rendez-vous
            $prochainRendezVous = RendezVous::with(['medecin.user', 'medecin.specialite'])
                ->where('patient_id', $patientId)
                ->where('date_rendezvous', '>=', now())
                ->whereIn('statut', ['en_attente', 'confirme'])
                ->orderBy('date_rendezvous')
                ->orderBy('heure_rendezvous')
                ->first();

            // Historique des rendez-vous
            $historiqueRendezVous = RendezVous::with(['medecin.user', 'medecin.specialite'])
                ->where('patient_id', $patientId)
                ->orderBy('date_rendezvous', 'desc')
                ->orderBy('heure_rendezvous', 'desc')
                ->limit(10)
                ->get();

            // Dernières consultations
            $dernieresConsultations = Consultation::with([
                'rendezvous.medecin.user',
                'rendezvous.medecin.specialite',
                'compteRendu',
                'paiement'
            ])
                ->whereHas('rendezvous', function($query) use ($patientId) {
                    $query->where('patient_id', $patientId);
                })
                ->orderBy('date_consultation', 'desc')
                ->limit(5)
                ->get();

            $dashboard = [
                'patient' => new PatientResource($patient),
                'statistiques' => [
                    'total_rendezvous' => $totalRendezVous,
                    'rendezvous_en_attente' => $rendezVousEnAttente,
                    'rendezvous_confirmes' => $rendezVousConfirmes,
                    'total_consultations' => $totalConsultations,
                ],
                'dossier_medical' => $dossierMedical,
                'prochain_rendezvous' => $prochainRendezVous,
                'historique_rendezvous' => $historiqueRendezVous,
                'dernieres_consultations' => $dernieresConsultations,
            ];

            return ApiResponse::success($dashboard, 200, 'Dashboard patient récupéré');
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

            // Récupérer le patient associé à l'utilisateur
            $patient = Patient::where('user_id', $user->id)->first();

            if (!$patient) {
                return ApiResponse::error('Patient introuvable', 404);
            }

            // Récupérer tous les rendez-vous du patient
            $rendezVous = RendezVous::with([
                'medecin.user',
                'medecin.specialite',
                'consultation'
            ])
                ->where('patient_id', $patient->id)
                ->orderBy('date_rendezvous', 'desc')
                ->orderBy('heure_rendezvous', 'desc')
                ->get();

            return ApiResponse::success($rendezVous, 200, 'Rendez-vous récupérés avec succès');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    public function getMesPaiements()
    {
        try {
            // Récupérer l'utilisateur connecté
            $user = Auth::user();

            if (!$user) {
                return ApiResponse::error('Non authentifié', 401);
            }

            // Récupérer le patient associé à l'utilisateur
            $patient = Patient::where('user_id', $user->id)->first();

            if (!$patient) {
                return ApiResponse::error('Patient introuvable', 404);
            }

            // Récupérer tous les paiements du patient via les consultations
            $paiements = Paiement::with([
                'consultation.rendezvous.medecin.user',
                'consultation.rendezvous.medecin.specialite'
            ])
                ->whereHas('consultation.rendezvous', function($query) use ($patient) {
                    $query->where('patient_id', $patient->id);
                })
                ->orderBy('date_paiement', 'desc')
                ->get();

            // Calculer le total et les statistiques
            $statistiquesPaiements = [
                'total_paiements' => $paiements->count(),
                'montant_total' => $paiements->where('statut', 'valide')->sum('montant'),
                'montant_en_attente' => $paiements->where('statut', 'en_attente')->sum('montant'),
                'paiements_valides' => $paiements->where('statut', 'valide')->count(),
                'paiements_en_attente' => $paiements->where('statut', 'en_attente')->count(),
                'paiements_annules' => $paiements->where('statut', 'annule')->count(),
            ];

            return ApiResponse::success([
                'paiements' => $paiements,
                'statistiques' => $statistiquesPaiements
            ], 200, 'Paiements récupérés avec succès');
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

            // Récupérer le patient associé à l'utilisateur
            $patient = Patient::where('user_id', $user->id)->first();

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
            ];

            return ApiResponse::success([
                'consultations' => $consultations,
                'statistiques' => $statistiquesConsultations
            ], 200, 'Consultations récupérées avec succès');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    public function getMonDossierMedical()
    {
        try {
            // Récupérer l'utilisateur connecté
            $user = Auth::user();

            if (!$user) {
                return ApiResponse::error('Non authentifié', 401);
            }

            // Récupérer le patient associé à l'utilisateur
            $patient = Patient::where('user_id', $user->id)->first();

            if (!$patient) {
                return ApiResponse::error('Patient introuvable', 404);
            }

            // Récupérer le dossier médical
            $dossierMedical = DossierMedical::where('patient_id', $patient->id)->first();

            if (!$dossierMedical) {
                return ApiResponse::error('Dossier médical introuvable', 404);
            }

            // Enrichir avec des informations supplémentaires
            $dossierComplet = [
                'dossier_medical' => $dossierMedical,
                'patient' => new PatientResource($patient->load('user')),
                'nombre_consultations' => Consultation::whereHas('rendezvous', function($query) use ($patient) {
                    $query->where('patient_id', $patient->id);
                })->count(),
                'derniere_consultation' => Consultation::with([
                    'rendezvous.medecin.user',
                    'compteRendu'
                ])
                    ->whereHas('rendezvous', function($query) use ($patient) {
                        $query->where('patient_id', $patient->id);
                    })
                    ->orderBy('date_consultation', 'desc')
                    ->first(),
            ];

            return ApiResponse::success($dossierComplet, 200, 'Dossier médical récupéré avec succès');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

}
