<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaiementRequest;
use App\Models\Paiement;
use App\Services\PaiementService;
use Illuminate\Http\Request;

class PaiementController extends Controller
{
    protected $paiementService;
    /**
     * Display a listing of the resource.
     */
    public function __construct(){
        $this->paiementService = new PaiementService();
    }
    public function index()
    {
        $paiement = $this->paiementService->index();
        return response()->json($paiement,200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PaiementRequest $request)
    {
        $paiement = $this->paiementService->store($request->validated());
        return response()->json($paiement,201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $paiement = $this->paiementService->show($id);
        return response()->json($paiement,200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PaiementRequest $request, string $id)
    {
        $paiement = $this->paiementService->update($request->validated(), $id);
        return response()->json([
            "message" => "paiement modifie",
            "paiement" => $paiement
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $paiement = $this->paiementService->destroy($id);
        return response()->json("paiement supprimé",200);
    }

    /**
     * Récupérer le paiement d'une consultation
     */
    public function getByConsultation(string $consultationId)
    {
        $paiement = $this->paiementService->getByConsultation($consultationId);
        return response()->json($paiement, 200);
    }

    /**
     * Récupérer tous les paiements d'un patient
     */
    public function getByPatient(string $patientId)
    {
        $paiements = $this->paiementService->getByPatient($patientId);
        return response()->json($paiements, 200);
    }

    /**
     * Mettre à jour le statut d'un paiement
     */
    public function updateStatut(Request $request, string $id)
    {
        $request->validate([
            'statut' => 'required|in:en_attente,valide,annule'
        ]);

        $paiement = $this->paiementService->updateStatut($id, $request->statut);
        return response()->json([
            "message" => "Statut du paiement mis à jour",
            "paiement" => $paiement
        ], 200);
    }
}
