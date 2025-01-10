@php
    use Illuminate\Support\Str;
    use Stichoza\GoogleTranslate\GoogleTranslate;
    use App\Http\Controllers\MockUpController;

    $jpgFilePath = MockUpController::genImageLocaleAndName($locale, $musicScore->name);

    $locale = App::getLocale();

    $lista_instrumentos = $musicScore->instruments->isNotEmpty() ? $musicScore->instruments->first()->name : '';
    $lista_estilos_musicales = $musicScore->style_musics->isNotEmpty() ? $musicScore->style_musics->first()->name : '';

    $etr = new GoogleTranslate(); // La configuracion por defecto es 'en' (Ingles)
    $etr->setSource('es'); // Idioma fuente (opcional)
    $etr->setTarget($locale); // Idioma destino

    $txt_meta_description = $etr->translate(
        'Faristol es una plataforma para músicos y compositores, ofreciendo acceso a partituras musicales con diferentes planes de suscripción y herramientas exclusivas. Protege los derechos de autor.',
    );
    $txt_meta_keyboard = $etr->translate(
        'Faristol, música, compositores, partituras, suscripción, música en línea, protección de derechos de autor,',
    );
    $txt_og_title = $etr->translate('Faristol - Partituras para Músicos y Compositores');
    $txt_og_description = $etr->translate(
        'Con Faristol, conecta con partituras musicales exclusivas. Ideal para músicos y compositores con planes de suscripción y protección de derechos de autor.',
    );
    $txt_title = $etr->translate('Faristol - Plataforma de Partituras para Músicos y Compositores');
    //
    $txt_Estilo_Musical = $etr->translate('Estilo Musical');
    $txt_Instrumento_Musical = $etr->translate('Instrumento Musical');
    //
    $txt_Descripcion_partitura = $etr->translate('Descripción de la partitura musical');
    $txt_Estilos_Musicales = $etr->translate('Estilos Musicales');
    $txt_No_hay_estilos_para_partitura = $etr->translate('No hay estilos musicales disponibles para esta partitura.');
    $txt_Instrumentos = $etr->translate('Instrumentos Musicales');
    $txt_No_hay_instrumentos_para_partitura = $etr->translate(
        'No hay instrumentos musicales disponibles para esta partitura.',
    );
    $txt_contenido = $etr->translate('Contenido');
    $txt_ver_archivo_pdf = $etr->translate('Ver archivo PDF');

    $txt_enlaces = $etr->translate('Enlaces');
    $txt_ver_mapa_sitio = $etr->translate('Ver el mapa del sitio');

    $ptr = new GoogleTranslate(); // La configuracion por defecto es 'en' (Ingles)
    $ptr->setSource('en'); // Idioma fuente (opcional)
    $ptr->setTarget($locale); // Idioma destino

    $txt_estilos_musicales = $ptr->translate($lista_estilos_musicales);
    $txt_instrumentos = $ptr->translate($lista_instrumentos);

    $utr = new GoogleTranslate(); // La configuracion por defecto es 'en' (Ingles)
    // $utr->setSource('en'); // Idioma fuente (opcional)
    $utr->setTarget($locale); // Idioma destino

    $txt_score_name = $utr->translate(Str::title($musicScore->name));
    $txt_score_description = $utr->translate(ucfirst($musicScore->description ?? ''));

    $estiloMusical = '';
    if (!empty($styleName)) {
        $estiloMusical = $ptr->translate($styleName);
    }
    $instrumento = '';
    if (!empty($instrumentName)) {
        $instrumento = $ptr->translate($instrumentName);
    }

@endphp

<!DOCTYPE html>
<html lang="{{ $locale }}">

<head>
    <base href="{{ asset('web') }}/">
    <meta charset="UTF-8">
    <meta content="IE=Edge" http-equiv="X-UA-Compatible">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Meta Description with Keywords -->
    <meta name="description"
        content="{{ $txt_meta_description }} {{ $txt_score_name }}. {{ $txt_score_description }}. {{ $txt_estilos_musicales }}. {{ $txt_instrumentos }}.">
    <meta name="keywords"
        content="{{ $txt_meta_keyboard }}, {{ $txt_score_name }}, {{ $txt_score_description }}, {{ $txt_estilos_musicales }}, {{ $txt_instrumentos }}">

    <!-- Social Media / Open Graph -->
    <meta property="og:title"
        content="{{ $txt_og_title }} - {{ $txt_score_name }} | {{ \Carbon\Carbon::now()->locale(app()->getLocale())->isoFormat('LLLL') }}">
    <meta property="og:description" content="{{ $txt_og_description }} {{ $txt_score_description }}">
    <meta property="og:image" content="{{ asset('web/icons/Icon-512.png') }}">
    <meta property="og:url" content="https://web.faristol.net">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">

    <!-- Canonical URL -->
    <link rel="canonical" href="https://web.faristol.net">

    <!-- iOS meta tags & icons -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="apple-mobile-web-app-title" content="Faristol">
    <link rel="apple-touch-icon" href="{{ asset('web/icons/Icon-192.png') }}">

    <meta name="mobile-web-app-capable" content="yes">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('web/favicon.png') }}" />

    <title>{{ $txt_title }} - {{ $txt_score_name }}</title>
    <link rel="manifest" href="{{ asset('web/manifest.json') }}">

    <script>
        const serviceWorkerVersion = '"4013560198"';
    </script>
    <script src="{{ asset('web/flutter.js') }}?v={{ date('YmdH') }}" defer></script>

    <style>
        html {
            background-color: #0C1934;
            color: antiquewhite;
        }

        h1 {
            /*text-align: center;*/
        }

        a {
            color: antiquewhite
        }
    </style>
