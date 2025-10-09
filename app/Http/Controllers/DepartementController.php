<?php

namespace App\Http\Controllers;

use App\Http\Requests\DepartementRequest;
use App\Models\Departement;
use App\Services\DepartementService;
use Illuminate\Http\Request;

class DepartementController extends Controller
{
    protected $depatementService;

    /**
     * Display a listing of the resource.
     */

    public function __construct(){
        $this->depatementService = new DepartementService();
    }

    public function index()
    {
        $departement = $this->depatementService->index();
        return response()->json($departement,200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(DepartementRequest $request)
    {
        $departement = $this->depatementService->store($request->validated());
        return response()->json($departement,201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $departement = $this->depatementService->show($id);
        return response()->json($departement,200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(DepartementRequest $request, string $id)
    {
        $departement = $this->depatementService->update($request->validated(), $id);
        return response()->json([
            "message" => "departement modifié" ,
            "departement" => $departement
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $departement = $this->depatementService->destroy($id);
        return response()->json("departement supprimé",204);
    }
}
