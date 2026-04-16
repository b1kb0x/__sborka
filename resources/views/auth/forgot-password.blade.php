@extends('layouts.auth')

@section('title', 'Forgot Password')
@section('heading', 'Reset your password')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card mb-4 mx-4">
                <div class="card-body p-4">
                    <div class="mb-3 text-muted">
                        Enter your email address and we will send you a password reset link.
                    </div>

                    <form method="POST" action="{{ route('password.email') }}">
                        @csrf

                        <div class="input-group mb-3">
                                    <span class="input-group-text">
                                        <svg class="icon">
                                            <use xlink:href="/icons/free.svg#cil-envelope-open"></use>
                                        </svg>
                                    </span>
                            <input id="email"
                                   type="email"
                                   name="email"
                                   value="{{ old('email') }}"
                                   required
                                   autofocus
                                   class="form-control"
                                   placeholder="Email">
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                Email Password Reset Link
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
