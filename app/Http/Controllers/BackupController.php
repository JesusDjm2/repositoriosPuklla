<?php

namespace App\Http\Controllers;

use App\Services\DatabaseBackupService;

class BackupController extends Controller
{
    public function __construct(private DatabaseBackupService $backups)
    {
    }

    public function index()
    {
        $backups = $this->backups->listar();

        return view('admin.backups.index', compact('backups'));
    }

    public function store()
    {
        $this->backups->crear();

        return redirect()->route('backups.index')->with('success', 'Backup generado correctamente.');
    }

    public function download(string $nombre)
    {
        $ruta = $this->backups->rutaSegura($nombre);

        if (! $ruta || ! file_exists($ruta)) {
            abort(404);
        }

        return response()->download($ruta);
    }

    public function destroy(string $nombre)
    {
        $this->backups->eliminar($nombre);

        return redirect()->route('backups.index')->with('success', 'Backup eliminado.');
    }
}
