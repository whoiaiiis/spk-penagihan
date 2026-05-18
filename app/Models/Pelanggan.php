<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pelanggan extends Model
{
    protected $table = 'pelanggan';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'idpel',
        'nama',
        'daya',
        'lembar',
        'rptag',
        'rpbk',
        'status_penagihan',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'daya' => 'integer',
        'lembar' => 'integer',
        'rptag' => 'float',
        'rpbk' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Scope: search by idpel or nama
     */
    public function scopeSearch($query, $search)
    {
        if (empty($search)) {
            return $query;
        }

        return $query->where('idpel', 'LIKE', "%{$search}%")
            ->orWhere('nama', 'LIKE', "%{$search}%");
    }

    /**
     * Scope: filter by daya range
     */
    public function scopeByDayaRange($query, $minDaya, $maxDaya)
    {
        if ($minDaya) {
            $query->where('daya', '>=', $minDaya);
        }

        if ($maxDaya) {
            $query->where('daya', '<=', $maxDaya);
        }

        return $query;
    }

    /**
     * Scope: filter by rptag range
     */
    public function scopeByRptagRange($query, $minRptag, $maxRptag)
    {
        if ($minRptag) {
            $query->where('rptag', '>=', $minRptag);
        }

        if ($maxRptag) {
            $query->where('rptag', '<=', $maxRptag);
        }

        return $query;
    }

    /**
     * Scope: filter by lembar
     */
    public function scopeByLembar($query, $lembar)
    {
        if (empty($lembar)) {
            return $query;
        }

        return $query->where('lembar', $lembar);
    }

    /**
     * Scope: sort
     */
    public function scopeOrderByDynamic($query, $sortBy = 'newest')
    {
        return match ($sortBy) {
            'oldest' => $query->orderBy('created_at', 'asc'),
            'nama_az' => $query->orderBy('nama', 'asc'),
            'nama_za' => $query->orderBy('nama', 'desc'),
            'daya_high' => $query->orderBy('daya', 'desc'),
            'daya_low' => $query->orderBy('daya', 'asc'),
            'rptag_high' => $query->orderBy('rptag', 'desc'),
            'rptag_low' => $query->orderBy('rptag', 'asc'),
            default => $query->orderBy('created_at', 'desc'),
        };
    }
}
