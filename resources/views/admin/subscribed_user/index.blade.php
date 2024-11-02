{{-- Extends layout --}}
@extends('layout.default')

{{-- Content --}}
@section('content')

<div class="card card-custom">      
    <div class="card-header flex-wrap border-0 pt-6 pb-0">
        <div class="card-title"></div>
        <div class="card-toolbar"> 
            <!--begin::Button-->
            <!-- <a href="{{ route('user.create') }}" class="btn btn-primary font-weight-bolder"> -->
                <!-- <span class="svg-icon svg-icon-md"> -->
                    <!--begin::Svg Icon | path:assets/media/svg/icons/Design/Flatten.svg-->
                    <!-- <svg xmlns="http://www.w3.org/2000/svg" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                        <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                            <rect x="0" y="0" width="24" height="24"/>
                            <circle fill="#000000" cx="9" cy="15" r="6"/>
                            <path d="M8.8012943,7.00241953 C9.83837775,5.20768121 11.7781543,4 14,4 C17.3137085,4 20,6.6862915 20,10 C20,12.2218457 18.7923188,14.1616223 16.9975805,15.1987057 C16.9991904,15.1326658 17,15.0664274 17,15 C17,10.581722 13.418278,7 9,7 C8.93357256,7 8.86733422,7.00080962 8.8012943,7.00241953 Z" fill="#000000" opacity="0.3"/>
                        </g>
                    </svg> -->
                    <!--end::Svg Icon-->
                <!-- </span> -->
                <!-- Add New -->
            <!-- </a> -->
            <!--end::Button-->
        </div>
    </div>

    <div class="card-body">
        <table class="table table-bordered table-hover" id="subscribed_user_datatable" data-url="{{route('get_subscribed_user')}}"  data-main_route="{{ env('APP_URL') }}"  >
            <thead>
                <tr>
                    <th>Index</th>
                    <th>User Id</th>
                    <th>Plan Id</th>
                    <th>Registered Date</th>
                    <th>Plan End Date</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>

@endsection

{{-- Styles Section --}}
@section('styles')
<link href="https://cdn.jsdelivr.net/gh/gitbrent/bootstrap4-toggle@3.6.1/css/bootstrap4-toggle.min.css" rel="stylesheet">


<link href="{{ asset('plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">
@endsection

{{-- Scripts Section --}}
@section('scripts')
<script src="https://cdn.jsdelivr.net/gh/gitbrent/bootstrap4-toggle@3.6.1/js/bootstrap4-toggle.min.js"></script>

{{-- vendors --}}
<script src="{{ asset('plugins/custom/datatables/datatables.bundle.js') }}" type="text/javascript"></script>

{{-- page scripts --}}

<script src="{{ asset('js/pages/crud/datatables/basic/basic.js') }}" type="text/javascript"></script>
<script src="{{ asset('js/app.js') }}" type="text/javascript"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
{{--custom js--}}
<script src="{{ asset('js/custom.js') }}" type="text/javascript"></script>
    @if(Session::has('success'))
        <script>
            var success = "{{ Session::get('success') }}";
            toastr.success(success);
        </script>
    @endif
@endsection