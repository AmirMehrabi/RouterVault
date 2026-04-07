@php
    $site = $site ?? null;
    $mapLocale = $mapLocale ?? str_replace('_', '-', app()->getLocale());
    $siteLocationMapConfig = [
        'locale' => $mapLocale,
        'defaultCenter' => [0.0, 20.0],
        'defaultZoom' => 2,
        'site' => [
            'name' => old('name', $site?->name),
            'city' => old('city', $site?->city),
            'state' => old('state', $site?->state),
            'country' => old('country', $site?->country),
            'latitude' => old('latitude', $site?->latitude),
            'longitude' => old('longitude', $site?->longitude),
            'status' => old('status', $site?->status ?? 'active'),
        ],
    ];
@endphp

<div class="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,1.15fr)_minmax(360px,0.85fr)]" x-data='siteLocationPicker(@json($siteLocationMapConfig))'>
    <div class="space-y-6">
        <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm">
            <h3 class="text-lg font-semibold text-gray-900">Basic Information</h3>
            <p class="mt-1 text-sm text-gray-500">Capture the site profile first, then pin its exact coordinates on the map.</p>
            <div class="mt-6 grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                <x-ui.input.text id="site-name" label="Site Name" name="name" :value="old('name', $site?->name)" :required="true" :error="$errors->first('name')" placeholder="e.g., North Tower" />
                <x-ui.input.text id="site-code" label="Site Code" name="code" :value="old('code', $site?->code)" :error="$errors->first('code')" placeholder="e.g., SITE-001" />
                <x-ui.input.select label="Status" name="status" :options="['active' => 'Active', 'inactive' => 'Inactive', 'maintenance' => 'Maintenance']" :value="old('status', $site?->status ?? 'active')" :required="true" :error="$errors->first('status')" />
                <x-ui.input.text id="site-address" label="Address" name="address" :value="old('address', $site?->address)" :error="$errors->first('address')" placeholder="e.g., 123 Main Street" />
                <x-ui.input.text id="site-city" label="City" name="city" :value="old('city', $site?->city)" :error="$errors->first('city')" placeholder="e.g., Nairobi" />
                <x-ui.input.text id="site-state" label="State / Region" name="state" :value="old('state', $site?->state)" :error="$errors->first('state')" placeholder="e.g., Central" />
                <x-ui.input.text id="site-country" label="Country" name="country" :value="old('country', $site?->country)" :error="$errors->first('country')" placeholder="e.g., Kenya" />
                <x-ui.input.text id="site-latitude" type="number" step="0.0000001" label="Latitude" name="latitude" :value="old('latitude', $site?->latitude)" :error="$errors->first('latitude')" placeholder="e.g., -1.2920659" />
                <x-ui.input.text id="site-longitude" type="number" step="0.0000001" label="Longitude" name="longitude" :value="old('longitude', $site?->longitude)" :error="$errors->first('longitude')" placeholder="e.g., 36.8219462" />
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm">
            <h3 class="text-lg font-semibold text-gray-900">Contact Details</h3>
            <div class="mt-4 grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                <x-ui.input.text label="Contact Name" name="contact_name" :value="old('contact_name', $site?->contact_name)" :error="$errors->first('contact_name')" placeholder="e.g., John Doe" />
                <x-ui.input.text label="Contact Phone" name="contact_phone" :value="old('contact_phone', $site?->contact_phone)" :error="$errors->first('contact_phone')" placeholder="e.g., +1 555 010 999" />
                <x-ui.input.text type="email" label="Contact Email" name="contact_email" :value="old('contact_email', $site?->contact_email)" :error="$errors->first('contact_email')" placeholder="e.g., noc@example.com" />
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm">
            <h3 class="text-lg font-semibold text-gray-900">Notes</h3>
            <x-ui.input.textarea label="Description" name="description" rows="5" :value="old('description', $site?->description)" :error="$errors->first('description')" placeholder="Add deployment details, landmarks, access notes, or maintenance context" />
        </div>
    </div>

    <div class="space-y-6 xl:sticky xl:top-24 xl:self-start">
        <div class="overflow-hidden rounded-3xl border border-slate-200 bg-slate-950 shadow-xl shadow-slate-200/80">
            <div class="border-b border-white/10 bg-[radial-gradient(circle_at_top_left,_rgba(56,189,248,0.18),_transparent_38%),linear-gradient(135deg,_rgba(15,23,42,0.98),_rgba(30,41,59,0.95))] px-6 py-5 text-white">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-sky-200/80">Location Intelligence</p>
                        <h3 class="mt-2 text-xl font-semibold">Pin the site with confidence</h3>
                        <p class="mt-2 max-w-lg text-sm text-slate-200/80">The map uses your locale for city lookup, centers on the city's midpoint, and lets you fine-tune the exact lat/long with one click.</p>
                    </div>
                    <span class="inline-flex items-center rounded-full border border-sky-400/40 bg-sky-400/10 px-3 py-1 text-xs font-medium text-sky-100" x-text="locationLabel"></span>
                </div>
            </div>

            <div class="bg-slate-950/80 p-4">
                <div id="site-location-map" class="h-[360px] overflow-hidden rounded-2xl border border-white/10"></div>
            </div>

            <div class="space-y-4 bg-white p-6">
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Latitude</p>
                        <p class="mt-2 font-mono text-base text-slate-900" x-text="formattedLatitude"></p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Longitude</p>
                        <p class="mt-2 font-mono text-base text-slate-900" x-text="formattedLongitude"></p>
                    </div>
                </div>

                <div class="flex flex-wrap gap-3">
                    <button type="button" @click="centerOnLocale()" class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-slate-700" :disabled="isSearching">
                        <span x-show="!isSearching">Center on city</span>
                        <span x-show="isSearching">Finding city...</span>
                    </button>
                    <button type="button" @click="fitCurrentSelection()" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50">Focus marker</button>
                    <button type="button" @click="clearCoordinates()" class="inline-flex items-center justify-center rounded-xl border border-rose-200 px-4 py-2.5 text-sm font-medium text-rose-700 transition hover:bg-rose-50">Clear coordinates</button>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
                    <p class="font-medium text-slate-900">How it works</p>
                    <ul class="mt-2 space-y-1.5 text-sm">
                        <li>Enter the city, state, or country, then use <span class="font-medium text-slate-900">Center on city</span>.</li>
                        <li>Click anywhere on the map or drag the marker to store the exact latitude and longitude.</li>
                        <li>The form keeps the coordinate fields and the marker synchronized.</li>
                    </ul>
                </div>

                <div class="rounded-2xl border px-4 py-3 text-sm"
                    :class="messageTone === 'error' ? 'border-rose-200 bg-rose-50 text-rose-700' : (messageTone === 'success' ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-slate-200 bg-slate-50 text-slate-600')">
                    <p x-text="statusMessage"></p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="flex items-center justify-end gap-3">
    <a href="{{ route('sites.index') }}" class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50">Cancel</a>
    <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-blue-700">{{ $submitLabel }}</button>
