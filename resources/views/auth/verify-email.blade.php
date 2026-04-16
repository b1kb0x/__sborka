@extends('layouts.auth')

@section('title', 'Verify Email')
@section('heading', 'Verify your email address')

@section('content')
    <div class="mb-3 text-muted">
        Thanks for signing up. Before getting started, please verify your email address
        by clicking on the link we just emailed to you.
    </div>

    <div class="mb-3 text-muted">
        If you didn’t receive the email, we can send another one.
    </div>

    <form method="POST" action="{{ route('verification.send') }}" class="mb-3">
        @csrf

        <div class="d-grid">
            <button type="submit" class="btn btn-primary">
                Resend Verification Email
            </button>
        </div>
    </form>

    <form method="POST" action="{{ route('logout') }}">
        @csrf

        <div class="d-grid">
            <button type="submit" class="btn btn-outline-secondary">
                Logout
            </button>
        </div>
    </form>
@endsection
