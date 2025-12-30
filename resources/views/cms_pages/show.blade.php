@extends('layouts.app')

@section('content')
    <div class="page-header">
        <h3 class="page-title"> {{ __('Edit Daily Quotes') }} </h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">{{ __('Daily Quotes') }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">Edit</li>
            </ol>
        </nav>
    </div>
    <div class="row">
        <div class="col-md-6 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    {{-- <h4 class="card-title">Default form</h4> --}}
                    {{-- <p class="card-description"> Basic form layout </p> --}}
                    <form action="{{ route('quotes.update', ['quote' => $quote->id]) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="form-group">
                            <label for="quote">Quote*</label>
                            <textarea required type="text" class="form-control" name="quote" id="quote" placeholder="Enter Quote" rows="4" style="height: unset">{{ old('quote', $quote->quote) }}</textarea>
                        </div>
                        <div class="form-group">
                            <label for="author">Author</label>
                            <input type="name" class="form-control" name="author" id="author" placeholder="Enter Author" value="{{ old('author', $quote->author) }}">
                        </div>
                        <div class="form-group">
                            <label for="is_active">Status</label>
                            <select class="form-select" name="is_active" id="is_active">
                              <option value="1" {{ old('is_active', $quote->is_active) == true ? 'selected' : '' }}>Active</option>
                              <option value="0" {{ old('is_active', $quote->is_active) == false ? 'selected' : '' }}>Inactive</option>
                            </select>
                          </div>
                        <button type="submit" class="btn btn-primary me-2">Update</button>
                        <a href="{{ route('quotes.index') }}" class="btn btn-dark">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
