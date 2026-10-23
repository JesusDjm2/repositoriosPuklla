<?php

namespace Database\Seeders;

use App\Models\KawsayArchivo;
use App\Models\KawsayArea;
use App\Models\KawsayCategoria;
use App\Models\KawsayLibro;
use App\Models\KawsayTema;
use App\Models\KawsayTemaComentario;
use App\Models\KawsayVideo;
use App\Models\KawsayVideoCategoria;
use App\Services\GoogleDriveService;
use App\Services\KawsayTaxonomia;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * Contenido de PRUEBA para Kawsay con archivos reales (PDF, PPT, fotos, mapas y
 * videos) tomados de una carpeta local y subidos a la carpeta de Kawsay en
 * Drive, igual que lo haría el panel. Cada archivo se usa una sola vez.
 *
 *   php artisan db:seed --class=KawsayDemoSeeder
 *   php artisan tinker --execute="Database\Seeders\KawsayDemoSeeder::limpiar()"       (solo BD, deja Drive)
 *   php artisan tinker --execute="Database\Seeders\KawsayDemoSeeder::limpiar(true)"   (BD + Drive)
 *
 * Los IDs de Drive se guardan en storage/app/kawsay-demo-drive.json, así que
 * volver a correr el seeder no vuelve a subir nada. La carpeta de origen se
 * puede cambiar con KAWSAY_DEMO_DIR en el .env.
 */
class KawsayDemoSeeder extends Seeder
{
    private const ORIGEN = 'D:/Mis Documentos/Documentos/Pukllasunchis';

    private const CACHE = 'kawsay-demo-drive.json';

    private const IMAGENES_TEMAS = 'img/kawsay/temas/demo';

