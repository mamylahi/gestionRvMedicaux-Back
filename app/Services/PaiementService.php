<?php

namespace App\Services;

use App\Models\Paiement;

class PaiementService
{
    public function index()
    {
        // CORRECTION: Charger toutes les relations nécessaires
        $paiement = Paiement::with([
            'consultation.rendezvous.patient.user',
            'consultation.rendezvous.medecin.user'
        ])->get();

        return $paiement;
    }


    public function store(array $request)
    {
        $paiement = Paiement::create($request);

        // Charger les relations après la création
        return $paiement->load([
            'consultation.rendezvous.patient.user',
            'consultation.rendezvous.medecin.user'
        ]);
    }


    public function show(string $id)
    {
        // Charger les relations pour show()
        return Paiement::with([
            'consultation.rendezvous.patient.user',
            'consultation.rendezvous.medecin.user'
        ])->find($id);
    }


    public function update(array $request, string $id)
    {
        $paiement = Paiement::find($id);
        $paiement->update($request);

        // Recharger avec les relations
        return $paiement->load([
            'consultation.rendezvous.patient.user',
            'consultation.rendezvous.medecin.user'
        ]);
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
