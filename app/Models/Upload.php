<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Upload extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_name',
        'original_name',
        'total_rows',
        'processed_rows',
        'status',
    ];

    public function importLogs()
    {
        return $this->hasMany(ImportLog::class);
    }
}
