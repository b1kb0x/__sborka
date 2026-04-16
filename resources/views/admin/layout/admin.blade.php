<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', config('app.name'))</title>

    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    <style>
        .wrapper {
            width: 100%;
            padding-inline: var(--cui-sidebar-occupy-start, 0) var(--cui-sidebar-occupy-end, 0);
            will-change: auto;
            transition: padding 0.15s;
        }

        .header > .container-fluid,
        .sidebar-header {
            min-height: calc(4rem + 1px);
        }
    </style>
</head>
<body>

@include('admin.components.sidebar')

<div class="wrapper d-flex flex-column min-vh-100">

    @include('admin.components.header')

    <div class="body flex-grow-1">
        <div class="container-lg px-4">
            @yield('content')
        </div>
    </div>

    @include('admin.components.footer')

</div>

<script src="{{ asset('js/app.js') }}"></script>
<script>
    function getPreferredTheme() {
        return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'
    }

    function applyTheme(theme) {
        const resolvedTheme = theme === 'auto' ? getPreferredTheme() : theme
        document.documentElement.setAttribute('data-coreui-theme', resolvedTheme)
    }

    function updateActiveTheme(theme) {
        document.querySelectorAll('.theme-switcher').forEach((button) => {
            const isActive = button.getAttribute('data-theme-value') === theme
            button.classList.toggle('active', isActive)
        })
    }

    function setTheme(theme) {
        localStorage.setItem('coreui-theme', theme)
        applyTheme(theme)
        updateActiveTheme(theme)
    }

    const savedTheme = localStorage.getItem('coreui-theme') || 'light'
    applyTheme(savedTheme)
    updateActiveTheme(savedTheme)

    document.querySelectorAll('.theme-switcher').forEach((button) => {
        button.addEventListener('click', function () {
            const theme = this.getAttribute('data-theme-value')
            setTheme(theme)
        })
    })

    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
        const currentTheme = localStorage.getItem('coreui-theme') || 'light'

        if (currentTheme === 'auto') {
            applyTheme('auto')
        }
    })
</script>

@stack('scripts')
</body>
</html>
