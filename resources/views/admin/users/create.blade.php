

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
                            {{ __('User') }}
                        </h2>

                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            {{ __("Create new user") }}
                        </p>
                    </header>
                    <form method="post" action="{{ route('user.store') }}" class="mt-6 space-y-6">
                        @csrf
                        <div>
                            <x-input-label for="name" :value="__('Name')" />
                            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" required autofocus autocomplete="name" />
                            <x-input-error class="mt-2" :messages="$errors->get('name')" />
                        </div>
                        <div>
                            <x-input-label for="email" :value="__('Email')" />
                            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email')" required autocomplete="email" />
                            <x-input-error class="mt-2" :messages="$errors->get('email')" />
                        </div>

                        <div>
                            <x-input-label for="telephone" :value="__('Telephone')" />
                            <input type="hidden" name="country_code" id="country_code" value="">                            
                            <input type="tel" id="phone" name="telephone" class="intl-tel-input" value="{{ old('telephone') }}" >
                            <x-input-error class="mt-2" :messages="$errors->get('telephone')" />
                        </div>

                        <div>
                            <x-input-label for="email" :value="__('Password')" />
                            <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" :value="old('password')" required  />
                            <x-input-error class="mt-2" :messages="$errors->get('password')" />
                        </div>

                        <div class="user_toggle">
                            <x-input-label for="status" :value="__('Status')" />                                
                                <input type="hidden" name="user_status" value="0">
                                <input name="user_status" class="toggle-class" type="checkbox" data-on="Active" data-off="Suspended"  data-toggle="toggle" data-offstyle="danger" checked >
                            <x-input-error class="mt-2" :messages="$errors->get('Status')" />
                        </div>
                        
                        <div class="flex items-center gap-4">
                            <x-primary-button>{{ __('Save') }}</x-primary-button>

                            @if (session('status') === 'profile-updated')
                                <p
                                    x-data="{ show: true }"
                                    x-show="show"
                                    x-transition
                                    x-init="setTimeout(() => show = false, 2000)"
                                    class="text-sm text-gray-600 dark:text-gray-400"
                                >{{ __('Saved.') }}</p>
                            @endif
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
            var mobileInput = $("#phone");
            // var selected_country = $('#phone').data('country');
            
            // if(selected_country){                
            //     var dialCodeToFind = selected_country.toString(); // Example: India's dial code    
            //     var countryData = window.intlTelInputGlobals.getCountryData();
            //     var iso2 = null;

            //     for (var i = 0; i < countryData.length; i++) {
            //         if (countryData[i].dialCode === dialCodeToFind) {
            //             iso2 = countryData[i].iso2;
            //             break; // Exit the loop once a match is found
            //         }
            //     }
    
            //     if (iso2) {
            //         mobileInput.intlTelInput({
            //             initialCountry: iso2,
            //             separateDialCode: true,
            //         });
            //     } else {
            //         mobileInput.intlTelInput({
            //             initialCountry: "in",
            //             separateDialCode: true,
            //         });
            //     }
            // }   
            // else{
                mobileInput.intlTelInput({
                    initialCountry: "es",
                    separateDialCode: true,
                });
            // }        
            $("#phone").on("countrychange", function (e, countryData) {                
                var countryCode = $("#country_code").val($("#phone").intlTelInput("getSelectedCountryData").dialCode);    
            });            
        });

    </script>
@endsection
