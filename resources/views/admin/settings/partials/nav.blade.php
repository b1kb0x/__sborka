<div class="card">
    <div class="list-group list-group-flush">
        <a
            href="{{ route('admin.settings.general.edit') }}"
            @class([
                'list-group-item list-group-item-action',
                'active' => request()->routeIs('admin.settings.general.*'),
            ])
        >
            General
        </a>

        <a
            href="{{ route('admin.settings.checkout.edit') }}"
            @class([
                'list-group-item list-group-item-action',
                'active' => request()->routeIs('admin.settings.checkout.*'),
            ])
        >
            Checkout
        </a>

        <a
            href="{{ route('admin.settings.admin.edit') }}"
            @class([
                'list-group-item list-group-item-action',
                'active' => request()->routeIs('admin.settings.admin.*'),
            ])
        >
            Admin
        </a>
    </div>
</div>
