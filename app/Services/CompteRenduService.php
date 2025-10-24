<?php

namespace App\Services;

use App\Models\CompteRendu;

class CompteRenduService
{
    public function index()
    {
        $compteRendu = CompteRendu::all();
        return $compteRendu;
    }


    public function store(array $request)
    {

        $compteRendu = CompteRendu::create($request);
        return $compteRendu;
    }


    public function show(string $id)
    {
        return CompteRendu::find($id);
    }


    public function update(array $request, string $id)
    {
        $compteRendu = $this->show($id);
        $compteRendu->update($request);
        return $compteRendu;
    }


    public function destroy(int $id)
    {
        CompteRendu::destroy($id);
    }

    public function getByConsultation(string $consultationId)
    {
        return CompteRendu::with([
            'consultation.rendezvous.patient.user',
            'consultation.rendezvous.medecin.user'
        ])
            ->where('consultation_id', $consultationId)
            ->first();
    }

    public function getByMedecin(string $medecinId)
    {
        return CompteRendu::with([
            'consultation.rendezvous.patient.user',
            'consultation'
        ])
            ->whereHas('consultation.rendezvous', function($query) use ($medecinId) {
                $query->where('medecin_id', $medecinId);
            })
            ->orderBy('date_creation', 'desc')
            ->get();
    }

    public function getByPatient(string $patientId)
    {
        return CompteRendu::with([
            'consultation.rendezvous.medecin.user',
            'consultation.rendezvous.medecin.specialite',
            'consultation'
        ])
            ->whereHas('consultation.rendezvous', function($query) use ($patientId) {
                $query->where('patient_id', $patientId);
            })
            ->orderBy('date_creation', 'desc')
            ->get();
    }
}
