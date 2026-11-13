<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\CancionesCategoriaController;
use App\Http\Controllers\CancionesController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\CategoriaFisicaController;
use App\Http\Controllers\LibroFisicoController;
use App\Http\Controllers\PrestamoController;
use App\Http\Controllers\CategoriaMaterialDidacticoController;
use App\Http\Controllers\EnlacesController;
use App\Http\Controllers\KawsayArchivoController;
use App\Http\Controllers\KawsayAreaAdminController;
use App\Http\Controllers\KawsayAreaController;
use App\Http\Controllers\KawsayBusquedaController;
use App\Http\Controllers\KawsayCategoriaController;
use App\Http\Controllers\KawsayComentarioController;
use App\Http\Controllers\KawsayLibroController;
use App\Http\Controllers\KawsayTemaController;
use App\Http\Controllers\KawsayVideoCategoriaController;
use App\Http\Controllers\KawsayVideoController;
use App\Http\Controllers\LibroController;
use App\Http\Controllers\MaterialDidacticoController;
use App\Http\Controllers\ProyectoController;
use App\Http\Controllers\SeccionController;
use App\Http\Controllers\sisipedia\AportacionController;
use App\Http\Controllers\sisipedia\AudioStreamController;
use App\Http\Controllers\sisipedia\CategoryController;
use App\Http\Controllers\TaqeCategoriaAdminController;
use App\Http\Controllers\TaqeController;
use App\Http\Controllers\TaqeTrabajoAdminController;
use App\Http\Controllers\VideoCategoriaController;
use App\Http\Controllers\VideoController;
use App\Http\Controllers\VideoStreamController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/* Route::get('/', function () {
    return view('welcome'); })->name('index')->middleware('auth'); */
Route::get('/', [EnlacesController::class, 'hub'])->name('hub');
Route::get('/repositorio/sisipedia', [EnlacesController::class, 'index'])->name('index');
Route::get('/eesppukllasunchis', [EnlacesController::class, 'eesp'])->name('eesppukllasunchis');
Route::get('/kawsay', [EnlacesController::class, 'kawsay'])->name('kawsay');
Route::get('/kawsay/nosotros', fn () => view('kawsay.nosotros'))->name('kawsay.nosotros');
Route::get('/kawsay/contacto', fn () => view('kawsay.contacto'))->name('kawsay.contacto');
// Streaming de video de Kawsay (proxy a Drive con soporte de Range). Ruta fuera de
// /kawsay/... para no chocar con los wildcards /kawsay/{area}/{tema}/{tipo} de abajo.
Route::get('kawsay-video/{archivo}/stream', [VideoStreamController::class, 'kawsayArchivo'])->name('kawsay.archivo.video.stream');
// Contador de vistas de un archivo de Kawsay (lo llama la portada/tema al abrir un video, PDF o foto).
Route::post('kawsay-archivo/{archivo}/vista', [KawsayArchivoController::class, 'vista'])->name('kawsay.archivo.vista')->middleware('throttle:60,1');
Route::get('kawsay-videos-catalogo/{kawsayVideo}/stream', [VideoStreamController::class, 'kawsayVideo'])->name('kawsayvideos.stream');
Route::get('/kawsay/buscar', [KawsayBusquedaController::class, 'buscar'])->name('kawsay.buscar')->middleware('throttle:60,1');
Route::get('/kawsay/{area}', [KawsayAreaController::class, 'area'])->name('kawsay.area');
Route::get('/kawsay/{area}/{tema}', [KawsayAreaController::class, 'tema'])->name('kawsay.tema');
Route::post('/kawsay/{area}/{tema}/comentarios', [KawsayAreaController::class, 'comentarioStore'])->name('kawsay.tema.comentarios.store')->middleware('throttle:20,1');
Route::get('/kawsay/{area}/{tema}/{tipo}', [KawsayAreaController::class, 'contenido'])->name('kawsay.contenido');
Auth::routes(['register' => false]);
/* Route::get('administrador', [EnlacesController::class, 'admin'])->name('administrador')->middleware('auth'); */
Route::resource('administradores', AdminController::class)->names('admin')->middleware('auth');
Route::get('admin/principal', [AdminController::class, 'principal'])
    ->middleware(['auth', 'role:admin'])
    ->name('admin.principal');
Route::resource('admin/proyectos', ProyectoController::class)
    ->names('proyectos')
    ->except(['show'])
    ->middleware(['auth', 'role:admin']);
