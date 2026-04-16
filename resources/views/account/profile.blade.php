@extends('layouts.auth')

@section('title', 'Профиль')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h1 class="h4 mb-4">Профиль</h1>

                    @if (session('status') === 'profile-information-updated')
                        <div class="alert alert-success">
                            Профиль обновлён.
                        </div>
                    @endif

                    @if ($errors->updateProfileInformation->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->updateProfileInformation->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ url('/user/profile-information') }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="name" class="form-label">Имя</label>
                            <input
                                id="name"
                                type="text"
                                name="name"
                                value="{{ old('name', auth()->user()->name) }}"
                                class="form-control"
                                required
                            >
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input
                                id="email"
                                type="email"
                                name="email"
                                value="{{ old('email', auth()->user()->email) }}"
                                class="form-control"
                                required
                            >
                        </div>

                        <button type="submit" class="btn btn-primary">
                            Сохранить
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
