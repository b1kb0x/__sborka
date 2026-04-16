@extends('layouts.auth')

@section('title', 'Confirm Password')
@section('heading', 'Confirm your password')

@section('content')
    <div class="mb-3 text-muted">
        This is a secure area of the application. Please confirm your password before continuing.
    </div>

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input
                id="password"
                type="password"
                name="password"
                required
                autocomplete="current-password"
                class="form-control"
            >
        </div>

        <div class="d-grid">
            <button type="submit" class="btn btn-primary">
                Confirm
            </button>
        </div>
    </form>
@endsection
