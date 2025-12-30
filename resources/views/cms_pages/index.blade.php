@extends('layouts.app')

@section('content')
    <div class="page-header">
        <h3 class="page-title"> {{ __('CMS Pages') }} </h3>
        <nav aria-label="breadcrumb">
            <a class="btn btn-primary" href="{{ route('cms-pages.create') }}">
                <i class="mdi mdi-plus"></i> {{ __('Add') }}
            </a>
            <ol class="breadcrumb">
                {{-- <li class="breadcrumb-item"><a href="#">{{ __('CMS Pages') }}</a></li> --}}
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
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Quote</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($pages as $page)
                                    <tr>
                                        <td>{{ $page->id }}</td>
                                        <td>{{ $page->name }}</td>
                                        <td>{{ $page->key }}</td>
                                        <td class="d-flex gap-3">
                                            <a href="{{ route('cms-pages.edit', ['cms_page' => $page->id]) }}"
                                                class="btn btn-sm btn-info"><i class="mdi mdi-pencil"></i></a>
                                            <a href=""
                                                onclick="event.preventDefault(); document.getElementById('delete-form-{{ $page->id }}').submit()"
                                                class="btn btn-sm btn-danger"><i class="mdi mdi-delete"></i></a>
                                            <form id="delete-form-{{ $page->id }}"
                                                action="{{ route('cms-pages.destroy', ['cms_page' => $page->id]) }}"
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
                            {{ $pages->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
