<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Services\AuthService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    protected $authService;

    /**
     * Display a listing of the resource.
     */

    public function __construct()
    {
        $this->authService = new AuthService();
    }
    /**
     * Enregistrement (register)
     */
    public function register(RegisterRequest $request)
    {
        return $this->authService->register($request->validated());
    }

    /**
     * Connexion (login)
     */
    public function login(LoginRequest $request)
    {
        return $this->authService->login($request->validated());
    }

    /**
     * Déconnexion
     */
    public function logout()
    {
        return $this->authService->logout();
    }

    /**
     * Utilisateur connecté
     */
    public function me()
    {
        return $this->authService->getAuthenticatedUser();
    }

    /**
     * Récupérer tous les utilisateurs
     */
    public function index()
    {
        return $this->authService->getAll();
    }

    /**
     * Récupérer un utilisateur par ID
     */
    public function show(int $id)
    {
        return $this->authService->show($id);
    }

    /**
     * Mettre à jour un utilisateur
     */
    public function update(RegisterRequest $request, int $id)
    {
        return $this->authService->update($request->validated(), $id);

    }

    /**
     * Supprimer un utilisateur
     */
    public function destroy(int $id)
    {
        return $this->authService->destroy($id);
    }
}
