<?php

namespace App\Services;

use App\Helpers\ApiResponse;
use App\Http\Resources\UserResource;
use App\Models\Medecin;
use App\Models\Patient;
use App\Models\Secretaire;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthService
{
    public function register(array $request)
    {
        try {
            // Utiliser une transaction pour garantir la cohérence des données
            DB::beginTransaction();

            // Création du User
            $user = User::create([
                'nom'       => $request['nom'],
                'prenom'    => $request['prenom'],
                'adresse'   => $request['adresse'] ?? null,
                'telephone' => $request['telephone'] ?? null,
                'email'     => $request['email'],
                'password'  => bcrypt($request['password']),
                'role'      => $request['role'],
            ]);

            // Créer l'entrée correspondante selon le rôle
            $additionalData = null;

            switch ($request['role']) {
                case 'medecin':
                    // Vérifier que specialite_id est fourni
                    if (!isset($request['specialite_id'])) {
                        throw new \Exception('La spécialité est obligatoire pour un médecin');
                    }

                    $additionalData = Medecin::create([
                        'numero_medecin' => $this->generateNumeroMedecin(),
                        'disponible'     => $request['disponible'] ?? true,
                        'user_id'        => $user->id,
                        'specialite_id'  => $request['specialite_id'],
                    ]);

                    Log::info('Médecin créé: ' . $additionalData->numero_medecin);
                    break;

                case 'patient':
                    $additionalData = Patient::create([
                        'numero_patient' => $this->generateNumeroPatient(),
                        'user_id'        => $user->id,
                    ]);

                    Log::info('Patient créé: ' . $additionalData->numero_patient);
                    break;

                case 'secretaire':
                    $additionalData = Secretaire::create([
                        'numero_employe' => $this->generateNumeroEmploye(),
                        'user_id'        => $user->id,
                    ]);

                    Log::info('Secrétaire créé: ' . $additionalData->numero_employe);
                    break;

                // Lea rôle admin n'a pas de table spécifique
                case 'admin':
                    Log::info('Admin créé - pas de table additionnelle');
                    break;

                default:
                    throw new \Exception('Rôle invalide: ' . $request['role']);
            }

            // Génération du token JWT
            $token = JWTAuth::fromUser($user);

            // Valider la transaction
            DB::commit();

            return ApiResponse::success([
                'access_token'    => $token,
                'token_type'      => 'bearer',
                'expires_in'      => auth('api')->factory()->getTTL() * 60,
                'user'            => $user,
                'additional_data' => $additionalData,
            ], 201, 'Utilisateur créé avec succès');

        } catch (\Throwable $th) {
            // Annuler la transaction en cas d'erreur
            DB::rollBack();
            Log::error('Register error: ' . $th->getMessage());
            Log::error('Stack trace: ' . $th->getTraceAsString());
            return ApiResponse::error($th->getMessage(), 500);
        }
    }

    private function generateNumeroMedecin(): string
    {
        $year = date('Y');
        $attempts = 0;
        $maxAttempts = 100;

        do {
            // Générer un numéro aléatoire entre 1 et 9999
            $numero = 'MED-' . $year . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $attempts++;

            if ($attempts >= $maxAttempts) {
                // Si on a trop d'essais, utiliser un timestamp pour garantir l'unicité
                $numero = 'MED-' . $year . '-' . substr(time(), -4);
            }
        } while (Medecin::where('numero_medecin', $numero)->exists());

        return $numero;
    }

    /**
     * Générer un numéro unique pour un patient
     * Format: PAT-YYYY-XXXX (ex: PAT-2025-0001)
     */
    private function generateNumeroPatient(): string
    {
        $year = date('Y');
        $attempts = 0;
        $maxAttempts = 100;

        do {
            // Générer un numéro aléatoire entre 1 et 9999
            $numero = 'PAT-' . $year . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $attempts++;

            if ($attempts >= $maxAttempts) {
                // Si on a trop d'essais, utiliser un timestamp pour garantir l'unicité
                $numero = 'PAT-' . $year . '-' . substr(time(), -4);
            }
        } while (Patient::where('numero_patient', $numero)->exists());

        return $numero;
    }

    /**
     * Générer un numéro unique pour un secrétaire
     * Format: SEC-YYYY-XXXX (ex: SEC-2025-0001)
     */
    private function generateNumeroEmploye(): string
    {
        $year = date('Y');
        $attempts = 0;
        $maxAttempts = 100;

        do {
            // Générer un numéro aléatoire entre 1 et 9999
            $numero = 'SEC-' . $year . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $attempts++;

            if ($attempts >= $maxAttempts) {
                // Si on a trop d'essais, utiliser un timestamp pour garantir l'unicité
                $numero = 'SEC-' . $year . '-' . substr(time(), -4);
            }
        } while (Secretaire::where('numero_employe', $numero)->exists());

        return $numero;
    }


    public function login(array $request)
    {
        try {
            // ⬅️ Ajout de logs pour debug
            \Log::info('Login attempt for email: ' . $request['email']);

            if (! $token = auth('api')->attempt($request)) {
                \Log::warning('Login failed for email: ' . $request['email']);
                return ApiResponse::error('Identifiants invalides', 401);
            }

            $user = auth('api')->user();

            \Log::info('Login successful for user: ' . $user->id);

            return ApiResponse::success([
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => auth('api')->factory()->getTTL() * 60,
                'user' => $user,
            ], 200, 'Connexion réussie');

        } catch (\Exception $e) {
            \Log::error('Login exception: ' . $e->getMessage());
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    public function logout()
    {
        try {
            // ⬅️ CORRECTION: Utilisez auth('api') au lieu de auth()
            auth('api')->logout();
            return ApiResponse::success([], 200, 'Déconnecté avec succès');
        } catch (\Exception $e) {
            \Log::error('Logout error: ' . $e->getMessage());
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    public function getAuthenticatedUser()
    {
        try {
            // ⬅️ Ajout de logs pour debug
            \Log::info('=== getAuthenticatedUser called ===');
            \Log::info('Authorization header: ' . request()->header('Authorization'));

            $user = auth('api')->user();

            if (!$user) {
                \Log::warning('No authenticated user found');
                return ApiResponse::error('Utilisateur non authentifié', 401);
            }

            \Log::info('User authenticated: ' . $user->id);
            return ApiResponse::success($user, 200, 'Utilisateur connecté récupéré');
        } catch (\Exception $e) {
            \Log::error('getAuthenticatedUser error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    public function getAll()
    {
        try {
            // ⬅️ Vérifiez d'abord si l'utilisateur est authentifié
            $currentUser = auth('api')->user();
            if (!$currentUser) {
                \Log::warning('Unauthorized access to getAll');
                return ApiResponse::error('Non authentifié', 401);
            }

            \Log::info('User ' . $currentUser->id . ' accessing users list');

            $users = User::all();
            return ApiResponse::success(UserResource::collection($users), 200, 'Liste des utilisateurs récupérée');
        } catch (\Exception $e) {
            \Log::error('getAll error: ' . $e->getMessage());
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    public function show($id)
    {
        try {
            // ⬅️ Vérifiez l'authentification
            $currentUser = auth('api')->user();
            if (!$currentUser) {
                return ApiResponse::error('Non authentifié', 401);
            }

            $user = User::find($id);
            if (!$user) {
                return ApiResponse::error('Utilisateur introuvable', 404);
            }
            return ApiResponse::success(new UserResource($user), 200, 'Utilisateur trouvé');
        } catch (\Exception $e) {
            \Log::error('show error: ' . $e->getMessage());
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    public function destroy($id)
    {
        try {
            // ⬅️ Vérifiez l'authentification
            $currentUser = auth('api')->user();
            if (!$currentUser) {
                return ApiResponse::error('Non authentifié', 401);
            }

            $user = User::find($id);
            if (!$user) {
                return ApiResponse::error('Utilisateur introuvable', 404);
            }
            $user->delete();
            return ApiResponse::success([], 200, 'Utilisateur supprimé avec succès');
        } catch (\Exception $e) {
            \Log::error('destroy error: ' . $e->getMessage());
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    public function update(array $data, int $id)
    {
        try {
            // ⬅️ Vérifiez l'authentification
            $currentUser = auth('api')->user();
            if (!$currentUser) {
                return ApiResponse::error('Non authentifié', 401);
            }

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
            \Log::error('update error: ' . $e->getMessage());
            return ApiResponse::error($e->getMessage(), 500);
        }
    }
}
