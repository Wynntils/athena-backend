<?php /** @var \App\Models\CrashReport[] $crashReports */ ?>

@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                    <h3 class="mb-0">Crash Reports</h3>
                    <form class="form-inline">
                        <div class="form-check mr-3 d-inline-block">
                            <input class="form-check-input" name="showHandled" type="checkbox" id="showHandled" {{ $showHandled ? 'checked' : '' }}>
                            <label class="form-check-label" for="showHandled">Show Handled Reports</label>
                        </div>
                    </form>
                </div>
                </div>
                <div class="card-body">
                @if($crashReports->isEmpty())
                        <div class="alert alert-info mb-0">
                            No crash reports have been found.
                        </div>
                    @else
                        <table class="table table-striped">
                            <caption>{{ $crashReports->links() }}</caption>
                            <thead>
                            <tr>
                                <th>Trace Hash</th>
                                <th>Trace</th>
                                @if($showHandled)
                                    <th>Handled</th>
                                @endif
                                <th>Occurrences</th>
                                <th>Last Occurred</th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($crashReports as $crashReport)
                                <tr>
                                    <td>{{ str($crashReport->trace_hash)->limit(7) }}</td>
                                    <td>{{ str($crashReport->trace)->before("\n") }}</td>
                                    @if($showHandled)
                                        <td>
                                            @if($crashReport->handled)
                                                <span class="badge rounded-pill bg-success text-white">Handled</span>
                                            @else
                                                <span class="badge rounded-pill bg-danger text-white">Unhandled</span>
                                            @endif
                                        </td>
                                    @endif
                                    <td>{{ count($crashReport->occurrences) }}</td>
                                    <td>{{ $crashReport->getLatestOccurrenceDate() }}</td>
                                    <td><a href="{{ route('crash.view', ['crashReport' => $crashReport->trace_hash]) }}"
                                           class="btn btn-sm btn-primary">View</a></td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
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
    </script>
@endpush
