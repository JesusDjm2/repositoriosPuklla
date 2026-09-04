<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Carpetas globales por tipo de archivo
    |--------------------------------------------------------------------------
    |
    | Usadas por Sisipedia (categorías/aportaciones) hoy. Antes de este archivo
    | GoogleDriveService leía config('google-drive.folders') sin que existiera
    | ningún config/google-drive.php, así que siempre daba [] y todo subía sin
    | 'parents' (a la raíz de la cuenta de servicio).
    |
    */
    'folders' => [
        'pdf' => env('GOOGLE_DRIVE_FOLDER_PDF'),
        'audio' => env('GOOGLE_DRIVE_FOLDER_AUDIO'),
        'video' => env('GOOGLE_DRIVE_FOLDER_VIDEO'),
        'imagen' => env('GOOGLE_DRIVE_FOLDER_IMAGEN'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Carpetas por proyecto ("grupo")
    |--------------------------------------------------------------------------
    |
    | Un proyecto puede tener su propia carpeta en vez de compartir las
    | globales de arriba. GoogleDriveService::upload() recibe un $grupo
    | opcional que busca aquí antes de caer a 'folders'.
    |
    */
    'grupos' => [
        'kawsay' => [
            'pdf' => env('GOOGLE_DRIVE_FOLDER_KAWSAY_PDF'),
            'audio' => env('GOOGLE_DRIVE_FOLDER_KAWSAY_AUDIO'),
            'imagen' => env('GOOGLE_DRIVE_FOLDER_KAWSAY_IMAGEN'),
        ],
    ],

];
