@php
    use Illuminate\Support\Str;
    use Stichoza\GoogleTranslate\GoogleTranslate;
    use App\Models\MusicScore;
    use Illuminate\Support\Facades\Cache;

    $locale = App::getLocale();
    $etr = new GoogleTranslate();
    $etr->setSource('es')->setTarget($locale); // Configuración de idioma

    // Traducciones
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
    ];

    // Realizamos la traducción para cada clave
    foreach ($translations as $key => $text) {
        $translations[$key] = $etr->translate($text);
    }

    // Obtener todas las partituras ordenadas por nombre
    $musicScores = MusicScore::orderBy('name')->get();

    // Traducir los nombres de las partituras con cache
    $translatedScores = $musicScores
        ->map(function ($score) use ($etr, $locale) {
            // Verificar si la traducción ya está en cache
            $cacheKey = 'score_name_' . $score->id . '_' . $locale;

            // Si no está en cache, traducir y almacenar
            $translated_name = Cache::remember($cacheKey, now()->addWeek(), function () use ($etr, $score) {
                return $etr->translate($score->name);
            });

            $score->translated_name = $translated_name;
            return $score;
        })
        ->sortBy(function ($score) {
            // Ordenar por el nombre traducido
            return $score->translated_name;
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
    <title>{{ $translations['title'] }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f4f7fa;
            margin: 0;
            padding: 0;
            color: #333;
        }

        .container {
            width: 80%;
            margin: 20px auto;
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 20px;
        }

        h2 {
            color: #16a085;
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
            color: #3498db;
            transition: color 0.3s ease;
        }

        a:hover {
            color: #2c3e50;
        }

        .footer {
            text-align: center;
            margin-top: 40px;
            font-size: 14px;
            color: #777;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>{{ $translations['title'] }}</h1>

        <section>
            <h2>{{ $translations['todas_partituras_musicales'] }}</h2>
            <ul>
                @foreach ($translatedScores as $score)
                    <li><a
                            href="{{ route('score-viewbyname', ['name' => $score->name]) }}">{{ $score->translated_name }}</a>
                    </li>
                @endforeach
            </ul>
        </section>

        <section>
            <h2>{{ $translations['enlaces_parturas_idioma'] }}</h2>
            <ul>
                @foreach ($translatedScores as $score)
                    <li><a href="{{ route('la-score-viewbyname', ['lang' => $locale, 'name' => $score->name]) }}">{{ $score->translated_name }}
                            ({{ ucfirst($locale) }})</a></li>
                @endforeach
            </ul>
        </section>

        <div class="footer">
            <p>&copy; {{ date('Y') }} - {{ $translations['faristol_partituras_musicales'] }}</p>
        </div>
    </div>
</body>

</html>
