<?php

namespace App\Http\Controllers;

use App\Http\Requests\MedecinRequest;
use App\Models\Medecin;
use App\Services\MedecinService;
use Illuminate\Http\Request;

class MedecinController extends Controller
{
    protected $medecinService;
    /**
     * Display a listing of the resource.
     */
    public function __construct(){
        $this->medecinService = new MedecinService();
    }

    public function index()
    {
        return $this->medecinService->index();

    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return $this->medecinService->show($id);

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(MedecinRequest $request, string $id)
    {
        return $this->medecinService->update($request->validated(), $id);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        return $this->medecinService->destroy($id);

    }

    /**
    * Récupérer tous les médecins disponibles
    */
    public function getDisponibles()
    {
        $medecins = $this->medecinService->getDisponibles();
        return response()->json($medecins, 200);
    }

    /**
     * Récupérer les médecins par spécialité
     */
    public function getBySpecialite(string $specialiteId)
    {
        $medecins = $this->medecinService->getBySpecialite($specialiteId);
        return response()->json($medecins, 200);
    }

    /**
     * Dashboard médecin avec statistiques
     */
    public function getDashboard(string $medecinId)
    {
        $dashboard = $this->medecinService->getDashboard($medecinId);
        return response()->json($dashboard, 200);
    }

    /**
     * Récupérer les utilisateurs avec rôle 'medecin' mais pas dans la table medecin
     */
    public function getOrphanMedecins(){
        return response()->json($this->medecinService->getOrphanMedecins(), 200);
    }

    public function getMesRendezVous()
    {
        return $this->medecinService->getMesRendezVous();
    }

    /**
     * Récupérer les dossier médical des patients
     * Route: GET /medecins/dossier-medicaux
     */
    public function getDossierMedicaux()
    {
        return $this->patientService->getDossierMedicaux();
    }
    /**
     * Récupérer les consultations du patient connecté
     * Route: GET /medecins/mes-consultations
     */
    public function getMesConsultations()
    {
        return $this->medecinService->getMesConsultations();
    }

    /**
     * Récupérer le dossier médical du patient connecté
     * Route: GET /medecins/mes-patients
     */
    public function getMesPatients()
    {
        return $this->medecinService->getMesPatients();
    }
 public function getCompteRenduPatients()
    {
        return $this->medecinService->getCompteRenduPatients();
    }

}
