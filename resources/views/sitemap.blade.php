@php
    use Illuminate\Support\Str;
    use Stichoza\GoogleTranslate\GoogleTranslate;
    use App\Models\MusicScore;
    use Illuminate\Support\Facades\Cache;

    // Obtener el idioma actual
    $locale = App::getLocale();

    // Configurar Google Translate
    $etr = new GoogleTranslate();
    $etr->setSource('es')->setTarget($locale); // Configuración de idioma

    // Traducciones de metadatos
    $translations = [
        'meta_description' =>
            'Faristol es una plataforma para músicos y compositores, ofreciendo acceso a partituras musicales con diferentes planes de suscripción y herramientas exclusivas. Protege los derechos de autor.',
        'meta_keywords' =>
            'Faristol, música, compositores, partituras, suscripción, música en línea, protección de derechos de autor',
        'og_title' => 'Faristol - Partituras para Músicos y Compositores',
        'og_description' =>
            'Con Faristol, conecta con partituras musicales exclusivas. Ideal para músicos y compositores con planes de suscripción y protección de derechos de autor.',
        'title' => 'Faristol - Plataforma de Partituras para Músicos y Compositores',
        'todas_partituras_musicales' => 'Todas las Partituras Musicales',
        'enlaces_parturas_idioma' => 'Enlaces a las Partituras Musicales en tu Idioma',
        'faristol_partituras_musicales' => 'Faristol Partituras Musicales',
        'lista_parturas_musicales_pdf' => 'Lista de Partituras Musicales en Formato PDF',
        'es_mapa_sitio_aplicacion_faristol' => 'Este es el Mapa del Sitio de la Aplicación Web Faristol',
    ];

    // Traducir los metadatos
    foreach ($translations as $key => $text) {
        $translations[$key] = $etr->translate($text);
    }

    // Obtener todas las partituras ordenadas por nombre
    $musicScores = MusicScore::orderBy('name')->get();

    // Traducir los nombres de las partituras con cache
    $translatedScores = $musicScores
        ->map(function ($score) use ($etr, $locale) {
            $cacheKey = 'score_name_' . $score->id . '_' . $locale;

            // Verificar si la traducción está en cache
            $translated_name = Cache::remember($cacheKey, now()->addWeek(), function () use ($etr, $score) {
                return $etr->translate($score->name);
            });

            $score->translated_name = $translated_name;
            return $score;
        })
        ->sortBy(function ($score) {
            return $score->translated_name; // Ordenar por el nombre traducido
        });
@endphp

<!DOCTYPE html>
<html lang="{{ $locale }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ $translations['meta_description'] }}">
    <meta name="keywords" content="{{ $translations['meta_keywords'] }}">
    <meta property="og:title" content="{{ $translations['og_title'] }}">
    <meta property="og:description" content="{{ $translations['og_description'] }}">
    <meta property="og:type" content="website">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('web/favicon.png') }}" />

    <title>{{ $translations['title'] }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        /* Estilos de la página */
        body {
            font-family: 'Poppins', 'Roboto', sans-serif;
            margin: 0;
            padding: 0;
            color: antiquewhite;
            background-color: #060e1d;
        }

        .container {
            width: 80%;
            margin: 20px auto;
            background-color: #0c1934;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        header {
            text-align: center;
            margin-bottom: 30px;
        }

        header img {
            max-width: 150px;
            margin-bottom: 0px;
        }

        h1 {
            color: antiquewhite;
            font-size: 2em;
            margin-top: 0;
            margin-bottom: 40px;
        }

        h2 {
            color: antiquewhite;
            font-size: 1.6em;
            margin-bottom: 10px;
        }

        ul {
            list-style-type: none;
            padding: 0;
        }

        li {
            font-size: 18px;
            margin-bottom: 10px;
        }

        a {
            text-decoration: none;
            color: antiquewhite;
            transition: color 0.3s ease;
        }

        a:hover {
            color: white;
        }

        .footer {
            text-align: center;
            margin-top: 40px;
            font-size: 18px;
            color: #777;
        }

        .social-links {
            margin-top: 20px;
        }

        .social-links a {
            margin: 0 10px;
            color: antiquewhite;
            font-size: 18px;
            text-decoration: none;
        }

        html {
            background-color: #060e1d;
            color: antiquewhite;
        }

        a {
            color: antiquewhite;
        }

        .imagen {
            height: 250px;
            display: block;
        }
    </style>
</head>

<body>
    <div class="container">
        <header>
            <img src="{{ asset('web/icons/Icon-512.png') }}" alt="Logo de Faristol">
            <h1>{{ $translations['title'] }}</h1>
        </header>

        @if($defaultLang)
            <h2>{{ $translations['es_mapa_sitio_aplicacion_faristol'] }}</h2>
        @endif

        <!-- Sección de partituras -->
        <section>
            <h2>{{ $translations['todas_partituras_musicales'] }}</h2>
            <ul>
                @foreach ($translatedScores as $score)
                    <li><a href="{{ route('score-viewbyname', ['name' => rawurlencode($score->name)]) }}"
                            target="_blank">{{ ucfirst($score->translated_name) }}</a>
                    </li>
                @endforeach
            </ul>
        </section>

        <!-- Enlaces por idioma -->
        <section>
            <h2>{{ $translations['enlaces_parturas_idioma'] }}</h2>
            <ul>
                @foreach ($translatedScores as $score)
                    <li><a href="{{ route('la-score-viewbyname', ['lang' => $locale, 'name' => rawurlencode($score->name)]) }}"
                            target="_blank">{{ ucfirst($score->translated_name) }}
                            ({{ ucfirst($locale) }})
                        </a></li>
                @endforeach
            </ul>
        </section>

        <!-- Enlaces por idioma -->
        <section>
            <h2>{{ $translations['lista_parturas_musicales_pdf'] }}</h2>
            <ul>
                @foreach ($translatedScores as $score)
                    <li><a href="{{ route('getPdfByLangAndName', ['locale' => $locale, 'name' => rawurlencode($score->name)]) }}"
                            target="_blank">{{ ucfirst($score->translated_name) }}
                            ({{ ucfirst($locale) }})
                        </a></li>
                    <li><a href="{{ route('getPdfByLangAndName', ['locale' => $locale, 'name' => rawurlencode($score->name)]) }}"
                            target="_blank"><img class="imagen"
                                src="{{ route('showImageByLangAndName', ['locale' => $locale, 'name' => rawurlencode($score->name)]) }}" /></a>
                    </li>
                @endforeach
            </ul>
        </section>

        <!-- Pie de página -->
        <div class="footer">
            <p>&copy; {{ date('Y') }} - {{ $translations['faristol_partituras_musicales'] }}</p>
        </div>
    </div>
</body>

</html>