    /** tipo_contenido => [ruta relativa a ORIGEN => título] */
    private const ARCHIVOS = [
        'ficha' => [
            'Archivos Drive/Curso 4 - Primaria Investigacion e Innovación Educativa/Ficha1-ColegioPukllasunchis.pdf' => 'Ficha 1: Colegio Pukllasunchis',
            'Archivos Drive/Curso 4 - Primaria Investigacion e Innovación Educativa/Ficha2-ColegioKurmiWasi.pdf' => 'Ficha 2: Colegio Kurmi Wasi',
            'Archivos Drive/Curso 4 - Primaria Investigacion e Innovación Educativa/Ficha3-EscuelaApuTuspin.pdf' => 'Ficha 3: Escuela Apu Tuspin',
            'Archivos Drive/Curso 4 - Primaria Investigacion e Innovación Educativa/Ficha4-EscuelaDemocraticaHuamachuco.pdf' => 'Ficha 4: Escuela Democrática de Huamachuco',
            'Archivos Drive/Curso 4 - Primaria Investigacion e Innovación Educativa/Ficha5-EscuelaPublicaAmaraBerri.pdf' => 'Ficha 5: Escuela Pública Amara Berri',
            'Archivos Drive/Curso 5 - Planificacion/Semana 1/GUIA 1.pdf' => 'Guía de trabajo 1: Planificación curricular',
            'Archivos Drive/Curso 4 - Primaria Investigacion e Innovación Educativa/1ra Sem plantilla Investigacion-I.pdf' => 'Plantilla de investigación – Semana 1 (Primaria)',
            'Archivos Drive/Curso 6 - Inicial Investigacion e Innovación Educativa/1ra Sem plantilla INVESTIGACIÓN-INI.pdf' => 'Plantilla de investigación – Semana 1 (Inicial)',
            'Archivos Drive/Curso 6 - Inicial Investigacion e Innovación Educativa/2nda Sem plantilla INVESTIGACIÓN-INI.pdf' => 'Plantilla de investigación – Semana 2 (Inicial)',
            'Archivos Drive/Curso 2 - Infancia y Familia/1da Sem Plantilla Infancia Concep de infancia.pdf' => 'Plantilla: concepciones de infancia',
            'Silabos/silabo/Guia de investigacion 1.pdf' => 'Guía de investigación 1',
            'Silabos/silabo/Guia-de-Investigacion.pdf' => 'Guía de investigación',
            'Silabos/silabo/Guia-de-redaccion.pdf' => 'Guía de redacción académica',
        ],
        'teoria' => [
            'Archivos Drive/Curso 4 - Inicial Atencion a la diversidad/Inteligencias Múltiples.pdf' => 'Inteligencias múltiples',
            'Archivos Drive/Curso 5 - Planificacion/Semana 1/COMPONENTES CURRICULARES.pdf' => 'Componentes curriculares',
            'Archivos Drive/Curso 5 - Planificacion/Semana 1/Concepciones de Curriculo Nacional PPT.pdf' => 'Concepciones del Currículo Nacional',
            'Archivos Drive/Curso 5 - Planificacion/Semana 2/Enfoques transversales.pdf' => 'Enfoques transversales',
            'Archivos Drive/Curso 5 - Planificacion/Semana 2/EVALUACIÓN FORMTIVA.PDF' => 'Evaluación formativa',
            'Archivos Drive/Curso 5 - Planificacion/Semana 2/Perfil del estudiante.pdf' => 'Perfil de egreso del estudiante',
            'Archivos Drive/Curso 2 - Infancia y Familia/Materiales/Semana 1/Lectura Complementaria- Descubrimiento de la Infancia Aries.pdf' => 'El descubrimiento de la infancia (Ariès)',
            'Archivos Drive/Curso 2 - Infancia y Familia/Material de Clase INFANCIA Links to an external site. - Huchuy-Runa.pdf' => 'Huchuy Runa: la infancia andina',
            'PPD/Files PPD/Lectura Taller Agricultura cosmovisión completo.pdf' => 'Agricultura y cosmovisión andina',
            'Archivos Drive/Curso 4 - Primaria Investigacion e Innovación Educativa/Texto Taller 2 !Sin Barreras!.pdf' => '¡Sin barreras! Atención a la diversidad',
            'Redes/Artículos/Puntualidad en Perú.pdf' => 'La puntualidad en el Perú',
        ],
        'bibliografia' => [
            'Archivos Drive/Curso 4 - Primaria Investigacion e Innovación Educativa/Pukllasunchis_Colegio.una_experiencia_intercultural_en_ciudad.2009.pdf' => 'Colegio Pukllasunchis: una experiencia intercultural en la ciudad',
            'Archivos Drive/Curso 4 - Primaria Investigacion e Innovación Educativa/EscuelaApuTuspin.Libro.Proyectos de Aprendizaje Intercultural.Costilla.K.PRATEC-2018.pdf' => 'Proyectos de aprendizaje intercultural',
            'Archivos Drive/Curso 4 - Primaria Investigacion e Innovación Educativa/AmaraBerri.BONA.C.2016.pdf' => 'Amara Berri: un sistema educativo',
            'Archivos Drive/Curso 4 - Primaria Investigacion e Innovación Educativa/AmaraBerri.Resumen-Ashoka.pdf' => 'Amara Berri – resumen Ashoka',
            'Archivos Drive/Curso 4 - Primaria Investigacion e Innovación Educativa/EDHU.Raigambre.EducacionDemocratica.intro.2019.pdf' => 'Raigambre: educación democrática',
            'Archivos Drive/Curso 4 - Primaria Investigacion e Innovación Educativa/EDHU.Visita-por-El-Caracol-inquieto.2016.pdf' => 'Visita por El Caracol Inquieto',
            'Archivos Drive/Curso 4 - Primaria Investigacion e Innovación Educativa/KurmiWasi.Rondas-Asambleas-Idiomas-Padrinazgo.pdf' => 'Kurmi Wasi: rondas, asambleas, idiomas y padrinazgo',
            'Archivos Drive/Curso 4 - Primaria Investigacion e Innovación Educativa/EscuelaApuTuspin.Reportaje.ElComercio-2020-09-21.pdf' => 'Escuela Apu Tuspin – reportaje El Comercio',
            'Archivos Drive/Curso 4 - Primaria Investigacion e Innovación Educativa/PÁGINAS DE CONSULTA ACADÉMICA (1).pdf' => 'Páginas de consulta académica',
            'Archivos Drive/Curso 7 Bases para una didactica lúdica e intercultural/Sílabo Curso Didáctica.pdf' => 'Sílabo: Bases para una didáctica lúdica e intercultural',
            'Archivos Drive/Curso 4 - Primaria Investigacion e Innovación Educativa/Silabo Curso Investigación I - PRIMARIA.pdf' => 'Sílabo: Investigación I – Primaria',
            'Archivos Drive/Curso 6 - Inicial Investigacion e Innovación Educativa/Silabo Curso Investigación I - INICIAL.pdf' => 'Sílabo: Investigación I – Inicial',
            'Archivos Drive/Curso 5 - Planificacion/Semana 1/Silabo Planificación Curricular y Evaluación formtiva.pdf' => 'Sílabo: Planificación curricular y evaluación formativa',
            'Silabos/silabo/2024-II-VIII EIB Silabus Electivo VI Enfoques educativos y aprendizaje intercultural.pdf' => 'Sílabo: Enfoques educativos y aprendizaje intercultural (EIB)',
            'Silabos/2025-I/silabo_practica-e-investigacion-VII primaria.pdf' => 'Sílabo: Práctica e investigación VII',
            'Silabos/2025-I/silabo_practica-e-investigacion-IX primaria.pdf' => 'Sílabo: Práctica e investigación IX',
        ],
        'presentacion' => [
            'PPts de ejemplo/Presentación - Proyecto escolar - Diapositivas modernas, color negro.pptx' => 'Proyecto escolar',
            'PPts de ejemplo/Presentación de Gráficos Visuales Líneas de Tiempo Profesional Infográfica Multicolor.pptx' => 'Líneas de tiempo e infografías',
            'PPts de ejemplo/Presentación Porfolio arquitecto elegante minimal azul beige.pptx' => 'Portafolio de trabajos',
            'PPts de ejemplo/Presentación proyecto de negocio formas orgánicas profesional azul y beis.pptx' => 'Proyecto productivo comunal',
            'PPts de ejemplo/Propuesta de Proyecto Scribble Azul.pptx' => 'Propuesta de proyecto de aula',
        ],
        'experiencia' => [
            'Archivos Drive/Curso 4 - Primaria Investigacion e Innovación Educativa/EscuelaApuTuspin.Proyecto.Esquila-de-ovejas.Collage-Adivinanza.pdf' => 'Proyecto: la esquila de ovejas',
            'Feria de Investigaci+on/Feria/1.png' => 'Feria de Investigación Yachay Kawsaypaq Raymi',
            'Feria de Investigaci+on/Feria/2.png' => 'Feria de Investigación – proyecto 2',
            'Feria de Investigaci+on/Feria/3.png' => 'Feria de Investigación – proyecto 3',
            'Feria de Investigaci+on/Feria/4.png' => 'Feria de Investigación – proyecto 4',
            'Feria de Investigaci+on/Feria/5.png' => 'Feria de Investigación – proyecto 5',
            'Feria de Investigaci+on/Feria/6.png' => 'Feria de Investigación – proyecto 6',
            'Feria de Investigaci+on/Feria/feria 2/1.png' => 'Segunda feria – presentación',
            'Feria de Investigaci+on/Feria/feria 2/2.png' => 'Segunda feria – experiencia 2',
            'Feria de Investigaci+on/Feria/feria 2/3.png' => 'Segunda feria – experiencia 3',
            'Feria de Investigaci+on/Feria/feria 2/4.png' => 'Segunda feria – experiencia 4',
            'Redes/Visitas/Colombia/Entrevistas/chicas-01.jpg' => 'Visita de estudiantes de Colombia',
            'Redes/Visitas/Colombia/Entrevistas/min-chico.png' => 'Entrevista: intercambio con Colombia',
        ],
        'galeria' => [
            'Redes/Visitas/Colombia/Fotos/visita01.jpg' => 'Visita intercultural 1',
            'Redes/Visitas/Colombia/Fotos/visita03.jpg' => 'Visita intercultural 2',
            'Redes/Visitas/Colombia/Fotos/visita04.jpg' => 'Visita intercultural 3',
            'Redes/Visitas/Colombia/Fotos/visita05.jpg' => 'Visita intercultural 4',
            'Redes/Visitas/Colombia/Fotos/visita06.jpg' => 'Visita intercultural 5',
            'Redes/Visitas/Colombia/Fotos/visita07.jpg' => 'Visita intercultural 6',
            'Redes/Visitas/Colombia/Fotos/visita09.jpg' => 'Visita intercultural 7',
            'Redes/Visitas/Colombia/Fotos/visita10.jpg' => 'Visita intercultural 8',
            'Redes/Visitas/Colombia/Fotos/visita11.jpg' => 'Visita intercultural 9',
            'Redes/Visitas/Colombia/Fotos/visita12.jpg' => 'Visita intercultural 10',
            'Redes/Visitas/Colombia/Fotos/Sin título-1.png' => 'Recorrido por la escuela 1',
            'Redes/Visitas/Colombia/Fotos/Sin título-2.png' => 'Recorrido por la escuela 2',
            'Redes/Visitas/Colombia/Fotos/Sin título-3.png' => 'Recorrido por la escuela 3',
            'Redes/Visitas/Colombia/Fotos/Sin título-4.png' => 'Recorrido por la escuela 4',
            'Redes/Visitas/Colombia/Fotos/Sin título-5.png' => 'Recorrido por la escuela 5',
            'Redes/Visitas/Colombia/Fotos/Sin título-6.png' => 'Recorrido por la escuela 6',
            'Redes/Visitas/Colombia/Fotos/Sin título-7.png' => 'Recorrido por la escuela 7',
            'Redes/Visitas/Colombia/Fotos/Sin título-8.png' => 'Recorrido por la escuela 8',
            'Redes/Visitas/Colombia/Fotos/Sin título-9.png' => 'Recorrido por la escuela 9',
            'Imgs/Danza 2025/WhatsApp Image 2025-06-08 at 11.02.22 AM.jpeg' => 'Danza 2025 – ensayo',
            'Imgs/Danza 2025/WhatsApp Image 2025-06-08 at 11.02.22 AM (1).jpeg' => 'Danza 2025 – vestuario',
            'Imgs/Danza 2025/WhatsApp Image 2025-06-09 at 10.17.15 AM.jpeg' => 'Danza 2025 – presentación',
            'Imgs/Danza 2025/WhatsApp Image 2025-06-09 at 12.35.18 PM.jpeg' => 'Danza 2025 – comparsa',
            'Imgs/Danza 2025/WhatsApp Image 2025-06-09 at 2.32.14 PM.jpeg' => 'Danza 2025 – música',
            'Imgs/Danza 2025/WhatsApp Image 2025-06-09 at 2.32.14 PM (1).jpeg' => 'Danza 2025 – público',
            'Imgs/Danza 2025/WhatsApp Image 2025-06-09 at 8.51.24 AM.jpeg' => 'Danza 2025 – inicio',
        ],
        'mapa' => [
            'Mapa Puklla1.png' => 'Mapa del local EESP Pukllasunchis',
            'Mapa-puklla-jornada.jpg' => 'Mapa de la jornada',
            'Imgs/Mapa-jornada-23-jun.jpg' => 'Mapa de la jornada del 23 de junio',
            'Archivos Drive/Curso 4 - Primaria Investigacion e Innovación Educativa/Escenarios-Primaria-mapa.pdf' => 'Mapa de escenarios de práctica – Primaria',
        ],
        'video' => [
            'Redes/Recursos/Intro-animacion.mp4' => 'Animación de introducción',
            'Fredy/CAMBIO CLIMÁTICO/ANIMACION .mp4' => 'Cambio climático explicado',
            'Fredy/CAMBIO CLIMÁTICO/videoplayback (10).mp4' => 'Cambio climático y comunidades',
            'Fredy/ESTACIÓN EXTRACTIVISMO/MINERIA LA PAMPA 1.mp4' => 'Minería en La Pampa – parte 1',
            'Fredy/ESTACIÓN EXTRACTIVISMO/MINERIA LA PAMPA 3.mp4' => 'Minería en La Pampa – parte 3',
            'Imgs/Danza 2025/WhatsApp Video 2025-06-09 at 10.16.42 AM.mp4' => 'Danza 2025 en video',
            'Videos/Spot 2025 redes/Pukllasunchis ｜ EESP.mp4' => 'Spot EESP Pukllasunchis 2025',
        ],
    ];

