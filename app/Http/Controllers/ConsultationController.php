<?php

namespace App\Http\Controllers;

use App\Http\Requests\ConsultationRequest;
use App\Models\Consultation;
use App\Services\ConsultationService;
use Illuminate\Http\Request;

class ConsultationController extends Controller
{
    protected $consultationService;
    /**
     * Display a listing of the resource.
     */

    public function __construct(){
        $this->consultationService = new ConsultationService();
    }
    public function index()
    {
        $consultation = $this->consultationService->index();
        return response()->json($consultation, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ConsultationRequest $request)
    {
        $consultation = $this->consultationService->store($request->validated());
        return response()->json($consultation, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $consultation = $this->consultationService->show($id);
        return response()->json($consultation, 200);

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ConsultationRequest $request, string $id)
    {
        $consultation = $this->consultationService->update($request->validated(),$id);
        return response()->json([
            "message" => "consultation modifiée",
            "consultation" => $consultation

        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $consultation = $this->consultationService->destroy($id);
        return response()->json("consultation supprimé",204);
    }

    /**
     * Récupérer la consultation d'un rendez-vous
     */
    public function getByRendezVous(string $rendezVousId)
    {
        $consultation = $this->consultationService->getByRendezVous($rendezVousId);
        return response()->json($consultation, 200);
    }

    /**
     * Récupérer toutes les consultations d'un médecin
     */
    public function getByMedecin(string $medecinId)
    {
        $consultations = $this->consultationService->getByMedecin($medecinId);
        return response()->json($consultations, 200);
    }

    /**
     * Récupérer toutes les consultations d'un patient
     */
    public function getByPatient(string $patientId)
    {
        $consultations = $this->consultationService->getByPatient($patientId);
        return response()->json($consultations, 200);
    }
}
