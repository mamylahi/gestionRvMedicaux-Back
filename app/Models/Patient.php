<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class Patient extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function rendezvous()
    {
        return $this->hasMany(RendezVous::class);
    }

    public function dossierMedical()
    {
        return $this->hasOne(DossierMedical::class);
    }
}
