<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    {{ __("You're logged in!") }}
                </div>
            </div>
        </div>
    </div>

    <!-- Sección de exportaciones existente (donde están los otros botones de Excel) -->
    <div class="card-body">
        <div class="row">
            <!-- Botones de exportación existentes -->
            <div class="col-md-4 mb-3">
                <a href="{{ route('exportar_usuarios_suscritos') }}" class="btn btn-success btn-block">
                    <i class="fas fa-file-excel"></i>
                    Exportar Usuarios Suscritos
                </a>
            </div>

            <div class="col-md-4 mb-3">
                <a href="{{ route('exportar_uso') }}" class="btn btn-info btn-block">
                    <i class="fas fa-file-excel"></i>
                    Exportar Uso de Usuarios
                </a>
            </div>

            <!-- Nuevo botón para logs personales -->
            <div class="col-md-4 mb-3">
                <a href="{{ route('exportar_uso_offline') }}" class="btn btn-warning btn-block">
                    <i class="fas fa-file-excel"></i>
                    Exportar Uso Offline
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
