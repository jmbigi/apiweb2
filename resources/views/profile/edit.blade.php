

{{-- Extends layout --}}
@extends('layout.default')

{{-- Content --}}
@section('content')

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <!-- <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div> -->

            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>

@endsection



@section('styles')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.13/css/intlTelInput.css" rel="stylesheet">
@endsection

{{-- Scripts Section --}}
@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.13/js/intlTelInput-jquery.min.js"></script>
    <script src="{{ asset('js/pages/widgets.js') }}" type="text/javascript"></script>
    
    <script>
        $(document).ready(function() { 
            var mobileInput = $("#phone");
            var selected_country = $('#phone').data('country');
            
            if(selected_country){                
                var dialCodeToFind = selected_country.toString(); // Example: spain's dial code    
                var countryData = window.intlTelInputGlobals.getCountryData();
                var iso2 = null;

                for (var i = 0; i < countryData.length; i++) {
                    if (countryData[i].dialCode === dialCodeToFind) {
                        iso2 = countryData[i].iso2;
                        break; // Exit the loop once a match is found
                    }
                }
    
                if (iso2) {
                    mobileInput.intlTelInput({
                        initialCountry: iso2,
                        separateDialCode: true,
                    });
                } else {
                    mobileInput.intlTelInput({
                        initialCountry: "es",
                        separateDialCode: true,
                    });
                }
            }   
            else{
                mobileInput.intlTelInput({
                    initialCountry: "es",
                    separateDialCode: true,
                });
            }        
            $("#phone").on("countrychange", function (e, countryData) {                
                var countryCode = $("#country_code").val($("#phone").intlTelInput("getSelectedCountryData").dialCode);    
            });            
        });

    </script>
@endsection
