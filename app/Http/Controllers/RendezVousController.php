<?php

namespace App\Http\Controllers;

use App\Http\Requests\RendezVousRequest;
use App\Models\Rendezvous;
use App\Services\RendezVousService;
use Illuminate\Http\Request;

class RendezVousController extends Controller
{
    protected $rendezvousService;
    /**
     * Display a listing of the resource.
     */
    public function __construct(){
        $this->rendezvousService = new RendezVousService();
    }
    public function index()
    {
        $rendezVous = $this->rendezvousService->index();
        return response()->json($rendezVous,200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RendezVousRequest $request)
    {
        try {
            $rendezVous = $this->rendezvousService->store($request->validated());
            return response()->json([
                'message' => 'Rendez-vous créé avec succès',
                'rendezvous' => $rendezVous
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la création du rendez-vous',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $rendezVous = $this->rendezvousService->show($id);
        return response()->json($rendezVous,200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(RendezVousRequest $request, string $id)
    {
        $rendezVous = $this->rendezvousService->update($request->validated(), $id);
        return response()->json([
            "message" => "rendez vous modifié",
            "rendezvous" => $rendezVous,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $rendezVous = $this->rendezvousService->destroy($id);
        return response()->json("rendez-vous supprimé",204);
    }

    /**
     * Récupérer les rendez-vous d'un patient
     */
    public function getByPatient(string $patientId)
    {
        $rendezVous = $this->rendezvousService->getByPatient($patientId);
        return response()->json($rendezVous, 200);
    }

    /**
     * Récupérer les rendez-vous d'un médecin
     */
    public function getByMedecin(string $medecinId)
    {
        $rendezVous = $this->rendezvousService->getByMedecin($medecinId);
        return response()->json($rendezVous, 200);
    }

    /**
     * Récupérer les rendez-vous par date
     */
    public function getByDate(string $date)
    {
        $rendezVous = $this->rendezvousService->getByDate($date);
        return response()->json($rendezVous, 200);
    }

    /**
     * Mettre à jour le statut d'un rendez-vous
     */
    public function updateStatut(Request $request, string $id)
    {
        $request->validate([
            'statut' => 'required|in:en_attente,confirme,annule,termine'
        ]);

        $rendezVous = $this->rendezvousService->updateStatut($id, $request->statut);
        return response()->json([
            "message" => "Statut du rendez-vous mis à jour",
            "rendezvous" => $rendezVous
        ], 200);
    }
}
