

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
                            {{ __('Information About Music Score') }}
                        </h2>

                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            {{ __("Music Score information.") }}
                        </p>
                    </header>
                    <table  class="table">
                        <!-- <thead>
                            <tr>
                                <th>Attribute</th>
                                <th>Value</th>
                            </tr>
                        </thead> -->
                        <?php
                            $data = $music_scores->toArray();
                            $data = array_diff_key($data, ['style_musics' => '','instruments'=> '','links_info'=> '']);
                        ?>
                        <tbody>
                            @foreach($data as $key => $value)
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
                            <tr>
                                <td>Total View</td>
                                <td>{{ $total_view }}</td>
                            </tr>
                            <tr>
                                <td>Comoser Name</td>
                                <td><a href="{{ route('composer.show',$composer_id) }}">{{ $composer_name ? $composer_name : 'NA' }}</a></td>
                            </tr>
                            <tr>
                                <td>PDF Name</td>
                                <td>{{ $pdf_name ? $pdf_name : 'NA' }}</td>
                            </tr>
                            <tr>
                                <td>Music Style</td>
                                <td>
                                    @if($style_of_music)
                                        @foreach($style_of_music as $style)
                                        {{ $style }},
                                        @endforeach
                                    @else
                                        NA
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td>Music Instrument</td>
                                <td> 
                                    @if($instrument_of_music)
                                        @foreach($instrument_of_music as $instrument)
                                        {{ $instrument ? $instrument : 'NA' }},
                                        @endforeach
                                    @else
                                        NA
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td>Links</td>
                                <td><a href="{{ $link_of_music }}" target="_blank">{{ $link_of_music ? $link_of_music : 'NA' }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <form method="post" action="{{ route('music_score_status_update', $music_scores->id) }}" class="mt-6 space-y-6">
                        @csrf
                        <div class="user_toggle">
                            <x-input-label for="status" :value="__('Status')" />
                                <?php $checked = $music_scores->status ? 'checked' : '' ?>
                                <input type="hidden" name="music_score_status" value="0">
                                <input data-id="{{$music_scores->id}}" name="music_score_status" class="toggle-class" type="checkbox" data-on="Active" data-off="Suspended"  data-toggle="toggle" data-offstyle="danger" {{ $checked }} >
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
