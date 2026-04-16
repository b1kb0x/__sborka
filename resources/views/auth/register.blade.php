@extends('layouts.auth')

@section('title', 'Register')
@section('heading', 'Create a new account')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card mb-4 mx-4">
                <div class="card-body p-4">
                    <h1>Register</h1>
                    <p class="text-body-secondary">Create your account</p>
                    <form method="POST" action="{{ route('register') }}">
                        @csrf

                        <div class="input-group mb-3">
                            <span class="input-group-text">
                                <svg class="icon">
                                  <use xlink:href="/icons/free.svg#cil-user"></use>
                                </svg></span>
                            <input id="name"
                                   type="text"
                                   name="name"
                                   value="{{ old('name') }}"
                                   required
                                   autofocus
                                   autocomplete="name"
                                   class="form-control"
                                   placeholder="Username">
                        </div>

                        <div class="input-group mb-3">
                            <span class="input-group-text">
                                <svg class="icon">
                                  <use xlink:href="/icons/free.svg#cil-envelope-open"></use>
                                </svg></span>
                            <input id="email"
                                   type="email"
                                   name="email"
                                   value="{{ old('email') }}"
                                   required
                                   autocomplete="username"
                                   class="form-control"
                                   placeholder="Email">
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

                        <button class="btn btn-block btn-primary" type="submit">Create Account</button>
                    </form>
                </div>
            </div>
        </div>
    </div>



    <div class="text-center">
        <span class="text-muted">Already registered?</span>
        <a href="{{ route('login') }}" class="text-decoration-none">Login</a>
    </div>

@endsection
