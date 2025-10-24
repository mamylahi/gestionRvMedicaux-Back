<?php

namespace App\Services;

use App\Models\RendezVous;

class RendezVousService
{
    public function index()
    {
        $rendezVous = RendezVous::all();
        return $rendezVous;
    }


    public function store(array $request)
    {

        $rendezVous = RendezVous::create($request);
        return $rendezVous;
    }


    public function show(string $id)
    {
        return RendezVous::find($id);
    }


    public function update(array $request, string $id)
    {
        $rendezVous = $this->show($id);
        $rendezVous->update($request);
        return $rendezVous;
    }


    public function destroy(int $id)
    {
        RendezVous::destroy($id);
    }

    public function getByPatient(string $patientId)
    {
        return RendezVous::with(['medecin.user', 'medecin.specialite', 'consultation'])
            ->where('patient_id', $patientId)
            ->orderBy('date_rendezvous', 'desc')
            ->orderBy('heure_rendezvous', 'desc')
            ->get();
    }

    public function getByMedecin(string $medecinId)
    {
        return RendezVous::with(['patient.user', 'consultation'])
            ->where('medecin_id', $medecinId)
            ->orderBy('date_rendezvous', 'desc')
            ->orderBy('heure_rendezvous', 'desc')
            ->get();
    }

    public function getByDate(string $date)
    {
        return RendezVous::with(['patient.user', 'medecin.user', 'medecin.specialite'])
            ->whereDate('date_rendezvous', $date)
            ->orderBy('heure_rendezvous')
            ->get();
    }

    public function updateStatut(string $id, string $statut)
    {
        $rendezVous = RendezVous::find($id);

        if (!$rendezVous) {
            throw new \Exception('Rendez-vous introuvable');
        }

        $rendezVous->statut = $statut;
        $rendezVous->save();

        return $rendezVous->load(['patient.user', 'medecin.user', 'medecin.specialite']);
    }
}
