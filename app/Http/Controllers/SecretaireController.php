<?php

namespace App\Http\Controllers;

use App\Http\Requests\SecretaireRequest;
use App\Models\Secretaire;
use App\Services\SecretaireService;
use Illuminate\Http\Request;

class SecretaireController extends Controller
{
    protected $secretaireService;
    /**
     * Display a listing of the resource.
     */
    public function __construct(){
        $this->secretaireService = new SecretaireService();
    }
    public function index()
    {
       return $this->secretaireService->index();
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
       return $this->secretaireService->show($id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SecretaireRequest $request, string $id)
    {
        return $this->secretaireService->update($request->validated(), $id);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
       return $this->secretaireService->index();
    }

    /**
     * Récupérer les Rendezvous à venir
     * Route: GET /secretaires/rendez-vous
     */
    public function getRendezVousAVenir()
    {
        return $this->secretaireService->getRendezVousAVenir();
    }

    /**
     * Récupérer les DossiersMedicaux des patient
     * Route: GET /secretaires/dossiers-medicaux
     */
    public function getDossiersMedicaux()
    {
        return $this->secretaireService->getDossiersMedicaux();
    }

    /**
     * Récupérer les paiement effectué
     * Route: GET /secretaires/paiements
     */
    public function getPaiementsNonPayes()
    {
        return $this->secretaireService->getPaiementsNonPayes();
    }


}
