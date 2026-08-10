@extends('layouts.app')
@section('titulo', '- Importar libros')
@section('contenido')
    @include('partials.biblioteca-virtual-crud-header', ['active' => 'libros'])

    <div data-animate-stagger>
        <x-page-header titulo="Importación masiva de libros"
            subtitulo="Carga varios libros a la vez desde un archivo CSV o JSON">
            <x-slot:actions>
                <a href="{{ route('libros.index') }}" class="btn btn-outline-secondary btn-sm">Volver</a>
            </x-slot:actions>
        </x-page-header>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="admin-table-card p-4 h-100">
                    <h2 class="h6 font-weight-bold mb-1">Subir archivo de datos</h2>
                    <p class="text-muted small">Selecciona un archivo JSON o CSV con los libros organizados por
                        secciones y categorías.</p>

                    <form action="{{ route('libros.import.process') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group my-4">
                            <label for="archivo" class="font-weight-bold">Archivo (JSON, CSV, TXT):</label>
                            <div class="custom-file">
                                <input type="file" name="archivo" id="archivo" class="custom-file-input"
                                    accept=".json,.csv,.txt" required>
                                <label class="custom-file-label" for="archivo" data-browse="Buscar">Elegir
                                    archivo...</label>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fa fa-upload mr-2"></i> Procesar importación
                        </button>
                    </form>
                </div>
            </div>

            <div class="col-md-6 mb-4">
                <div class="admin-table-card p-4 h-100">
                    <h2 class="h6 font-weight-bold mb-3">Instrucciones de formato</h2>

                    <ul class="nav nav-tabs" id="formatTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active py-1" id="csv-tab" data-toggle="tab" href="#csvFormat"
                                role="tab">CSV / Excel</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link py-1" id="json-tab" data-toggle="tab" href="#jsonFormat" role="tab">JSON</a>
                        </li>
                    </ul>

                    <div class="tab-content mt-3" id="formatTabsContent">
                        <div class="tab-pane fade show active" id="csvFormat" role="tabpanel">
                            <p class="small text-muted">El archivo CSV puede usar coma (<code>,</code>) o punto y coma
                                (<code>;</code>) como delimitador. Debe incluir una cabecera con nombres similares a:</p>
                            <pre class="bg-dark text-white p-2 rounded small" style="font-size: 0.75rem;">seccion;categoria;nombre;autor;identificador
Libros;Literatura;Don Quijote;Miguel de Cervantes;1aBcDe...
Catálogo;Pedagogía;Guía Curricular;Ministerio;https://drive.google.com/file/d/...</pre>
                            <small class="text-info d-block mt-2">
                                <i class="fa fa-info-circle"></i> <strong>Extractor de Drive:</strong> Puedes usar el
                                script de Google Apps Script para exportar las carpetas de Drive directamente a Google
                                Sheets y descargarlo como CSV.
                            </small>
                        </div>

                        <div class="tab-pane fade" id="jsonFormat" role="tabpanel">
                            <p class="small text-muted">Estructura esperada del JSON:</p>
                            <pre class="bg-dark text-white p-2 rounded small" style="font-size: 0.75rem;">[
  {
    "seccion": "Libros",
    "categoria": "Literatura",
    "nombre": "Don Quijote de la Mancha",
    "autor": "Miguel de Cervantes",
    "identificador": "ID_DE_DRIVE_O_ENLACE"
  }
]</pre>
                            <small class="text-info d-block mt-2">
                                <i class="fa fa-info-circle"></i> <strong>Extractor de Google Sites:</strong> Puedes
                                pegar el script extractor de la consola en la antigua biblioteca de Google Sites para
                                generar este archivo automáticamente.
                            </small>
                        </div>
                    </div>

                    <hr>
                    <h3 class="h6 font-weight-bold text-muted small">Notas importantes:</h3>
                    <ul class="text-muted small pl-3 mb-0">
                        <li>Si la <strong>Sección</strong> o <strong>Categoría</strong> no existe en el sistema actual,
                            se creará de forma automática generando una URL amigable (slug).</li>
                        <li>Si el libro ya existe (según el identificador de Drive), se actualizarán su título y autor.
                        </li>
                        <li>El sistema limpia y extrae automáticamente el ID del archivo si se ingresa una URL completa
                            de Google Drive.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('custom-file-input')) {
                const fileName = e.target.files[0] ? e.target.files[0].name : "Elegir archivo...";
                const label = e.target.nextElementSibling;
                label.innerText = fileName;
            }
        });
    </script>
@endsection
