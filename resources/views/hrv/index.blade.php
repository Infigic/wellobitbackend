@extends('layouts.app')

@section('content')
    <div class="page-header">
        <h3 class="page-title"> {{ __('HRV') }} </h3>
        <nav aria-label="breadcrumb">
            {{-- <a class="btn btn-primary" href="{{ route('quotes.create') }}">
                <i class="mdi mdi-plus"></i> {{ __('Add') }}
            </a> --}}
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">{{ __('HRVs') }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">Data</li>
            </ol>
        </nav>
    </div>
    {{-- <div class="row">
        <div class="col-lg-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Filters</h4>
                    <form method="GET" action="{{ route('hrvs.index') }}">
                        <div class="d-flex flex-wrap align-items-end gap-3">
                            <!-- User Filter -->
                            <div class="flex-grow-1" style="min-width: 180px;">
                                <label for="user_id" class="form-label mb-1">User</label>
                                <select name="user_id" id="user_id" class="form-select">
                                    <option value="">All Users</option>
                                    @foreach ($users as $user)
                                        <option value="{{ $user->id }}"
                                            {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Status Filter -->
                            <div class="flex-grow-1" style="min-width: 180px;">
                                <label for="status" class="form-label mb-1">Status</label>
                                <select name="status" id="status" class="form-select">
                                    <option value="">All Statuses</option>
                                    @foreach(\App\Models\Quote::USER_MOOD_TYPES as $key => $label)
                                        <option value="{{ $key }}" {{ old('status') == $key ? 'selected' : '' }}>
                                            {{ $label }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- From Date -->
                            <div class="flex-grow-1" style="min-width: 180px;">
                                <label for="from_date" class="form-label mb-1">From</label>
                                <input type="date" name="from_date" id="from_date" value="{{ request('from_date') }}"
                                    class="form-control">
                            </div>

                            <!-- To Date -->
                            <div class="flex-grow-1" style="min-width: 180px;">
                                <label for="to_date" class="form-label mb-1">To</label>
                                <input type="date" name="to_date" id="to_date" value="{{ request('to_date') }}"
                                    class="form-control">
                            </div>

                            <!-- Action Buttons -->
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-success mt-1">Filter</button>
                                <a href="{{ route('hrvs.index') }}" class="btn btn-outline-danger mt-1">Reset</a>
                            </div>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div> --}}

    <div class="row">
        <div class="col-lg-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    {{-- <h4 class="card-title">Basic Table</h4> --}}
                    {{-- <p class="card-description">Filters</p> --}}
                    {{ $dataTable->table() }}
                    {{-- <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Customer</th>
                                    <th>HRV</th>
                                    <th>Status</th>
                                    <th>Date Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($hrvs as $hrv)
                                    <tr>
                                        <td>{{ $hrv->id }}</td>
                                        <td>{{ $hrv->user->name }}</td>
                                        <td>{{ $hrv->hrv }}</td>
                                        <td>
                                            <label
                                                class="badge badge-outline-{{ $hrv->status_class_name }}">{{ $hrv->status_name }}
                                            </label>
                                        </td>
                                        <td>{{ $hrv->datetime->format('F j, Y \a\t g:i A') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">No data found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        <div class="mt-3">
                            {{ $hrvs->links() }}
                        </div>
                    </div> --}}
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    {{ $dataTable->scripts() }}
@endpush
