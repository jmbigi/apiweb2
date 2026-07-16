{{-- Extends layout --}}
@extends('layout.default')

{{-- Content --}}
@section('content')

<div class="card card-custom">
    <div class="card-header flex-wrap border-0 pt-6 pb-0">
        <div class="card-title">
            <h3 class="card-label">{{ $ensemble->name }}
                <span class="d-block text-muted pt-2 font-size-sm">CIF: {{ $ensemble->cif ?? 'N/A' }}</span>
            </h3>
        </div>
        <div class="card-toolbar">
            <a href="{{ route('ensemble.edit', $ensemble->id) }}" class="btn btn-primary font-weight-bolder mr-2">Edit</a>
            <a href="{{ route('ensemble.index') }}" class="btn btn-secondary font-weight-bolder">Back</a>
        </div>
    </div>

    <div class="card-body">
        @if(Session::has('success'))
            <div class="alert alert-success">{{ Session::get('success') }}</div>
        @endif

        <div class="row mb-6">
            <div class="col-lg-6">
                <label class="font-weight-bold">Status:</label>
                <span class="badge {{ $ensemble->status ? 'badge-success' : 'badge-danger' }}">
                    {{ $ensemble->status ? 'Active' : 'Inactive' }}
                </span>
            </div>
            <div class="col-lg-6">
                <label class="font-weight-bold">Owner:</label>
                <p>{{ $ensemble->owner->name ?? 'N/A' }} ({{ $ensemble->owner->email ?? '' }})</p>
            </div>
        </div>

        <div class="row mb-6">
            <div class="col-lg-12">
                <label class="font-weight-bold">Description:</label>
                <p>{{ $ensemble->description ?? 'No description' }}</p>
            </div>
        </div>

        <hr>
        <h4 class="mb-4">Members ({{ $ensemble->members->count() }})</h4>
        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($ensemble->members as $member)
                <tr>
                    <td>{{ $member->name }}</td>
                    <td>{{ $member->email }}</td>
                    <td>{{ $member->pivot->role }}</td>
                    <td>
                        <span class="badge {{ $member->pivot->status ? 'badge-success' : 'badge-danger' }}">
                            {{ $member->pivot->status ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td>
                        <form action="{{ route('ensemble.member.remove', [$ensemble->id, $member->id]) }}" method="POST" style="display:inline" onsubmit="return confirm('Remove this member?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">Remove</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center">No members</td></tr>
                @endforelse
            </tbody>
        </table>

        <hr>
        <h4 class="mb-4">Folders ({{ $ensemble->folders->count() }})</h4>
        <table class="table table-bordered table-hover">
            <thead>
                <tr><th>Name</th><th>Path</th></tr>
            </thead>
            <tbody>
                @forelse($ensemble->folders as $folder)
                <tr><td>{{ $folder->name }}</td><td>{{ $folder->path ?: $folder->name }}</td></tr>
                @empty
                <tr><td colspan="2" class="text-center">No folders</td></tr>
                @endforelse
            </tbody>
        </table>

        <hr>
        <h4 class="mb-4">Rehearsals ({{ $ensemble->rehearsals->count() }})</h4>
        <table class="table table-bordered table-hover">
            <thead>
                <tr><th>Date</th><th>Location</th></tr>
            </thead>
            <tbody>
                @forelse($ensemble->rehearsals as $rehearsal)
                <tr><td>{{ $rehearsal->date }}</td><td>{{ $rehearsal->location }}</td></tr>
                @empty
                <tr><td colspan="2" class="text-center">No rehearsals</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection

@section('scripts')
<script src="{{ asset('js/app.js') }}" type="text/javascript"></script>
@endsection
