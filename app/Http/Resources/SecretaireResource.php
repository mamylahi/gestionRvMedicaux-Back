<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SecretaireResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'numero_employe'   => $this->numero_employe,
            'user'             => new UserResource($this->whenLoaded('user')), //charge la relation utilisateur seulement si elle est déjà chargée (évite les requêtes N+1)/
            'created_at'       => $this->created_at,
        ];
    }
}