    /** Temas nuevos por área (Gestión Sostenible ya tiene los suyos reales). */
    private const TEMAS = [
        'territorio-vivo' => [
            'Agua y puquios', 'Suelos vivos', 'Flora nativa', 'Fauna andina',
            'Apus y paisaje sagrado', 'Cuencas y microcuencas', 'Calendario agrícola',
        ],
        'expresion-biocultural' => [
            'Música y danzas tradicionales', 'Textilería andina', 'Gastronomía local',
            'Lengua quechua', 'Fiestas y rituales', 'Medicina tradicional',
            'Cuentos y mitos andinos', 'Cerámica y arte popular',
        ],
        'gestion-sostenible' => [
            'Crianza de animales menores', 'Semillas nativas',
        ],
        'resiliencia-socioecologica' => [
            'Cambio climático y comunidad', 'Gestión de riesgos', 'Ayni y trabajo comunal',
            'Seguridad alimentaria', 'Economía solidaria', 'Juventud y liderazgo',
        ],
    ];

    private const NOMBRES = [
        'Rosa Quispe', 'Juan Mamani', 'Prof. Lucía Huamán', 'Carlos Ccori', 'Marisol Condori',
        'Edwin Puma', 'Sonia Apaza', 'Familia Choque', 'Docente de 4.º grado', 'Wilber Turpo',
        'Nélida Sutta', 'Ana Paucar', 'Grupo de 5.º B', 'Hugo Ttito',
    ];

