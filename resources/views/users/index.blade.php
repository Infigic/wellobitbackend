@extends('layouts.app')

@section('content')
    <div class="page-header">
        <h3 class="page-title"> {{ __('Users') }} </h3>
        <nav aria-label="breadcrumb">
            {{-- <a class="btn btn-primary" href="{{ route('users.create') }}">
                <i class="mdi mdi-plus"></i> {{ __('Add') }}
            </a> --}}
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
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($users as $user)
                                    <tr>
                                        <td>{{ $user->id }}</td>
                                        <td>{{ $user->name }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>{{ ucfirst($user->role) }}</td>
                                        <td>
                                            <label
                                                class="badge {{ $user->is_active ? 'badge-outline-success' : 'badge-outline-danger' }}">{{ $user->is_active ? 'Active' : 'Inactive' }}
                                            </label>
                                        </td>
                                        <td class="d-flex gap-3">
                                            <a href="{{ route('users.edit', ['user' => $user->id]) }}" title="Edit"
                                                class="btn btn-sm btn-info"><i class="mdi mdi-pencil"></i></a>
                                            <a href="javascript:void(0)" title="Soft Delete"
                                                onclick="event.preventDefault(); document.getElementById('delete-form-{{ $user->id }}').submit()"
                                                class="btn btn-sm btn-warning"><i class="mdi mdi-delete"></i></a>
                                            <a href="javascript:void(0)" title="Hard Delete"
                                                onclick="event.preventDefault(); document.getElementById('force-delete-form-{{ $user->id }}').submit()"
                                                class="btn btn-sm btn-danger"><i class="mdi mdi-delete-forever"></i></a>
                                            <form id="delete-form-{{ $user->id }}"
                                                action="{{ route('users.destroy', ['user' => $user->id]) }}"
                                                method="POST">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                            <form id="force-delete-form-{{ $user->id }}"
                                                action="{{ route('users.forceDelete', ['user' => $user->id]) }}"
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
                            {{ $users->links() }}
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
