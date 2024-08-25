@extends('partials.app')
@section('title', 'Child Category Add')
@section('container')
    <div class="container">

        <div class="page-inner">
            <div class="page-header">
                <h4 class="page-title">Dashboard</h4>
                <ul class="breadcrumbs">
                    <li class="nav-home">
                        <a href="{{ route('dashboard') }}">
                            <i class="icon-home"></i>
                        </a>
                    </li>
                    <li class="separator">
                        <i class="icon-arrow-right"></i>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('child_cat_list') }}">Child Category</a>
                    </li>
                    <li class="separator">
                        <i class="icon-arrow-right"></i>
                    </li>
                    <li class="nav-item">
                        <a href="#">Child Category Add</a>
                    </li>
                </ul>
            </div>
        </div>

        <div class="card mx-4">
            <div class="card-header">
                <h5><a href="{{ route('child_cat_list') }}"><i class="fas fa-arrow-alt-circle-left"
                            title="Back To Categories List"></i></a> Add Child Category</h5>
            </div>
            <div class="card-body">

                @include('status')

                <form action="{{ route('store_child_category') }}" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="row">

                        <div class="col-md-4 form-group">
                            <label for="category_id">Category <span class="text-danger">*</span></label>
                            <select name="category_id" id="category_id" class="form-control">
                                <option value="" selected disabled>-- Select Category --</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}"
                                        {{ old('category_id') === $category->id ? 'selected' : '' }}>{{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-4 form-group">
                            <label for="category_id">Sub Category <span class="text-danger">*</span></label>
                            <select name="sub_category_id" id="sub_category_id" class="form-control">
                                <option value="" selected disabled>-- Select Sub Category --</option>
                                @foreach ($sub_categories as $category)
                                    <option value="{{ $category->id }}"
                                        {{ old('sub_category_id') === $category->id ? 'selected' : '' }}>
                                        {{ $category->sub_cat_name }}</option>
                                @endforeach
                            </select>
                            @error('sub_category_id')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-4 form-group">
                            <label for="name">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control"
                                value="{{ old('name') }}" placeholder="Enter Child Category Name">
                            @error('name')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-4 form-group">
                            <label for="status">Status <span class="text-danger">*</span></label>
                            <select name="status" id="status" class="form-control">
                                <option value="" selected disabled> --Select Status-- </option>
                                <option value="1" {{ old('status') === 1 ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ old('status') === 0 ? 'selected' : '' }}>Disable</option>
                            </select>
                            @error('status')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-6 form-group">
                            <label for="icon">Icon </label>
                            <input type="file" name="icon" id="icon" class="form-control"
                                value="{{ old('icon') }}" placeholder="Enter icon">
                            @error('icon')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="sb_btn text-end">
                        <button type="submit" class="btn btn-primary">Create</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
@endsection
@push('script')
<script>
    var getSubCategoryUrl = "{{ route('get_sub_category') }}";
</script>
    <script src="{{ asset('public/assets/js/common.js') }}"></script>
@endpush
