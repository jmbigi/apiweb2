<!DOCTYPE html>
<html lang="es">

<head>
    <base href="{{ asset('web') }}/">
    <meta charset="UTF-8">
    <meta content="IE=Edge" http-equiv="X-UA-Compatible">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Meta Description with Keywords -->
    <meta name="description"
        content="Faristol es una plataforma para músicos y compositores, ofreciendo acceso a partituras musicales con diferentes planes de suscripción y herramientas exclusivas. Protege los derechos de autor. {{ $musicScore->name }}. {{ $musicScore->description }}. @if ($musicScore->style_musics->isNotEmpty()) {{ $musicScore->style_musics->first()->name }}. @endif @if ($musicScore->instruments->isNotEmpty()) {{ $musicScore->instruments->first()->name }}. @endif">
    <meta name="keywords"
        content="Faristol, música, compositores, partituras, suscripción, música en línea, protección de derechos de autor, {{ $musicScore->name }}, {{ $musicScore->description }}, @if ($musicScore->style_musics->isNotEmpty()) {{ $musicScore->style_musics->first()->name }}, @endif @if ($musicScore->instruments->isNotEmpty()) {{ $musicScore->instruments->first()->name }} @endif">

    <!-- Social Media / Open Graph -->
    <meta property="og:title" content="Faristol - Partituras para Músicos y Compositores - {{ $musicScore->name }}">
    <meta property="og:description"
        content="Con Faristol, conecta con partituras musicales exclusivas. Ideal para músicos y compositores con planes de suscripción y protección de derechos de autor. {{ $musicScore->description }}">
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

    <title>Faristol - Plataforma de Partituras para Músicos y Compositores - {{ $musicScore->name }}</title>
    <link rel="manifest" href="{{ asset('web/manifest.json') }}">

    <script>
        const serviceWorkerVersion = '"2911965985"';
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
            /* opacity: 0; */
            transition: margin-top 2s ease 0s;
        }
    </style>
    <div id="splash-screen"></div>
    <script>
        window.addEventListener('load', function(ev) {
            // Oculta el splash screen cuando la página ha cargado
            const splashScreen = document.getElementById('splash-screen');
            const title = document.getElementById('title');
            // title.style.opacity = 1;
            setTimeout(() => {
                splashScreen.style.opacity = 0;
                // title.style.opacity = 0;
                setTimeout(() => {
                    splashScreen.style.display = 'none';
                    // title.style.display = 'none';
                    title.style.marginTop = '100vh';
                }, 2500);
            }, 1000);
        });
    </script>

    <!-- Título -->
    <h1 id="title">Faristol</h1>

    <h2><a href="{{ route('score-viewbyname', ['name' => $musicScore->name]) }}">{{ $musicScore->name }}</a></h2>

    <!-- Descripción de la partitura -->
    <section>
        <h3>Descripción</h3>
        <p>{{ $musicScore->description }}</p>
    </section>

    <!-- Lista de estilos -->
    <section>
        <h3>Estilos</h3>
        <ul>
            @forelse($musicScore->style_musics as $style)
                <li>{{ $style->name }}</li>
            @empty
                <li>No hay estilos disponibles para esta partitura.</li>
            @endforelse
        </ul>
    </section>

    <!-- Lista de instrumentos -->
    <section>
        <h3>Instrumentos</h3>
        <ul>
            @forelse($musicScore->instruments as $instrument)
                <li>{{ $instrument->name }}</li>
            @empty
                <li>No hay instrumentos disponibles para esta partitura.</li>
            @endforelse
        </ul>
    </section>

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
            _flutter.loader.loadEntrypoint({
                serviceWorker: {
                    serviceWorkerVersion: serviceWorkerVersion,
                    serviceWorkerPath: "{{ asset('web/flutter_service_worker.js') }}?v={{ date('YmdH') }}",
                },
                onEntrypointLoaded: function(engineInitializer) {
                    engineInitializer.initializeEngine().then(function(appRunner) {
                        appRunner.runApp();
                    });
                }
            });
        });
    </script>
</body>

</html>
