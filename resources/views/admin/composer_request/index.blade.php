{{-- Extends layout --}}
@extends('layout.default')

{{-- Content --}}
@section('content')

<div class="card card-custom">    
    <div class="card-body">
        <table class="table table-bordered table-hover" id="composer_request_datatable" data-url="{{route('get_composers')}}"  data-main_route="{{ env('APP_URL') }}">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User ID</th>
                    <th>Name</th>
                    <th>Last Name</th>
                    <th>Registration Date</th>
                    <th>Last Update Date</th>
                    <!-- <th>Description</th> -->
                    <th>Request Status</th>
                    <th>Composer Status</th>
                    <th>Action</th>
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