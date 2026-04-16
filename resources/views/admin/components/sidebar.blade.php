<div class="sidebar sidebar-dark sidebar-fixed border-end" id="sidebar">
    <div class="sidebar-header border-bottom">
        <div class="sidebar-brand">
            <svg class="sidebar-brand-full" width="88" height="32" alt="CoreUI Logo">
                <use xlink:href="{{ asset('/images/coreui.svg#full') }}"></use>
            </svg>
            <svg class="sidebar-brand-narrow" width="32" height="32" alt="CoreUI Logo">
                <use xlink:href="{{ asset('/images/coreui.svg#signet') }}"></use>
            </svg>
        </div>

        <button class="btn-close d-lg-none"
                type="button"
                data-coreui-theme="dark"
                aria-label="Close"
                onclick="coreui.Sidebar.getInstance(document.querySelector('#sidebar')).toggle()"></button>
    </div>

    <ul class="sidebar-nav" data-coreui="navigation">
        <li class="nav-item">
            <a class="nav-link" href="{{ route('admin.dashboard') }}">
                <svg class="nav-icon">
                    <use xlink:href="{{ asset('/icons/free.svg#cil-speedometer') }}"></use>
                </svg>
                Dashboard
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="{{ route('admin.products.index') }}">
                <svg class="nav-icon">
                    <use xlink:href="/icons/free.svg#cil-folder-open"></use>
                </svg>
                Products
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="{{ route('admin.product-attributes.index') }}">
                <svg class="nav-icon">
                    <use xlink:href="/icons/free.svg#cil-equalizer"></use>
                </svg>
                Attribute
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="{{ route('admin.orders.index') }}">
                <svg class="nav-icon">
                    <use xlink:href="/icons/free.svg#cil-cart"></use>
                </svg>
                Orders
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="{{ route('admin.customers.index') }}">
                <svg class="nav-icon">
                    <use xlink:href="/icons/free.svg#cil-user"></use>
                </svg>
                Сustomers
            </a>
        </li>

    </ul>


    <div class="sidebar-footer border-top d-none d-md-flex">
        <button class="sidebar-toggler" type="button" data-coreui-toggle="unfoldable"></button>
    </div>
</div>
