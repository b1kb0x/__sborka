<footer class="footer footer-transparent d-print-none">
    @php($storeName = app(\App\Services\SettingsService::class)->storeName())
    <div class="container-xl">
        <div class="row text-center align-items-center flex-row-reverse">
            <div class="col-lg-auto ms-lg-auto">
                <ul class="list-inline list-inline-dots mb-0">
                    <li class="list-inline-item">Version 0.9.0</li>
                </ul>
            </div>
            <div class="col-12 col-lg-auto mt-3 mt-lg-0">
                <ul class="list-inline list-inline-dots mb-0">
                    <li class="list-inline-item">
                        &copy; {{ now()->year }} {{ $storeName }}
                    </li>
                </ul>
            </div>
        </div>
    </div>
</footer>