    private const COMENTARIOS = [
        'Muy buen material, lo usamos en clase y los niños participaron un montón.',
        'Gracias por compartir, me sirvió para preparar mi sesión de la semana.',
        '¿Podrían subir más fichas para primaria? Están muy bonitas.',
        'Excelente iniciativa del colegio, felicitaciones al equipo.',
        'Lo trabajamos con las familias y fue una experiencia linda.',
        'Sería bueno tener este contenido también en quechua.',
        'Las fotos muestran muy bien el proceso, se entiende todo.',
        'Me gustó mucho el video, lo compartí con otros docentes de la UGEL.',
        'Aplicamos la ficha en la salida de campo, funcionó perfecto.',
        'Tengo una duda sobre la teoría, ¿hay bibliografía adicional?',
        'Sulpayki por compartir estos saberes.',
        'Nuestros estudiantes hicieron su propio mapa parlante a partir de este ejemplo.',
    ];

    /** Libros del catálogo: los PDF de bibliografía ya subidos, con su autor y categorías. */
    private const LIBROS = [
        'Pukllasunchis_Colegio.una_experiencia_intercultural_en_ciudad.2009.pdf' => ['Asociación Pukllasunchis', ['Educación intercultural', 'Experiencias educativas']],
        'EscuelaApuTuspin.Libro.Proyectos de Aprendizaje Intercultural.Costilla.K.PRATEC-2018.pdf' => ['Costilla, K. – PRATEC', ['Educación intercultural', 'Cultura andina']],
        'AmaraBerri.BONA.C.2016.pdf' => ['Bona, C.', ['Experiencias educativas', 'Pedagogía']],
        'AmaraBerri.Resumen-Ashoka.pdf' => ['Ashoka', ['Experiencias educativas']],
        'EDHU.Raigambre.EducacionDemocratica.intro.2019.pdf' => ['EDHU', ['Pedagogía']],
        'EDHU.Visita-por-El-Caracol-inquieto.2016.pdf' => ['EDHU', ['Experiencias educativas']],
        'KurmiWasi.Rondas-Asambleas-Idiomas-Padrinazgo.pdf' => ['Colegio Kurmi Wasi', ['Educación intercultural', 'Experiencias educativas']],
        'EscuelaApuTuspin.Reportaje.ElComercio-2020-09-21.pdf' => ['Diario El Comercio', ['Cultura andina']],
        'PÁGINAS DE CONSULTA ACADÉMICA (1).pdf' => ['EESP Pukllasunchis', ['Pedagogía']],
    ];

