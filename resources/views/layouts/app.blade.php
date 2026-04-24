<!doctype html>
<html data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link
        rel="stylesheet"
        href="{{ asset('css/app.css') }}"
    >
    <title>Document</title>
</head>
<body>
<div class="page">
    <header class="site-header">
        <div class="container navbar">
            <a href="/" class="navbar-brand">__sborka</a>

            <nav class="navbar-menu">
                <a href="/" class="navbar-link">Home</a>
                <a href="/products" class="navbar-link">Products</a>
                <a href="/cart" class="navbar-link">
                    Cart <span class="cart-badge">2</span>
                </a>
            </nav>
        </div>
    </header>

    <main class="main section">
        <div class="container">
            @yield('content')
        </div>
    </main>

    <footer class="site-footer">
        <div class="container site-footer__inner">
            <span>© __sborka</span>
            <span>Fresh roasted coffee</span>
        </div>
    </footer>
</div>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@stack('scripts')
</body>
</html>
