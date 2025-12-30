@extends('layouts.app')

@section('content')
    <div class="page-header">
        <h3 class="page-title"> {{ __('Create CMS Pages') }} </h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">{{ __('CMS Pages') }}</a></li>
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
                    <form action="{{ route('cms-pages.store') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="name">Name*</label>
                            <input type="name" class="form-control" name="name" id="name"
                                placeholder="Enter Name" value="{{ old('name') }}" required>
                        </div>
                        <div class="form-group">
                            <label for="key">Key*</label>
                            <input type="name" class="form-control" name="key" id="key" placeholder="Enter Key"
                                value="{{ old('key') }}" required>
                        </div>
                        <div class="form-group">
                            <label for="content">Content*</label>
                            <input type="hidden" name="content" id="content">
                            <div id="editor" style="height: 300px;">
                                {!! old('content') !!}
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary me-2" id="btn-submitform">Submit</button>
                        <a href="{{ route('cms-pages.index') }}" class="btn btn-dark">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Quill editor
            var quill = new Quill('#editor', {
                theme: 'snow',
                modules: {
                    toolbar: [
                        [{
                            'header': [1, 2, 3, false]
                        }],
                        ['bold', 'italic', 'underline', 'strike'],
                        [{
                            'list': 'ordered'
                        }, {
                            'list': 'bullet'
                        }],
                        ['link', 'image'],
                        ['clean']
                    ]
                },
                placeholder: 'Write your content here...',
            });

            // Get the initial content from the hidden input or empty string
            var content = document.querySelector('input[name="content"]').value;
            if (content) {
                quill.root.innerHTML = content;
            }

            document.querySelector('#btn-submitform').onclick = function() {
                debugger
                document.querySelector('#content').value = quill.root.innerHTML;
            };
        });
    </script>
@endsection
