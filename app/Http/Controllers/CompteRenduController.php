<?php

namespace App\Http\Controllers;

use App\Http\Requests\CompteRenduRequest;
use App\Models\CompteRendu;
use App\Services\CompteRenduService;
use Illuminate\Http\Request;

class CompteRenduController extends Controller
{
    protected $compteRenduService;
    /**
     * Display a listing of the resource.
     */

    public function __construct(){
        $this->compteRenduService = new CompteRenduService();
    }


    public function index()
    {
        $compteRendu = $this->compteRenduService->index();
        return response()->json($compteRendu,200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CompteRenduRequest $request)
    {
        $compteRendu = $this->compteRenduService->store($request->validated());
        return response()->json($compteRendu,201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $compteRendu = $this->compteRenduService->show($id);
        return response()->json($compteRendu,200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CompteRenduRequest $request, string $id)
    {
        $compteRendu = $this->compteRenduService->update( $request->validated(),$id);
        return response()->json([
            "message" => "compte rendu modifier avec succes",
            "compteRendu" => $compteRendu,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $compteRendu = $this->compteRenduService->destroy($id);
        return response()->json("compte rendu supprimé", 204);
    }

    /**
     * Récupérer le compte rendu d'une consultation
     */
    public function getByConsultation(string $consultationId)
    {
        $compteRendu = $this->compteRenduService->getByConsultation($consultationId);
        return response()->json($compteRendu, 200);
    }

    /**
     * Récupérer tous les comptes rendus d'un médecin
     */
    public function getByMedecin(string $medecinId)
    {
        $comptesRendus = $this->compteRenduService->getByMedecin($medecinId);
        return response()->json($comptesRendus, 200);
    }

    /**
     * Récupérer tous les comptes rendus d'un patient
     */
    public function getByPatient(string $patientId)
    {
        $comptesRendus = $this->compteRenduService->getByPatient($patientId);
        return response()->json($comptesRendus, 200);
    }
}
