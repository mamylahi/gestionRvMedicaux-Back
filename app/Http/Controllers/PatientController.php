<?php

namespace App\Http\Controllers;

use App\Http\Requests\PatientRequest;
use App\Models\Patient;
use App\Services\PatientService;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    protected $patientService;
    /**
     * Display a listing of the resource.
     */
    public function __construct(){
        $this->patientService = new PatientService();
    }
    public function index()
    {
        return $this->patientService->index();
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
       return $this->patientService->show($id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PatientRequest $request, string $id)
    {
        return $this->patientService->index();

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
       return $this->patientService->destroy($id);

    }

    /**
     * Rechercher des patients
     */
    public function search(Request $request)
    {
        $query = $request->input('q', '');
        $patients = $this->patientService->search($query);
        return response()->json($patients, 200);
    }

    /**
     * Dashboard patient avec statistiques
     */
    public function getDashboard(string $patientId)
    {
        $dashboard = $this->patientService->getDashboard($patientId);
        return response()->json($dashboard, 200);
    }

    /**
     * Récupérer les rendez-vous du patient connecté
     * Route: GET /patients/mes-rendezvous
     */
    public function getMesRendezVous()
    {
        return $this->patientService->getMesRendezVous();
    }

    /**
     * Récupérer les paiements du patient connecté
     * Route: GET /patients/mes-paiements
     */
    public function getMesPaiements()
    {
        return $this->patientService->getMesPaiements();
    }

    /**
     * Récupérer les consultations du patient connecté
     * Route: GET /patients/mes-consultations
     */
    public function getMesConsultations()
    {
        return $this->patientService->getMesConsultations();
    }

    /**
     * Récupérer le dossier médical du patient connecté
     * Route: GET /patients/mon-dossier-medical
     */
    public function getMonDossierMedical()
    {
        return $this->patientService->getMonDossierMedical();
    }

}
