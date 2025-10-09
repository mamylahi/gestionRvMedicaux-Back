<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaiementRequest;
use App\Models\Paiement;
use App\Services\PaiementService;
use Illuminate\Http\Request;

class PaiementController extends Controller
{
    protected $paiementService;
    /**
     * Display a listing of the resource.
     */
    public function __construct(){
        $this->paiementService = new PaiementService();
    }
    public function index()
    {
        $paiement = $this->paiementService->index();
        return response()->json($paiement,200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PaiementRequest $request)
    {
        $paiement = $this->paiementService->store($request->validated());
        return response()->json($paiement,201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $paiement = $this->paiementService->show($id);
        return response()->json($paiement,200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PaiementRequest $request, string $id)
    {
        $paiement = $this->paiementService->update($request->validated(), $id);
        return response()->json([
            "message" => "paiement modifie",
            "paiement" => $paiement
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $paiement = $this->paiementService->destroy($id);
        return response()->json("paiement supprimé",200);
    }
}
