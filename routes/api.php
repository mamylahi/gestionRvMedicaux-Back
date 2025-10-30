<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\MedecinController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\SecretaireController;
use App\Http\Controllers\SpecialiteController;
use App\Http\Controllers\DepartementController;
use App\Http\Controllers\DisponibiliteController;
use App\Http\Controllers\RendezVousController;
use App\Http\Controllers\ConsultationController;
use App\Http\Controllers\CompteRenduController;
use App\Http\Controllers\DossierMedicalController;
use App\Http\Controllers\PaiementController;
use App\Http\Controllers\StatistiqueController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Routes d'authentification (sans authentification requise)
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login'])->name('login');
});

// Routes protégées par authentification JWT
Route::middleware('auth:api')->group(function () {

    // ========== AUTHENTIFICATION ==========
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);

    // ========== MÉDECINS ==========
    // Routes spécifiques AVANT apiResource
    Route::prefix('medecins')->group(function () {
        Route::get('/mes-rendezvous', [MedecinController::class, 'getMesRendezVous']);
        Route::get('/mes-patients', [MedecinController::class, 'getMesPatients']);
        Route::get('/mes-consultations', [MedecinController::class, 'getMesConsultations']);
        Route::get('/mes-dossiers-medicaux', [MedecinController::class, 'getMesDossiersMedicaux']);
        Route::get('/mes-comptes-rendus', [MedecinController::class, 'getMesComptesRendus']);
        Route::get('/mon-profil', [MedecinController::class, 'getMonProfil']);
        Route::get('/disponibles/all', [MedecinController::class, 'getDisponibles']);
        Route::get('/specialite/{specialiteId}', [MedecinController::class, 'getBySpecialite']);
        Route::get('/{medecinId}/dashboard', [MedecinController::class, 'getDashboard']);
    });
    Route::apiResource('/medecins', MedecinController::class);

    // ========== PATIENTS ==========
    // Routes spécifiques AVANT apiResource
    Route::prefix('patients')->group(function () {
        Route::get('/mes-rendezvous', [PatientController::class, 'getMesRendezVous']);
        Route::get('/mes-paiements', [PatientController::class, 'getMesPaiements']);
        Route::get('/mes-consultations', [PatientController::class, 'getMesConsultations']);
        Route::get('/mon-dossier-medical', [PatientController::class, 'getMonDossierMedical']);
        Route::get('/mes-comptes-rendus', [PatientController::class, 'getMesComptesRendus']);
        Route::get('/mon-profil', [PatientController::class, 'getMonProfil']);
        Route::get('/search/query', [PatientController::class, 'search']);
        Route::get('/{patientId}/dashboard', [PatientController::class, 'getDashboard']);
    });
    Route::apiResource('/patients', PatientController::class);

    // ========== SECRÉTAIRES ==========
    // Routes spécifiques AVANT apiResource
    Route::prefix('secretaires')->group(function () {
        Route::get('/mes-rendezvous', [SecretaireController::class, 'getRendezVousAVenir']);
        Route::get('/dossier-medicaux', [SecretaireController::class, 'getDossiersMedicaux']);
        Route::get('/paiements', [SecretaireController::class, 'getPaiementsNonPayes']);
    });
    Route::apiResource('/secretaires', SecretaireController::class);

    // ========== SPÉCIALITÉS ==========
    Route::apiResource('/specialites', SpecialiteController::class);

    // ========== DÉPARTEMENTS ==========
    Route::apiResource('/departements', DepartementController::class);

    // ========== DISPONIBILITÉS ==========
    Route::get('/disponibilites/medecin/{medecinId}/all', [DisponibiliteController::class, 'getByMedecin']);
    Route::get('/disponibilites/range/search', [DisponibiliteController::class, 'getByDateRange']);
    Route::apiResource('/disponibilites', DisponibiliteController::class);

    // ========== RENDEZ-VOUS ==========
    Route::get('/rendezvous/patient/{patientId}/all', [RendezVousController::class, 'getByPatient']);
    Route::get('/rendezvous/medecin/{medecinId}/all', [RendezVousController::class, 'getByMedecin']);
    Route::get('/rendezvous/date/{date}/all', [RendezVousController::class, 'getByDate']);
    Route::patch('/rendezvous/{id}/statut', [RendezVousController::class, 'updateStatut']);
    Route::apiResource('/rendezvous', RendezVousController::class);

    // ========== CONSULTATIONS ==========
    Route::get('/consultations/rendezvous/{rendezVousId}/single', [ConsultationController::class, 'getByRendezVous']);
    Route::get('/consultations/medecin/{medecinId}/all', [ConsultationController::class, 'getByMedecin']);
    Route::get('/consultations/patient/{patientId}/all', [ConsultationController::class, 'getByPatient']);
    Route::apiResource('/consultations', ConsultationController::class);

    // ========== COMPTES RENDUS ==========
    Route::get('/compterendus/consultation/{consultationId}/single', [CompteRenduController::class, 'getByConsultation']);
    Route::get('/compterendus/medecin/{medecinId}/all', [CompteRenduController::class, 'getByMedecin']);
    Route::get('/compterendus/patient/{patientId}/all', [CompteRenduController::class, 'getByPatient']);
    Route::apiResource('/compterendus', CompteRenduController::class);

    // ========== PAIEMENTS ==========
    Route::get('/paiements/consultation/{consultationId}/single', [PaiementController::class, 'getByConsultation']);
    Route::get('/paiements/patient/{patientId}/all', [PaiementController::class, 'getByPatient']);
    Route::patch('/paiements/{id}/statut', [PaiementController::class, 'updateStatut']);
    Route::apiResource('/paiements', PaiementController::class);

    // ========== DOSSIERS MÉDICAUX ==========
    Route::get('/dossiermedicaux/patient/{patientId}/single', [DossierMedicalController::class, 'getByPatient']);
    Route::apiResource('/dossiermedicaux', DossierMedicalController::class);

    // ========== STATISTIQUES ==========
    Route::get('/statistiques/admin', [StatistiqueController::class, 'getStatistiquesAdmin']);
    Route::get('/statistiques/generales', [StatistiqueController::class, 'getStatistiquesGenerales']);
    Route::get('/statistiques/rendezvous', [StatistiqueController::class, 'getStatistiquesRendezVous']);
    Route::get('/statistiques/financieres', [StatistiqueController::class, 'getStatistiquesFinancieres']);
    Route::get('/statistiques/consultations', [StatistiqueController::class, 'getStatistiquesConsultations']);
    Route::get('/statistiques/medecins', [StatistiqueController::class, 'getStatistiquesMedecins']);
    Route::get('/statistiques/patients', [StatistiqueController::class, 'getStatistiquesPatients']);
    Route::get('/statistiques/departements', [StatistiqueController::class, 'getStatistiquesDepartements']);

    // ========== USERS ==========
    Route::apiResource('/users', AuthController::class);
});
