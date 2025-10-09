<?php

namespace App\Services;

use App\Models\Medecin;

class MedecinService
{
    public function index()
    {
        $medecin = Medecin::all();
        return $medecin;
    }


    public function store(array $request)
    {

        $medecin = Medecin::create($request);
        return $medecin;
    }


    public function show(string $id)
    {
        return Medecin::find($id);
    }


    public function update(array $request, string $id)
    {
        $medecin = $this->show($id);
        $medecin->update($request);
        return $medecin;
    }


    public function destroy(int $id)
    {
        Medecin::destroy($id);
    }
}
