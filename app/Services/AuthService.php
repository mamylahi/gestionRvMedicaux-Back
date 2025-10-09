<?php

namespace App\Services;

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
        switch ($request['role']) {
            case 'secretaire':
                $lastSecretaire = Secretaire::latest('id')->first();
                $nextId = $lastSecretaire ? $lastSecretaire->id + 1 : 1;
                $numeroEmploye = 'EMP-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);

                Secretaire::create([
                    'user_id'        => $user->id,
                    'numero_employe' => $numeroEmploye,
                ]);
                break;

            case 'medecin':
                $lastMedecin = Medecin::latest('id')->first();
                $nextId = $lastMedecin ? $lastMedecin->id + 1 : 1;
                $numeroMedecin = 'MED-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);

                Medecin::create([
                    'user_id'       => $user->id,
                    'numero_medecin'=> $numeroMedecin,
                    // tu peux ajouter d’autres colonnes spécifiques (spécialité, etc.)
                ]);
                break;

            case 'patient':
                $lastPatient = Patient::latest('id')->first();
                $nextId = $lastPatient ? $lastPatient->id + 1 : 1;
                $numeroPatient = 'PAT-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);

                Patient::create([
                    'user_id'       => $user->id,
                    'numero_patient'=> $numeroPatient,
                    // tu peux ajouter d’autres colonnes spécifiques (groupe sanguin, etc.)
                ]);
                break;
        }

        // Génération du token JWT
        $token = JWTAuth::fromUser($user);

        return response()->json([
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => auth('api')->factory()->getTTL() * 60,
            'user'         => $user,
        ]);
    }


    public function login(array $request)
    {
        if (! $token = auth('api')->attempt($request)) {
            return response()->json(['error' => 'Identifiants invalides'], 401);
        }

        return response()->json([
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => auth('api')->factory()->getTTL() * 60,
            'user'         => auth('api')->user(),
        ]);
    }

    public function logout()
    {
        auth()->logout();
        return response()->json(['message' => 'Déconnecté avec succès']);
    }

    public function getAuthenticatedUser()
    {
        return response()->json(auth('api')->user());
    }

    public function getAll()
    {
        return User::all();
    }

    public function show($id)
    {
        return User::findOrFail($id);
    }

    /**
     * Supprimer un utilisateur
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json(['message' => 'Utilisateur supprimé avec succès']);
    }

    public function update(array $data, int $id)
    {
        $user = User::findOrFail($id);


        $user->nom = $data['nom'] ?? $user->nom;
        $user->prenom = $data['prenom'] ?? $user->prenom;
        $user->adresse = $data['adresse'] ?? $user->adresse;
        $user->telephone = $data['telephone'] ?? $user->telephone;
        $user->email = $data['email'] ?? $user->email;
        $user->role = $data['role'] ?? $user->role;

        if (!empty($data['password'])) {
            $user->password = bcrypt($data['password']);
        }

        $user->save();

        return $user;
    }
}
