<?php

namespace App\Services;

use App\Models\Patient;

class PatientService
{
    public function index()
    {
        $patient = Patient::all();
        return $patient;
    }


    public function store(array $request)
    {

        $patient = Patient::create($request);
        return $patient;
    }


    public function show(string $id)
    {
        return Patient::find($id);
    }


    public function update(array $request, string $id)
    {
        $patient = $this->show($id);
        $patient->update($request);
        return $patient;
    }


    public function destroy(int $id)
    {
        Patient::destroy($id);
    }
}