Route::get('Lista-de-alumnos', [AdminController::class, 'alumnos'])->name('alumnos')->middleware('auth');
Route::get('Bibliotecario', [AdminController::class, 'bibliotecario'])->middleware('auth')->name('bibliotecario');
Route::get('Administrador-Kawsay', [AdminController::class, 'kawsay'])->middleware('auth')->name('adminKawsay');
Route::get('Administrador-Videos', [AdminController::class, 'videos'])->middleware(['auth', 'role:admin|videos'])->name('adminVideos');
Route::get('Administrador-Canciones', [AdminController::class, 'canciones'])->middleware(['auth', 'role:admin|audios'])->name('adminCanciones');
Route::get('Admini-Sisichakunay', [AdminController::class, 'sisicha'])->middleware(['auth', 'role:admin|sisicha'])->name('adminSisicha');
Route::get('Administrador-Radio', [AdminController::class, 'radio'])->middleware(['auth', 'role:admin|sisicha'])->name('adminRadio');

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

//Biblioteca
Route::resource('Secciones', SeccionController::class)->names('secciones')->middleware(['auth', 'role:admin|biblioteca']);
Route::resource('Categorias', CategoriaController::class)->names('categorias')->except('show')->middleware(['auth', 'role:admin|biblioteca']);
Route::get('Categorias/{Categoria}', [CategoriaController::class, 'show'])->name('categorias.show');
Route::get('Categoria/{id}/', [CategoriaController::class, 'detallesCat'])->name('detallesCat')->middleware(['auth', 'role:admin|biblioteca']);
Route::resource('Libros', LibroController::class)->names('libros')->middleware(['auth', 'role:admin|biblioteca']);

Route::get('libros/create/{categoria?}', [LibroController::class, 'create'])->name('libros.create')->middleware(['auth', 'role:admin|biblioteca']);
Route::get('Lista-de-Libros', [LibroController::class, 'libros'])->name('libros');

//Videos
Route::resource('Videos', VideoController::class)->names('videos')->middleware(['auth', 'role:admin|videos|sisicha']);
Route::resource('video-categorias', VideoCategoriaController::class)->names('videoscat')->except('show')->middleware(['auth', 'role:admin|videos|sisicha']);
Route::get('video-categorias/{video_categoria}', [VideoCategoriaController::class, 'show'])->name('videoscat.show');
Route::get('videos-Puklla', [VideoCategoriaController::class, 'lista'])->name('videos');
Route::get('videos/{video}/stream', [VideoStreamController::class, 'video'])->name('videos.stream');

//Kawsay
Route::resource('Kawsay-Categorias', KawsayCategoriaController::class)->names('kawsaycategorias')->except('show')->middleware(['auth', 'role:admin|kawsay']);
Route::get('Kawsay-Categorias/{categoria}', [KawsayCategoriaController::class, 'show'])->name('kawsaycategorias.show');
Route::resource('Kawsay-Libros', KawsayLibroController::class)->names('kawsaylibros')->middleware(['auth', 'role:admin|kawsay']);
Route::get('kawsay-libros/create/{categoria?}', [KawsayLibroController::class, 'create'])->name('kawsaylibros.create')->middleware(['auth', 'role:admin|kawsay']);
Route::get('Lista-de-Libros-Kawsay', [KawsayLibroController::class, 'libros'])->name('kawsaylibros.lista');

Route::resource('kawsay-video-categorias', KawsayVideoCategoriaController::class)->names('kawsayvideoscat')->except('show')->middleware(['auth', 'role:admin|kawsay']);
Route::get('kawsay-video-categorias/{video_categoria}', [KawsayVideoCategoriaController::class, 'show'])->name('kawsayvideoscat.show');
Route::resource('Kawsay-Videos', KawsayVideoController::class)->names('kawsayvideos')->middleware(['auth', 'role:admin|kawsay']);
Route::get('videos-Kawsay', [KawsayVideoCategoriaController::class, 'lista'])->name('kawsayvideos.lista');

