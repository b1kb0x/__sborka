@extends('layouts.app')

@section('content')
    <div class="container" x-data="checkoutDelivery()" x-init="init()">
        <h1>Checkout</h1>

        @if (! auth()->check())
            <div style="background:#f5f7fb; border:1px solid #d8e0ef; padding:16px; margin:16px 0; border-radius:8px;">
                <strong>Already have an account?</strong>
                <div style="margin-top:6px;">
                    <a href="{{ url('/login') }}">Sign in</a>
                    to use your saved details and see your order history. You can still continue as guest.
                </div>
            </div>
        @endif

        @if (! empty($messages))
            <div style="background:#fff3cd; color:#856404; border:1px solid #ffe08a; padding:12px 14px; margin-bottom:15px; border-radius:8px;">
                <ul>
                    @foreach($messages as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(session('error'))
            <div style="background:#f8d7da; color:#721c24; border:1px solid #f1aeb5; padding:12px 14px; margin-bottom:15px; border-radius:8px;">
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div style="background:#f8d7da; color:#721c24; border:1px solid #f1aeb5; padding:12px 14px; margin-bottom:15px; border-radius:8px;">
                <strong>Please check the form.</strong>
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('cart.checkout') }}" method="POST">
            @csrf

            <div style="display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:16px; max-width:900px;">
                <div>
                    <label for="first_name">First name *</label>
                    <input id="first_name" type="text" name="first_name" value="{{ old('first_name', $checkoutData['first_name']) }}" required aria-invalid="{{ $errors->has('first_name') ? 'true' : 'false' }}" style="width:100%; border:1px solid {{ $errors->has('first_name') ? '#dc3545' : '#ced4da' }};">
                </div>

                <div>
                    <label for="last_name">Last name *</label>
                    <input id="last_name" type="text" name="last_name" value="{{ old('last_name', $checkoutData['last_name']) }}" required aria-invalid="{{ $errors->has('last_name') ? 'true' : 'false' }}" style="width:100%; border:1px solid {{ $errors->has('last_name') ? '#dc3545' : '#ced4da' }};">
                </div>

                <div>
                    <label for="phone">Phone *</label>
                    <input id="phone" type="text" name="phone" value="{{ old('phone', $checkoutData['phone']) }}" required aria-invalid="{{ $errors->has('phone') ? 'true' : 'false' }}" placeholder="+380..." style="width:100%; border:1px solid {{ $errors->has('phone') ? '#dc3545' : '#ced4da' }};">
                </div>

                <div>
                    <label for="email">Email *</label>
                    <input id="email" type="email" name="email" value="{{ old('email', $checkoutData['email']) }}" required aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}" style="width:100%; border:1px solid {{ $errors->has('email') ? '#dc3545' : '#ced4da' }};">
                </div>

                <div style="grid-column:1 / -1; margin-top:8px; padding:16px; border:1px solid #e9ecef; border-radius:8px; background:#f8f9fa;">
                    <h2 style="margin:0 0 12px 0; font-size:1.1rem;">Delivery</h2>
                    <p style="margin:0 0 12px 0; color:#6c757d;">Choose your delivery service and branch first. Contact and address fields stay below for order details.</p>

                    <div style="display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:16px;">
                        <div>
                            <label for="delivery_service_id">Delivery service</label>
                            <select
                                id="delivery_service_id"
                                name="delivery_service_id"
                                x-model="selectedService"
                                x-on:change="onServiceChange()"
                                :disabled="loadingServices"
                                aria-invalid="{{ $errors->has('delivery_service_id') ? 'true' : 'false' }}"
                                style="width:100%; border:1px solid {{ $errors->has('delivery_service_id') ? '#dc3545' : '#ced4da' }};"
                            >
                                <option value="" x-text="servicePlaceholder()"></option>
                                <template x-for="service in services" :key="service.id">
                                    <option :value="String(service.id)" x-text="service.name"></option>
                                </template>
                            </select>
                            @error('delivery_service_id')
                                <div style="color:#dc3545; margin-top:6px;">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label for="delivery_region_id">Region</label>
                            <select
                                id="delivery_region_id"
                                name="delivery_region_id"
                                x-model="selectedRegion"
                                x-on:change="onRegionChange()"
                                :disabled="!selectedService || loadingRegions"
                                aria-invalid="{{ $errors->has('delivery_region_id') ? 'true' : 'false' }}"
                                style="width:100%; border:1px solid {{ $errors->has('delivery_region_id') ? '#dc3545' : '#ced4da' }};"
                            >
                                <option value="" x-text="regionPlaceholder()"></option>
                                <template x-for="region in regions" :key="region.id">
                                    <option :value="String(region.id)" x-text="region.name"></option>
                                </template>
                            </select>
                            @error('delivery_region_id')
                                <div style="color:#dc3545; margin-top:6px;">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label for="delivery_city_id">City</label>
                            <select
                                id="delivery_city_id"
                                name="delivery_city_id"
                                x-model="selectedCity"
                                x-on:change="onCityChange()"
                                :disabled="!selectedRegion || loadingCities"
                                aria-invalid="{{ $errors->has('delivery_city_id') ? 'true' : 'false' }}"
                                style="width:100%; border:1px solid {{ $errors->has('delivery_city_id') ? '#dc3545' : '#ced4da' }};"
                            >
                                <option value="" x-text="cityPlaceholder()"></option>
                                <template x-for="city in cities" :key="city.id">
                                    <option :value="String(city.id)" x-text="city.name"></option>
                                </template>
                            </select>
                            @error('delivery_city_id')
                                <div style="color:#dc3545; margin-top:6px;">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label for="delivery_branch_id">Branch</label>
                            <select
                                id="delivery_branch_id"
                                name="delivery_branch_id"
                                x-model="selectedBranch"
                                :disabled="!selectedCity || loadingBranches"
                                aria-invalid="{{ $errors->has('delivery_branch_id') ? 'true' : 'false' }}"
                                style="width:100%; border:1px solid {{ $errors->has('delivery_branch_id') ? '#dc3545' : '#ced4da' }};"
                            >
                                <option value="" x-text="branchPlaceholder()"></option>
                                <template x-for="branch in branches" :key="branch.id">
                                    <option :value="String(branch.id)" x-text="branch.name"></option>
                                </template>
                            </select>
                            @error('delivery_branch_id')
                                <div style="color:#dc3545; margin-top:6px;">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <template x-if="showDeliverySummary()">
                        <div style="margin-top:16px; padding:12px; border:1px solid #d8e0ef; border-radius:8px; background:#fff;">
                            <strong style="display:block; margin-bottom:8px;">Selected delivery</strong>
                            <div style="display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:8px 16px;">
                                <div><strong>Service:</strong> <span x-text="selectedServiceOption()?.name || '—'"></span></div>
                                <div><strong>Region:</strong> <span x-text="selectedRegionOption()?.name || '—'"></span></div>
                                <div><strong>City:</strong> <span x-text="selectedCityOption()?.name || '—'"></span></div>
                                <div><strong>Branch:</strong> <span x-text="selectedBranchOption() ? formatBranchLabel(selectedBranchOption()) : '—'"></span></div>
                                <div><strong>Address:</strong> <span x-text="selectedBranchOption()?.address || '—'"></span></div>
                                <div><strong>Postal code:</strong> <span x-text="selectedBranchOption()?.postal_code || '—'"></span></div>
                            </div>
                        </div>
                    </template>
                </div>

                <div style="grid-column:1 / -1; padding:16px; border:1px solid #e9ecef; border-radius:8px;">
                    <h2 style="margin:0 0 12px 0; font-size:1.1rem;">Contact and address details</h2>
                    <p style="margin:0 0 12px 0; color:#6c757d;">These fields stay part of the order snapshot and delivery contact details.</p>

                    <div style="display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:16px;">
                        <div>
                            <label for="region">Region *</label>
                            <input id="region" type="text" name="region" value="{{ old('region', $checkoutData['region']) }}" required aria-invalid="{{ $errors->has('region') ? 'true' : 'false' }}" style="width:100%; border:1px solid {{ $errors->has('region') ? '#dc3545' : '#ced4da' }};">
                        </div>

                        <div>
                            <label for="city">City *</label>
                            <input id="city" type="text" name="city" value="{{ old('city', $checkoutData['city']) }}" required aria-invalid="{{ $errors->has('city') ? 'true' : 'false' }}" style="width:100%; border:1px solid {{ $errors->has('city') ? '#dc3545' : '#ced4da' }};">
                        </div>

                        <div style="grid-column:1 / -1;">
                            <label for="address">Address *</label>
                            <input id="address" type="text" name="address" value="{{ old('address', $checkoutData['address']) }}" required aria-invalid="{{ $errors->has('address') ? 'true' : 'false' }}" style="width:100%; border:1px solid {{ $errors->has('address') ? '#dc3545' : '#ced4da' }};">
                        </div>
                    </div>
                </div>

                <div style="grid-column:1 / -1;">
                    <label for="comment">Comment</label>
                    <textarea id="comment" name="comment" rows="4" style="width:100%;">{{ old('comment', $checkoutData['comment']) }}</textarea>
                </div>
            </div>

            <div style="margin-top:20px; background:#f8f9fa; border:1px solid #e9ecef; padding:16px; border-radius:8px; max-width:900px;">
                <p>Total items: {{ $cart->count }}</p>
                <p>Subtotal: {{ $cart->subtotal }}</p>
            </div>

            <div style="margin-top:20px;">
                <button type="submit" id="checkout-submit">Place order</button>
                <a href="{{ route('cart.index') }}" style="margin-left:12px;">Back to cart</a>
            </div>
        </form>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var form = document.querySelector('form[action="{{ route('cart.checkout') }}"]');
                var submitButton = document.getElementById('checkout-submit');

                if (! form || ! submitButton) {
                    return;
                }

                form.addEventListener('submit', function () {
                    if (submitButton.disabled) {
                        return;
                    }

                    submitButton.disabled = true;
                    submitButton.textContent = 'Placing order...';
                });
            });
        </script>
    </div>