    private const CATEGORIAS_LIBROS = ['Educación intercultural', 'Experiencias educativas', 'Cultura andina', 'Pedagogía'];

    private const CATEGORIAS_VIDEOS = ['Ambiente y territorio', 'Cultura y arte', 'Institucional'];

    /** Videos del catálogo: archivo de video ya subido => categoría. */
    private const VIDEOS = [
        'ANIMACION .mp4' => 'Ambiente y territorio',
        'videoplayback (10).mp4' => 'Ambiente y territorio',
        'MINERIA LA PAMPA 1.mp4' => 'Ambiente y territorio',
        'MINERIA LA PAMPA 3.mp4' => 'Ambiente y territorio',
        'WhatsApp Video 2025-06-09 at 10.16.42 AM.mp4' => 'Cultura y arte',
        'Intro-animacion.mp4' => 'Institucional',
        'Pukllasunchis ｜ EESP.mp4' => 'Institucional',
    ];

    /** @var array<string, array{id:string, bytes:int}> ruta relativa => archivo en Drive */
    private array $drive = [];

    public function run(): void
    {
        self::limpiar();
        mt_srand(2026);

        $origen = rtrim(env('KAWSAY_DEMO_DIR', self::ORIGEN), '/\\');
        $this->drive = self::leerCache();

        $temas = $this->sembrarTemas($origen);

        // Cada tipo arranca en un tema distinto, para que no todos reciban el mismo tipo.
        $desfase = 0;
        foreach (self::ARCHIVOS as $tipo => $archivos) {
            $i = $desfase;
            foreach ($archivos as $ruta => $titulo) {
                $this->sembrarArchivo($origen, $ruta, $titulo, $tipo, $temas[$i++ % $temas->count()]);
            }
            $desfase += 4;
        }

        foreach ($temas as $tema) {
            $this->sembrarComentarios($tema);
        }

        $this->sembrarLibros();
        $this->sembrarVideos();
    }

