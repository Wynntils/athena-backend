@extends('layouts.app')

@section('title', 'Voice Bans')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="mb-0">Voice Bans</h3>
                    <a class="btn btn-danger btn-sm" href="{{ route('voice.bans.create') }}">New ban</a>
                </div>
                <div class="card-body">
                    @if(session('status'))
                        <div class="alert alert-success">{{ session('status') }}</div>
                    @endif
                    @if($bans->isEmpty())
                        <div class="alert alert-info mb-0">No voice bans have been found.</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th>User</th>
                                    <th class="d-none d-md-table-cell">Reason</th>
                                    <th class="d-none d-md-table-cell">By</th>
                                    <th class="d-none d-md-table-cell">Banned</th>
                                    <th class="d-none d-md-table-cell">Expires</th>
                                    <th>Status</th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($bans as $ban)
                                    @php $status = $ban->lifted_at ? 'lifted' : ($ban->isActive($now) ? 'active' : 'expired'); @endphp
                                    <tr data-ban="{{ $ban->id }}" data-status="{{ $status }}">
                                        <td>{{ $ban->id }}</td>
                                        <td>{{ $ban->user_id }}</td>
                                        <td class="d-none d-md-table-cell">{{ str($ban->reason)->limit(60) }}</td>
                                        <td class="d-none d-md-table-cell">{{ $ban->banned_by }}</td>
                                        <td class="d-none d-md-table-cell">{{ \Carbon\Carbon::createFromTimestampMs($ban->banned_at)->diffForHumans() }}</td>
                                        <td class="d-none d-md-table-cell">{{ $ban->expires_at ? \Carbon\Carbon::createFromTimestampMs($ban->expires_at) : 'never' }}</td>
                                        <td><span class="badge rounded-pill bg-{{ $status === 'active' ? 'danger' : 'secondary' }} text-white">{{ $status }}</span></td>
                                        <td>
                                            @if($status === 'active')
                                                <form method="post" action="{{ route('voice.bans.lift', $ban) }}">
                                                    @csrf @method('PUT')
                                                    <button class="btn btn-sm btn-outline-light">Lift</button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="text-center">{{ $bans->links() }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
