<!-- navbar -->

<nav class="is-flex is-justify-content-space-between is-align-items-center px-5 py-5">

    <div class="">
        <a href="/">
            <h1 class="title">Coffee</h1>
        </a>
    </div>

    <div class="">
        <div class="is-flex">
            @if (Route::has('login'))
                    @auth
                        @if(auth()->user()?->role?->value === 'customer')
                            <a href="{{ url('/cabinet') }}" class="button is-primary">
                                <strong>Cabinet</strong>
                            </a>
                        @endif
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="button is-light">
                                Logout
                            </button>
                        </form>
                    @else
                        <a class="button is-primary">
                            <strong>Sign up</strong>
                        </a>
                        <a href="{{ route('login') }}" class="button is-light">
                            Log in
                        </a>
                    @endauth
            @endif
                <a href="{{ route('cart.index') }}" class="navbar-item">
                    Cart
                    @if(($cartCount ?? 0) > 0)
                        <span class="tag is-primary ml-2">{{ $cartCount }}</span>
                    @endif
                </a>
        </div>
    </div>
</nav>

<!-- /navbar -->

@push('scripts')
    <script>
        const burgerIcon = document.querySelector('#burger');
        const navbarMenu = document.querySelector('#nav-links');

        burgerIcon.addEventListener('click', () => {
            console.log('test');
            burgerIcon.classList.toggle('is-active');
            navbarMenu.classList.toggle('is-active');
        });
    </script>
@endpush
