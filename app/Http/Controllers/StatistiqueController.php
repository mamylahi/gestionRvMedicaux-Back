<?php

namespace App\Http\Controllers;

use App\Services\StatistiqueService;
use Illuminate\Http\JsonResponse;

class StatistiqueController extends Controller
{
    protected $statistiqueService;

    public function __construct(StatistiqueService $statistiqueService)
    {
        $this->statistiqueService = $statistiqueService;
    }

    /**
     * Récupère toutes les statistiques pour l'administrateur
     *
     * @return JsonResponse
     */
    public function getStatistiquesAdmin(): JsonResponse
    {
        try {
            $statistiques = $this->statistiqueService->getStatistiquesAdmin();

            return response()->json([
                'success' => true,
                'message' => 'Statistiques récupérées avec succès',
                'data' => $statistiques
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des statistiques',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupère les statistiques générales uniquement
     *
     * @return JsonResponse
     */
    public function getStatistiquesGenerales(): JsonResponse
    {
        try {
            $statistiques = $this->statistiqueService->getStatistiquesAdmin();

            return response()->json([
                'success' => true,
                'message' => 'Statistiques générales récupérées avec succès',
                'data' => $statistiques['general']
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des statistiques générales',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupère les statistiques des rendez-vous
     *
     * @return JsonResponse
     */
    public function getStatistiquesRendezVous(): JsonResponse
    {
        try {
            $statistiques = $this->statistiqueService->getStatistiquesAdmin();

            return response()->json([
                'success' => true,
                'message' => 'Statistiques des rendez-vous récupérées avec succès',
                'data' => $statistiques['rendezvous']
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des statistiques des rendez-vous',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupère les statistiques financières
     *
     * @return JsonResponse
     */
    public function getStatistiquesFinancieres(): JsonResponse
    {
        try {
            $statistiques = $this->statistiqueService->getStatistiquesAdmin();

            return response()->json([
                'success' => true,
                'message' => 'Statistiques financières récupérées avec succès',
                'data' => $statistiques['financier']
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des statistiques financières',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupère les statistiques des consultations
     *
     * @return JsonResponse
     */
    public function getStatistiquesConsultations(): JsonResponse
    {
        try {
            $statistiques = $this->statistiqueService->getStatistiquesAdmin();

            return response()->json([
                'success' => true,
                'message' => 'Statistiques des consultations récupérées avec succès',
                'data' => $statistiques['consultations']
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des statistiques des consultations',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupère les statistiques des médecins
     *
     * @return JsonResponse
     */
    public function getStatistiquesMedecins(): JsonResponse
    {
        try {
            $statistiques = $this->statistiqueService->getStatistiquesAdmin();

            return response()->json([
                'success' => true,
                'message' => 'Statistiques des médecins récupérées avec succès',
                'data' => $statistiques['medecins']
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des statistiques des médecins',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupère les statistiques des patients
     *
     * @return JsonResponse
     */
    public function getStatistiquesPatients(): JsonResponse
    {
        try {
            $statistiques = $this->statistiqueService->getStatistiquesAdmin();

            return response()->json([
                'success' => true,
                'message' => 'Statistiques des patients récupérées avec succès',
                'data' => $statistiques['patients']
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des statistiques des patients',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupère les statistiques des départements
     *
     * @return JsonResponse
     */
    public function getStatistiquesDepartements(): JsonResponse
    {
        try {
            $statistiques = $this->statistiqueService->getStatistiquesAdmin();

            return response()->json([
                'success' => true,
                'message' => 'Statistiques des départements récupérées avec succès',
                'data' => $statistiques['departements']
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des statistiques des départements',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
