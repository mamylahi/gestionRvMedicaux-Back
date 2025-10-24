<?php

namespace App\Services;

use App\Models\Disponibilite;

class DisponibiliteService
{
    public function index()
    {
        $disponibilite = Disponibilite::all();
        return $disponibilite;
    }


    public function store(array $request)
    {

        $disponibilite = Disponibilite::create($request);
        return $disponibilite;
    }


    public function show(string $id)
    {
        return Disponibilite::find($id);
    }


    public function update(array $request, string $id)
    {
        $disponibilite = $this->show($id);
        $disponibilite->update($request);
        return $disponibilite;
    }


    public function destroy(int $id)
    {
        Disponibilite::destroy($id);
    }

    public function getByMedecin(string $medecinId)
    {
        return Disponibilite::with(['medecin.user'])
            ->where('medecin_id', $medecinId)
            ->orderBy('date_debut', 'asc')
            ->get();
    }
}
