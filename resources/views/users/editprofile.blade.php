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
                    <h4 class="card-title">Change Profile</h4>
                    {{-- <p class="card-description"> Basic form layout </p> --}}
                    <form action="{{ route('users.updateprofile') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="form-group">
                            <label for="quote">Name*</label>
                            <input type="name" class="form-control" name="name" id="name"
                                placeholder="Enter Name" value="{{ old('name', $user->name) }}" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email*</label>
                            <input type="name" class="form-control" name="email" id="email"
                                placeholder="Enter Email" value="{{ old('email', $user->email) }}" required>
                        </div>
                        <button type="submit" class="btn btn-primary me-2">Update</button>
                        <a href="{{ route('home') }}" class="btn btn-dark">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-6 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Change Password</h4>
                    {{-- <p class="card-description"> Basic form layout </p> --}}
                    <form action="{{ route('users.updatepassword') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" class="form-control" name="password" id="password"
                                placeholder="Enter Password" required>
                        </div>
                        <div class="form-group">
                            <label for="password-confirm">Confirm Password</label>
                            <input type="password" class="form-control" name="password_confirmation" id="password-confirm"
                                placeholder="Enter Confirm Password" required>
                        </div>
                        <button type="submit" class="btn btn-primary me-2">Update Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
