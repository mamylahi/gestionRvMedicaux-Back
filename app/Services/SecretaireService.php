<?php

namespace App\Services;

use App\Models\Secretaire;

class SecretaireService
{
    public function index()
    {
        $secretaire = Secretaire::all();
        return $secretaire;
    }


    public function store(array $request)
    {

        $secretaire = Secretaire::create($request);
        return $secretaire;
    }


    public function show(string $id)
    {
        return Secretaire::find($id);
    }


    public function update(array $request, string $id)
    {
        $secretaire = $this->show($id);
        $secretaire->update($request);
        return $secretaire;
    }


    public function destroy(int $id)
    {
        Secretaire::destroy($id);
    }
}
