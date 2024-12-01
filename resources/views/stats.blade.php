<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estadísticas del Sitio</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="container my-5">
        <h1 class="mb-4 text-center">📊 Estadísticas del Sitio</h1>

        <!-- Formulario de filtro por fecha -->
        <form method="GET" action="{{ route('stats') }}" class="mb-4">
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="start_date" class="form-label">Fecha Inicio:</label>
                    <input type="date" name="start_date" id="start_date" class="form-control" value="{{ $startDate ?? '' }}">
                </div>
                <div class="col-md-4">
                    <label for="end_date" class="form-label">Fecha Fin:</label>
                    <input type="date" name="end_date" id="end_date" class="form-control" value="{{ $endDate ?? '' }}">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Filtrar</button>
                </div>
            </div>
        </form>

        <!-- Estadísticas por página -->
        <h2 class="mt-5">📄 Estadísticas por Página</h2>
        @if ($statistics->isEmpty())
            <p class="text-center text-muted">No hay datos disponibles para el rango de fechas seleccionado.</p>
        @else
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Página</th>
                        <th>Visitas</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($statistics as $stat)
                        <tr>
                            <td><a href="{{ str_replace('%2F', '/', rawurlencode($stat->page)) }}" target="_blank">{{ $stat->page }}</a></td>
                            <td>{{ $stat->views }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <!-- Estadísticas por fecha -->
        <h2 class="mt-5">📅 Estadísticas por Fecha</h2>
        @if ($statisticsByDate->isEmpty())
            <p class="text-center text-muted">No hay datos disponibles para el rango de fechas seleccionado.</p>
        @else
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Fecha</th>
                        <th>Visitas Totales</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($statisticsByDate as $dateStat)
                        <tr>
                            <td>{{ $dateStat->date }}</td>
                            <td>{{ $dateStat->total_views }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <!-- Gráficos -->
        @if (!$statistics->isEmpty() || !$statisticsByDate->isEmpty())
            <div class="mt-5">
                <h2>📊 Gráficos de Visitas</h2>
                <div class="row g-4">
                    <div class="col-md-6">
                        <h4 class="text-center">Visitas por Página</h4>
                        <canvas id="pageChart"></canvas>
                    </div>
                    <div class="col-md-6">
                        <h4 class="text-center">Visitas por Fecha</h4>
                        <canvas id="dateChart"></canvas>
                    </div>
                </div>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    // Gráfico de visitas por página
                    const pageCtx = document.getElementById('pageChart').getContext('2d');
                    new Chart(pageCtx, {
                        type: 'bar',
                        data: {
                            labels: @json($statistics->pluck('page')),
                            datasets: [{
                                label: 'Visitas',
                                data: @json($statistics->pluck('views')),
                                backgroundColor: 'rgba(54, 162, 235, 0.5)',
                                borderColor: 'rgba(54, 162, 235, 1)',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            }
                        }
                    });

                    // Gráfico de visitas por fecha
                    const dateCtx = document.getElementById('dateChart').getContext('2d');
                    new Chart(dateCtx, {
                        type: 'line',
                        data: {
                            labels: @json($statisticsByDate->pluck('date')),
                            datasets: [{
                                label: 'Visitas Totales',
                                data: @json($statisticsByDate->pluck('total_views')),
                                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                                borderColor: 'rgba(255, 99, 132, 1)',
                                borderWidth: 1,
                                tension: 0.3,
                                fill: true
                            }]
                        },
                        options: {
                            responsive: true,
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            }
                        }
                    });
                });
            </script>
        @endif
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
