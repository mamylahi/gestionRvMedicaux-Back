<?php

namespace App\Services;

use App\Helpers\ApiResponse;
use App\Http\Resources\PatientResource;
use App\Http\Resources\UserResource;
use App\Models\Patient;
use App\Models\User;

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
     * Créer un patient avec son utilisateur
     */
    public function store(array $request)
    {
        try {
            // Créer l'utilisateur d'abord
            $user = User::create([
                'nom'       => $request['nom'],
                'prenom'    => $request['prenom'],
                'adresse'   => $request['adresse'] ?? null,
                'telephone' => $request['telephone'] ?? null,
                'email'     => $request['email'],
                'password'  => bcrypt($request['password']),
                'role'      => 'patient',
            ]);

            // Générer le numéro patient
            $lastPatient = Patient::latest('id')->first();
            $nextId = $lastPatient ? $lastPatient->id + 1 : 1;
            $numeroPatient = 'PAT-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);

            //Créer le patient avec l'ID de l'utilisateur
            $patient = Patient::create([
                'user_id'        => $user->id,
                'numero_patient' => $numeroPatient,
            ]);

            return ApiResponse::success(new PatientResource($patient->load('user'), 201, 'Patient créé avec succès'));
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
}
