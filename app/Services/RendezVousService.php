<?php

namespace App\Services;

use App\Models\Patient;
use App\Models\Rendezvous;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RendezVousService
{
    public function index()
    {
        $rendezVous = Rendezvous::all();
        return $rendezVous;
    }


    public function store(array $data)
    {
        try {
            $user = Auth::user();

            Log::info('Création rendez-vous - Utilisateur', [
                'user_id' => $user->id,
                'user_email' => $user->email
            ]);

            // Récupérer ou créer le patient basé sur user_id
            $patient = Patient::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'nom' => $user->nom,
                    'prenom' => $user->prenom,
                    'email' => $user->email,
                    'telephone' => $user->telephone ?? null,
                    'adresse' => null,
                    'date_naissance' => null,
                ]
            );

            Log::info('Patient trouvé/créé', [
                'patient_id' => $patient->id,
                'user_id' => $user->id
            ]);

            // Remplacer user_id par patient_id dans les données
            $data['patient_id'] = $patient->id;  // ID de la table patients

            // Supprimer user_id si présent dans les données
            unset($data['user_id']);

            // Créer le rendez-vous
            $rendezVous = Rendezvous::create($data);

            Log::info('Rendez-vous créé avec succès', [
                'rendezvous_id' => $rendezVous->id,
                'patient_id' => $patient->id,
                'medecin_id' => $data['medecin_id']
            ]);

            return $rendezVous->load(['patient.user', 'medecin.user', 'medecin.specialite']);

        } catch (\Exception $e) {
            Log::error('Erreur création rendez-vous', [
                'message' => $e->getMessage(),
                'user_id' => Auth::id(),
                'data' => $data
            ]);

            throw $e;
        }
    }


    public function show(string $id)
    {
        return Rendezvous::find($id);
    }


    public function update(array $request, string $id)
    {
        $rendezVous = $this->show($id);
        $rendezVous->update($request);
        return $rendezVous;
    }


    public function destroy(int $id)
    {
        Rendezvous::destroy($id);
    }

    public function getByPatient(string $patientId)
    {
        return Rendezvous::with(['medecin.user', 'medecin.specialite', 'consultation'])
            ->where('patient_id', $patientId)
            ->orderBy('date_rendezvous', 'desc')
            ->orderBy('heure_rendezvous', 'desc')
            ->get();
    }

    public function getByMedecin(string $medecinId)
    {
        return Rendezvous::with(['patient.user', 'consultation'])
            ->where('medecin_id', $medecinId)
            ->orderBy('date_rendezvous', 'desc')
            ->orderBy('heure_rendezvous', 'desc')
            ->get();
    }

    public function getByDate(string $date)
    {
        return Rendezvous::with(['patient.user', 'medecin.user', 'medecin.specialite'])
            ->whereDate('date_rendezvous', $date)
            ->orderBy('heure_rendezvous')
            ->get();
    }

    public function updateStatut(string $id, string $statut)
    {
        $rendezVous = Rendezvous::find($id);

        if (!$rendezVous) {
            throw new \Exception('Rendez-vous introuvable');
        }

        $rendezVous->statut = $statut;
        $rendezVous->save();

        return $rendezVous->load(['patient.user', 'medecin.user', 'medecin.specialite']);
    }
}
