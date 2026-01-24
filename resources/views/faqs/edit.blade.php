@extends('layouts.app')
<link href="{{ asset('css/faq.css') }}" rel="stylesheet">
<script src="{{ asset('js/faqs.js') }}"></script>

@section('content')
    <div class="page-header">
        <h3 class="page-title"> {{ __('Create FAQs') }} </h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('faqs.index') }}">{{ __('FAQs') }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">Edit</li>
            </ol>
        </nav>
    </div>
    <div class="row">
        <div class="col-md-12 grid-margin">
            <div class="content-card">
                <form id="faqForm" method="POST" action="{{ route('faqs.update', $faq->id) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="form-section">
                        <h2>Add New FAQ</h2>
                        <div class="format-info">
                            <h4>Formatting Tips</h4>
                            <ul>
                                <li>Use bold for emphasis and key points</li>
                                <li>Break content into short paragraphs for readability</li>
                                <li>Use bullet points for lists</li>
                                <li>Keep paragraphs 2-3 sentences max</li>
                                <li>Formatting will be preserved in the mobile app</li>
                            </ul>
                        </div>
                        {{-- Question --}}
                        <div class="form-group">
                            <label for="faqQuestion">Question</label>
                            <input type="text" id="faqQuestion" name="title" value="{{ old('title', $faq->title) }}"
                                placeholder="e.g., My friend's HRV is way higher than mine. Is that bad?" required="">
                        </div>
                        {{-- Answer  --}}
                        <div class="form-group">
                            <label for="faqAnswer">Answer (Rich Text)</label>
                            <textarea id="editor-container" name="subtitle"
                                placeholder="e.g., My friend's HRV is way higher than mine. Is that bad?" required>{{ old('subtitle', $faq->subtitle) }}</textarea>
                        </div>
                        {{-- Category --}}
                        <div class="form-group">
                            <label>Category</label>
                            <input type="hidden" id="category_id" name="category_id"
                                value="{{ old('category_id', $faq->category_id) }}">
                            <div class="category-chips">
                                @foreach ($categories as $category)
                                    <div class="chip {{ old('category_id', $faq->category_id) == $category->id ? 'selected' : '' }}"
                                        data-category="{{ $category->id }}">{{ $category->name }}</div>
                                @endforeach
                            </div>
                        </div>
                        {{-- Image Upload --}}
                        <div class="form-group">
                            <label for="faqImage">FAQ Image</label>
                            <input type="file" id="faqImage" accept="image/*" name="image">
                            <div class="upload-area" id="uploadArea"
                                style="display: {{ $faq->image_url ? 'none' : 'block' }}">
                                <div class="upload-icon">📸</div>
                                <div class="upload-text">Click to upload or drag and drop</div>
                                <div class="upload-subtext">PNG, JPG up to 5MB (Recommended: 800x800px)</div>
                            </div>
                            <div class="image-preview" id="imagePreview"
                                style="display: {{ $faq->image_url ? 'block' : 'none' }}">
                                <input type="hidden" id="remove_image" name="remove_image" value="" >
                                <div class="preview-container">
                                    <img id="previewImg" class="preview-image"
                                        src="{{ $faq->image_url ? asset(config('files.faq_file_path') . $faq->image_url) : '' }}"
                                        alt="Preview">
                                    <button type="button" class="remove-image" id="removeImage">×</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- Buttons --}}
                    <div class="actions">
                            <button type="submit" class="btn-1 btn-primary">Save FAQ</button>
                    </div>
                </form>
                <div id="jsonOutput" style="display: none;">
                    <h3 style="margin: 24px 0 16px; color: #e1e8ed;">JSON Output</h3>
                    <pre class="json-output" id="jsonContent"></pre>
                    <button class="copy-json" id="copyJson">Copy JSON</button>
                </div>
            </div>
        </div>
    </div>
@endsection
