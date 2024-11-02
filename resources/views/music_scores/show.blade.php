{{-- resources/views/music_scores/show.blade.php --}}

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $musicScore->name }}</title>
</head>
<body>
    <h1>{{ $musicScore->name }}</h1>
    <p>{{ $musicScore->description }}</p>
    
    {{-- Agrega más información o enlaces si es necesario --}}
</body>
</html>
