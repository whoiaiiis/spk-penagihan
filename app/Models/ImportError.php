<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportError extends Model
{
    protected $table = 'import_errors';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'import_log_id',
        'row_number',
        'row_data',
        'error_message',
        'error_type',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function importLog()
    {
        return $this->belongsTo(ImportLog::class);
    }
}
