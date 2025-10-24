<?php

namespace App\Services;

use App\Helpers\ApiResponse;
use App\Http\Resources\MedecinResource;
use App\Http\Resources\PatientResource;
use App\Http\Resources\UserResource;
use App\Models\Consultation;
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

}
