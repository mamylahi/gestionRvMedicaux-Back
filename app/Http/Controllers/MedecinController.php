<?php

namespace App\Http\Controllers;

use App\Http\Requests\MedecinRequest;
use App\Models\Medecin;
use App\Services\MedecinService;
use Illuminate\Http\Request;

class MedecinController extends Controller
{
    protected $medecinService;
    /**
     * Display a listing of the resource.
     */
    public function __construct(){
        $this->medecinService = new MedecinService();
    }

    public function index()
    {
        $medecin = $this->medecinService->index();
        return response()->json($medecin,200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(MedecinRequest $request)
    {
        $medecin = $this->medecinService->store($request->validated());
        return response()->json($medecin,201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $medecin = $this->medecinService->show($id);
        return response()->json($medecin,200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(MedecinRequest $request, string $id)
    {
        $medecin = $this->medecinService->update($request->validated(), $id);
        return response()->json([
            "message" => "medecin modifié",
            "medecin" => $medecin
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $medecin = $this->medecinService->destroy($id);
        return response()->json("medecin supprimé",204);
    }
}
