<?php

namespace App\Services;

use App\Models\Departement;

class DepartementService
{
    public function index()
    {
        $departement = Departement::all();
        return $departement;
    }


    public function store(array $request)
    {

        $departement = Departement::create($request);
        return $departement;
    }


    public function show(string $id)
    {
        return Departement::find($id);
    }


    public function update(array $request, string $id)
    {
        $departement = $this->show($id);
        $departement->update($request);
        return $departement;
    }


    public function destroy(int $id)
    {
        Departement::destroy($id);
    }
}
