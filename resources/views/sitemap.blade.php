@php
    use Illuminate\Support\Str;
    use Stichoza\GoogleTranslate\GoogleTranslate;
    use App\Models\MusicScore; // Asegúrate de tener el modelo correcto

    $locale = App::getLocale();

    $etr = new GoogleTranslate(); // La configuración por defecto es 'en' (Inglés)
    $etr->setSource('es'); // Idioma fuente (opcional)
    $etr->setTarget($locale); // Idioma destino

    $txt_meta_description = $etr->translate('Faristol es una plataforma para músicos y compositores, ofreciendo acceso a partituras musicales con diferentes planes de suscripción y herramientas exclusivas. Protege los derechos de autor.');
    $txt_meta_keywords = $etr->translate('Faristol, música, compositores, partituras, suscripción, música en línea, protección de derechos de autor');
    $txt_og_title = $etr->translate('Faristol - Partituras para Músicos y Compositores');
    $txt_og_description = $etr->translate('Con Faristol, conecta con partituras musicales exclusivas. Ideal para músicos y compositores con planes de suscripción y protección de derechos de autor.');
    $txt_title = $etr->translate('Faristol - Plataforma de Partituras para Músicos y Compositores');
    $txt_todas_partituras_musicales = $etr->translate('Todas las Partituras Musicales');
    $txt_enlaces_parturas_idioma = $etr->translate('Enlaces a las Partituras Musicales en tu Idioma');
    $txt_faristol_partituras_musicales = $etr->translate('Faristol Partituras Musicales');

    $musicScores = MusicScore::all();
@endphp

<!DOCTYPE html>
<html lang="{{ $locale }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ $txt_meta_description }}">
    <meta name="keywords" content="{{ $txt_meta_keywords }}">
    <meta property="og:title" content="{{ $txt_og_title }}">
    <meta property="og:description" content="{{ $txt_og_description }}">
    <meta property="og:type" content="website">
    <title>{{ $txt_title }}</title>
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
        <h1>{{ $txt_title }}</h1>

        <section>
            <h2>{{ $txt_todas_partituras_musicales }}</h2>
            <ul>
                @foreach ($musicScores as $score)
                    <li><a href="{{ route('score-viewbyname', ['name' => Str::slug($score->name)]) }}">{{ $score->name }}</a></li>
                @endforeach
            </ul>
        </section>

        <section>
            <h2>{{ $txt_enlaces_parturas_idioma }}</h2>
            <ul>
                @foreach ($musicScores as $score)
                    <li><a href="{{ route('la-score-viewbyname', ['lang' => $locale, 'name' => Str::slug($score->name)]) }}">{{ $score->name }} ({{ ucfirst($locale) }})</a></li>
                @endforeach
            </ul>
        </section>

        <div class="footer">
            <p>&copy; {{ date('Y') }} - {{ $txt_faristol_partituras_musicales }}</p>
        </div>
    </div>
</body>

</html>
