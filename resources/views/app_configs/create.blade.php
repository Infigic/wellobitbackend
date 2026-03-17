@extends('layouts.app')

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
                            <label for="config_key">Config Key <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('config_key') is-invalid @enderror"
                                id="config_key" name="config_key" value="{{ old('config_key') }}"
                                placeholder="Enter config key" required>


                        </div>

                        <div class="form-group">
                            <label for="value_type">Value Type <span class="text-danger">*</span></label>
                            <select name="value_type" class="form-select" id="value_type" required>
                                <option value="string">String</option>
                                <option value="boolean">Boolean</option>
                                <option value="integer">Integer</option>
                                <option value="json">JSON</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <div class="form-group" id="config-value-wrapper">
                            </div>

                        </div>

                        <div class="form-group">
                            <label for="description" class="mb-2">Description</label>
                            <textarea type="text" class="form-control" name="description" id="description" placeholder="Enter Description"
                                rows="4" style="height: unset">{{ old('description') }}</textarea>
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
        document.addEventListener('DOMContentLoaded', () => {
            const typeSelect = document.getElementById('value_type');
            const container = document.getElementById('config-value-wrapper');
            const oldType = @json(old('value_type', 'string'));
            const oldConfigValue = @json(old('config_value'));

            typeSelect.addEventListener('change', handleTypeChange);
            typeSelect.value = oldType;
            handleTypeChange();

            function handleTypeChange() {
                const selectedType = typeSelect.value;

                let html = '';

                if (selectedType === 'boolean') {
                    html = `
                <label for="config_value">Config Value <span class="text-danger">*</span></label>
                <select class="form-select" name="config_value" id="config_value">
                    <option value="true">True</option>
                    <option value="false">False</option>
                </select>
            `;
                } else if (selectedType === 'integer') {
                    html = `
                <label for="config_value">Config Value <span class="text-danger">*</span></label>
                <input type="number" step="1" min="0" class="form-control" name="config_value" id="config_value">
            `;
                } else if (selectedType === 'json') {
                    html = `
                <label for="config_value">Config Value <span class="text-danger">*</span></label>
                <textarea class="form-control" name="config_value" id="config_value" rows="3"></textarea>
            `;
                } else {
                    html = `
                <label for="config_value">Config Value <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="config_value" id="config_value">
            `;
                }

                container.innerHTML = html;

                const configValueInput = document.getElementById('config_value');
                if (configValueInput && oldConfigValue !== null) {
                    configValueInput.value = oldConfigValue;
                }
            }
        });
    </script>
@endpush