Route::get('Kawsay-Archivos', [KawsayArchivoController::class, 'index'])->name('kawsayarchivos.index')->middleware(['auth', 'role:admin|kawsay']);
Route::post('Kawsay-Archivos', [KawsayArchivoController::class, 'store'])->name('kawsayarchivos.store')->middleware(['auth', 'role:admin|kawsay']);
Route::delete('Kawsay-Archivos/{archivo}', [KawsayArchivoController::class, 'destroy'])->name('kawsayarchivos.destroy')->middleware(['auth', 'role:admin|kawsay']);
Route::post('Kawsay-Archivos/{archivo}/portada', [KawsayArchivoController::class, 'portada'])->name('kawsayarchivos.portada')->middleware(['auth', 'role:admin|kawsay']);
Route::delete('Kawsay-Archivos/{archivo}/portada', [KawsayArchivoController::class, 'quitarPortada'])->name('kawsayarchivos.portada.destroy')->middleware(['auth', 'role:admin|kawsay']);
Route::post('Kawsay-Archivos/youtube', [KawsayArchivoController::class, 'youtube'])->name('kawsayarchivos.youtube')->middleware(['auth', 'role:admin|kawsay']);
Route::patch('Kawsay-Archivos/{archivo}', [KawsayArchivoController::class, 'update'])->name('kawsayarchivos.update')->middleware(['auth', 'role:admin|kawsay']);

Route::resource('Kawsay-Areas', KawsayAreaAdminController::class)->names('kawsayareas')->parameters(['Kawsay-Areas' => 'area'])->except('show')->middleware(['auth', 'role:admin|kawsay']);

Route::resource('Kawsay-Temas', KawsayTemaController::class)->names('kawsaytemas')->parameters(['Kawsay-Temas' => 'tema'])->except('show')->middleware(['auth', 'role:admin|kawsay']);
Route::post('Kawsay-Temas/{tema}/destacar', [KawsayTemaController::class, 'destacar'])->name('kawsaytemas.destacar')->middleware(['auth', 'role:admin|kawsay']);
Route::delete('Kawsay-Temas/{tema}/comentarios/{comentario}', [KawsayTemaController::class, 'destroyComentario'])->name('kawsaytemas.comentarios.destroy')->middleware(['auth', 'role:admin|kawsay']);

Route::get('Kawsay-Comentarios', [KawsayComentarioController::class, 'index'])->name('kawsaycomentarios.index')->middleware(['auth', 'role:admin|kawsay']);
Route::delete('Kawsay-Comentarios/masivo', [KawsayComentarioController::class, 'destroyMasivo'])->name('kawsaycomentarios.destroyMasivo')->middleware(['auth', 'role:admin|kawsay']);
Route::delete('Kawsay-Comentarios/{comentario}', [KawsayComentarioController::class, 'destroy'])->name('kawsaycomentarios.destroy')->middleware(['auth', 'role:admin|kawsay']);

Route::get('Administrador-Backups', [BackupController::class, 'index'])->name('backups.index')->middleware(['auth', 'role:admin']);
Route::post('Administrador-Backups', [BackupController::class, 'store'])->name('backups.store')->middleware(['auth', 'role:admin']);
Route::get('Administrador-Backups/{nombre}/descargar', [BackupController::class, 'download'])->name('backups.download')->middleware(['auth', 'role:admin']);
Route::delete('Administrador-Backups/{nombre}', [BackupController::class, 'destroy'])->name('backups.destroy')->middleware(['auth', 'role:admin']);

//Canciones
Route::resource('Categorias-canciones', CancionesCategoriaController::class)->names('cancionescat')->middleware(['auth', 'role:admin|audios|sisicha']);
Route::resource('canciones', CancionesController::class)->names('canciones')->middleware(['auth', 'role:admin|audios|sisicha']);
Route::get('canciones-Puklla', [CancionesController::class, 'canciones'])->name('cancionesPuklla')->middleware('auth');
Route::get('canciones/{cancion}/stream', [AudioStreamController::class, 'cancion'])->name('canciones.stream');

Route::get('resultados-de-busqueda', [EnlacesController::class, 'busqueda'])->name('busqueda');

Route::middleware('auth')->group(function () {
    Route::resource('categories', CategoryController::class);
    Route::get('categories/tree', [CategoryController::class, 'tree'])->name('categories.tree');
    Route::post('categories/update-order', [CategoryController::class, 'updateOrder'])->name('categories.update-order');
});

