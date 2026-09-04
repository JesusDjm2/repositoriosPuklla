<?php

namespace App\Models;

use App\Services\GoogleDriveService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KawsayArchivo extends Model
{
    protected $fillable = ['kawsay_tema_id', 'tipo_contenido', 'tipo', 'titulo', 'drive_id', 'nombre_original', 'peso_mb'];

    public function tema(): BelongsTo
    {
        return $this->belongsTo(KawsayTema::class, 'kawsay_tema_id');
    }

    public function getUrl(): string
    {
        return GoogleDriveService::getUrl($this->drive_id);
    }

    public function getPreviewUrl(): string
    {
        return GoogleDriveService::getPreviewUrl($this->drive_id);
    }
}
