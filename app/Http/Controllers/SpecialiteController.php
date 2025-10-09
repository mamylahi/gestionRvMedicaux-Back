<?php

namespace App\Http\Controllers;

use App\Http\Requests\SpecialiteRequest;
use App\Models\Specialite;
use App\Services\SpecialiteService;
use Illuminate\Http\Request;

class SpecialiteController extends Controller
{
    protected $specialiteService;
    /**
     * Display a listing of the resource.
     */
    public function __construct(){
        $this->specialiteService = new SpecialiteService();
    }
    public function index()
    {
        $specialite = $this->specialiteService->index();
        return response()->json($specialite,200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SpecialiteRequest $request)
    {
        $specialite = $this->specialiteService->store($request->validated());
        return response()->json($specialite,201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $specialite = $this->specialiteService->show($id);
        return response()->json($specialite,200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SpecialiteRequest $request, string $id)
    {
        $specialite = $this->specialiteService->update($request->validated(), $id);
        return response()->json([
            "message" => "specialite modifié",
            "specialite" => $specialite
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $specialite = $this->specialiteService->destroy($id);
        return response()->json("specialite supprimé",204);
    }
}