</head>

<body>
    <style>
        /* Estilos para el splash screen */
        #splash-screen {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url('{{ asset('web/faristol_splash.jpg') }}');
            background-size: cover;
            background-position: center;
            z-index: 9999;
            opacity: 1;
            transition: opacity 3s ease 0s;
        }

        #title {
            transition: margin-top 2s ease 0s;
        }

        #flutter_target {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }

        #pagecontent {
            transition: margin-top 2s ease 0s;
        }

        .imagen {
            height: 250px;
            display: block;
        }
    </style>
    <div id="flutter_target"></div>
    <div id="splash-screen"></div>
    <script>
        window.addEventListener('load', function(ev) {
            // Oculta el splash screen cuando la pagina ha cargado
            const splashScreen = document.getElementById('splash-screen');
            const pagecontent = document.getElementById('pagecontent');
            // title.style.opacity = 1;
            setTimeout(() => {
                splashScreen.style.opacity = 0;
                setTimeout(() => {
                    splashScreen.style.display = 'none';
                    pagecontent.style.marginTop = '120vh';
                    //
                    var txtOgTitle = "{{ $txt_og_title }}";
                    var txtScoreName = "{{ $txt_score_name }}";
                    var currentDatetime =
                        "{{ \Carbon\Carbon::now()->locale(app()->getLocale())->isoFormat('LLLL') }}";
                    document.title = txtOgTitle + ' - ' + txtScoreName + ' | ' + currentDatetime;
                }, 2500);
            }, 1000);
        });
    </script>

    <div id="pagecontent">
        <!-- Título -->
        <h1 id="title">Faristol | {{ \Carbon\Carbon::now()->locale(app()->getLocale())->isoFormat('LLLL') }} </h1>

        @if (!empty($estiloMusical))
            <!-- Estilo Musical -->
            <h2>{{ $txt_Estilo_Musical }} | {{ $estiloMusical }} </h2>
        @endif

        @if (!empty($instrumento))
            <!-- Instrumento Musical -->
            <h2>{{ $txt_Instrumento_Musical }} | {{ $instrumento }}</h2>
        @endif

        <h2><a
                href="{{ route('score-viewbyname', ['name' => rawurlencode($musicScore->name)]) }}">{{ $txt_score_name }}</a>
        </h2>

        <!-- Descripcion de la partitura -->
        <section>
            <h3>{{ $txt_Descripcion_partitura }}</h3>
            <p>{{ $txt_score_description }}</p>
        </section>

        <!-- Lista de estilos -->
        <section>
            <h3>{{ $txt_Estilos_Musicales }}</h3>
            <ul>
                @forelse($musicScore->style_musics as $style)
                    <li>{{ $utr->translate($style->name) }}</li>
                @empty
                    <li>{{ $txt_No_hay_estilos_para_partitura }}</li>
                @endforelse
            </ul>
        </section>

        <!-- Lista de instrumentos -->
        <section>
            <h3>{{ $txt_Instrumentos }}</h3>
            <ul>
                @forelse($musicScore->instruments as $instrument)
                    <li>{{ $utr->translate($instrument->name) }}</li>
                @empty
                    <li>{{ $txt_No_hay_instrumentos_para_partitura }}</li>
                @endforelse
            </ul>
        </section>

        <section>
            <h3>{{ $txt_contenido }}</h3>
            <ul>
                <a
                    href="{{ route('getPdfByLangAndName', ['locale' => $locale, 'name' => rawurlencode($musicScore->name)]) }}">
                    <img class="imagen"
                        src="{{ route('showImageByLangAndName', ['locale' => $locale, 'name' => rawurlencode($musicScore->name)]) }}" />
                </a>
                <a
                    href="{{ route('getPdfByLangAndName', ['locale' => $locale, 'name' => rawurlencode($musicScore->name)]) }}">{{ $txt_ver_archivo_pdf }}</a>
            </ul>
        </section>

        <section>
            <h3>{{ $txt_enlaces }}</h3>
            <a href="{{ route('sitemapLang', ['lang' => $locale]) }}"
                style="color: antiquewhite; text-decoration: underline;">
                {{ $txt_ver_mapa_sitio }}
            </a>
        </section>

    </div>
    <script src="https://cdn.jsdelivr.net/npm/pdfjs-dist@2.12.313/build/pdf.js" type="text/javascript"></script>
    <script type="text/javascript">
        pdfjsLib.GlobalWorkerOptions.workerSrc = "https://cdn.jsdelivr.net/npm/pdfjs-dist@2.12.313/build/pdf.worker.min.js";
        pdfRenderOptions = {
            cMapUrl: 'https://cdn.jsdelivr.net/npm/pdfjs-dist@2.12.313/cmaps/',
            cMapPacked: true,
        }
    </script>
    <script>
        window.addEventListener('load', function(ev) {
            let target = document.querySelector("#flutter_target");
            _flutter.loader.loadEntrypoint({
                serviceWorker: {
                    serviceWorkerVersion: serviceWorkerVersion,
                    serviceWorkerPath: "{{ asset('web/flutter_service_worker.js') }}?v={{ date('YmdH') }}",
                },
                onEntrypointLoaded: function(engineInitializer) {
                    engineInitializer.initializeEngine({
                        hostElement: target,
                    }).then(function(appRunner) {
                        appRunner.runApp();
                        var adsScript = document.createElement('script');
                        adsScript.src = "https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-6468139714055912";
                        adsScript.async = true;
                        adsScript.crossOrigin = "anonymous";
                        document.body.appendChild(adsScript);
                        console.log("googlesyndication");
                    });
                }
            });
        });
    </script>
</body>

</html>
