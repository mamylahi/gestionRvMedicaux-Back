<?php

namespace App\Services;

use App\Models\DossierMedical;

class DossierMedicalService
{
    public function index()
    {
        $dossierMedical = DossierMedical::all();
        return $dossierMedical;
    }


    public function store(array $request)
    {

        $dossierMedical = DossierMedical::create($request);
        return $dossierMedical;
    }


    public function show(string $id)
    {
        return DossierMedical::find($id);
    }


    public function update(array $request, string $id)
    {
        $dossierMedical = $this->show($id);
        $dossierMedical->update($request);
        return $dossierMedical;
    }


    public function destroy(int $id)
    {
        DossierMedical::destroy($id);
    }
}
