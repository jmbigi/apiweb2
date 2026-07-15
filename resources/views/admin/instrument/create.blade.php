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
                        {{ __('Instrument Create') }}
                    </h2>

                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        {{ __("Create instrument.") }}
                    </p>
                </header>
                <form method="post" action="{{ route('instrument.store') }}" class="mt-6 space-y-6">
                    @csrf
                    <div>
                        <x-input-label for="name" :value="__('Name')" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" autofocus autocomplete="name" />
                        <x-input-error class="mt-2" :messages="$errors->get('name')" />
                    </div>
                    <div>
                        <x-input-label for="family_instrument" :value="__('Name')" />
                        <select id="family_instrument" name="family_instrument" class="w-full" >
                            <option value=""></option>
                            @foreach($family_instruments as $family_instrument)
                            <option value="{{ $family_instrument->id }}">{{ $family_instrument->name }}</option>
                            @endforeach
                            <!-- Add more options as needed -->
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('family_instrument')" />
                    </div>

                    <div class="user_toggle">
                        <x-input-label for="status" :value="__('Status')" />
                        <input type="hidden" name="instrument_status" value="0">
                        <input data-id="" name="instrument_status" class="toggle-class" type="checkbox" data-on="Active" data-off="Suspended" data-toggle="toggle" data-offstyle="danger">
                        <x-input-error class="mt-2" :messages="$errors->get('instrument_status')" />
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
        <link href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.13/css/intlTelInput.css" rel="stylesheet">

        @endsection


        {{-- Scripts Section --}}
        @section('scripts')
        <script src="{{ asset('js/pages/widgets.js') }}" type="text/javascript"></script>
        <script src="https://cdn.jsdelivr.net/gh/gitbrent/bootstrap4-toggle@3.6.1/js/bootstrap4-toggle.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.13/js/intlTelInput-jquery.min.js"></script>

        {{--custom js--}}
        <script src="{{ asset('js/custom.js') }}?v={{ date('YmdH') }}" type="text/javascript"></script>
        <script>
            $(document).ready(function() {
                const select2 = $('#family_instrument').select2({
                    placeholder: "Select family instrument",
                    allowClear: true
                });
            });
        </script>
        @endsection