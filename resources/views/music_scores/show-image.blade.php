@php
    use Illuminate\Support\Str;
    use Stichoza\GoogleTranslate\GoogleTranslate;

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
    $txt_Descripcion_partitura = $etr->translate('Descripción de la partitura musical');
    $txt_Estilos_Musicales = $etr->translate('Estilos Musicales');
    $txt_No_hay_estilos_para_partitura = $etr->translate('No hay estilos musicales disponibles para esta partitura.');
    $txt_Instrumentos = $etr->translate('Instrumentos Musicales');
    $txt_No_hay_instrumentos_para_partitura = $etr->translate(
        'No hay instrumentos musicales disponibles para esta partitura.',
    );
    $txt_Visita_sitio_web = $etr->translate('Por favor, visita el sitio web de la aplicación Faristol');

    $ptr = new GoogleTranslate(); // La configuracion por defecto es 'en' (Ingles)
    $ptr->setSource('en'); // Idioma fuente (opcional)
    $ptr->setTarget($locale); // Idioma destino

    $txt_instrumentos = $ptr->translate($lista_instrumentos);
    $txt_estilos_musicales = $ptr->translate($lista_estilos_musicales);

    $utr = new GoogleTranslate(); // La configuracion por defecto es 'en' (Ingles)
    // $utr->setSource('en'); // Idioma fuente (opcional)
    $utr->setTarget($locale); // Idioma destino

    $txt_score_name = $utr->translate(Str::title($musicScore->name));
    $txt_score_description = $utr->translate(ucfirst($musicScore->description ?? ''));

    $url = route('la-score-viewbyname', ['lang' => $locale, 'name' => rawurlencode($musicScore->name)]);
@endphp

<!DOCTYPE html>
<html lang="{{ $locale }}">

<head>
    <base href="{{ asset('web') }}/">

    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta charset="UTF-8">
    <meta content="IE=Edge" http-equiv="X-UA-Compatible">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Meta Description with Keywords -->
    <meta name="description"
        content="{{ $txt_meta_description }} {{ $txt_score_name }}. {{ $txt_score_description }}. {{ $txt_estilos_musicales }}. {{ $txt_instrumentos }}.">
    <meta name="keywords"
        content="{{ $txt_meta_keyboard }}, {{ $txt_score_name }}, {{ $txt_score_description }}, {{ $txt_estilos_musicales }}, {{ $txt_instrumentos }}">

    <!-- Social Media / Open Graph -->
    <meta property="og:title" content="{{ $txt_og_title }} - {{ $txt_score_name }}">
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

    <style>
        /* Estilos generales */
        html,
        body {
            font-family: 'Noto Sans';
            background-color: #0C1934;
            color: #f1f1f1;
            padding: 0;
            width: 800px;
            margin: 0 auto;
        }

        /* Contenedor principal */
        .container {
            width: 660px;
            margin: 0 auto;
            padding: 0;
            text-align: center;
        }

        /* Logo */
        .logo {
            width: 100px;
            margin-top: 20px;
        }

        .webaddress {
            font-size: 1.5rem;
            margin: 0;
            color: #f9f9f9;
            margin-bottom: 15px;
        }

        /* Estilo para la cabecera */
        .encabezado {
            margin-bottom: 10px;
        }

        .partitura {
            font-size: 1rem;
            color: black;
            background-color: white;
            padding: 20px;
            margin-top: 10px;
            margin-bottom: 10px;
        }

        .titulo {
            font-size: 1.5rem;
            margin-top: 10px;
            margin-bottom: 10px;
        }

        .descripcion {
            font-size: 1.25rem;
            margin-bottom: 10px;
        }

        .instrumentos {
            margin-bottom: 40px;
        }

        .estilos {
            margin-top: 60px;
            margin-bottom: 40px;
        }

        * {
            font-family: 'Noto Sans';
        }

        /* Superposición */
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            /* Semitransparente */
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            /* Asegúrate de que esté por encima de otros elementos */
        }

        /* Círculo de carga */
        .spinner {
            width: 50px;
            height: 50px;
            border: 6px solid rgba(255, 255, 255, 0.2);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        a {
            text-decoration: none;
            color: antiquewhite;
            transition: color 0.3s ease;
        }

        a:hover {
            color: white;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="encabezado">
            <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('img/logo-img.png'))) }}"
                alt="Logo de Faristol" class="logo">
            <div class="webaddress">web.faristol.net</div>
        </div>
        <div class="partitura">
            <div class="titulo">{{ $txt_score_name }}</div>
            <div class="descripcion">{{ $txt_score_description }}</div>
            <div class="instrumentos">
                @forelse($musicScore->instruments as $instrument)
                    {{ strtolower($utr->translate(ucfirst($instrument->name))) }}
                @empty
                    {{ $txt_No_hay_instrumentos_para_partitura }}
                @endforelse
            </div>
            <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('img/pdf-music-score-sheet.png'))) }}"
                alt="Partitura" class="img-partitura">
            <div class="estilos">
                @forelse($musicScore->style_musics as $style)
                    {{ strtolower($utr->translate(ucfirst($style->name))) }}
                @empty
                    {{ $txt_No_hay_estilos_para_partitura }}
                @endforelse
            </div>
        </div>
        @if (isset($redirect) && $redirect == true)
            <h3>{{ $txt_Visita_sitio_web }}</h3>
            <h3><a href="{{ $url }}" target="_blank">{{ $url }}</a></h3>
        @endif
    </div>

    @if (isset($redirect) && $redirect == true)
        <div class="overlay" id="loadingOverlay" style="display: none;">
            <div class="spinner"></div>
        </div>
        <script>
            let isRedirecting = false; // Evitar redirecciones múltiples

            // Mostrar el círculo de carga
            function showLoading() {
                document.getElementById('loadingOverlay').style.display = 'flex';
            }

            // Ocultar el círculo de carga
            function hideLoading() {
                document.getElementById('loadingOverlay').style.display = 'none';
            }

            function activateRedirect() {
                // Detectar movimiento del mouse
                document.addEventListener('mousemove', redirectToTarget);

                // Detectar desplazamiento del scroll
                document.addEventListener('scroll', redirectToTarget);
            }

            function redirectToTarget() {
                if (!isRedirecting) {
                    isRedirecting = true; // Evita múltiples redirecciones
                    setTimeout(function() {
                        showLoading();
                        setTimeout(function() {
                            window.location.href = "{{ $url }}";
                            setTimeout(function() {
                                hideLoading();
                            }, 2000);
                        }, 2000);
                    }, 1000);
                }
            }

            activateRedirect();
        </script>
    @endif

</body>

</html>
