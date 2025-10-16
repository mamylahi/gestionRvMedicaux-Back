<?php

namespace App\Services;

use App\Helpers\ApiResponse;
use App\Http\Resources\SecretaireResource;
use App\Http\Resources\UserResource;
use App\Models\Secretaire;

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
     * Créer une secrétaire avec son utilisateur
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
                'role'      => 'secretaire',
            ]);

            //Générer le numéro employé
            $lastSecretaire = Secretaire::latest('id')->first();
            $nextId = $lastSecretaire ? $lastSecretaire->id + 1 : 1;
            $numeroEmploye = 'EMP-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);

            //Créer la secrétaire
            $secretaire = Secretaire::create([
                'user_id'        => $user->id,
                'numero_employe' => $numeroEmploye,
            ]);

            return ApiResponse::success(new SecretaireResource($secretaire->load('user'), 201, 'Secrétaire créée avec succès'));
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
}
