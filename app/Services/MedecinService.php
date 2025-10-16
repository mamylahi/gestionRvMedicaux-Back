<?php

namespace App\Services;

use App\Helpers\ApiResponse;
use App\Http\Resources\MedecinResource;
use App\Http\Resources\PatientResource;
use App\Http\Resources\UserResource;
use App\Models\Medecin;
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
     * Créer un médecin avec son utilisateur
     */
    public function store(array $request)
    {
        try {
            //Créer l'utilisateur
            $user = User::create([
                'nom'       => $request['nom'],
                'prenom'    => $request['prenom'],
                'adresse'   => $request['adresse'] ?? null,
                'telephone' => $request['telephone'] ?? null,
                'email'     => $request['email'],
                'password'  => bcrypt($request['password']),
                'role'      => 'medecin',
            ]);

            //Générer le numéro médecin
            $lastMedecin = Medecin::latest('id')->first();
            $nextId = $lastMedecin ? $lastMedecin->id + 1 : 1;
            $numeroMedecin = 'MED-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);

            //Créer le médecin
            $medecin = Medecin::create([
                'user_id'        => $user->id,
                'numero_medecin' => $numeroMedecin,
                'disponible' => $disponible = true,
                'departement' =>  $request['departement'] ?? null,
                'specialite'     => $request['specialite'] ?? null,

            ]);

            return ApiResponse::success(new MedecinResource($medecin->load('user'), 201, 'Médecin créé avec succès'));
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
}
