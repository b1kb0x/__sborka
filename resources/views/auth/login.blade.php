@extends('layouts.auth')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="row g-0 shadow-sm">
                <div class="col-md-7">
                    <div class="card h-100 p-4 border-0">
                        <div class="card-body">
                            <h1>Login</h1>
                            <p class="text-body-secondary">Sign in to your account</p>

                            <form method="POST" action="{{ route('login') }}">
                                @csrf

                                <div class="input-group mb-3">
                                    <span class="input-group-text">
                                        <svg class="icon">
                                            <use xlink:href="/icons/free.svg#cil-envelope-open"></use>
                                        </svg>
                                    </span>
                                    <input class="form-control"
                                           id="email"
                                           type="email"
                                           name="email"
                                           value="{{ old('email') }}"
                                           required
                                           autofocus
                                           autocomplete="username"
                                           placeholder="Email">
                                </div>

                                <div class="input-group mb-4">
                                    <span class="input-group-text">
                                        <svg class="icon">
                                            <use xlink:href="/icons/free.svg#cil-lock-locked"></use>
                                        </svg>
                                    </span>
                                    <input
                                        class="form-control"
                                        type="password"
                                        id="password"
                                        name="password"
                                        required
                                        autocomplete="current-password"
                                        placeholder="Password">
                                </div>

                                <div class="form-check mb-3">
                                    <input
                                        id="remember"
                                        type="checkbox"
                                        name="remember"
                                        class="form-check-input"
                                    >
                                    <label for="remember" class="form-check-label">Remember me</label>
                                </div>

                                <div class="row">
                                    <div class="col-6">
                                        <button class="btn btn-primary px-4" type="submit">Login</button>
                                    </div>

                                    @if (Route::has('password.request'))
                                        <div class="col-6 text-end">
                                            <a href="{{ route('password.request') }}" class="btn btn-link px-0"
                                               type="button">Forgot password?</a>
                                        </div>
                                    @endif
                                </div>

                            </form>

                        </div>
                    </div>
                </div>

                <div class="col-md-5">
                    <div class="card h-100 text-white bg-primary py-5 border-0">
                        <div class="card-body text-center d-flex align-items-center justify-content-center">
                            <div>
                                <h2>Sign up</h2>
                                <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit.</p>
                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" class="btn btn-lg btn-outline-light mt-3"
                                       type="button">Register Now!</a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
