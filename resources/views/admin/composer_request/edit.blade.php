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
                        {{ __('Information About Composer Request') }}
                    </h2>

                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        {{ __("Update composer request information.") }}
                    </p>
                </header>
                <form method="post" action="{{ route('composer_request.update', $composer_request->id) }}" class="mt-6 space-y-6">
                    @csrf
                    @method('patch')
                    <!-- <div>
                            <x-input-label for="name" :value="__('Name')" />
                            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $composer->name)" required autofocus  />
                            <x-input-error class="mt-2" :messages="$errors->get('name')" />
                        </div>

                        <div>
                            <x-input-label for="surname" :value="__('Last Name')" />
                            <x-text-input id="surname" name="surname" type="text" class="mt-1 block w-full" :value="old('name', $composer->surname)" required autofocus  />
                            <x-input-error class="mt-2" :messages="$errors->get('surname')" />
                        </div> -->

                    <div>
                        <x-input-label for="public_name" :value="__('Artistic Name')" />
                        <x-text-input id="public_name" name="public_name" type="text" class="mt-1 block w-full" :value="old('name', $composer->public_name)" readonly required autofocus />
                        <x-input-error class="mt-2" :messages="$errors->get('public_name')" />
                    </div>

                    <div>
                        <x-input-label for="notification_email" :value="__('Notification Email')" />
                        <x-text-input id="notification_email" name="notification_email" type="text" class="mt-1 block w-full" style="background-color: lightgrey" :value="old('notification_email', $composer->notification_email)" readonly required autofocus />
                        <x-input-error class="mt-2" :messages="$errors->get('notification_email')" />
                    </div>

                    <div>
                        <x-input-label for="notify" :value="__('Notify')" />
                        <input id="notify" name="notify" type="checkbox" class="mt-1 block" :value="1" checked />
                        <x-input-error class="mt-2" :messages="$errors->get('notify')" />
                    </div>

                    <!-- <div>
                            <x-input-label for="telephone" :value="__('Phone Number')" />
                            <x-text-input id="telephone" name="telephone" type="text" class="mt-1 block w-full" :value="old('name', $composer->telephone)" required autofocus  />
                            <x-input-error class="mt-2" :messages="$errors->get('telephone')" />
                        </div>

                        <div>
                            <x-input-label for="vat_number" :value="__('VAT Number')" />
                            <x-text-input id="vat_number" name="vat_number" type="text" class="mt-1 block w-full" :value="old('name', $composer->vat_number)" required autofocus  />
                            <x-input-error class="mt-2" :messages="$errors->get('vat_number')" />
                        </div>

                        <div>
                            <x-input-label for="created_date" :value="__('Registered Date')" />
                            <?php
                            $dateTime = new DateTime($composer_request->created_at);
                            $formattedDate = $dateTime->format('d-m-Y');
                            ?>
                            <x-text-input id="created_date" name="created_date" type="text" class="mt-1 block w-full" :value="old('requested_date', $formattedDate)" readonly required />
                            <x-input-error class="mt-2" :messages="$errors->get('created_date')" />
                        </div>

                        <div>
                            <x-input-label for="updated_date" :value="__('Last Updated Date')" />
                            <?php
                            $dateTime = new DateTime($composer_request->updated_at);
                            $formattedDate = $dateTime->format('d-m-Y');
                            ?>
                            <x-text-input id="updated_date" name="updated_date" type="text" class="mt-1 block w-full" :value="old('requested_date', $formattedDate)" readonly required />
                            <x-input-error class="mt-2" :messages="$errors->get('updated_date')" />
                        </div> -->

                    <div>
                        <x-input-label for="kt_datatable_search_status" :value="__('Composer Status')" />
                        <select class="form-control composer_status" id="kt_datatable_search_status" name="composer_status">
                            @foreach($composer_all_status as $status)
                            <option value="{{ $status->id }}" {{ old('composer_status', $composer_request->composer_status_id) == $status->id ? 'selected' : '' }}>{{ $status->name }}</option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('composer_status')" />
                    </div>

                    <div class="status_comment">
                        <x-input-label for="reject_comment" :value="__('Why is Rejected?')" />
                        <textarea id="reject_comment" name="reject_comment" type="text" class="form-control" autofocus>{{old('reject_comment',$composer_request->comment)}}</textarea>
                        <x-input-error class="mt-2 status_err" :messages="$errors->get('reject_comment')" />
                    </div>

                    <div>
                        <x-input-label for="request_status" :value="__('Request Status')" />
                        <select class="form-control" id="kt_datatable_search_status" name="request_status">
                            @foreach($request_all_status as $status)
                            <option value="{{ $status->id }}" {{ ($composer_request->request_status_id == $status->id) ? 'selected' : '' }}>{{ $status->name }}</option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('request_status')" />
                    </div>

                    <!-- <div class="user_toggle">
                            <x-input-label for="status" :value="__('Status')" />
                                <?php
                                if ($composer_request->composer_status_id == 2) {
                                    $checked = 'checked';
                                } else {
                                    $checked = '';
                                }
                                ?>
                                <input type="hidden" name="user_status" value="0">
                                <input data-id="{{$composer_request->id}}" name="user_status" class="toggle-class" type="checkbox" data-on="Active" data-off="Suspended"  data-toggle="toggle" data-offstyle="danger" {{ $checked }} >
                            <x-input-error class="mt-2" :messages="$errors->get('email')" />
                        </div> -->

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
<script src="{{ asset('js/custom.js') }}" type="text/javascript"></script>
@endsection