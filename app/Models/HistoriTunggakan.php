<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistoriTunggakan extends Model
{
    protected $table = 'histori_tunggakan';
    
    // Secara default primary key Laravel adalah 'id'
    protected $primaryKey = 'id';
    
    public $timestamps = true;

    protected $fillable = [
        'idpel',
        'periode',
    ];

    /**
     * Relasi balik (belongsTo) ke model Pelanggan
     */
    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'idpel', 'idpel');
    }
}
