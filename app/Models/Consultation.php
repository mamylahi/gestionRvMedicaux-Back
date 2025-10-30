<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Consultation extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'date_consultation' => 'date',
    ];

    public function rendezvous()
    {
        return $this->belongsTo(RendezVous::class, 'rendezvous_id');
    }

    public function compteRendu()
    {
        return $this->hasOne(CompteRendu::class);
    }

    public function paiement()
    {
        return $this->hasOne(Paiement::class);
    }
}
