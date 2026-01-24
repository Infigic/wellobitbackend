@extends('layouts.app')

@section('content')
    <div class="page-header">
        <h3 class="page-title">{{ __('FAQ Categories') }}</h3>

        <nav aria-label="breadcrumb">
            <a class="btn btn-primary" href="{{ route('faq-categories.create') }}">
                <i class="mdi mdi-plus"></i> {{ __('Add') }}
            </a>

            <ol class="breadcrumb">
            </ol>
        </nav>
    </div>

    <div class="row">
        <div class="col-lg-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    {{ $dataTable->table() }}
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    {{ $dataTable->scripts() }}
@endpush
