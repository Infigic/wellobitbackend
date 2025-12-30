@extends('layouts.app')

@section('content')
    <div class="page-header">
        <h3 class="page-title"> {{ __('Edit User') }} </h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">{{ __('User') }}</a></li>
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
                    <form action="{{ route('users.update', ['user' => $user->id]) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="form-group">
                            <label for="quote">Name*</label>
                            <input type="name" class="form-control" name="name" id="name"
                                placeholder="Enter Name" value="{{ old('name', $user->name) }}" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="name" class="form-control" name="email" id="email"
                                placeholder="Enter Email" value="{{ old('email', $user->email) }}">
                        </div>
                        <div class="form-group">
                            <label for="is_active">Status</label>
                            <select class="form-select" name="is_active" id="is_active">
                                <option value="1" {{ old('is_active', $user->is_active) == true ? 'selected' : '' }}>
                                    Active</option>
                                <option value="0" {{ old('is_active', $user->is_active) == false ? 'selected' : '' }}>
                                    Inactive</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary me-2">Update</button>
                        <a href="{{ route('users.index') }}" class="btn btn-dark">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
