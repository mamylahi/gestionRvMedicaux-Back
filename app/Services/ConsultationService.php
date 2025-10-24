<?php

namespace App\Services;

use App\Models\Consultation;

class ConsultationService
{
    public function index()
    {
        $consultation = Consultation::all();
        return $consultation;
    }


    public function store(array $request)
    {

        $consultation = Consultation::create($request);
        return $consultation;
    }


    public function show(string $id)
    {
        return Consultation::find($id);
    }


    public function update(array $request, string $id)
    {
        $consultation = $this->show($id);
        $consultation->update($request);
        return $consultation;
    }


    public function destroy(int $id)
    {
        Consultation::destroy($id);
    }


    public function getByRendezVous(string $rendezVousId)
    {
        return Consultation::with([
            'rendezvous.patient.user',
            'rendezvous.medecin.user',
            'rendezvous.medecin.specialite',
            'compteRendu',
            'paiement'
        ])
            ->where('rendezvous_id', $rendezVousId)
            ->first();
    }


    public function getByMedecin(string $medecinId)
    {
        return Consultation::with([
            'rendezvous.patient.user',
            'compteRendu',
            'paiement'
        ])
            ->whereHas('rendezvous', function($query) use ($medecinId) {
                $query->where('medecin_id', $medecinId);
            })
            ->orderBy('date_consultation', 'desc')
            ->get();
    }


    public function getByPatient(string $patientId)
    {
        return Consultation::with([
            'rendezvous.medecin.user',
            'rendezvous.medecin.specialite',
            'compteRendu',
            'paiement'
        ])
            ->whereHas('rendezvous', function($query) use ($patientId) {
                $query->where('patient_id', $patientId);
            })
            ->orderBy('date_consultation', 'desc')
            ->get();
    }
}
