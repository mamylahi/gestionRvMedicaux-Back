<?php

namespace App\Services;

use App\Models\Specialite;

class SpecialiteService
{
    public function index()
    {
        $specialite = Specialite::all();
        return $specialite;
    }


    public function store(array $request)
    {

        $specialite = Specialite::create($request);
        return $specialite;
    }


    public function show(string $id)
    {
        return Specialite::find($id);
    }


    public function update(array $request, string $id)
    {
        $specialite = $this->show($id);
        $specialite->update($request);
        return $specialite;
    }


    public function destroy(int $id)
    {
        Specialite::destroy($id);
    }
}
