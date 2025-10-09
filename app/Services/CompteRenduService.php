<?php

namespace App\Services;

use App\Models\CompteRendu;

class CompteRenduService
{
    public function index()
    {
        $compteRendu = CompteRendu::all();
        return $compteRendu;
    }


    public function store(array $request)
    {

        $compteRendu = CompteRendu::create($request);
        return $compteRendu;
    }


    public function show(string $id)
    {
        return CompteRendu::find($id);
    }


    public function update(array $request, string $id)
    {
        $compteRendu = $this->show($id);
        $compteRendu->update($request);
        return $compteRendu;
    }


    public function destroy(int $id)
    {
        CompteRendu::destroy($id);
    }
}
