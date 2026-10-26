@extends('layouts.app')

@section('title', 'Create Voice Ban')

@section('content')
    <div class="row">
        <div class="col-md-8 col-lg-6">
            <div class="card">
                <div class="card-header"><h3 class="mb-0">Ban {{ $username ?? $user ?: 'a player' }} from voice</h3></div>
                <div class="card-body">
                    <form method="post" action="{{ route('voice.bans.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label" for="user_id">User UUID</label>
                            <input class="form-control @error('user_id') is-invalid @enderror" id="user_id" name="user_id" value="{{ old('user_id', $user) }}" required>
                            @error('user_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="reason">Reason</label>
                            <textarea class="form-control @error('reason') is-invalid @enderror" id="reason" name="reason" rows="3" required>{{ old('reason') }}</textarea>
                            @error('reason')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="duration">Duration</label>
                            <select class="form-select @error('duration') is-invalid @enderror" id="duration" name="duration">
                                @foreach(['permanent' => 'Permanent', '1d' => '1 day', '7d' => '7 days', '30d' => '30 days'] as $value => $label)
                                    <option value="{{ $value }}" @selected(old('duration') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('duration')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <button class="btn btn-danger" type="submit">Ban</button>
                        <a class="btn btn-secondary" href="{{ route('voice.bans.index') }}">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
