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
        $secretaire = $this->secretaireService->index();
        return response()->json($secretaire,200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SecretaireRequest $request)
    {
        $secretaire = $this->secretaireService->store($request->validated());
        return response()->json($secretaire,201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $secretaire = $this->secretaireService->show($id);
        return response()->json($secretaire,200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SecretaireRequest $request, string $id)
    {
        $secretaire = $this->secretaireService->update($request->validated(), $id);
        return response()->json([
            "message" => "secretaire modifié",
            "secretaire" => $secretaire
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $secretaire = $this->secretaireService->index();
        return response()->json("secretaire",204);
    }
}
