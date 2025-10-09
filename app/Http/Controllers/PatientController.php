<?php

namespace App\Http\Controllers;

use App\Http\Requests\PatientRequest;
use App\Models\Patient;
use App\Services\PatientService;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    protected $patientService;
    /**
     * Display a listing of the resource.
     */
    public function __construct(){
        $this->patientService = new PatientService();
    }
    public function index()
    {
        $patient = $this->patientService->index();
        return response()->json($patient,200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PatientRequest $request)
    {
        $patient = $this->patientService->store($request->validated());
        return response()->json($patient,201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $patient = $this->patientService->show($id);
        return response()->json($patient,200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PatientRequest $request, string $id)
    {
        $patient = $this->patientService->index();
        return response()->json([
            "message" => "patient modifié",
            "patient" => $patient,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $patient = $this->patientService->destroy($id);
        return response()->json("patient supprimé",204);
    }
}
