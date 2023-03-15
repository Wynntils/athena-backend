<?php /** @var \App\Models\CrashReport $crashReport */ ?>

@extends('layouts.app')

@section('title', 'Crash Report')

@push('header')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/simplemde/latest/simplemde.min.css">
    <script src="https://cdn.jsdelivr.net/simplemde/latest/simplemde.min.js"></script>
@endpush

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
                                            <td>{{ \Carbon\Carbon::parse($occurrence->time) }}</td>
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
                    <div class="row" id="comments">
                        @foreach($crashReport->comments ?? [] as $comment)
                            <div class="col-sm-12 mt-3" data-comment="{{ $comment->id }}">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title d-flex justify-content-between">{{ $comment->user }}
                                            <small
                                                class="text-muted">{{ \Carbon\Carbon::parse($comment->time) }}</small>
                                        </h5>
                                        <p class="card-text">{!! $comment->comment !!}</p>
                                    </div>
                                    @if($comment->user_id === auth()->id())
                                        <div class="card-footer">
                                            <button class="btn btn-danger" data-remove>Remove</button>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="row mt-3">
                        <div class="col-sm-12">
                            <form id="comment-form">
                                <div class="mb-3">
                                    <label for="comment-text" class="form-label">Add a comment</label>
                                    <textarea class="form-control" id="comment-text" rows="3"></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">Submit</button>
                            </form>
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

        var simplemde = new SimpleMDE({
            element: document.getElementById('comment-text'),
            spellChecker: false
        })

        const commentForm = document.querySelector('#comment-form')
        commentForm.addEventListener('submit', function (e) {
            e.preventDefault()
            const url = "{{ route('crash.comment', ['crashReport' => $crashReport->trace_hash]) }}"
            simplemde.togglePreview()
            // get the html
            const comment = document.querySelector('#comment-form .editor-preview').innerHTML
            console.log(comment === '', comment)

            // check if the comment is empty
            if (comment === '') {
                setTimeout(() => {
                    simplemde.togglePreview()
                }, 100)
                return
            }
            const data = {
                comment: comment
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
                        simplemde.togglePreview()
                        simplemde.value('')
                        // Add the comment to the list
                        document.querySelector('#comments').innerHTML += `
                            <div class="col-sm-12 mt-3" data-comment="${data.comment.id}">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title d-flex justify-content-between">${data.comment.user} <small class="text-muted">${data.comment.time}</small></h5>
                                        <p class="card-text">${data.comment.comment}</p>
                                    </div>
                                    <div class="card-footer">
                                        <button class="btn btn-danger" data-remove>Remove</button>
                                    </div>
                                </div>
                            </div>
                        `
                        // add remove button handler
                        const removeButton = document.querySelector('[data-comment="' + data.comment.id + '"] [data-remove]')
                        addRemoveEvent(removeButton)
                    }
                })
                .catch(error => console.log(error))
        })

        const removeButtons = document.querySelectorAll('[data-remove]')
        removeButtons.forEach(button => {
            console.log(button, button.closest('[data-comment]').getAttribute('data-comment'))
            addRemoveEvent(button)
        })

        function addRemoveEvent (button) {
            button.addEventListener('click', function () {
                const url = "{{ route('crash.comment.delete', ['crashReport' => $crashReport->trace_hash]) }}"
                const comment = button.closest('[data-comment]')
                const commentId = comment.getAttribute('data-comment')
                const data = {
                    commentId: commentId
                }
                fetch(url, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(data)
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // remove the comment
                            comment.remove()
                        }
                    })
                    .catch(error => console.log(error))
            })
        }
    </script>

@endpush
