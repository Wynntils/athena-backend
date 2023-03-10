<?php /** @var \App\Models\CrashReport[] $crashReports */ ?>

@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="mb-0">Crash Reports</h3>
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
                                <th>Trace</th>
                                <th>Occurrences</th>
                                <th>First Occurred</th>
                                <th>Last Occurred</th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($crashReports as $crashReport)
                                <tr>
                                    <td>{{ str($crashReport->trace)->before("\n") }}</td>
                                    <td>{{ count($crashReport->occurrences) }}</td>
                                    <td>{{ $crashReport->getEarliestOccurrenceDate() }}</td>
                                    <td>{{ $crashReport->getLatestOccurrenceDate() }}</td>
                                    <td><a href="{{ route('crash.view', ['hash' => $crashReport->trace_hash]) }}"
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
