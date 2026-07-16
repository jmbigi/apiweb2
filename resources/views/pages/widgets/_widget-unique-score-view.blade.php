{{-- Stats Widget unique-score-view --}}

<div class="card card-custom info_width {{ @$class }}">
    <div class="card-body d-flex flex-column p-0">
        <div class="d-flex align-items-center justify-content-between card-spacer flex-grow-1">
            <div class="d-flex flex-column mr-2">
                <a href="#" class="text-dark-75 text-hover-primary font-weight-bolder font-size-h5 card_title">Weekly Unique Score Views</a>
            </div>
            <span class="symbol symbol-light-success symbol-45">
                <span class="text-dark-75 font-weight-bolder font-size-h3">{{ $unique_score_view_count }}</span>
            </span>
        </div>
        <!-- Añade una clase o modifica el estilo inline para ajustar el ancho -->
        <div id="kt_stats_widget_unique_score_view_chart" class="card-rounded-bottom" style="height: 150px; width: 100%; background: white !important;"></div>
    </div>
</div>
