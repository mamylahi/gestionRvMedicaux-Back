<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Consultation extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function rendezvous()
    {
        return $this->belongsTo(Rendezvous::class);
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
