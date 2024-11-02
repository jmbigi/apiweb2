

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
                            {{ __('Music Style Information') }}
                        </h2>

                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            {{ __("Update music style's information.") }}
                        </p>
                    </header>
                    <form method="post" action="{{ route('style_music.update', $style_music->id) }}" class="mt-6 space-y-6">
                        @csrf
                        @method('patch')
                        <div>
                            <x-input-label for="name" :value="__('Name')" />
                            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $style_music->name)" required autofocus autocomplete="name" />
                            <x-input-error class="mt-2" :messages="$errors->get('name')" />
                        </div>
                        
                        <div class="user_toggle">
                            <x-input-label for="status" :value="__('Status')" />
                                <?php $checked = $style_music->approved ? 'checked' : '' ?>
                                <input type="hidden" name="style_status" value="0">
                                <input data-id="{{$style_music->id}}" name="style_status" class="toggle-class" type="checkbox" data-on="Active" data-off="Suspended"  data-toggle="toggle" data-offstyle="danger" {{ $checked }} >
                            <x-input-error class="mt-2" :messages="$errors->get('style_status')" />
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
    <script src="{{ asset('js/custom.js') }}" type="text/javascript"></script>
@endsection
