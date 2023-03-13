<?php /** @var \App\Models\CrashReport $crashReport */ ?>

@extends('layouts.app')

@section('title', 'Crash Report')

@section('content')
    <div class="row mt-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-md-flex justify-content-between align-items-center flex-wrap">
                    <h3 class="card-title">
                        Crash Report
                        <span class="d-block d-md-inline text-truncate">{{ $crashReport->trace_hash }}</span>
                        @if($crashReport->handled)
                            <span class="badge rounded-pill bg-success text-white">Handled</span>
                        @else
                            <span class="badge rounded-pill bg-danger text-white">Unhandled</span>
                        @endif
                    </h3>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox"
                               id="handledSwitch" {{ $crashReport->handled ? 'checked' : '' }}>
                        <label class="form-check-label" for="handledSwitch">Handled</label>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <pre><code>{{ $crashReport->trace }}</code></pre>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-sm-12 col-md-6">
                            <h5>Occurrences</h5>
                            <div class="table-responsive">
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
                        </div>
                        <div class="col-sm-12 col-md-6">
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

@push('scripts')
    <script>
        const handledSwitch = document.querySelector('#handledSwitch')
        handledSwitch.addEventListener('change', function () {
            const url = "{{ route('crash.handled', ['crashReport' => $crashReport->trace_hash]) }}"
            const data = {
                handled: handledSwitch.checked.toString()
            }
            fetch(url, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(data)
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // update the badge
                        const badge = document.querySelector('.card-title .badge')
                        if (handledSwitch.checked) {
                            badge.classList.remove('bg-danger')
                            badge.classList.add('bg-success')
                            badge.innerText = 'Handled'
                        } else {
                            badge.classList.remove('bg-success')
                            badge.classList.add('bg-danger')
                            badge.innerText = 'Unhandled'
                        }
                    }
                })
                .catch(error => console.log(error))
        })
    </script>
@endpush
