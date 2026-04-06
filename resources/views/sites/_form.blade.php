@php
    $site = $site ?? null;
@endphp

<div class="bg-white rounded-2xl p-6 border border-gray-200 shadow-sm">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">Basic Information</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <x-ui.input.text label="Site Name" name="name" :value="old('name', $site?->name)" :required="true" :error="$errors->first('name')" placeholder="e.g., North Tower" />
        <x-ui.input.text label="Site Code" name="code" :value="old('code', $site?->code)" :error="$errors->first('code')" placeholder="e.g., SITE-001" />
        <x-ui.input.select label="Status" name="status" :options="['active' => 'Active', 'inactive' => 'Inactive', 'maintenance' => 'Maintenance']" :value="old('status', $site?->status ?? 'active')" :required="true" :error="$errors->first('status')" />
        <x-ui.input.text label="Address" name="address" :value="old('address', $site?->address)" :error="$errors->first('address')" placeholder="e.g., 123 Main Street" />
        <x-ui.input.text label="City" name="city" :value="old('city', $site?->city)" :error="$errors->first('city')" placeholder="e.g., Nairobi" />
        <x-ui.input.text label="State / Region" name="state" :value="old('state', $site?->state)" :error="$errors->first('state')" placeholder="e.g., Central" />
        <x-ui.input.text label="Country" name="country" :value="old('country', $site?->country)" :error="$errors->first('country')" placeholder="e.g., Kenya" />
        <x-ui.input.text type="number" step="0.0000001" label="Latitude" name="latitude" :value="old('latitude', $site?->latitude)" :error="$errors->first('latitude')" placeholder="e.g., -1.2920659" />
        <x-ui.input.text type="number" step="0.0000001" label="Longitude" name="longitude" :value="old('longitude', $site?->longitude)" :error="$errors->first('longitude')" placeholder="e.g., 36.8219462" />
    </div>
</div>

<div class="bg-white rounded-2xl p-6 border border-gray-200 shadow-sm">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">Contact Details</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <x-ui.input.text label="Contact Name" name="contact_name" :value="old('contact_name', $site?->contact_name)" :error="$errors->first('contact_name')" placeholder="e.g., John Doe" />
        <x-ui.input.text label="Contact Phone" name="contact_phone" :value="old('contact_phone', $site?->contact_phone)" :error="$errors->first('contact_phone')" placeholder="e.g., +1 555 010 999" />
        <x-ui.input.text type="email" label="Contact Email" name="contact_email" :value="old('contact_email', $site?->contact_email)" :error="$errors->first('contact_email')" placeholder="e.g., noc@example.com" />
    </div>
</div>

<div class="bg-white rounded-2xl p-6 border border-gray-200 shadow-sm">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">Notes</h3>
    <x-ui.input.textarea label="Description" name="description" rows="5" :value="old('description', $site?->description)" :error="$errors->first('description')" placeholder="Add deployment details, landmarks, access notes, or maintenance context" />
</div>

<div class="flex items-center justify-end gap-3">
    <a href="{{ route('sites.index') }}" class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 transition">Cancel</a>
    <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-blue-700 transition">{{ $submitLabel }}</button>
</div>
