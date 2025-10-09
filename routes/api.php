<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::apiResource("/users",\App\Http\Controllers\AuthController::class)->middleware('auth:api');
Route::apiResource("/medecin",\App\Http\Controllers\MedecinController::class)->middleware('auth:api');
Route::apiResource("/patient",\App\Http\Controllers\PatientController::class)->middleware('auth:api');
Route::apiResource("/secretaire",\App\Http\Controllers\SecretaireController::class)->middleware('auth:api');
Route::apiResource("/specialite",\App\Http\Controllers\SpecialiteController::class)->middleware('auth:api');
Route::apiResource("/departement",\App\Http\Controllers\DepartementController::class)->middleware('auth:api');
Route::apiResource("/departement",\App\Http\Controllers\DepartementController::class)->middleware('auth:api');
Route::apiResource("/disponibilite",\App\Http\Controllers\DisponibiliteController::class)->middleware('auth:api');
Route::apiResource("/rendezvous",\App\Http\Controllers\RendezVousController::class)->middleware('auth:api');
Route::apiResource("/consultation",\App\Http\Controllers\ConsultationController::class)->middleware('auth:api');
Route::apiResource("/compterendu",\App\Http\Controllers\CompteRenduController::class)->middleware('auth:api');
Route::apiResource("/dossiermedical",\App\Http\Controllers\DossierMedicalController::class)->middleware('auth:api');
Route::apiResource("/paiement",\App\Http\Controllers\PaiementController::class)->middleware('auth:api');
