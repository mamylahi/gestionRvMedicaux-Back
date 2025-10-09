<?php

namespace App\Http\Controllers;

use App\Http\Requests\RendezVousRequest;
use App\Models\RendezVous;
use App\Services\RendezVousService;
use Illuminate\Http\Request;

class RendezVousController extends Controller
{
    protected $rendezvousService;
    /**
     * Display a listing of the resource.
     */
    public function __construct(){
        $this->rendezvousService = new RendezVousService();
    }
    public function index()
    {
        $rendezVous = $this->rendezvousService->index();
        return response()->json($rendezVous,200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RendezVousRequest $request)
    {
        $rendezVous = $this->rendezvousService->store($request->validated());
        return response()->json($rendezVous,201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $rendezVous = $this->rendezvousService->show($id);
        return response()->json($rendezVous,200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(RendezVousRequest $request, string $id)
    {
        $rendezVous = $this->rendezvousService->update($request->validated(), $id);
        return response()->json([
            "message" => "rendez vous modifié",
            "rendezvous" => $rendezVous,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $rendezVous = $this->rendezvousService->destroy($id);
        return response()->json("rendez-vous supprimé",204);
    }
}
