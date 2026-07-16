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
                        {{ __('Edit Ensemble') }}
                    </h2>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        {{ __("Edit ensemble details.") }}
                    </p>
                </header>
                <form method="post" action="{{ route('ensemble.update', $ensemble->id) }}" class="mt-6 space-y-6">
                    @csrf
                    @method('PATCH')
                    <div>
                        <x-input-label for="name" :value="__('Name')" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $ensemble->name)" required />
                        <x-input-error class="mt-2" :messages="$errors->get('name')" />
                    </div>
                    <div>
                        <x-input-label for="cif" :value="__('CIF')" />
                        <x-text-input id="cif" name="cif" type="text" class="mt-1 block w-full" :value="old('cif', $ensemble->cif)" />
                        <x-input-error class="mt-2" :messages="$errors->get('cif')" />
                    </div>
                    <div>
                        <x-input-label for="description" :value="__('Description')" />
                        <textarea id="description" name="description" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" rows="3">{{ old('description', $ensemble->description) }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('description')" />
                    </div>
                    <div>
                        <x-input-label for="owner_id" :value="__('Owner')" />
                        <select id="owner_id" name="owner_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                            <option value="">Select owner</option>
                            @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ old('owner_id', $ensemble->owner_id) == $user->id ? 'selected' : '' }}>{{ $user->name }} ({{ $user->email }})</option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('owner_id')" />
                    </div>
                    <div>
                        <x-input-label for="status" :value="__('Status')" />
                        <input type="hidden" name="status" value="0">
                        <input name="status" class="toggle-class" type="checkbox" data-on="Active" data-off="Inactive" data-toggle="toggle" data-offstyle="danger" {{ $ensemble->status ? 'checked' : '' }}>
                        <x-input-error class="mt-2" :messages="$errors->get('status')" />
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

{{-- Scripts Section --}}
@section('scripts')
<script src="https://cdn.jsdelivr.net/gh/gitbrent/bootstrap4-toggle@3.6.1/js/bootstrap4-toggle.min.js"></script>
<script src="{{ asset('js/app.js') }}" type="text/javascript"></script>
    @if(Session::has('success'))
        <script>
            var success = "{{ Session::get('success') }}";
            toastr.success(success);
        </script>
    @endif
@endsection
