{{-- Extends layout --}}
@extends('layout.default')

{{-- Content --}}
@section('content')
    {{-- Dashboard 1 --}}
    <input type="hidden" value="{{ json_encode($monthlyUserCounts) }}" name="mothly_user" id="monthly_user_counts" />
    <input type="hidden" value="{{ json_encode($monthlyRequestCounts) }}" name="mothly_request" id="monthly_request_counts" />
    <input type="hidden" value="{{ json_encode($monthlyComposerCounts) }}" name="mothly_composer"
        id="monthly_composer_counts" />
    <input type="hidden" value="{{ json_encode($monthlyMusicScoreCounts) }}" name="mothly_music_score"
        id="monthly_music_score_counts" />
    <input type="hidden" value="{{ json_encode($weeklyUniqueScoreViewCounts) }}" name="weekly_unique_score_view"
        id="weekly_unique_score_view_counts" />
    <input type="hidden" value="{{ json_encode($weeklyActiveUserCounts) }}" name="weekly_active_user"
        id="weekly_active_user_counts" />
    <input type="hidden" value="{{ json_encode($dailyScoreViewCounts) }}" name="daily_score_view"
        id="daily_score_view_counts" />
    <input type="hidden" value="{{ json_encode($dailyActiveUserCounts) }}" name="daily_active_user"
        id="daily_active_user_counts" />
    <input type="hidden" value="{{ json_encode($statDates) }}" name="stat_dates" id="stat_dates" />
    <input type="hidden" value="{{ json_encode($statMonths) }}" name="stat_months" id="stat_months" />
    <input type="hidden" value="{{ json_encode($statDailyDates) }}" name="stat_daily_dates" id="stat_daily_dates" />
    <input type="hidden" value="{{ json_encode($statDailyMonths) }}" name="stat_daily_months" id="stat_daily_months" />


    <div class="row" style="justify-content: center; gap: 5px;">

        <div class="flex col-10 col-lg-5 mb-3">
            @include('pages.widgets._widget-daily-view', [
                'class' => 'col-12 card-stretch card-stretch-half gutter-b',
            ])
        </div>

        <div class="flex col-10 col-lg-5 mb-3">
            @include('pages.widgets._widget-daily-active-user', [
                'class' => 'col-12 card-stretch card-stretch-half gutter-b',
            ])
        </div>

    </div>

    <div class="row" style="justify-content: center; gap: 5px;">

        <div class="flex col-10 col-lg-5 mb-3">
            @include('pages.widgets._widget-unique-score-view', [
                'class' => 'col-12 card-stretch card-stretch-half gutter-b',
            ])
        </div>

        <div class="flex col-10 col-lg-5 mb-3">
            @include('pages.widgets._widget-active-user', [
                'class' => 'col-12 card-stretch card-stretch-half gutter-b',
            ])
        </div>
    </div>

    <div class="row" style="justify-content: center; gap: 5px;">

        <div class="flex col-10 col-lg-5 mb-3">
            @include('pages.widgets._widget-3', [
                'class' => 'col-12 card-stretch card-stretch-half gutter-b',
            ])
        </div>

        <div class="flex col-10 col-lg-5 mb-3">
            @include('pages.widgets._widget-5', [
                'class' => 'col-12 card-stretch card-stretch-half gutter-b',
            ])
        </div>

    </div>

    <div class="row" style="justify-content: center; gap: 5px;">
        <div class="flex col-10 col-lg-5 mb-3">
            @include('pages.widgets._widget-4', [
                'class' => 'col-12 card-stretch card-stretch-half gutter-b',
            ])
        </div>

        <div class="flex col-10 col-lg-5 mb-3">
            @include('pages.widgets._widget-2', [
                'class' => 'col-12 card-stretch card-stretch-half gutter-b',
            ])
        </div>
    </div>

    <div class="row">
        <div class="order-2 col-xxl-12 order-xxl-1">
            @include('pages.widgets._widget-6', ['class' => 'card-stretch gutter-b'])
        </div>
    </div>
@endsection


{{-- Styles Section --}}
@section('styles')
    <link href="https://cdn.jsdelivr.net/gh/gitbrent/bootstrap4-toggle@3.6.1/css/bootstrap4-toggle.min.css"
        rel="stylesheet">


    <link href="{{ asset('plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />
@endsection

{{-- Scripts Section --}}

@section('scripts')
    <script src="{{ asset('plugins/custom/datatables/datatables.bundle.js') }}" type="text/javascript"></script>

    <script>
        $(document).ready(function() {
            $(document).on('click', ".home_delete_req", function() {
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.value) {
                        var request_id = $(this).data('id');
                        var main_route = $('#home_request_datatable').data('main_route');
                        $.ajax({
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            type: "DELETE",
                            url: main_route + 'delete-composer-request/' + request_id,
                            data: {
                                'id': request_id
                            },
                            success: function(data) {
                                console.log(data.success)
                            }
                        });
                        Swal.fire({
                            title: 'Deleted',
                            text: "Your record has been deleted.",
                            type: 'success'
                        }).then(okay => {
                            if (okay) {
                                $('#home_request_datatable').DataTable().ajax.reload();
                            }
                        });
                    }
                });
            });


            var table = $('#home_request_datatable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                lengthChange: false,
                dom: 'lrt',
                ajax: {
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: $(this).data('url'),
                    data: function(d) {
                        d.page_length = $('.number_of_user').val();

                    },
                    dataType: 'json',
                    method: 'get',
                },
                columns: [{
                        data: 'index',
                        name: 'index',
                        orderable: false,
                        searchable: false
                    },
                    {
                        "data": "name",
                        "name": "name"
                    },
                    {
                        "data": "requested_date",
                        "name": "requested_date",
                        orderable: false,
                        searchable: false
                    },
                    {
                        "data": "description",
                        "name": "description",
                        orderable: false,
                        searchable: false
                    },
                    {
                        "data": "composer_req_status",
                        "name": "composer_req_status",
                        orderable: false,
                        searchable: false
                    },
                    {
                        "data": "composer_status",
                        "name": "composer_status",
                        orderable: false,
                        searchable: false
                    },
                    // { "data": "composer_status_id", "name":"composer_status_id" },
                    // { "data": "last_updated", "name":"updated_at" },	  
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },
                ],
            });



        });
    </script>
    <script src="{{ asset('js/custom.js') }}" type="text/javascript"></script>
    <!-- <script src="{{ asset('js/pages/widgets.js') }}" type="text/javascript"></script> -->
@endsection
