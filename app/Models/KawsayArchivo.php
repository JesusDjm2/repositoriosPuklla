<?php

namespace App\Models;

use App\Services\GoogleDriveService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Archivo de un tema de Kawsay. Normalmente vive en Google Drive (drive_id);
 * los videos también pueden ser un enlace de YouTube (youtube_id, con drive_id vacío).
 */
class KawsayArchivo extends Model
{
    protected $fillable = [
        'kawsay_tema_id', 'tipo_contenido', 'tipo', 'titulo', 'descripcion', 'nivel',
        'drive_id', 'youtube_id', 'nombre_original', 'peso_mb', 'portada',
    ];

    protected $casts = ['vistas' => 'integer'];

    public function esYoutube(): bool
    {
        return ! empty($this->youtube_id);
    }

    /** Imagen para mostrar el archivo: portada propia, miniatura de YouTube o de Drive. */
    public function miniaturaUrl(int $ancho = 640): string
    {
        if ($this->portada) {
            return asset($this->portada);
        }
        if ($this->esYoutube()) {
            return "https://i.ytimg.com/vi/{$this->youtube_id}/" . ($ancho > 400 ? 'hqdefault' : 'mqdefault') . '.jpg';
        }

        return GoogleDriveService::getThumbnailUrl($this->drive_id, $ancho);
    }

    public function tema(): BelongsTo
    {
        return $this->belongsTo(KawsayTema::class, 'kawsay_tema_id');
    }

    /** Enlace para abrir el archivo fuera del sitio (Drive o YouTube). */
    public function getUrl(): string
    {
        return $this->esYoutube()
            ? "https://www.youtube.com/watch?v={$this->youtube_id}"
            : GoogleDriveService::getUrl($this->drive_id);
    }

    public function getPreviewUrl(): string
    {
        return GoogleDriveService::getPreviewUrl($this->drive_id);
    }

    /**
     * ID de un video a partir de cualquier enlace de YouTube (watch?v=, youtu.be/,
     * shorts/, embed/, live/) o del ID suelto. null si no es reconocible.
     */
    public static function youtubeIdDe(string $enlace): ?string
    {
        $enlace = trim($enlace);
        if (preg_match('/^[A-Za-z0-9_-]{11}$/', $enlace)) {
            return $enlace;
        }
        $patron = '~(?:youtube\.com/(?:watch\?(?:.*&)?v=|shorts/|embed/|live/|v/)|youtu\.be/)([A-Za-z0-9_-]{11})~i';

        return preg_match($patron, $enlace, $m) ? $m[1] : null;
    }
}