//Sisipedia
Route::prefix('sisipedia')->name('sisipedia.')->group(function () {
    // Admin primero (rutas fijas como /create, /reorder deben resolverse antes que {category})
    Route::middleware(['auth', 'role:admin|sisicha'])->group(function () {
        Route::get('categories/export', [CategoryController::class, 'export'])
            ->name('categories.export');
        Route::resource('categories', CategoryController::class)->except(['show']);
        Route::get('categories/{category}/admin', [CategoryController::class, 'adminShow'])
            ->name('categories.admin-show');
        Route::get('categories/{category}/children', [CategoryController::class, 'getChildren'])
            ->name('categories.children');
        Route::post('categories/reorder', [CategoryController::class, 'reorder'])
            ->name('categories.reorder');
        Route::post('categories/{category}/toggle-status', [CategoryController::class, 'toggleStatus'])
            ->name('categories.toggle-status');
        Route::delete('categories/{category}/files/{file}', [CategoryController::class, 'destroyFile'])
            ->name('categories.files.destroy');
    });

    // Aportaciones
    Route::middleware(['auth', 'role:admin|sisicha'])->group(function () {
        Route::get('aportaciones', [AportacionController::class, 'adminIndex'])
            ->name('aportaciones.index');
    });
    // Crear aportación: público (cualquier visitante puede aportar)
    Route::post('categories/{category}/aportaciones', [AportacionController::class, 'store'])
        ->name('categories.aportaciones.store');

    Route::post('aportaciones/general', [AportacionController::class, 'storeStandalone'])
        ->name('aportaciones.general.store');

    // Eliminar: solo admin o sisicha
    Route::middleware(['auth', 'role:admin|sisicha'])
        ->delete('categories/{category}/aportaciones/{aportacion}', [AportacionController::class, 'destroy'])
        ->name('categories.aportaciones.destroy');

    Route::middleware(['auth', 'role:admin|sisicha'])->group(function () {
        Route::post('aportaciones/{aportacion}/approve', [AportacionController::class, 'approve'])
            ->name('aportaciones.approve');
        Route::patch('aportaciones/{aportacion}', [AportacionController::class, 'update'])
            ->name('aportaciones.update');
        Route::delete('aportaciones/{aportacion}', [AportacionController::class, 'destroyStandalone'])
            ->name('aportaciones.destroy');
    });

    // Streaming de audio (proxy a Google Drive con soporte de Range para poder adelantar/atrasar)
    Route::get('audio/{categoryFile}/stream', [AudioStreamController::class, 'categoryFile'])
        ->name('audio.stream');
    Route::get('audio/aportacion/{aportacionArchivo}/stream', [AudioStreamController::class, 'aportacionArchivo'])
        ->name('audio.aportacion.stream');
    Route::get('audio/aportacion-directa/{aportacion}/stream', [AudioStreamController::class, 'aportacionSingle'])
        ->name('audio.aportacion-directa.stream');

    // Streaming de video (mismo proxy a Google Drive con soporte de Range)
    Route::get('video/{categoryFile}/stream', [VideoStreamController::class, 'categoryFile'])
        ->name('video.stream');
    Route::get('video/aportacion/{aportacionArchivo}/stream', [VideoStreamController::class, 'aportacionArchivo'])
        ->name('video.aportacion.stream');

    // Descargas contadas (suman y redirigen a Google Drive)
    Route::get('descargar/material/{categoryFile}', [\App\Http\Controllers\sisipedia\DescargaController::class, 'material'])
        ->middleware('throttle:60,1')->name('descargar.material');
    Route::get('descargar/aporte/{aportacionArchivo}', [\App\Http\Controllers\sisipedia\DescargaController::class, 'aporte'])
        ->middleware('throttle:60,1')->name('descargar.aporte');

    // Detalle público al final (wildcard {category} no captura rutas fijas de arriba)
    Route::get('categories/{category}', [CategoryController::class, 'show'])
        ->name('categories.show');
});

Route::get('sisipedia/registros', [CategoryController::class, 'registros'])->name('public.sisi');
Route::get('sisipedia/categorias', [CategoryController::class, 'publicIndex'])->name('public.categoria.sisi');
Route::get('sisipedia/search', [CategoryController::class, 'search'])->name('sisipedia.search');

// Búsqueda propia de Sisipedia (la global de la Asociación es la del pie de página).
Route::get('sisipedia/buscar', [\App\Http\Controllers\sisipedia\BusquedaController::class, 'index'])
    ->middleware('throttle:60,1')->name('sisipedia.buscar');
