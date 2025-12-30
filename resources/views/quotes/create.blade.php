@extends('layouts.app')

@section('content')
    <div class="page-header">
        <h3 class="page-title"> {{ __('Create Daily Quotes') }} </h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">{{ __('Daily Quotes') }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">Create</li>
            </ol>
        </nav>
    </div>
    <div class="row">
        <div class="col-md-6 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    {{-- <h4 class="card-title">Default form</h4> --}}
                    {{-- <p class="card-description"> Basic form layout </p> --}}
                    <form action="{{ route('quotes.store') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="quote">Quote*</label>
                            <textarea required type="text" class="form-control" name="quote" id="quote" placeholder="Enter Quote" rows="4" style="height: unset">{{ old('quote') }}</textarea>
                        </div>
                        <div class="form-group">
                            <label for="type">Type</label>
                            <select class="form-select" name="type" id="type">
                                @foreach (\App\Models\Quote::USER_MOOD_TYPES as $key => $label)
                                    <option value="{{ $key }}" {{ old('type') == $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>


                        <div class="form-group">
                            <label for="status">Status</label>
                            <select class="form-select" name="status" id="status">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary me-2">Submit</button>
                        <a href="{{ route('quotes.index') }}" class="btn btn-dark">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
