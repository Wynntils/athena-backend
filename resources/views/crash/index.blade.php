{{-- If in dev environment --}}
@if(app()->environment('local'))
    @php
//        if get parameter
        if(request()->get('purge', false)) {
            \App\Models\CrashReport::truncate();
        }
        // Make sure at least 20 crash reports are available and able to be paginated
        if (\App\Models\CrashReport::count() < 20) {
            \App\Models\CrashReport::factory()->count(20)->create();
        }
    @endphp
@endif

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
                                <input class="form-check-input" name="showHandled" type="checkbox"
                                       id="showHandled" {{ $showHandled ? 'checked' : '' }}>
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
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                <tr>
                                    <th>Trace Hash</th>
                                    <th class="d-none d-md-table-cell">Trace</th>
                                    @if($showHandled)
                                        <th>Handled</th>
                                    @endif
                                    <th class="d-none d-md-table-cell">Occurrences</th>
                                    <th class="d-table-cell d-md-none">No.</th>
                                    <th class="d-none d-md-table-cell">Last Occurred</th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($crashReports as $crashReport)
                                    <tr>
                                        <td>{{ str($crashReport->trace_hash)->limit(7) }}</td>
                                        <td class="d-none d-md-table-cell">{{ str($crashReport->trace)->before("\n") }}</td>
                                        @if($showHandled)
                                            <td>
                                                @if($crashReport->handled)
                                                    <span
                                                        class="badge rounded-pill bg-success text-white">Handled</span>
                                                @else
                                                    <span
                                                        class="badge rounded-pill bg-danger text-white">Unhandled</span>
                                                @endif
                                            </td>
                                        @endif
                                        <td>{{ count($crashReport->occurrences) }}</td>
                                        <td class="d-none d-md-table-cell">{{ $crashReport->getLatestOccurrenceDate() }}</td>
                                        <td>
                                            <a href="{{ route('crash.view', ['crashReport' => $crashReport->trace_hash]) }}"
                                               class="btn btn-sm btn-primary">View</a></td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="text-center">
                            {{ $crashReports->appends(['showHandled' => $showHandled])->links() }}
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
    </script>
@endpush
