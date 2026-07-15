

{{-- Extends layout --}}
@extends('layout.default')

{{-- Content --}}
@section('content')
   
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="">
                    <header>
                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-600">
                            {{ __('Information About Composer') }}
                        </h2>

                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            {{ __("Composer information.") }}
                        </p>
                    </header>
                    <table  class="table">
                        <!-- <thead>
                            <tr>
                                <th>Attribute</th>
                                <th>Value</th>
                            </tr>
                        </thead> -->
                        <tbody>
                            @foreach($composer->toArray() as $key => $value)
                                <tr>
                                    <td>{{ ucfirst(str_replace('_', ' ', $key)) }}</td>
                                    <td>
                                        @if ($value !== null)
                                            @if ($key === 'created_at' && $value || $key === 'updated_at' && $value || $key === 'deleted_at' && $value)
                                                {{ \Carbon\Carbon::parse($value)->format('d/m/Y') }}
                                            @else
                                                {{ $value }}
                                            @endif
                                        @else
                                            NA
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <form method="post" action="{{ route('update_composer_status', $composer->id) }}" class="mt-6 space-y-6">
                        @csrf
                        <div>                                
                            <x-input-label for="kt_datatable_search_status" :value="__('Composer Status')" />
                            <select class="form-control composer_status" id="kt_datatable_search_status" name="composer_status">
                                @foreach($composer_all_status as $status)
                                    <option value="{{ $status->id }}" {{ old('composer_status', $composer_request->composer_status_id) == $status->id ? 'selected' : '' }}>{{ $status->name }}</option>
                                @endforeach
                            </select>   
                            <x-input-error class="mt-2" :messages="$errors->get('composer_status')" />                        
                        </div>
                        <div class="flex items-center gap-4">
                            <x-primary-button>{{ __('Save') }}</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>

           
        </div>
    </div>

@endsection

@section('styles')
    <link href="https://cdn.jsdelivr.net/gh/gitbrent/bootstrap4-toggle@3.6.1/css/bootstrap4-toggle.min.css" rel="stylesheet">
@endsection


{{-- Scripts Section --}}
@section('scripts')
    <script src="{{ asset('js/pages/widgets.js') }}" type="text/javascript"></script>
    <script src="https://cdn.jsdelivr.net/gh/gitbrent/bootstrap4-toggle@3.6.1/js/bootstrap4-toggle.min.js"></script>

{{--custom js--}}
    <script src="{{ asset('js/custom.js') }}?v={{ date('YmdH') }}" type="text/javascript"></script>
@endsection
