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
                            {{ __('User Information') }}
                        </h2>

                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            {{ __("Update user's information.") }}
                        </p>
                    </header>
                    <form method="post" action="{{ route('user.update', $user->id) }}" class="mt-6 space-y-6">
                        @csrf
                        @method('patch')

                        <div>
                            <x-input-label for="name" :value="__('Name')" />
                            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                                :value="old('name', $user->name)" required autofocus autocomplete="name" />
                            <x-input-error class="mt-2" :messages="$errors->get('name')" />
                        </div>

                        <div>
                            <x-input-label for="email" :value="__('Email')" />
                            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full"
                                :value="old('email', $user->email)" required autocomplete="username" />
                            <x-input-error class="mt-2" :messages="$errors->get('email')" />
                        </div>

                        <div>
                            <x-input-label for="telephone" :value="__('Telephone')" />
                            <!-- <input type="hidden" name="country_code" id="country_code" value="">   -->

                            <?php
                            if (isset($user->telephone)) {
                                $phoneNumber = "$user->telephone"; // Replace this with your input value
                            
                                // Use a regular expression to match the country code and phone number
                                $pattern = '/\((\+(\d+))\)(\d+)/';
                                preg_match($pattern, $phoneNumber, $matches);
                            
                                if (count($matches) === 4) {
                                    $countryCode = $matches[2]; // Extracted country code, e.g., "+91"
                                    $phoneNumber = $matches[3]; // Extracted phone number, e.g., "1234567890"
                                }
                            }
                            
                            ?>
                            @if (isset($phoneNumber))
                                <input type="hidden" name="country_code" id="country_code" value="{{ $countryCode }}">
                                <input type="tel" id="phone" name="telephone" class="intl-tel-input"
                                    value="{{ old('telephone', $phoneNumber) }}" {!! isset($countryCode) ? 'data-country="' . $countryCode . '"' : '' !!}>
                            @else
                                <input type="hidden" name="country_code" id="country_code" value="">
                                <input type="tel" id="phone" name="telephone" class="intl-tel-input"
                                    value="{{ old('telephone') }}">
                            @endif

                            <!-- <input type="tel" id="phone" name="telephone" class="intl-tel-input" value="{{ old('telephone') }}" > -->
                            <x-input-error class="mt-2" :messages="$errors->get('telephone')" />
                        </div>

                        <div>
                            <x-input-label for="email" :value="__('Password')" />
                            <x-text-input id="password" name="password" type="password" class="mt-1 block w-full"
                                :value="old('password', $user->password)" required />
                            <x-input-error class="mt-2" :messages="$errors->get('password')" />
                        </div>

                        <div class="user_toggle">
                            <x-input-label for="status" :value="__('Status')" />
                            <?php $checked = $user->status ? 'checked' : ''; ?>
                            <input type="hidden" name="user_status" value="0">
                            <input data-id="{{ $user->id }}" name="user_status" class="toggle-class" type="checkbox"
                                data-on="Active" data-off="Suspended" data-toggle="toggle" data-offstyle="danger"
                                {{ $checked }}>
                            <x-input-error class="mt-2" :messages="$errors->get('status')" />
                        </div>
                        <?php $level = $subscription_data['level']; ?>
                        <div>
                            <x-input-label for="sync_subscr_type" :value="__('Change subscription plan (warning!)')" />
                            <select id="sync_subscr_type" name="sync_subscr_type" class="mt-1 block w-full"
                                style="background-color: lightgrey" required>
                                <option value="-1">No changes</option>
                                <option value="0">Free {{ $level == 0 ? '(Current)' : '' }}</option>
                                <option value="1">Basic {{ $level == 1 ? '(Current)' : '' }}</option>
                                <option value="2">Premium {{ $level == 2 ? '(Current)' : '' }}</option>
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('sync_subscr_type')" />
                            <!-- Warning about subscription changes -->
                            <div class="alert alert-warning" role="alert" id="subscription-warning"
                                style="display: none;">
                                <strong>Warning!</strong> Changing the subscription type may affect the user's current plan
                                and result in data loss.
                            </div>
                        </div>

                        <div>
                            <x-input-label for="premium_trial_count" :value="__('Premium trail count (warning!)')" />
                            <x-text-input id="premium_trial_count" name="premium_trial_count" type="number"
                                class="mt-1 block w-full" :value="old('premium_trial_count', $user->premiumTrial?->used_count)" style="background-color: lightgrey" />
                            <x-input-error class="mt-2" :messages="$errors->get('premium_trial_count')" />
                            <!-- Warning about subscription changes -->
                            <div class="alert alert-warning" role="alert" id="premium_trial_count-warning"
                                style="display: none;">
                                <strong>Warning!</strong> Changing the premium trial count may affect the premium trial
                                availability.
                            </div>
                        </div>
                        <div class="flex items-center gap-4">
                            <x-primary-button>{{ __('Save') }}</x-primary-button>
                            <a href="{{ route('user.sendUserRegisteredEmail', $user->id) }}"
                                class="btn btn-secondary">{{ __('Send Registered Email') }}</a>
                            <a href="{{ route('user.sendPlanOfferEmail', $user->id) }}"
                                class="btn btn-secondary">{{ __('Send Plan Offer Email') }}</a>

                            @if (session('status') === 'profile-updated')
                                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                                    class="text-sm text-gray-600 dark:text-gray-400">{{ __('Saved.') }}</p>
                            @endif

                            @if (session('status') === 'email-sent')
                                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 5000)"
                                    class="text-base text-green-600 dark:text-green-400">
                                    {{ __('Email sent successfully.') }}</p>
                            @endif
                        </div>
                    </form>

                </div>
            </div>


        </div>
    </div>
