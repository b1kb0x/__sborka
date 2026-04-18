<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>@yield('title', config('app.name'))</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@latest/dist/css/tabler.min.css">
    <link rel="stylesheet" href="https://unpkg.com/dropzone@5/dist/min/dropzone.min.css">
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
        .invalid-feedback {
            display: block;
        }

        .product-dropzone {
            display: block;
            border: 1px dashed var(--tblr-border-color, #cbd5e1);
            border-radius: var(--tblr-border-radius, 8px);
            background: var(--tblr-bg-surface, #fff);
            cursor: pointer;
            transition: border-color .2s ease, background-color .2s ease, box-shadow .2s ease;
            max-width: 420px;
        }

        .product-dropzone:hover {
            border-color: var(--tblr-primary, #206bc4);
            background: rgba(32, 107, 196, 0.03);
        }

        .product-dropzone.is-dragover {
            border-color: var(--tblr-primary, #206bc4);
            background: rgba(32, 107, 196, 0.06);
            box-shadow: 0 0 0 3px rgba(32, 107, 196, 0.08);
        }

        .product-dropzone-inner {
            min-height: 180px;
            padding: 2rem 1.5rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
        }

        .product-dropzone-title {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 600;
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
<script src="https://unpkg.com/dropzone@5/dist/min/dropzone.min.js"></script>
<script>
    if (window.Dropzone) {
        Dropzone.autoDiscover = false;
    }
</script>
@stack('scripts')
</body>
</html>
