<?php

namespace App\Http\Controllers;

use App\Http\Requests\DisponibiliteRequest;
use App\Models\Disponibilite;
use App\Services\DisponibiliteService;
use Illuminate\Http\Request;

class DisponibiliteController extends Controller
{
    protected $disponibiliteService;
    /**
     * Display a listing of the resource.
     */
    public function __construct(){
        $this->disponibiliteService = new DisponibiliteService();
    }

    public function index()
    {
        $disponibilite = $this->disponibiliteService->index();
        return response()->json($disponibilite,200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(DisponibiliteRequest $request)
    {
        $disponibilite = $this->disponibiliteService->store($request->validated());
        return response()->json($disponibilite,201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $disponibilite = $this->disponibiliteService->show($id);
        return response()->json($disponibilite,200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(DisponibiliteRequest $request, string $id)
    {
        $disponibilite = $this->disponibiliteService->update($request->validated(), $id);
        return response()->json([
            "message" => "disponibilite modifie" ,
            "disponibilite" => $disponibilite
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $disponibilite = $this->disponibiliteService->destroy($id);
        return response()->json("disponibilite supprimé",204);
    }
}
