<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompteRendu extends Model
{
    use HasFactory;
    protected $table = 'compterendus';
    protected $guarded = [];
    protected $casts = [
        'date_creation' => 'datetime',
    ];

    public function consultation()
    {
        return $this->belongsTo(Consultation::class);
    }
}
