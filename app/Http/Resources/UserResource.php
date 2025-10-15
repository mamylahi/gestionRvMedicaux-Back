<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'=>$this->id,
            'nomComplet'=>$this->prenom. ' ' .$this->nom,
            'nom'=>$this->nom,
            'prenom'=>$this->prenom,
            'email'=>$this->email,
            'adresse'=>$this->adresse,
            'telephone'=>$this->telephone,
            'role'=>$this->role,
            'created_at'=>$this->created_at,
        ];
    }
}
