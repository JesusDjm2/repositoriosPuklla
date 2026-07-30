<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KawsayVideoCategoria extends Model
{
    use HasFactory;

    protected $fillable = ['nombre', 'url'];

    public function videos()
    {
        return $this->hasMany(KawsayVideo::class, 'categoria_id');
    }
}