    private function sembrarTemas(string $origen)
    {
        $areas = KawsayArea::pluck('id', 'slug');
        // Fotos de la galería como portada: cada tema nuevo con una distinta, y algunos sin foto.
        $portadas = array_keys(self::ARCHIVOS['galeria']);
        File::ensureDirectoryExists(public_path(self::IMAGENES_TEMAS));

        foreach (self::TEMAS as $areaSlug => $titulos) {
            if (! isset($areas[$areaSlug])) {
                continue;
            }
            $orden = KawsayTema::where('kawsay_area_id', $areas[$areaSlug])->max('orden') ?? -1;

            foreach ($titulos as $titulo) {
                $slug = Str::slug($titulo);
                $imagen = null;
                $portada = mt_rand(1, 5) === 1 ? null : array_shift($portadas);
                if ($portada) {
                    $destino = self::IMAGENES_TEMAS."/{$slug}.".strtolower(pathinfo($portada, PATHINFO_EXTENSION));
                    // Las portadas ya vienen en el repo; la carpeta de origen solo hace falta para regenerarlas.
                    if (! is_file(public_path($destino)) && is_file("{$origen}/{$portada}")) {
                        File::copy("{$origen}/{$portada}", public_path($destino));
                    }
                    $imagen = is_file(public_path($destino)) ? $destino : null;
                }

                KawsayTema::create([
                    'kawsay_area_id' => $areas[$areaSlug],
                    'slug' => $slug,
                    'titulo' => $titulo,
                    'descripcion' => "Contenido de prueba sobre {$titulo}: recursos, experiencias y materiales para trabajar este tema en el aula y con la comunidad.",
                    'imagen' => $imagen,
                    'orden' => ++$orden,
                ]);
            }
        }

        return KawsayTema::orderBy('kawsay_area_id')->orderBy('orden')->get();
    }

    private function sembrarArchivo(string $origen, string $ruta, string $titulo, string $tipo, KawsayTema $tema): void
    {
        $nombre = basename($ruta);

        if (! isset($this->drive[$ruta])) {
            $completa = "{$origen}/{$ruta}";
            if (! is_file($completa)) {
                $this->command?->warn("No existe, se omite: {$ruta}");

                return;
            }

            $this->command?->line("Subiendo a Drive: {$nombre}");
            $formato = KawsayTaxonomia::formatoPorExtension(pathinfo($nombre, PATHINFO_EXTENSION)) ?? 'pdf';
            $id = app(GoogleDriveService::class)->upload(new UploadedFile($completa, $nombre, null, null, true), $formato, "demo-{$nombre}", 'kawsay');

            $this->drive[$ruta] = ['id' => $id, 'bytes' => filesize($completa)];
            self::guardarCache($this->drive);
        }

        $archivo = new KawsayArchivo([
            'kawsay_tema_id' => $tema->id,
            'tipo_contenido' => $tipo,
            'tipo' => KawsayTaxonomia::formatoPorExtension(pathinfo($nombre, PATHINFO_EXTENSION)) ?? 'pdf',
            'titulo' => $titulo,
            'drive_id' => $this->drive[$ruta]['id'],
            'nombre_original' => $nombre,
            'peso_mb' => round($this->drive[$ruta]['bytes'] / (1024 * 1024), 2),
        ]);
        $archivo->created_at = $archivo->updated_at = now()->subDays(mt_rand(0, 120))->subMinutes(mt_rand(0, 1440));
        $archivo->save();
    }

