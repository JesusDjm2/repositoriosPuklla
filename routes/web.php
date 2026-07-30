<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\CancionesCategoriaController;
use App\Http\Controllers\CancionesController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\EnlacesController;
use App\Http\Controllers\KawsayCategoriaController;
use App\Http\Controllers\KawsayLibroController;
use App\Http\Controllers\KawsayVideoCategoriaController;
use App\Http\Controllers\KawsayVideoController;
use App\Http\Controllers\LibroController;
use App\Http\Controllers\ProyectoController;
use App\Http\Controllers\SeccionController;
use App\Http\Controllers\sisipedia\AportacionController;
use App\Http\Controllers\sisipedia\AudioStreamController;
use App\Http\Controllers\sisipedia\CategoryController;
use App\Http\Controllers\VideoCategoriaController;
use App\Http\Controllers\VideoController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/* Route::get('/', function () {
    return view('welcome'); })->name('index')->middleware('auth'); */
Route::get('/', [EnlacesController::class, 'hub'])->name('hub');
Route::get('/repositorio/sisipedia', [EnlacesController::class, 'index'])->name('index');
Route::get('/eesppukllasunchis', [EnlacesController::class, 'eesp'])->name('eesppukllasunchis');
Route::get('/kawsay', [EnlacesController::class, 'kawsay'])->name('kawsay');
Auth::routes();
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
Route::get('Administrador-Videos', [AdminController::class, 'videos'])->middleware('auth')->name('adminVideos');
Route::get('Administrador-Canciones', [AdminController::class, 'canciones'])->middleware('auth')->name('adminCanciones');
Route::get('Admini-Sisichakunay', [AdminController::class, 'sisicha'])->middleware('auth')->name('adminSisicha');

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

//Biblioteca
Route::resource('Secciones', SeccionController::class)->names('secciones')->middleware('auth');
Route::resource('Categorias', CategoriaController::class)->names('categorias')->except('show')->middleware('auth');
Route::get('Categorias/{Categoria}', [CategoriaController::class, 'show'])->name('categorias.show');
Route::get('Categoria/{id}/', [CategoriaController::class, 'detallesCat'])->name('detallesCat')->middleware('auth');
Route::resource('Libros', LibroController::class)->names('libros')->middleware('auth');

Route::get('libros/create/{categoria?}', [LibroController::class, 'create'])->name('libros.create')->middleware('auth');
Route::get('Lista-de-Libros', [LibroController::class, 'libros'])->name('libros');

//Videos
Route::resource('Videos', VideoController::class)->names('videos');
Route::resource('video-categorias', VideoCategoriaController::class)->names('videoscat')->except('show')->middleware('auth');
Route::get('video-categorias/{video_categoria}', [VideoCategoriaController::class, 'show'])->name('videoscat.show');
Route::get('videos-Puklla', [VideoCategoriaController::class, 'lista'])->name('videos');

//Kawsay
Route::resource('Kawsay-Categorias', KawsayCategoriaController::class)->names('kawsaycategorias')->except('show')->middleware('auth');
Route::get('Kawsay-Categorias/{categoria}', [KawsayCategoriaController::class, 'show'])->name('kawsaycategorias.show');
Route::resource('Kawsay-Libros', KawsayLibroController::class)->names('kawsaylibros')->middleware('auth');
Route::get('kawsay-libros/create/{categoria?}', [KawsayLibroController::class, 'create'])->name('kawsaylibros.create')->middleware('auth');
Route::get('Lista-de-Libros-Kawsay', [KawsayLibroController::class, 'libros'])->name('kawsaylibros.lista');

Route::resource('kawsay-video-categorias', KawsayVideoCategoriaController::class)->names('kawsayvideoscat')->except('show')->middleware('auth');
Route::get('kawsay-video-categorias/{video_categoria}', [KawsayVideoCategoriaController::class, 'show'])->name('kawsayvideoscat.show');
Route::resource('Kawsay-Videos', KawsayVideoController::class)->names('kawsayvideos')->middleware('auth');
Route::get('videos-Kawsay', [KawsayVideoCategoriaController::class, 'lista'])->name('kawsayvideos.lista');

//Canciones
Route::resource('Categorias-canciones', CancionesCategoriaController::class)->names('cancionescat')->middleware('auth');
Route::resource('canciones', CancionesController::class)->names('canciones')->middleware('auth');
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
    Route::middleware('auth')->group(function () {
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
    Route::middleware('auth')->group(function () {
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

    // Detalle público al final (wildcard {category} no captura rutas fijas de arriba)
    Route::get('categories/{category}', [CategoryController::class, 'show'])
        ->name('categories.show');
});

Route::get('sisipedia/registros', [CategoryController::class, 'registros'])->name('public.sisi');
Route::get('sisipedia/categorias', [CategoryController::class, 'publicIndex'])->name('public.categoria.sisi');
Route::get('sisipedia/search', [CategoryController::class, 'search'])->name('sisipedia.search');

Route::get('/limpiar-cache', function () {
    Artisan::call('cache:clear');
    Artisan::call('view:clear');
    Artisan::call('config:clear');
    Artisan::call('route:clear');
    return 'Cache limpiada correctamente';
});
// ===== BIBLIOTECA FISICA =====  
  
Route::middleware(['auth', 'role:admin|biblioteca'])->prefix('biblioteca-fisica')->name('biblioteca-fisica.')->group(function () {  
    Route::resource('categorias', App\Http\Controllers\CategoriaFisicaController::class)->except(['show'])->parameters(['categorias' => 'categoria_fisica']);  
    Route::resource('libros', App\Http\Controllers\LibroFisicoController::class)->except(['show'])->parameters(['libros' => 'libro_fisico']);  
    Route::get('libros-buscar-ajax', [App\Http\Controllers\LibroFisicoController::class, 'buscarAjax'])->name('libros.buscar-ajax');  
    Route::resource('prestamos', App\Http\Controllers\PrestamoController::class)->except(['show']);  
    Route::patch('prestamos/{prestamo}/devolver', [App\Http\Controllers\PrestamoController::class, 'devolver'])->name('prestamos.devolver');  
});  
  
Route::middleware(['auth'])->group(function () {  
    Route::get('libros-importar', [App\Http\Controllers\LibroController::class, 'importForm'])->name('libros.import.form');  
    Route::post('libros-importar', [App\Http\Controllers\LibroController::class, 'importProcess'])->name('libros.import.process');  
    Route::get('videos-importar', [App\Http\Controllers\VideoController::class, 'importForm'])->name('videos.import.form');  
    Route::post('videos-importar', [App\Http\Controllers\VideoController::class, 'importProcess'])->name('videos.import.process');  
});  
  
Route::get('catalogo-fisico', [App\Http\Controllers\LibroFisicoController::class, 'catalogo'])->name('catalogo-fisico');  
Route::get('api/codigo-fisico/sugerir', [App\Http\Controllers\LibroFisicoController::class, 'sugerirCodigo'])->name('libros-fisicos.sugerir-codigo')->middleware('auth'); 

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
