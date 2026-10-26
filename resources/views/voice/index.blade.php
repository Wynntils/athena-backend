@extends('layouts.app')

@section('title', 'Voice Reports')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-md-flex justify-content-between align-items-center">
                        <h3 class="mb-0">Voice Reports</h3>
                        <form class="form-inline">
                            <div class="form-check mr-3 d-inline-block">
                                <input class="form-check-input" name="showHandled" type="checkbox"
                                       id="showHandled" {{ $showHandled ? 'checked' : '' }}>
                                <label class="form-check-label" for="showHandled">Show Handled Reports</label>
                            </div>
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="Search" name="search" id="search" value="{{ request('search') }}">
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card-body">
                    @if($reports->isEmpty())
                        <div class="alert alert-info mb-0">
                            No voice reports have been found.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Reporter</th>
                                    <th>Target</th>
                                    <th class="d-none d-md-table-cell">World</th>
                                    <th class="d-none d-md-table-cell">Reason</th>
                                    @if($showHandled)
                                        <th>Handled</th>
                                    @endif
                                    <th class="d-none d-md-table-cell">Reported</th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($reports as $report)
                                    <tr data-report="{{ $report->id }}">
                                        <td>{{ $report->id }}</td>
                                        <td>{{ $usernames[$report->reporter_id] }}</td>
                                        <td>{{ $usernames[$report->target_id] }}</td>
                                        <td class="d-none d-md-table-cell">{{ $report->world }}</td>
                                        <td class="d-none d-md-table-cell">{{ str($report->reason)->limit(60) }}</td>
                                        @if($showHandled)
                                            <td>
                                                @if($report->handled)
                                                    <span class="badge rounded-pill bg-success text-white">Handled</span>
                                                @else
                                                    <span class="badge rounded-pill bg-danger text-white">Unhandled</span>
                                                @endif
                                            </td>
                                        @endif
                                        <td class="d-none d-md-table-cell">{{ $report->createdAtCarbon()->diffForHumans() }}</td>
                                        <td>
                                            <a href="{{ route('voice.show', $report) }}" class="btn btn-sm btn-primary">View</a>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="text-center">
                            {{ $reports->withQueryString()->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.getElementById('showHandled').addEventListener('change', function () {
            this.closest('form').submit();
        });

        let searchTimeout = null;
        document.getElementById('search').addEventListener('keyup', function () {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                this.closest('form').submit();
            }, 500);
        });
    </script>
@endpush
