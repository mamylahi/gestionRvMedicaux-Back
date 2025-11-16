<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medecin extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function specialite()
    {
        return $this->belongsTo(Specialite::class);
    }


    public function disponibilites()
    {
        return $this->hasMany(Disponibilite::class);
    }

    public function rendezvous()
    {
        return $this->hasMany(RendezVous::class);
    }

    public function consultation()
    {
        return $this->hasMany(Consultation::class);
    }

}