    private function sembrarComentarios(KawsayTema $tema): void
    {
        $comentarios = self::COMENTARIOS;
        shuffle($comentarios);

        foreach (array_slice($comentarios, 0, mt_rand(0, 4)) as $texto) {
            $comentario = new KawsayTemaComentario([
                'kawsay_tema_id' => $tema->id,
                'nombre' => self::NOMBRES[array_rand(self::NOMBRES)],
                'comentario' => $texto,
            ]);
            $comentario->created_at = $comentario->updated_at = now()->subDays(mt_rand(0, 60))->subMinutes(mt_rand(0, 1440));
            $comentario->save();
        }
    }

    private function sembrarLibros(): void
    {
        $categorias = collect(self::CATEGORIAS_LIBROS)->mapWithKeys(fn ($nombre) => [$nombre => KawsayCategoria::create([
            'nombre' => $nombre,
            'url' => Str::slug($nombre),
        ])->id]);

        foreach (self::LIBROS as $nombre => [$autor, $cats]) {
            [$ruta, $driveId] = $this->buscarSubido($nombre);
            if (! $driveId) {
                continue;
            }

            KawsayLibro::create([
                'nombre' => self::ARCHIVOS['bibliografia'][$ruta],
                'autor' => $autor,
                'identificador' => $driveId,
            ])->categorias()->attach($categorias->only($cats)->values());
        }
    }

    private function sembrarVideos(): void
    {
        $categorias = collect(self::CATEGORIAS_VIDEOS)->mapWithKeys(fn ($nombre) => [$nombre => KawsayVideoCategoria::create([
            'nombre' => $nombre,
            'url' => Str::slug($nombre),
        ])->id]);

        foreach (self::VIDEOS as $nombre => $categoria) {
            [$ruta, $driveId] = $this->buscarSubido($nombre);
            if (! $driveId) {
                continue;
            }

            KawsayVideo::create([
                'nombre' => self::ARCHIVOS['video'][$ruta],
                'descripcion' => 'Video de prueba del repositorio Kawsay.',
                'drive' => $driveId,
                'categoria_id' => $categorias[$categoria],
            ]);
        }
    }

    /** @return array{0:?string, 1:?string} ruta relativa e ID de Drive del archivo subido con ese nombre */
    private function buscarSubido(string $nombre): array
    {
        foreach ($this->drive as $ruta => $info) {
            if (basename($ruta) === $nombre) {
                return [$ruta, $info['id']];
            }
        }

        return [null, null];
    }

    /** Quita todo lo sembrado por este seeder (deja intacto lo cargado a mano). */
    public static function limpiar(bool $borrarDeDrive = false): void
    {
        $cache = self::leerCache();
        $ids = array_column($cache, 'id');

        KawsayArchivo::whereIn('drive_id', $ids)->delete();
        KawsayTemaComentario::whereIn('comentario', self::COMENTARIOS)->delete();

        $slugs = collect(self::TEMAS)->flatten()->map(fn ($titulo) => Str::slug($titulo));
        KawsayTema::whereIn('slug', $slugs)->get()->each->delete();

        KawsayLibro::whereIn('identificador', $ids)->get()->each(function ($libro) {
            $libro->categorias()->detach();
            $libro->delete();
        });
        KawsayCategoria::whereIn('nombre', self::CATEGORIAS_LIBROS)->delete();

        KawsayVideo::whereIn('drive', $ids)->delete();
        KawsayVideoCategoria::whereIn('nombre', self::CATEGORIAS_VIDEOS)->delete();

        if ($borrarDeDrive) {
            $drive = app(GoogleDriveService::class);
            foreach ($ids as $id) {
                $drive->delete($id);
            }
            File::delete(storage_path('app/'.self::CACHE));
            File::deleteDirectory(public_path(self::IMAGENES_TEMAS));
        }
    }

    private static function leerCache(): array
    {
        $ruta = storage_path('app/'.self::CACHE);

        return is_file($ruta) ? (json_decode(File::get($ruta), true) ?: []) : [];
    }

    private static function guardarCache(array $cache): void
    {
        File::put(storage_path('app/'.self::CACHE), json_encode($cache, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }
}
