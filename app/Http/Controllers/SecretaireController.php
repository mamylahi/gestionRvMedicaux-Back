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
     * Constructeur
     */
    public function __construct()
    {
        $this->secretaireService = new SecretaireService();
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->secretaireService->index();
    }

    /**
     * Store a newly created resource in storage.
     * CORRECTION: Méthode manquante ajoutée
     */
    public function store(SecretaireRequest $request)
    {
        return $this->secretaireService->store($request->validated());
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
     * CORRECTION: Appel à destroy au lieu de index
     */
    public function destroy(string $id)
    {
        return $this->secretaireService->destroy($id);
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
     * Récupérer les paiement non payés
     * Route: GET /secretaires/paiements
     */
    public function getPaiementsNonPayes()
    {
        return $this->secretaireService->getPaiementsNonPayes();
    }
}
