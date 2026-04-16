<div style="margin-bottom:20px; padding:12px 16px; border:1px solid #ddd;">
    <a href="{{ route('customer.dashboard') }}">Dashboard</a>
    <span style="margin:0 8px;">|</span>
    <a href="{{ route('customer.orders.index') }}">My orders</a>
    <span style="margin:0 8px;">|</span>
    <a href="{{ route('customer.profile.edit') }}">Profile</a>
    <span style="margin:0 8px;">|</span>
    <form method="POST" action="{{ route('logout') }}" style="display:inline;">
        @csrf
        <button type="submit">Logout</button>
    </form>
</div>
