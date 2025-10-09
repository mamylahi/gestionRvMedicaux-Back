<?php

namespace App\Services;

use App\Models\Paiement;

class PaiementService
{
    public function index()
    {
        $paiement = Paiement::all();
        return $paiement;
    }


    public function store(array $request)
    {

        $paiement = Paiement::create($request);
        return $paiement;
    }


    public function show(string $id)
    {
        return Paiement::find($id);
    }


    public function update(array $request, string $id)
    {
        $paiement = $this->show($id);
        $paiement->update($request);
        return $paiement;
    }


    public function destroy(int $id)
    {
        Paiement::destroy($id);
    }
}
