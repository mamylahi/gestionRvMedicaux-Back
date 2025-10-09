<?php

namespace App\Services;

use App\Models\RendezVous;

class RendezVousService
{
    public function index()
    {
        $rendezVous = RendezVous::all();
        return $rendezVous;
    }


    public function store(array $request)
    {

        $rendezVous = RendezVous::create($request);
        return $rendezVous;
    }


    public function show(string $id)
    {
        return RendezVous::find($id);
    }


    public function update(array $request, string $id)
    {
        $rendezVous = $this->show($id);
        $rendezVous->update($request);
        return $rendezVous;
    }


    public function destroy(int $id)
    {
        RendezVous::destroy($id);
    }
}
