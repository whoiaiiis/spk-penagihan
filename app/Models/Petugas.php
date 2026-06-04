<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Petugas extends Model
{
    protected $table = 'petugas';
    
    // Asumsikan tabel petugas tidak menggunakan timestamps bawaan atau disesuaikan
    // $public $timestamps = false; // Jika tidak ada created_at/updated_at di tabel petugas
    
    protected $fillable = [
        'kode_petugas',
        'nama',
        'wilayah',
        'status'
    ];

    /**
     * Relasi ke Pelanggan menggunakan petugas_id sebagai foreign key
     */
    public function pelanggan()
    {
        return $this->hasMany(Pelanggan::class, 'petugas_id');
    }
}
