{{-- Content --}}
@if (config('layout.content.extended'))
    @yield('content')
@else
    <div class="d-block flex-column-fluid">
        <div class="{{ Metronic::printClasses('content-container', false) }}">
            @yield('content')
        </div>
    </div>
@endif
