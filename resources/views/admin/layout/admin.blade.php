<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>@yield('title', config('app.name'))</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@latest/dist/css/tabler.min.css">
    <style>
        .orders-menu-badge {
            margin-left: auto;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 1.5rem;
            height: 1.5rem;
            padding: 0 .4rem;
            border-radius: 9999px;
            font-size: .75rem;
            font-weight: 600;
            line-height: 1;
            background: rgba(214, 57, 57, .18);
            color: #ff6b6b;
        }
    </style>
</head>
<body>

<div class="page">

    @include('admin.components.sidebar')

    <div class="page-wrapper">

        @hasSection('header')
            <div class="page-header d-print-none">
                <div class="container-xl">
                    <div class="row g-2 align-items-center">
                        @yield('header')
                    </div>
                </div>
            </div>
        @endif

        <div class="page-body">
            <div class="container-xl">
                @yield('content')
            </div>
        </div>

        @include('admin.components.footer')

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/@tabler/core@latest/dist/js/tabler.min.js"></script>
{{-- @stack('scripts')--}}
</body>
</html>
