<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Paiement extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function consultation()
    {
        return $this->belongsTo(Consultation::class);
    }
}
