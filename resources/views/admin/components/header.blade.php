<header class="header header-sticky p-0 mb-4">
    <div class="container-fluid border-bottom px-4">
        <button
            class="header-toggler"
            type="button"
            aria-label="Toggle navigation"
            onclick="coreui.Sidebar.getOrCreateInstance(document.querySelector('#sidebar')).toggle()"
        >
            <svg class="icon icon-lg">
                <use xlink:href="{{ asset('icons/free.svg#cil-menu') }}"></use>
            </svg>
        </button>

        <ul class="header-nav ms-auto">
            <li class="nav-item py-1">
                <div class="vr h-100 mx-2 text-body text-opacity-75"></div>
            </li>

            <li class="nav-item dropdown">
                <button
                    class="btn btn-link nav-link py-2 px-2 d-flex align-items-center"
                    type="button"
                    aria-expanded="false"
                    data-coreui-toggle="dropdown"
                >
                    <svg class="icon icon-lg theme-icon-active">
                        <use xlink:href="{{ asset('icons/free.svg#cil-sun') }}"></use>
                    </svg>
                </button>

                <ul class="dropdown-menu dropdown-menu-end" style="--cui-dropdown-min-width: 8rem;">
                    <li>
                        <button
                            class="dropdown-item d-flex align-items-center theme-switcher active"
                            type="button"
                            data-theme-value="light"
                        >
                            <svg class="icon icon-lg me-3">
                                <use xlink:href="{{ asset('icons/free.svg#cil-sun') }}"></use>
                            </svg>
                            Light
                        </button>
                    </li>
                    <li>
                        <button
                            class="dropdown-item d-flex align-items-center theme-switcher"
                            type="button"
                            data-theme-value="dark"
                        >
                            <svg class="icon icon-lg me-3">
                                <use xlink:href="{{ asset('icons/free.svg#cil-moon') }}"></use>
                            </svg>
                            Dark
                        </button>
                    </li>
                    <li>
                        <button
                            class="dropdown-item d-flex align-items-center theme-switcher"
                            type="button"
                            data-theme-value="auto"
                        >
                            <svg class="icon icon-lg me-3">
                                <use xlink:href="{{ asset('icons/free.svg#cil-contrast') }}"></use>
                            </svg>
                            Auto
                        </button>
                    </li>
                </ul>
            </li>

            <li class="nav-item py-1">
                <div class="vr h-100 mx-2 text-body text-opacity-75"></div>
            </li>

            <li class="nav-item dropdown">
                <a
                    class="nav-link py-0 pe-0"
                    data-coreui-toggle="dropdown"
                    href="#"
                    role="button"
                    aria-expanded="false"
                >
                    <div class="avatar avatar-md">
                        <img
                            class="avatar-img"
                            src="{{ asset('images/default-avatar.jpg') }}"
                            alt="{{ auth()->user()?->email ?? 'User avatar' }}"
                        >
                    </div>
                </a>

                <div class="dropdown-menu dropdown-menu-end pt-0">
                    <div class="dropdown-header bg-body-tertiary text-body-secondary fw-semibold rounded-top mb-2">
                        Account
                    </div>

                    <a class="dropdown-item" href="{{ route('account.profile') }}">
                        Profile
                    </a>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item">
                            Logout
                        </button>
                    </form>
                </div>
            </li>
        </ul>
    </div>
</header>
