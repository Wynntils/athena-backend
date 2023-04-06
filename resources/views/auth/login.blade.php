@extends('layouts.app')

@section('title', 'Login')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card border-0">
                <div class="card-body text-center mt-4 mb-4 pb-4 pt-4 text-center d-flex flex-column justify-content-center align-items-center">
                    <img src="https://cdn.wynntils.com/athena_logo_1600x1600.png" alt="Athena Logo" class="mb-4" style="width: 200px; height: 200px;">
                    @if (isset($errors) && $errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <a href="{{ route('oauth.redirect', ['discord']) }}">
                        <button class="btn btn-success" style="background-color: #5865F2">
                            Login with Discord
                        </button>
                    </a>

                    <hr class="mt-4 mb-4" style="width: 50%;">

                    <a href="{{ route('oauth.redirect', ['minecraft']) }}">
                        <button class="btn btn-success" style="background-color: #00A8FF">
                           Login with Microsoft
                        </button>
                    </a>

                </div>
            </div>
        </div>
    </div>
@endsection
