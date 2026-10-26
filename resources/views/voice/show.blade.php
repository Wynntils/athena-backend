<?php /** @var \App\Models\VoiceReport $report */ ?>

@extends('layouts.app')

@section('title', 'Voice Report #'.$report->id)

@section('content')
    <div class="row mt-3">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header d-md-flex justify-content-between align-items-center flex-wrap">
                    <h3 class="card-title mb-0">
                        Voice Report #{{ $report->id }}
                        @if($report->handled)
                            <span class="badge rounded-pill bg-success text-white">Handled</span>
                        @else
                            <span class="badge rounded-pill bg-danger text-white">Unhandled</span>
                        @endif
                    </h3>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox"
                               id="handledSwitch" {{ $report->handled ? 'checked' : '' }}>
                        <label class="form-check-label" for="handledSwitch">Handled</label>
                    </div>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-3">Reporter</dt>
                        <dd class="col-sm-9">{{ $reporterName }} <small class="text-muted">{{ $report->reporter_id }}</small></dd>
                        <dt class="col-sm-3">Target</dt>
                        <dd class="col-sm-9">{{ $targetName }} <small class="text-muted">{{ $report->target_id }}</small></dd>
                        <dt class="col-sm-3">Reason</dt>
                        <dd class="col-sm-9">{{ $report->reason }}</dd>
                        <dt class="col-sm-3">World</dt>
                        <dd class="col-sm-9">{{ $report->world }} {{ $report->instance !== '' ? '('.$report->instance.')' : '' }}</dd>
                        <dt class="col-sm-3">Reporter position</dt>
                        <dd class="col-sm-9">{{ $report->reporter_x }}, {{ $report->reporter_y }}, {{ $report->reporter_z }}</dd>
                        <dt class="col-sm-3">Target position</dt>
                        <dd class="col-sm-9">{{ $report->target_x }}, {{ $report->target_y }}, {{ $report->target_z }}</dd>
                        <dt class="col-sm-3">Reported</dt>
                        <dd class="col-sm-9">{{ $report->createdAtCarbon() }}</dd>
                        @if($report->handled)
                            <dt class="col-sm-3">Handled</dt>
                            <dd class="col-sm-9">{{ $report->handledAtCarbon() }} by {{ $report->handled_by }}</dd>
                        @endif
                        <dt class="col-sm-3">Witnesses</dt>
                        <dd class="col-sm-9">{{ implode(', ', $witnessNames) ?: '—' }}</dd>
                    </dl>

                    @if($report->hasTargetAudio())
                        <h5 class="mt-4">Target audio ({{ $targetName }})</h5>
                        <audio controls preload="none" class="w-100"
                               src="{{ route('voice.audio', ['voiceReport' => $report, 'side' => 'target']) }}"></audio>
                    @endif
                    @if($report->hasReporterAudio())
                        <h5 class="mt-4">Reporter audio ({{ $reporterName }})</h5>
                        <audio controls preload="none" class="w-100"
                               src="{{ route('voice.audio', ['voiceReport' => $report, 'side' => 'reporter']) }}"></audio>
                    @endif
                    @if(! $report->hasTargetAudio() && ! $report->hasReporterAudio())
                        <div class="alert alert-secondary mt-4 mb-0">No audio available for this report.</div>
                    @endif

                    @if(Route::has('voice.bans.create'))
                        <div class="mt-4">
                            <a class="btn btn-danger" href="{{ route('voice.bans.create', ['user' => $report->target_id]) }}">Ban {{ $targetName }} from voice</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header"><h5 class="mb-0">Comments</h5></div>
                <div class="card-body" id="comments">
                    @foreach($report->comments ?? [] as $comment)
                        <div class="mb-3 comment" data-comment="{{ $comment['id'] }}">
                            <div class="d-flex justify-content-between">
                                <span><strong>{{ $comment['user'] }}</strong> <small class="text-muted">{{ $comment['time'] }}</small></span>
                                <button type="button" class="btn-close btn-close-white delete-comment" aria-label="Delete"></button>
                            </div>
                            <div>{!! $comment['comment'] !!}</div>
                        </div>
                    @endforeach
                    <form id="comment-form">
                        <textarea class="form-control mb-2" id="comment-text" rows="3" placeholder="Add a comment"></textarea>
                        <button class="btn btn-primary btn-sm" type="submit">Add comment</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const headers = {'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json'};

        document.getElementById('handledSwitch').addEventListener('change', async function () {
            const res = await fetch("{{ route('voice.handled', $report) }}", {
                method: 'PUT', headers, body: JSON.stringify({handled: this.checked.toString()})
            });
            if (res.ok) location.reload();
        });

        document.getElementById('comment-form').addEventListener('submit', async function (e) {
            e.preventDefault();
            const comment = document.getElementById('comment-text').value.trim();
            if (!comment) return;
            const res = await fetch("{{ route('voice.comment', $report) }}", {
                method: 'PUT', headers, body: JSON.stringify({comment})
            });
            if (res.ok) location.reload();
        });

        document.querySelectorAll('.delete-comment').forEach(btn => btn.addEventListener('click', async function () {
            const commentId = this.closest('.comment').dataset.comment;
            const res = await fetch("{{ route('voice.comment.delete', $report) }}", {
                method: 'DELETE', headers, body: JSON.stringify({commentId})
            });
            if (res.ok) location.reload();
        }));
    </script>
@endpush
