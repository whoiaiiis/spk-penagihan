<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportLog extends Model
{
    protected $table = 'import_logs';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'type',
        'filename',
        'total_rows',
        'inserted',
        'updated',
        'failed',
        'imported_by',
        'import_date',
        'notes',
    ];

    protected $casts = [
        'total_rows' => 'integer',
        'inserted' => 'integer',
        'updated' => 'integer',
        'failed' => 'integer',
        'import_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