@endsection

@section('styles')
    <link href="https://cdn.jsdelivr.net/gh/gitbrent/bootstrap4-toggle@3.6.1/css/bootstrap4-toggle.min.css"
        rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.13/css/intlTelInput.css" rel="stylesheet">
@endsection


{{-- Scripts Section --}}
@section('scripts')
    <script src="{{ asset('js/pages/widgets.js') }}" type="text/javascript"></script>
    <script src="https://cdn.jsdelivr.net/gh/gitbrent/bootstrap4-toggle@3.6.1/js/bootstrap4-toggle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.13/js/intlTelInput-jquery.min.js"></script>

    {{-- custom js --}}
    <script src="{{ asset('js/custom.js') }}?v={{ date('YmdH') }}" type="text/javascript"></script>
    <script>
        $(document).ready(function() {
            var mobileInput = $("#phone");
            var selected_country = $('#phone').data('country');

            if (selected_country) {
                var dialCodeToFind = selected_country.toString(); // Example: India's dial code    
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
            } else {
                mobileInput.intlTelInput({
                    initialCountry: "es",
                    separateDialCode: true,
                });
            }
            $("#phone").on("countrychange", function(e, countryData) {
                var countryCode = $("#country_code").val($("#phone").intlTelInput("getSelectedCountryData")
                    .dialCode);
            });
        });
    </script>

    <script>
        $(document).ready(function() {
            // Detecta el cambio en el select
            $('#sync_subscr_type').on('change', function() {
                // Obtén el valor seleccionado
                var selectedValue = $(this).val();

                // Verifica si el valor seleccionado no es "-1" (Sin cambios)
                if (selectedValue !== "-1") {
                    // Si no es "-1", muestra el popup de advertencia
                    $('#subscription-warning').show();
                } else {
                    // Si es "-1", oculta el popup de advertencia
                    $('#subscription-warning').hide();
                }
            });

            // Detecta el cambio en el select
            $('#premium_trial_count').on('change', function() {
                // Obtén el valor seleccionado
                var selectedValue = $(this).val();

                // Verifica si el valor seleccionado no es "-1" (Sin cambios)
                if (selectedValue !== "{{ $user->premium_trail?->user_count }}") {
                    // Si no es "-1", muestra el popup de advertencia
                    $('#premium_trial_count-warning').show();
                } else {
                    // Si es "-1", oculta el popup de advertencia
                    $('#premium_trial_count-warning').hide();
                }
            });

        });
    </script>
@endsection
