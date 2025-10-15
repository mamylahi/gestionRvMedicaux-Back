<?php

namespace App\Services;

use App\Helpers\ApiResponse;
use App\Http\Resources\UserResource;
use App\Models\Medecin;
use App\Models\Patient;
use App\Models\Secretaire;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthService
{
    public function register(array $request)
    {
        // Création du User
        try {
            $user = User::create([
                'nom'       => $request['nom'],
                'prenom'    => $request['prenom'],
                'adresse'   => $request['adresse'],
                'telephone' => $request['telephone'],
                'email'     => $request['email'],
                'password'  => bcrypt($request['password']),
                'role'      => $request['role'],
            ]);

            // 🔹 Si c'est une secrétaire, on ajoute dans la table "secretaire"
//            switch ($request['role']) {
//                case 'secretaire':
//                    $lastSecretaire = Secretaire::latest('id')->first();
//                    $nextId = $lastSecretaire ? $lastSecretaire->id + 1 : 1;
//                    $numeroEmploye = 'EMP-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);
//
//                    Secretaire::create([
//                        'user_id'        => $user->id,
//                        'numero_employe' => $numeroEmploye,
//                    ]);
//                    break;
//
//                case 'medecin':
//                    $lastMedecin = Medecin::latest('id')->first();
//                    $nextId = $lastMedecin ? $lastMedecin->id + 1 : 1;
//                    $numeroMedecin = 'MED-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);
//
//                    Medecin::create([
//                        'user_id'       => $user->id,
//                        'numero_medecin'=> $numeroMedecin,
//                        // tu peux ajouter d’autres colonnes spécifiques (spécialité, etc.)
//                    ]);
//                    break;
//
//                case 'patient':
//                    $lastPatient = Patient::latest('id')->first();
//                    $nextId = $lastPatient ? $lastPatient->id + 1 : 1;
//                    $numeroPatient = 'PAT-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);
//
//                    Patient::create([
//                        'user_id'       => $user->id,
//                        'numero_patient'=> $numeroPatient,
//                        // tu peux ajouter d’autres colonnes spécifiques (groupe sanguin, etc.)
//                    ]);
//                    break;
//            }

            // Génération du token JWT
            $token = JWTAuth::fromUser($user);

            return ApiResponse::success([
                'access_token' => $token,
                'token_type'   => 'bearer',
                'expires_in'   => auth('api')->factory()->getTTL() * 60,
                'user'         => $user,
            ], 200, 'Utilisateur créé avec succès');
        } catch (\Throwable $th) {
            return ApiResponse::error($th->getMessage(), 500);
        }
    }


    public function login(array $request)
    {
        try {
            if (! $token = auth('api')->attempt($request)) {
                return ApiResponse::error('Identifiants invalides', 401);
            }
            return ApiResponse::success([
                'access_token' => $token,
                'token_type'   => 'bearer',
                'expires_in'   => auth('api')->factory()->getTTL() * 60,
                'user'         => auth('api')->user(),
            ], 200, 'Connexion réussie');

        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    public function logout()
    {
        try {
            auth()->logout();
            return ApiResponse::success([], 200, 'Déconnecté avec succès');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }
    public function getAuthenticatedUser()
    {
        try {
            $user = auth('api')->user();
            if (!$user) {
                return ApiResponse::error('Utilisateur non authentifié', 401);
            }
            return ApiResponse::success($user, 200, 'Utilisateur connecté récupéré');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    public function getAll()
    {
        try {
            $users = User::all();
            return ApiResponse::success(UserResource::collection($users), 200, 'Liste des utilisateurs récupérée');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    public function show($id)
    {
        try {
            $user = User::find($id);
            if (!$user) {
                return ApiResponse::error('Utilisateur introuvable', 404);
            }
            return ApiResponse::success(new UserResource($user), 200, 'Utilisateur trouvé');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    /**
     * Supprimer un utilisateur
     */
    public function destroy($id)
    {
        try {
            $user = User::find($id);
            if (!$user) {
                return ApiResponse::error('Utilisateur introuvable', 404);
            }
            $user->delete();
            return ApiResponse::success([], 200, 'Utilisateur supprimé avec succès');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    public function update(array $data, int $id)
    {
        try {
            $user = User::find($id);
            if (!$user) {
                return ApiResponse::error('Utilisateur introuvable', 404);
            }

            $user->fill([
                'nom'       => $data['nom'] ?? $user->nom,
                'prenom'    => $data['prenom'] ?? $user->prenom,
                'adresse'   => $data['adresse'] ?? $user->adresse,
                'telephone' => $data['telephone'] ?? $user->telephone,
                'email'     => $data['email'] ?? $user->email,
                'role'      => $data['role'] ?? $user->role,
            ]);

            if (!empty($data['password'])) {
                $user->password = bcrypt($data['password']);
            }

            $user->save();

            return ApiResponse::success($user, 200, 'Utilisateur mis à jour avec succès');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }
}