Route::get('sisipedia/buscar/sugerencias', [\App\Http\Controllers\sisipedia\BusquedaController::class, 'sugerencias'])
    ->middleware('throttle:120,1')->name('sisipedia.buscar.sugerencias');
Route::get('sisipedia/busquedas-sin-resultados', [\App\Http\Controllers\sisipedia\BusquedaController::class, 'sinResultados'])
    ->middleware(['auth', 'role:admin|sisicha'])->name('sisipedia.busquedas.sin-resultados');
// Árbol de registros: los niveles profundos se cargan al abrirlos (ver sisichakuna.tree).
Route::get('sisipedia/registros/{category}/hijos', [CategoryController::class, 'hijos'])->name('sisipedia.registros.hijos');

// Páginas estáticas de Sisipedia (menú simplificado: Inicio/Nosotros/Contáctanos).
Route::get('sisipedia/nosotros', fn () => view('sisichakuna.nosotros'))->name('sisipedia.nosotros');
Route::get('sisipedia/contacto', fn () => view('sisichakuna.contacto'))->name('sisipedia.contacto');

Route::get('/limpiar-cache', function () {
    Artisan::call('cache:clear');
    Artisan::call('view:clear');
    Artisan::call('config:clear');
    Artisan::call('route:clear');
    return 'Cache limpiada correctamente';
});
// ===== BIBLIOTECA FISICA =====  
  
Route::middleware(['auth', 'role:admin|biblioteca'])->prefix('biblioteca-fisica')->name('biblioteca-fisica.')->group(function () {  
    Route::resource('categorias', CategoriaFisicaController::class)->except(['show'])->parameters(['categorias' => 'categoria_fisica']);  
    Route::resource('libros', LibroFisicoController::class)->except(['show'])->parameters(['libros' => 'libro_fisico']);  
    Route::get('libros-buscar-ajax', [LibroFisicoController::class, 'buscarAjax'])->name('libros.buscar-ajax');  
    Route::resource('prestamos', PrestamoController::class)->except(['show']);  
    Route::patch('prestamos/{prestamo}/devolver', [PrestamoController::class, 'devolver'])->name('prestamos.devolver');  
    Route::resource('material-categorias', CategoriaMaterialDidacticoController::class)->except(['show'])
        ->parameters(['material-categorias' => 'material_categoria']);
    Route::resource('materiales', MaterialDidacticoController::class)->except(['show'])
        ->parameters(['materiales' => 'material']);
    Route::get('materiales-buscar-ajax', [MaterialDidacticoController::class, 'buscarAjax'])
        ->name('materiales.buscar-ajax');
});  

// ===== TAQ'E — INVESTIGACIONES Y TESIS EESP =====
Route::middleware(['auth', 'role:admin|biblioteca'])->prefix('admin/taqe')->name('admin.taqe.')->group(function () {
    Route::resource('trabajos', TaqeTrabajoAdminController::class)->except(['show']);
    Route::resource('categorias', TaqeCategoriaAdminController::class)->except(['show'])
        ->parameters(['categorias' => 'taqe_categoria']);
});

Route::get('taqe', [TaqeController::class, 'index'])->name('taqe.index');
Route::get('taqe/{slug}', [TaqeController::class, 'show'])->name('taqe.show');
Route::get('taqe/{slug}/descargar', [TaqeController::class, 'descargar'])->name('taqe.descargar');  
  
Route::middleware(['auth', 'role:admin|biblioteca'])->group(function () {
    Route::get('libros-importar', [App\Http\Controllers\LibroController::class, 'importForm'])->name('libros.import.form');
    Route::post('libros-importar', [App\Http\Controllers\LibroController::class, 'importProcess'])->name('libros.import.process');
});

Route::middleware(['auth', 'role:admin|videos'])->group(function () {
    Route::get('videos-importar', [App\Http\Controllers\VideoController::class, 'importForm'])->name('videos.import.form');
    Route::post('videos-importar', [App\Http\Controllers\VideoController::class, 'importProcess'])->name('videos.import.process');
});
  
Route::get('catalogo-fisico', [App\Http\Controllers\LibroFisicoController::class, 'catalogo'])->name('catalogo-fisico');  
Route::get('api/codigo-fisico/sugerir', [App\Http\Controllers\LibroFisicoController::class, 'sugerirCodigo'])->name('libros-fisicos.sugerir-codigo')->middleware('auth');

Auth::routes(['register' => false]);

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
