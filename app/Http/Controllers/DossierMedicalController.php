<?php

namespace App\Http\Controllers;

use App\Http\Requests\DossierMedicalRequest;
use App\Models\DossierMedical;
use App\Services\DossierMedicalService;
use Illuminate\Http\Request;

class DossierMedicalController extends Controller
{
    protected $dossierMedicalService;
    /**
     * Display a listing of the resource.
     */
    public function __construct(){
        $this->dossierMedicalService = new DossierMedicalService();
    }

    public function index()
    {
        $dossierMedical = $this->dossierMedicalService->index();
        return response()->json($dossierMedical,200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(DossierMedicalRequest $request)
    {
        $dossierMedical = $this->dossierMedicalService->store($request->validated());
        return response()->json($dossierMedical,201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $dossierMedical = $this->dossierMedicalService->show($id);
        return response()->json($dossierMedical,200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(DossierMedicalRequest $request, string $id)
    {
        $dossierMedical = $this->dossierMedicalService->update($request->validated(), $id);
        return response()->json([
            "message" => "dossier medical modifié ",
            "dossierMedical" => $dossierMedical
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $dossierMedical = $this->dossierMedicalService->destroy($id);
        return response()->json("dossier medical supprimé",204);
    }

    /**
     * Récupérer le dossier médical d'un patient
     */
    public function getByPatient(string $patientId)
    {
        $dossierMedical = $this->dossierMedicalService->getByPatient($patientId);
        return response()->json($dossierMedical, 200);
    }
}
