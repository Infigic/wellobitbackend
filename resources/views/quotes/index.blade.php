@extends('layouts.app')

@section('content')
    <div class="page-header">
        <h3 class="page-title"> {{ __('Daily Quotes') }} </h3>
        <nav aria-label="breadcrumb">
            <a class="btn btn-primary" href="{{ route('quotes.create') }}">
                <i class="mdi mdi-plus"></i> {{ __('Add') }}
            </a>
            <ol class="breadcrumb">
                {{-- <li class="breadcrumb-item"><a href="#">{{ __('Daily Quotes') }}</a></li> --}}
                {{-- <li class="breadcrumb-item active" aria-current="page">Listing</li> --}}
            </ol>
        </nav>
    </div>
    <div class="row">
        <div class="col-lg-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    {{-- <h4 class="card-title">Basic Table</h4>
                    <p class="card-description"> Add class <code>.table</code>
                    </p> --}}
                    {{ $dataTable->table() }}
                    {{-- <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Quote</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($quotes as $quote)
                                    <tr>
                                        <td>{{ $quote->id }}</td>
                                        <td>{{ $quote->quote }}</td>
                                        <td>
                                            <label
                                                class="badge badge-pill badge-{{ $quote->type_class_name }}">{{ $quote->type_name }}
                                            </label>
                                        </td>
                                        <td>
                                            <label
                                                class="badge {{ $quote->is_active ? 'badge-outline-success' : 'badge-outline-danger' }}">{{ $quote->is_active ? 'Active' : 'Inactive' }}
                                            </label>
                                        </td>
                                        <td class="d-flex gap-3">
                                            <a href="{{ route('quotes.edit', ['quote' => $quote->id]) }}"
                                                class="btn btn-sm btn-info"><i class="mdi mdi-pencil"></i></a>
                                            <a href=""
                                                onclick="event.preventDefault(); document.getElementById('delete-form-{{ $quote->id }}').submit()"
                                                class="btn btn-sm btn-danger"><i class="mdi mdi-delete"></i></a>
                                            <form id="delete-form-{{ $quote->id }}"
                                                action="{{ route('quotes.destroy', ['quote' => $quote->id]) }}"
                                                method="POST">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="mt-3">
                            {{ $quotes->links() }}
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
