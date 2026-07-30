<?php

namespace App\Models\sisipedia;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AportacionArchivo extends Model
{
    protected $table = 'aportacion_archivos';

    protected $fillable = [
        'aportacion_id',
        'tipo',
        'drive_id',
        'nombre_original',
        'peso_mb',
    ];

    public function aportacion(): BelongsTo
    {
        return $this->belongsTo(Aportacion::class);
    }

    /**
     * Obtiene la URL de descarga desde Google Drive.
     */
    public function getUrl(): string
    {
        return \App\Services\GoogleDriveService::getUrl($this->drive_id);
    }

    /**
     * Obtiene la URL de previsualización desde Google Drive.
     */
    public function getPreviewUrl(): string
    {
        return \App\Services\GoogleDriveService::getPreviewUrl($this->drive_id);
    }
}
