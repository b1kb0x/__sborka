@extends('layouts.auth')

@section('title', 'Reset Password')
@section('heading', 'Set a new password')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card mb-4 mx-4">
                <div class="card-body p-4">
    <form method="POST" action="{{ route('password.update') }}">
        @csrf
        <input type="hidden" name="token" value="{{ request()->route('token') }}">

        <div class="input-group mb-3">
                            <span class="input-group-text">
                                <svg class="icon">
                                  <use xlink:href="/icons/free.svg#cil-user"></use>
                                </svg></span>
            <input id="email"
                   type="email"
                   name="email"
                   value="{{ old('email', request()->email) }}"
                   required
                   autofocus
                   class="form-control"
                   placeholder="Username">
        </div>

        <div class="input-group mb-3">
                            <span class="input-group-text">
                                <svg class="icon">
                                  <use xlink:href="/icons/free.svg#cil-lock-locked"></use>
                                </svg></span>
            <input id="password"
                   type="password"
                   name="password"
                   required
                   autocomplete="new-password"
                   class="form-control"
                   placeholder="Password">
        </div>

        <div class="input-group mb-4">
                            <span class="input-group-text">
                                <svg class="icon">
                                  <use xlink:href="/icons/free.svg#cil-lock-locked"></use>
                                </svg></span>
            <input id="password_confirmation"
                   type="password"
                   name="password_confirmation"
                   required
                   autocomplete="new-password"
                   class="form-control"
                   placeholder="Repeat password">
        </div>

        <button class="btn btn-block btn-primary" type="submit"> Reset Password</button>

    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
