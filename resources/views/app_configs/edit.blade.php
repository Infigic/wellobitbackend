@extends('layouts.app')
<link href="{{ asset('css/appconfig.css') }}" rel="stylesheet">

@section('content')
    <div class="page-header">
        <h3 class="page-title">{{ __('Edit App Config') }}</h3>

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
                    <form action="{{ route('app-configs.update', $appConfig->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="form-group">
                            <label for="config_key" class="mb-2"> App Config Key<span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="config_key" name="config_key"
                                value="{{ old('config_key', $appConfig->config_key) }}" required>
                        </div>
                        <input type="hidden" id="value_type" name="value_type" value="{{ $appConfig->value_type }}">
                        <div class="form-group mt-3" id="config-value-container">

                            @if ($configKey === 'maintenanceMode')
                                <div class="toggle-wrap">
                                    <input type="hidden" id="maintenanceModeValue" name="config_value"
                                        value="{{ old('config_value', $appConfig->config_value) }}">

                                    <label class="toggle">
                                        <input type="checkbox" id="boolInput">
                                        <span class="toggle-slider"></span>
                                    </label>
                                    <span id="boolLabel">Disabled</span>
                                </div>

                            @elseif ($configKey === 'maxUploadSize')
                                <div class="input-group">
                                    <input type="number" min="0" class="form-control" name="config_value"
                                        placeholder="Enter max upload size in MB"
                                        value="{{ old('config_value', $appConfig->config_value) }}" required>

                                    <span class="input-group-text" style="background-color: #585e63">MB</span>
                                </div>

                            @elseif ($configKey === 'supportedFileTypes')
                                @php
                                    $config = config('app_config.supportedFileTypes');
                                    $value = $config['value'];
                                @endphp
                                <input type="hidden" id="supportedFileTypesValue" name="config_value"
                                    value="{{ old('config_value', $appConfig->config_value) }}">

                                <div class="category-chips">
                                    @foreach ($value as $type)
                                        <div class="chip {{ in_array($type, json_decode($appConfig->config_value, true)) ? 'selected' : '' }}"
                                            data-type="{{ $type }}">{{ $type }}
                                        </div>
                                    @endforeach
                                </div>

                            @elseif ($configKey === 'featureFlags')
                                @php
                                    $config = config('app_config.featureFlags');
                                    $value = $config['value'];
                                    echo json_encode($value);
                                @endphp
                                <input type="hidden" id="featureFlagsValue" name="config_value"
                                    value="{{ old('config_value', $appConfig->config_value) }}">

                                <div class="feature-flag-list">
                                    @foreach ($value as $key => $enabled)
                                        @php
                                            $displayLabel = ucfirst(preg_replace('/([A-Z])/', ' $1', $key));
                                        @endphp
                                        <label class="feature-flag-item">
                                            <span>{{ $displayLabel }}</span>
                                            <input type="checkbox" class="feature-flag-check"
                                                data-key="{{ $key }}"
                                                {{ json_decode($appConfig->config_value, true)[$key] ?? false ? 'checked' : '' }}>
                                        </label>
                                    @endforeach
                                </div>

                            @elseif ($configKey === 'appVersion')
                                <input type="text" class="form-control" name="config_value"
                                    placeholder="Enter app version (e.g., 1.0.0)"
                                    value="{{ old('config_value', $appConfig->config_value) }}" required>
                            @endif

                            @error('config_key')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="description" class="mb-2">Description</label>
                            <textarea type="text" class="form-control" name="description" id="description" placeholder="Enter Description"
                                rows="4" style="height: unset">{{ old('description', $appConfig->description) }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-primary me-2">
                            {{ __('Submit') }}
                        </button>

                        <a href="{{ route('app-configs.index') }}" class="btn btn-dark">
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
        document.addEventListener('DOMContentLoaded', function() {

            const key = document.getElementById('config_key').value;
            const valueTypeInput = document.getElementById('value_type');
            console.log('Config Key:', key);

            const configKeys = @json(config('app_config'));

            if (configKeys[key] && configKeys[key].type) {
                valueTypeInput.value = configKeys[key].type;
            }

            if (key === 'maintenanceMode') {
                renderMaintenanceMode();
            } else if (key === 'supportedFileTypes') {
                renderSupportedFileTypes();
            } else if (key === 'featureFlags') {
                renderFeatureFlags();
            }

            function renderMaintenanceMode() {
                const checkbox = document.getElementById('boolInput');
                const label = document.getElementById('boolLabel');
                const hidden = document.getElementById('maintenanceModeValue');

                if (!checkbox || !label || !hidden) {
                    return;
                }

                function sync() {
                    label.textContent = checkbox.checked ? 'Enabled' : 'Disabled';
                    hidden.value = checkbox.checked ? 'true' : 'false';
                }

                checkbox.checked =
                    String(hidden.value).toLowerCase() === 'true' || hidden.value === '1';

                checkbox.addEventListener('change', sync);
                sync();
            }

            function renderSupportedFileTypes() {
                const chips = document.querySelectorAll('.chip');
                const hidden = document.getElementById('supportedFileTypesValue');

                function sync() {
                    const selected = Array.from(chips)
                        .filter(chip => chip.classList.contains('selected'))
                        .map(chip => chip.dataset.type);

                    hidden.value = JSON.stringify(selected);
                }

                chips.forEach(chip => {
                    chip.addEventListener('click', () => {
                        chip.classList.toggle('selected');
                        sync();
                    });
                });

                const persisted = JSON.parse(hidden.value || '[]');
                chips.forEach(chip => {
                    if (persisted.includes(chip.dataset.type)) {
                        chip.classList.add('selected');
                    }
                });

                sync();
            }

            function renderFeatureFlags() {
                const checkboxes = document.querySelectorAll('.feature-flag-check');
                const hidden = document.getElementById('featureFlagsValue');

                function sync() {
                    const flags = {};
                    checkboxes.forEach(box => {
                        flags[box.dataset.key] = box.checked;
                        console.log(`Flag ${box.dataset.key}: ${box.checked}`);
                    });
                    hidden.value = JSON.stringify(flags);
                }

                checkboxes.forEach(box => {
                    box.addEventListener('change', sync);
                });

                const persisted = JSON.parse(hidden.value || '{}');
                checkboxes.forEach(box => {
                    if (persisted[box.dataset.key]) {
                        box.checked = true;
                    }
                });

                sync();
            }

        });
    </script>
@endpush
