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
                            {{ __('Subscription Plan') }}
                        </h2>

                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            {{ __("Add new Subscription Plan") }}
                        </p>
                    </header>
                    <form method="post" action="{{ route('subscription.store') }}" class="mt-6 space-y-6">
                        @csrf
                        <div>
                            <x-input-label for="name" :value="__('Name')" />
                            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name')"  autofocus autocomplete="name" />
                            <x-input-error class="mt-2" :messages="$errors->get('name')" />
                        </div>
                        <div>
                            <div class="d-flex gap-4">
                                <x-input-label for="description" :value="__('Description')" />
                                <x-input-label for="description" :value="__('(Please add / after each setence.)')" />
                            </div>
                            <x-text-input id="description" name="description" type="text" class="mt-1 block w-full" :value="old('description')"   />
                            <x-input-error class="mt-2" :messages="$errors->get('description')" />
                        </div>

                        <div class="plan_price">
                            <div>
                                <x-input-label for="price" :value="__('Price')" />
                                <input type="text" id="price" name="price" class="intl-tel-input" value="{{ old('price') }}" >
                                <x-input-error class="mt-2" :messages="$errors->get('price')" />
                            </div>
                            <!-- <div>
                                <x-input-label for="plan_type" :value="__('Plan type')" />                                
                                    <input type="hidden" name="plan_type" value="0">
                                    <input name="plan_type" class="toggle-class" value="1" type="checkbox" data-on="Paid" data-off="Free"  data-toggle="toggle" data-offstyle="danger" checked >
                                <x-input-error class="mt-2" :messages="$errors->get('plan_type')" />
                            </div> -->
                        </div>

                        <div>
                            <x-input-label for="start_date" :value="__('Start Date')" />
                            <input type="date" id="start_date" name="start_date" class="mt-1 block w-full" value="{{ old('start_date') }}"   />
                            <x-input-error class="mt-2" :messages="$errors->get('start_date')" />
                        </div>

                        <div>
                            <x-input-label for="end_date" :value="__('End Date')" />
                            <input type="date" id="end_date" name="end_date"  class="mt-1 block w-full" value="{{ old('end_date') }}"   />
                            <x-input-error class="mt-2" :messages="$errors->get('end_date')" />
                        </div>

                        <div>
                            <x-input-label for="family_instrument" :value="__('Plan Type')" />
                            <select id="subscription_type" name="subscription_type" class="w-full">
                                <option value="0">FREE</option>
                                <option value="1">BASIC</option>
                                <option value="2">PREMIUM</option>
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('subscription_type')" />
                        </div>

                        <div class="user_toggle">
                            <x-input-label for="status" :value="__('Status')" />                                
                                <input type="hidden" name="plan_status" value="0">
                                <input name="plan_status" class="toggle-class" value="1" type="checkbox" data-on="Active" data-off="Suspended"  data-toggle="toggle" data-offstyle="danger" checked >
                            <x-input-error class="mt-2" :messages="$errors->get('plan_status')" />
                        </div>
                        
                        <div class="flex items-center gap-4">
                            <x-primary-button>{{ __('Save') }}</x-primary-button>                        </div>
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

@endsection
