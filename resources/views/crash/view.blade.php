<?php /** @var \App\Models\CrashReport $crashReport */ ?>

@extends('layouts.app')

@section('title', 'Crash Report')

@section('content')
    <div class="row mt-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="mb-0">Crash Report - {{ $crashReport->trace_hash }}</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <pre><code>{{ $crashReport->trace }}</code></pre>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <h5>Occurrences</h5>
                            <table class="table">
                                <thead>
                                <tr>
                                    <th scope="col">Version</th>
                                    <th scope="col">Timestamp</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($crashReport->occurrences as $occurrence)
                                    <tr>
                                        <td>{{ $occurrence->version }}</td>
                                        <td>{{ $occurrence->time }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5>Statistics</h5>
                            <ul class="list-group">
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    First timestamp
                                    <span
                                        class="badge rounded-pill text-bg-primary">{{ $crashReport->getEarliestOccurrenceDate() }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Last timestamp
                                    <span
                                        class="badge rounded-pill text-bg-primary">{{ $crashReport->getLatestOccurrenceDate() }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Number of occurrences
                                    <span
                                        class="badge rounded-pill text-bg-primary">{{ count($crashReport->occurrences) }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
