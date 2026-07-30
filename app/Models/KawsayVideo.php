<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KawsayVideo extends Model
{
    use HasFactory;

    protected $fillable = ['nombre', 'descripcion', 'youtube', 'drive', 'categoria_id'];

    public function categoria()
    {
        return $this->belongsTo(KawsayVideoCategoria::class, 'categoria_id');
    }
}
