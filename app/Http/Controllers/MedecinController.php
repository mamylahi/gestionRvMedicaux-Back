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
        return $this->medecinService->index();

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(MedecinRequest $request)
    {
        return $this->medecinService->store($request->validated());
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return $this->medecinService->show($id);

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(MedecinRequest $request, string $id)
    {
        return $this->medecinService->update($request->validated(), $id);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        return $this->medecinService->destroy($id);

    }
}
