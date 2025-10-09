<?php

namespace App\Services;

use App\Models\Consultation;

class ConsultationService
{
    public function index()
    {
        $consultation = Consultation::all();
        return $consultation;
    }


    public function store(array $request)
    {

        $consultation = Consultation::create($request);
        return $consultation;
    }


    public function show(string $id)
    {
        return Consultation::find($id);
    }


    public function update(array $request, string $id)
    {
        $consultation = $this->show($id);
        $consultation->update($request);
        return $consultation;
    }


    public function destroy(int $id)
    {
        Consultation::destroy($id);
    }
}
