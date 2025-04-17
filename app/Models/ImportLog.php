<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImportLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'upload_id',
        'product_id',
        'status',
        'operation',
        'message'
    ];

    public function upload()
    {
        return $this->belongsTo(Upload::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
