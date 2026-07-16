{{-- Advance Table Widget 2 --}}

<div class="card card-custom {{ @$class }}">
    {{-- Header --}}
    <div class="card-header border-0 pt-5">
        <h3 class="card-title align-items-start flex-column">
            <span class="card-label font-weight-bolder request_header">New Composer Request</span>
            <!-- <span class="text-muted mt-3 font-weight-bold font-size-sm">More than 400+ new members</span> -->
        </h3>
        <!-- <div class="card-toolbar">
            <ul class="nav nav-pills nav-pills-sm nav-dark-75">
                <li class="nav-item">
                    <a class="nav-link py-2 px-4" data-toggle="tab" href="#kt_tab_pane_1_1">Month</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-2 px-4" data-toggle="tab" href="#kt_tab_pane_1_2">Week</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-2 px-4 active" data-toggle="tab" href="#kt_tab_pane_1_3">Day</a>
                </li>
            </ul>
        </div> -->
    </div>

    {{-- Body --}}
    <div class="card-body pt-3 pb-0">
        {{-- Table --}}
        <div class="table-responsive">
        <table class="table table-bordered table-hover" id="home_request_datatable" data-url="{{route('get_composers')}}"  data-main_route="{{ env('APP_URL') }}">
            <thead>
                <tr>
                    <th>Index</th>
                    <th>Composer Name</th>
                    <th>Requested Date</th>
                    <th>Description</th>
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
</div>

