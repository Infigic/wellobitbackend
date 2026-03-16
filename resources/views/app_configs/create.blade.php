@extends('layouts.app')
<link href="{{ asset('css/appconfig.css') }}" rel="stylesheet">

@section('content')
    <div class="page-header">
        <h3 class="page-title">{{ __('Create App Config') }}</h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('app-configs.index') }}">{{ __('App Configs') }}</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Create</li>
            </ol>
        </nav>
    </div>

    <div class="row">
        <div class="col-md-6 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">

                    <form action="{{ route('app-configs.store') }}" method="POST">
                        @csrf

                        <div class="form-group">
                            <label for="config_key" class="mb-2"> App Config Key<span class="text-danger">*</span></label>
                            <select class="form-select @error('value') is-invalid @enderror" id="config_key"
                                name="config_key" required>
                                <script>
                                    let configKeys = @json($configKeys);
                                </script>
                                @foreach (array_keys($configKeys) as $key)
                                    <option value="{{ $key }}" {{ old('config_key') == $key ? 'selected' : '' }}>
                                        {{ $key }}
                                    </option>
                                @endforeach
                            </select>

                            @error('config_key')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <input type="hidden" id="value_type" name="value_type" value="">

                        <div class="form-group mt-3" id="config-value-container"></div>

                        <div class="form-group">
                            <label for="description" class="mb-2">Description</label>
                            <textarea type="text" class="form-control" name="description" id="description" placeholder="Enter Description"
                                rows="4" style="height: unset"></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary me-2">
                            {{ __('Submit') }}
                        </button>

                        <a href="{{ route('faq-categories.index') }}" class="btn btn-dark">
                            {{ __('Cancel') }}
                        </a>
                    </form>

                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {

            const configSelect = document.getElementById('config_key');
            const container = document.getElementById('config-value-container');
            const valueTypeInput = document.getElementById('value_type');

            const values = {
                maintenanceMode: false,
                supportedFileTypes: [],
                featureFlags: {}
            };

            if (!configSelect || !container) return;

            configSelect.addEventListener('change', handleConfigChange);

            function handleConfigChange() {
                const key = configSelect.value;
                container.innerHTML = '';

                if (configKeys[key]) {
                    valueTypeInput.value = configKeys[key].type || '';
                }

                if (key === 'maintenanceMode') renderMaintenanceMode();
                else if (key === 'maxUploadSize') renderMaxUploadSize();
                else if (key === 'supportedFileTypes') renderSupportedFileTypes();
                else if (key === 'featureFlags') renderFeatureFlags();
                else if (key === 'appVersion') renderAppVersion();
            }

            function renderMaintenanceMode() {

                container.innerHTML = `
                <div class="toggle-wrap">
                    <input type="hidden" id="maintenanceModeValue" name="config_value">

                    <label class="toggle">
                        <input type="checkbox" id="boolInput">
                        <span class="toggle-slider"></span>
                    </label>

                    <span id="boolLabel">Disabled</span>
                </div>
                `;

                const checkbox = document.getElementById('boolInput');
                const label = document.getElementById('boolLabel');
                const hidden = document.getElementById('maintenanceModeValue');

                function sync() {
                    values.maintenanceMode = checkbox.checked;
                    label.textContent = checkbox.checked ? 'Enabled' : 'Disabled';
                    hidden.value = checkbox.checked ? 'true' : 'false';
                }

                checkbox.addEventListener('change', sync);

                checkbox.checked = values.maintenanceMode;
                sync();
            }


            function renderMaxUploadSize() {

                container.innerHTML = `
                <div class="input-group">
                    <input type="number" min="0" class="form-control"
                        name="config_value"
                        placeholder="Enter max upload size in MB"
                        required>

                    <span class="input-group-text" style="background-color: #585e63">MB</span>
                </div>
                `;
            }


            function renderSupportedFileTypes() {

                const fileTypes = Array.isArray(configKeys.supportedFileTypes?.value) ?
                    configKeys.supportedFileTypes.value : [];

                const chips = fileTypes.map(type => {

                    const selected = values.supportedFileTypes.includes(type) ?
                        'selected' :
                        '';

                    return `<div class="chip ${selected}" data-type="${type}">${type}</div>`;
                }).join('');

                container.innerHTML = `
                <input type="hidden" id="supportedFileTypesValue" name="config_value">

                <div class="category-chips">
                    ${chips}
                </div>
                `;

                const chipElements = container.querySelectorAll('.chip');
                const hidden = document.getElementById('supportedFileTypesValue');

                function sync() {

                    const selected = [];

                    chipElements.forEach(chip => {
                        if (chip.classList.contains('selected')) {
                            selected.push(chip.dataset.type);
                        }
                    });

                    values.supportedFileTypes = selected;
                    hidden.value = JSON.stringify(selected);
                }

                chipElements.forEach(chip => {
                    chip.addEventListener('click', () => {
                        chip.classList.toggle('selected');
                        sync();
                    });
                });

                sync();
            }

            function renderFeatureFlags() {

                const flags = configKeys.featureFlags?.value ?? {};

                const flagHTML = Object.entries(flags).map(([key]) => {

                    const label = key
                        .replace(/([A-Z])/g, ' $1')
                        .replace(/^./, c => c.toUpperCase());

                    const checked = values.featureFlags[key] ? 'checked' : '';

                    return `
                    <label class="feature-flag-item">
                        <span>${label}</span>

                        <input type="checkbox"
                            class="feature-flag-check"
                            data-key="${key}"
                            ${checked}>
                    </label>
                    `;
                }).join('');

                container.innerHTML = `
                <input type="hidden" id="featureFlagsValue" name="config_value">

                <div class="feature-flag-list">
                    ${flagHTML}
                </div>
                `;

                const inputs = container.querySelectorAll('.feature-flag-check');
                const hidden = document.getElementById('featureFlagsValue');

                function sync() {

                    const result = {};

                    inputs.forEach(input => {
                        result[input.dataset.key] = input.checked;
                    });

                    values.featureFlags = result;
                    hidden.value = JSON.stringify(result);
                }

                inputs.forEach(input => {
                    input.addEventListener('change', sync);
                });

                sync();
            }

            function renderAppVersion() {

                container.innerHTML = `
                <div class="input-group">
                    <input type="text"
                        class="form-control"
                        name="config_value"
                        placeholder="Enter application version (e.g. 1.0.0)"
                        required>
                </div>
                `;
            }
            configSelect.dispatchEvent(new Event('change'));
        });
    </script>
@endpush
