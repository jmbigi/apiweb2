@php
    use Illuminate\Support\Str;
    use Stichoza\GoogleTranslate\GoogleTranslate;

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
    $txt_Descripcion_partitura = $etr->translate('Descripción de la partitura musical');
    $txt_Estilos_Musicales = $etr->translate('Estilos Musicales');
    $txt_No_hay_estilos_para_partitura = $etr->translate('No hay estilos musicales disponibles para esta partitura.');
    $txt_Instrumentos = $etr->translate('Instrumentos Musicales');
    $txt_No_hay_instrumentos_para_partitura = $etr->translate(
        'No hay instrumentos musicales disponibles para esta partitura.',
    );

    $ptr = new GoogleTranslate(); // La configuracion por defecto es 'en' (Ingles)
    $ptr->setSource('en'); // Idioma fuente (opcional)
    $ptr->setTarget($locale); // Idioma destino

    $txt_instrumentos = $ptr->translate($lista_instrumentos);
    $txt_estilos_musicales = $ptr->translate($lista_estilos_musicales);

    $utr = new GoogleTranslate(); // La configuracion por defecto es 'en' (Ingles)
    // $utr->setSource('en'); // Idioma fuente (opcional)
    $utr->setTarget($locale); // Idioma destino

    $txt_score_name = $utr->translate(Str::title($musicScore->name));
    $txt_score_description = $utr->translate($musicScore->description ?? '');

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

    <link rel="stylesheet" href="styles.css">

    <style>
        /* Estilos generales */
        html,
        body {
            font-family: 'Arial', sans-serif;
            background-color: #0C1934;
            color: #f1f1f1;
            margin: 0;
            padding: 0;
        }

        /* Contenedor principal */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            text-align: center;
        }

        /* Logo */
        .logo {
            width: 150px;
            margin-bottom: 20px;
        }

        /* Títulos principales */
        h1 {
            font-size: 3rem;
            margin: 0;
            color: #f9f9f9;
        }

        h2 {
            font-size: 2rem;
            color: #b8d8d8;
        }

        /* Sección de Descripción */
        .description h3 {
            font-size: 1.8rem;
            color: #FFD700;
            margin-bottom: 10px;
        }

        .description p {
            font-size: 1rem;
            line-height: 1.5;
            max-width: 800px;
            margin: 0 auto;
        }

        /* Lista de estilos e instrumentos */
        section ul {
            list-style-type: none;
            padding: 0;
        }

        section ul li {
            background-color: #2c3e50;
            margin: 5px 0;
            padding: 10px;
            border-radius: 5px;
            font-size: 1.1rem;
            text-align: left;
        }

        /* Estilos de los encabezados de lista */
        section h3 {
            font-size: 1.5rem;
            color: #FFD700;
            margin-top: 30px;
        }

        /* Estilo para la cabecera */
        header {
            margin-bottom: 30px;
        }

        /* Diseño responsivo */
        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }

            h1 {
                font-size: 2rem;
            }

            h2 {
                font-size: 1.5rem;
            }

            .description p {
                font-size: 0.9rem;
            }
        }
    </style>
</head>

<body>
    <div class="container">

        <header>
            <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('web/icons/Icon-512.png'))) }}"
                alt="Logo de Faristol" class="logo">
            <h1 id="title">Faristol</h1>
            <!-- Título -->
            <h2>{{ $txt_score_name }}</h2>
        </header>

        <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('img/blank-music-sheet.png'))) }}"
            alt="Partitura" class="partitura">

        <!-- Descripcion de la partitura -->
        <section class="description">
            <h3>{{ $txt_Descripcion_partitura }}</h3>
            <p>{{ $txt_score_description }}</p>
        </section>

        <!-- Lista de estilos -->
        <section class="styles">
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
        <section class="instruments">
            <h3>{{ $txt_Instrumentos }}</h3>
            <ul>
                @forelse($musicScore->instruments as $instrument)
                    <li>{{ $utr->translate($instrument->name) }}</li>
                @empty
                    <li>{{ $txt_No_hay_instrumentos_para_partitura }}</li>
                @endforelse
            </ul>
        </section>

    </div>
</body>

</html>
