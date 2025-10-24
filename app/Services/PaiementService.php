<?php

namespace App\Services;

use App\Models\Paiement;

class PaiementService
{
    public function index()
    {
        $paiement = Paiement::all();
        return $paiement;
    }


    public function store(array $request)
    {

        $paiement = Paiement::create($request);
        return $paiement;
    }


    public function show(string $id)
    {
        return Paiement::find($id);
    }


    public function update(array $request, string $id)
    {
        $paiement = $this->show($id);
        $paiement->update($request);
        return $paiement;
    }


    public function destroy(int $id)
    {
        Paiement::destroy($id);
    }


    public function getByConsultation(string $consultationId)
    {
        return Paiement::with([
            'consultation.rendezvous.patient.user',
            'consultation.rendezvous.medecin.user'
        ])
            ->where('consultation_id', $consultationId)
            ->first();
    }

    public function getByPatient(string $patientId)
    {
        return Paiement::with([
            'consultation.rendezvous.medecin.user',
            'consultation'
        ])
            ->whereHas('consultation.rendezvous', function($query) use ($patientId) {
                $query->where('patient_id', $patientId);
            })
            ->orderBy('date_paiement', 'desc')
            ->get();
    }

    public function updateStatut(string $id, string $statut)
    {
        $paiement = Paiement::find($id);

        if (!$paiement) {
            throw new \Exception('Paiement introuvable');
        }

        $paiement->statut = $statut;
        $paiement->save();

        return $paiement->load([
            'consultation.rendezvous.patient.user',
            'consultation.rendezvous.medecin.user'
        ]);
    }
}