@endsection

@push('scripts')
    <script>
        function checkoutDelivery() {
            return {
                services: [],
                regions: [],
                cities: [],
                branches: [],
                selectedService: '{{ old('delivery_service_id', '') }}',
                selectedRegion: '{{ old('delivery_region_id', '') }}',
                selectedCity: '{{ old('delivery_city_id', '') }}',
                selectedBranch: '{{ old('delivery_branch_id', '') }}',
                loadingServices: false,
                loadingRegions: false,
                loadingCities: false,
                loadingBranches: false,
                regionsRequestToken: 0,
                citiesRequestToken: 0,
                branchesRequestToken: 0,

                async init() {
                    await this.loadServices();

                    if (! this.selectedService) {
                        return;
                    }

                    await this.loadRegions();

                    if (! this.selectedRegion) {
                        return;
                    }

                    await this.loadCities();

                    if (! this.selectedCity) {
                        return;
                    }

                    await this.loadBranches();
                },

                async loadServices() {
                    this.loadingServices = true;

                    try {
                        const response = await fetch('/delivery/services', {
                            headers: {
                                'Accept': 'application/json',
                            },
                        });

                        if (!response.ok) {
                            throw new Error('Failed to load delivery services.');
                        }

                        this.services = await response.json();
                    } catch (error) {
                        this.services = [];
                        console.error(error);
                    } finally {
                        this.loadingServices = false;
                    }
                },

                async onServiceChange() {
                    this.selectedRegion = '';
                    this.selectedCity = '';
                    this.selectedBranch = '';
                    this.regions = [];
                    this.cities = [];
                    this.branches = [];

                    if (!this.selectedService) {
                        return;
                    }

                    await this.loadRegions();
                },

                async loadRegions() {
                    const serviceId = this.selectedService;
                    const requestToken = ++this.regionsRequestToken;

                    this.loadingRegions = true;

                    try {
                        const response = await fetch(`/delivery/services/${serviceId}/regions`, {
                            headers: {
                                'Accept': 'application/json',
                            },
                        });

                        if (!response.ok) {
                            throw new Error('Failed to load regions.');
                        }

                        const regions = await response.json();

                        if (requestToken !== this.regionsRequestToken || serviceId !== this.selectedService) {
                            return;
                        }

                        this.regions = regions;
                    } catch (error) {
                        if (requestToken !== this.regionsRequestToken || serviceId !== this.selectedService) {
                            return;
                        }

                        this.regions = [];
                        this.selectedRegion = '';
                        this.cities = [];
                        this.selectedCity = '';
                        this.selectedBranch = '';
                        this.branches = [];
                        console.error(error);
                    } finally {
                        if (requestToken === this.regionsRequestToken && serviceId === this.selectedService) {
                            this.loadingRegions = false;
                        }
                    }
                },

                async onRegionChange() {
                    this.selectedCity = '';
                    this.selectedBranch = '';
                    this.cities = [];
                    this.branches = [];

                    if (!this.selectedRegion) {
                        return;
                    }

                    await this.loadCities();
                },

                async loadCities() {
                    const regionId = this.selectedRegion;
                    const requestToken = ++this.citiesRequestToken;

                    this.loadingCities = true;

                    try {
                        const response = await fetch(`/delivery/regions/${regionId}/cities`, {
                            headers: {
                                'Accept': 'application/json',
                            },
                        });

                        if (!response.ok) {
                            throw new Error('Failed to load cities.');
                        }

                        const cities = await response.json();

                        if (requestToken !== this.citiesRequestToken || regionId !== this.selectedRegion) {
                            return;
                        }

                        this.cities = cities;
                    } catch (error) {
                        if (requestToken !== this.citiesRequestToken || regionId !== this.selectedRegion) {
                            return;
                        }

                        this.cities = [];
                        this.selectedCity = '';
                        this.selectedBranch = '';
                        this.branches = [];
                        console.error(error);
                    } finally {
                        if (requestToken === this.citiesRequestToken && regionId === this.selectedRegion) {
                            this.loadingCities = false;
                        }
                    }
                },

                async onCityChange() {
                    this.selectedBranch = '';
                    this.branches = [];

                    if (!this.selectedCity) {
                        return;
                    }

                    await this.loadBranches();
                },

                async loadBranches() {
                    const cityId = this.selectedCity;
                    const requestToken = ++this.branchesRequestToken;

                    this.loadingBranches = true;

                    try {
                        const response = await fetch(`/delivery/cities/${cityId}/branches`, {
                            headers: {
                                'Accept': 'application/json',
                            },
                        });

                        if (!response.ok) {
                            throw new Error('Failed to load branches.');
                        }

                        const branches = await response.json();

                        if (requestToken !== this.branchesRequestToken || cityId !== this.selectedCity) {
                            return;
                        }

                        this.branches = branches;
                    } catch (error) {
                        if (requestToken !== this.branchesRequestToken || cityId !== this.selectedCity) {
                            return;
                        }

                        this.branches = [];
                        this.selectedBranch = '';
                        console.error(error);
                    } finally {
                        if (requestToken === this.branchesRequestToken && cityId === this.selectedCity) {
                            this.loadingBranches = false;
                        }
                    }
                },

                servicePlaceholder() {
                    if (this.loadingServices) {
                        return 'Loading services...';
                    }

                    if (! this.services.length) {
                        return 'No delivery services available';
                    }

                    return 'Select delivery service';
                },

                regionPlaceholder() {
                    if (! this.selectedService) {
                        return 'Select region';
                    }

                    if (this.loadingRegions) {
                        return 'Loading regions...';
                    }

                    if (! this.regions.length) {
                        return 'No regions available';
                    }

                    return 'Select region';
                },

                cityPlaceholder() {
                    if (! this.selectedRegion) {
                        return 'Select city';
                    }

                    if (this.loadingCities) {
                        return 'Loading cities...';
                    }

                    if (! this.cities.length) {
                        return 'No cities available';
                    }

                    return 'Select city';
                },

                branchPlaceholder() {
                    if (! this.selectedCity) {
                        return 'Select city first';
                    }

                    if (this.loadingBranches) {
                        return 'Loading branches...';
                    }

                    if (! this.branches.length) {
                        return 'No branches available';
                    }

                    return 'Select branch';
                },

                formatBranchLabel(branch) {
                    return branch.address
                        ? `${branch.name} (${branch.address})`
                        : branch.name;
                },

                selectedServiceOption() {
                    return this.services.find((service) => String(service.id) === String(this.selectedService));
                },

                selectedRegionOption() {
                    return this.regions.find((region) => String(region.id) === String(this.selectedRegion));
                },

                selectedCityOption() {
                    return this.cities.find((city) => String(city.id) === String(this.selectedCity));
                },

                selectedBranchOption() {
                    return this.branches.find((branch) => String(branch.id) === String(this.selectedBranch));
                },

                showDeliverySummary() {
                    return Boolean(this.selectedService || this.selectedRegion || this.selectedCity || this.selectedBranch);
                },
            };
        }
    </script>
@endpush
