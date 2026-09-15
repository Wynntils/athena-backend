@extends('layouts.app')

@section('title', 'Voice Beta')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header"><h3 class="mb-0">Voice Beta Allowlist</h3></div>
                <div class="card-body">
                    @if(session('status'))
                        <div class="alert alert-success">{{ session('status') }}</div>
                    @endif
                    <form method="post" action="{{ route('voice.beta.store') }}" class="mb-3" style="max-width: 480px">
                        @csrf
                        <div class="input-group">
                            <input class="form-control @error('identifier') is-invalid @enderror" name="identifier" placeholder="Username or UUID" value="{{ old('identifier') }}" required>
                            <button class="btn btn-primary">Add</button>
                            @error('identifier')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </form>
                    @if($rows->isEmpty())
                        <div class="alert alert-info mb-0">Nobody is in the voice beta yet.</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                <tr>
                                    <th>User</th>
                                    <th class="d-none d-md-table-cell">UUID</th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($rows as $row)
                                    <tr>
                                        <td>{{ $row['username'] }}</td>
                                        <td class="d-none d-md-table-cell">{{ $row['user_id'] }}</td>
                                        <td>
                                            <form method="post" action="{{ route('voice.beta.destroy', $row['user_id']) }}">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-sm btn-outline-secondary">Remove</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
