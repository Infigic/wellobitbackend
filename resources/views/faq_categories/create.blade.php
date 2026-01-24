@extends('layouts.app')

@section('content')
    <div class="page-header">
        <h3 class="page-title">{{ __('Create FAQ Category') }}</h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('faq-categories.index') }}">{{ __('FAQ Categories') }}</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Create</li>
            </ol>
        </nav>
    </div>

    <div class="row">
        <div class="col-md-6 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">

                    <form action="{{ route('faq-categories.store') }}" method="POST">
                        @csrf

                        <div class="form-group">
                            <label for="name">Category Name <span class="text-danger">*</span></label>
                            <input
                                type="text"
                                class="form-control @error('name') is-invalid @enderror"
                                id="name"
                                name="name"
                                value="{{ old('name') }}"
                                placeholder="Enter category name"
                                required
                            >

                            @error('name')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
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
