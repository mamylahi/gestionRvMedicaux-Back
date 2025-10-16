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
        return $this->patientService->index();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PatientRequest $request)
    {
        return $this->patientService->store($request->validated());

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
       return $this->patientService->show($id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PatientRequest $request, string $id)
    {
        return $this->patientService->index();

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
       return $this->patientService->destroy($id);

    }
}
