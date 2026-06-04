<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PetugasRbm extends Model
{
    protected $table = 'petugas_rbm';

    protected $fillable = [
        'petugas_id',
        'kode_rbm'
    ];

    public function petugas()
    {
        return $this->belongsTo(Petugas::class, 'petugas_id');
    }
}