</div>

@once
    @push('styles')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
        <style>
            .site-map-pin {
                align-items: center;
                background: linear-gradient(135deg, #0f172a, #2563eb);
                border: 3px solid rgba(255, 255, 255, 0.95);
                border-radius: 9999px;
                box-shadow: 0 12px 24px rgba(15, 23, 42, 0.32);
                color: #fff;
                display: flex;
                font-size: 11px;
                font-weight: 700;
                height: 22px;
                justify-content: center;
                width: 22px;
            }

            .site-location-popup .leaflet-popup-content-wrapper {
                border-radius: 18px;
                box-shadow: 0 20px 45px rgba(15, 23, 42, 0.2);
            }
        </style>
    @endpush

    @push('scripts')
    
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    
    <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('siteLocationPicker', (config) => ({
                    config,
                    map: null,
                    tileLayer: null,
                    marker: null,
                    isSearching: false,
                    statusMessage: 'Enter a city and click the map to capture the exact site coordinates.',
                    messageTone: 'info',
                    searchTimer: null,
                    city: config.site?.city ?? '',
                    state: config.site?.state ?? '',
                    country: config.site?.country ?? '',
                    selectedLatitude: config.site?.latitude ?? '',
                    selectedLongitude: config.site?.longitude ?? '',

                    init() {
                        this.$nextTick(() => {
                            this.initializeMap();
                            this.bindLocationInputs();
                            this.syncFromInputs();
                            this.bootstrapDefaultCenter();
                        });
                    },

                    get locationQuery() {
                        return [this.city, this.state, this.country]
                            .filter((value) => value && value.trim() !== '')
                            .join(', ');
                    },

                    get locationLabel() {
                        return this.locationQuery || 'Awaiting city';
                    },

                    get formattedLatitude() {
                        return this.selectedLatitude !== '' ? Number(this.selectedLatitude).toFixed(6) : 'Not set';
                    },

                    get formattedLongitude() {
                        return this.selectedLongitude !== '' ? Number(this.selectedLongitude).toFixed(6) : 'Not set';
                    },

                    get latitudeInput() {
                        return this.$root.querySelector('input[name="latitude"]');
                    },

                    get longitudeInput() {
                        return this.$root.querySelector('input[name="longitude"]');
                    },

                    get cityInput() {
                        return this.$root.querySelector('input[name="city"]');
                    },

                    get stateInput() {
                        return this.$root.querySelector('input[name="state"]');
                    },

                    get countryInput() {
                        return this.$root.querySelector('input[name="country"]');
                    },

                    initializeMap() {
                        this.map = L.map('site-location-map', {
                            zoomControl: true,
                            scrollWheelZoom: true,
                        }).setView(this.config.defaultCenter, this.config.defaultZoom);

                        this.tileLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            maxZoom: 19,
                            attribution: '&copy; OpenStreetMap contributors',
                        }).addTo(this.map);

                        this.map.on('click', (event) => {
                            this.placeMarker(event.latlng.lat, event.latlng.lng, true);
                            this.setMessage('Coordinates updated from the map. You can still drag the marker for fine tuning.', 'success');
                        });
                    },

                    bindLocationInputs() {
                        [this.cityInput, this.stateInput, this.countryInput].filter(Boolean).forEach((input) => {
                            input.addEventListener('input', () => this.refreshLocationState());
                            input.addEventListener('change', () => this.handleLocationChange());
                            input.addEventListener('blur', () => this.handleLocationChange());
                        });

                        [this.latitudeInput, this.longitudeInput].filter(Boolean).forEach((input) => {
                            input.addEventListener('input', () => this.refreshCoordinateState());
                            input.addEventListener('change', () => this.syncFromInputs());
                            input.addEventListener('blur', () => this.syncFromInputs());
                        });
                    },

                    handleLocationChange() {
                        this.refreshLocationState();
                        clearTimeout(this.searchTimer);
                        this.searchTimer = setTimeout(() => {
                            if (!this.hasCoordinates()) {
                                this.centerOnLocale();
                            }
                        }, 350);
                    },

                    refreshLocationState() {
                        this.city = this.cityInput?.value ?? '';
                        this.state = this.stateInput?.value ?? '';
                        this.country = this.countryInput?.value ?? '';
                    },

                    refreshCoordinateState() {
                        this.selectedLatitude = this.latitudeInput?.value ?? '';
                        this.selectedLongitude = this.longitudeInput?.value ?? '';
                    },

                    bootstrapDefaultCenter() {
                        this.refreshLocationState();
                        this.refreshCoordinateState();

                        if (this.hasCoordinates()) {
                            this.syncFromInputs();
                            this.setMessage('Existing coordinates loaded. Click elsewhere on the map if this site has moved.', 'info');
                            return;
                        }

                        if (this.locationQuery) {
                            this.centerOnLocale();
                        }
                    },

                    hasCoordinates() {
                        return this.latitudeInput?.value !== '' && this.longitudeInput?.value !== '';
                    },

                    syncFromInputs() {
                        const latitude = Number(this.latitudeInput?.value);
                        const longitude = Number(this.longitudeInput?.value);

                        if (!Number.isFinite(latitude) || !Number.isFinite(longitude)) {
                            return;
                        }

                        this.placeMarker(latitude, longitude);
                        this.map.setView([latitude, longitude], 15);
                    },

                    async centerOnLocale() {
                        if (!this.locationQuery) {
                            this.setMessage('Add at least a city or country before centering the map.', 'error');
                            return;
                        }

                        this.isSearching = true;
                        this.setMessage(`Searching for ${this.locationQuery}...`, 'info');

                        try {
                            const params = new URLSearchParams({
                                q: this.locationQuery,
                                format: 'jsonv2',
                                limit: '1',
                            });

                            const response = await fetch(`https://nominatim.openstreetmap.org/search?${params.toString()}`, {
                                headers: {
                                    'Accept-Language': this.config.locale || 'en',
                                },
                            });

                            if (!response.ok) {
                                throw new Error('Search failed');
                            }

                            const results = await response.json();

                            if (!Array.isArray(results) || results.length === 0) {
                                this.setMessage('No city match found. Refine the city, state, or country and try again.', 'error');
                                return;
                            }

                            const match = results[0];
                            const latitude = Number(match.lat);
                            const longitude = Number(match.lon);

                            this.map.setView([latitude, longitude], 12);

                            if (!this.hasCoordinates()) {
                                this.placeMarker(latitude, longitude, true);
                                this.setMessage('City center loaded. Click the exact installation point to refine the marker.', 'success');
                            } else {
                                this.setMessage('Map centered on the selected city. Your saved coordinates remain unchanged.', 'success');
                            }
                        } catch (error) {
                            this.setMessage('Unable to fetch the city center right now. You can still place the marker manually.', 'error');
                        } finally {
                            this.isSearching = false;
                        }
                    },

                    placeMarker(latitude, longitude, persist = false) {
                        const latlng = [latitude, longitude];

                        if (!this.marker) {
                            this.marker = L.marker(latlng, {
                                draggable: true,
                                icon: L.divIcon({
                                    className: '',
                                    html: '<span class="site-map-pin">S</span>',
                                    iconSize: [22, 22],
                                    iconAnchor: [11, 11],
                                }),
                            }).addTo(this.map);

                            this.marker.on('dragend', () => {
                                const nextLatLng = this.marker.getLatLng();
                                this.applyCoordinates(nextLatLng.lat, nextLatLng.lng);
                                this.setMessage('Coordinates updated from the dragged marker.', 'success');
                            });
                        } else {
                            this.marker.setLatLng(latlng);
                        }

                        this.marker.bindPopup(this.buildPopup(latitude, longitude), {
                            className: 'site-location-popup',
                        });

                        if (persist) {
                            this.applyCoordinates(latitude, longitude);
                        }

                        this.refreshCoordinateState();
                    },

                    buildPopup(latitude, longitude) {
                        return `
                            <div class="space-y-2">
                                <p style="margin: 0; font-size: 12px; letter-spacing: 0.08em; text-transform: uppercase; color: #64748b;">Selected Coordinates</p>
                                <p style="margin: 0; font-weight: 700; color: #0f172a;">${this.locationLabel}</p>
                                <p style="margin: 0; font-family: ui-monospace, SFMono-Regular, monospace; color: #334155;">${Number(latitude).toFixed(6)}, ${Number(longitude).toFixed(6)}</p>
                            </div>
                        `;
                    },

                    applyCoordinates(latitude, longitude) {
                        if (this.latitudeInput) {
                            this.latitudeInput.value = Number(latitude).toFixed(7);
                            this.latitudeInput.dispatchEvent(new Event('input', { bubbles: true }));
                        }

                        if (this.longitudeInput) {
                            this.longitudeInput.value = Number(longitude).toFixed(7);
                            this.longitudeInput.dispatchEvent(new Event('input', { bubbles: true }));
                        }

                        this.selectedLatitude = Number(latitude).toFixed(7);
                        this.selectedLongitude = Number(longitude).toFixed(7);
                    },

                    fitCurrentSelection() {
                        if (!this.marker) {
                            this.setMessage('Set coordinates first, then use this to focus the current marker.', 'error');
                            return;
                        }

                        const current = this.marker.getLatLng();
                        this.map.flyTo([current.lat, current.lng], 15, {
                            duration: 0.8,
                        });
                        this.marker.openPopup();
                    },

                    clearCoordinates() {
                        if (this.latitudeInput) {
                            this.latitudeInput.value = '';
                        }

                        if (this.longitudeInput) {
                            this.longitudeInput.value = '';
                        }

                        this.selectedLatitude = '';
                        this.selectedLongitude = '';

                        if (this.marker) {
                            this.map.removeLayer(this.marker);
                            this.marker = null;
                        }

                        this.setMessage('Coordinates cleared. Center on the city again or click the map to set a new point.', 'info');
                    },

                    setMessage(message, tone = 'info') {
                        this.statusMessage = message;
                        this.messageTone = tone;
                    },
                }));
            });
        </script>
    @endpush
@endonce
