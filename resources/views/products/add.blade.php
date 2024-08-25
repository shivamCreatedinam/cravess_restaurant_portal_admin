@extends('partials.app')
@section('title', 'Add New Product')
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
                        <a href="{{route('product_list')}}">Products</a>
                    </li>
                    <li class="separator">
                        <i class="icon-arrow-right"></i>
                    </li>
                    <li class="nav-item">
                        <a href="#">Add New Product</a>
                    </li>
                </ul>
            </div>
        </div>

        <div class="card mx-4">
            <div class="card-header">
                <h5><a href="{{route('product_list')}}"><i class="fas fa-arrow-alt-circle-left" title="Back To Categories List"></i></a> Add
                    New Product</h5>
            </div>
            <div class="card-body">

                @include('status')

                <form action="{{ route('store_new_item') }}" method="post">
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
                            <label for="sub_category_id">Sub Category <span class="text-danger">*</span></label>
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
                            <label for="child_category_id">Child Category <span class="text-danger">*</span></label>
                            <select name="child_category_id" id="child_category_id" class="form-control">
                                <option value="" selected disabled>-- Select Child Category --</option>
                                @foreach ($child_categories as $category)
                                    <option value="{{ $category->id }}"
                                        {{ old('child_category_id') === $category->id ? 'selected' : '' }}>
                                        {{ $category->child_cat_name }}</option>
                                @endforeach
                            </select>
                            @error('child_category_id')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-12 form-group">
                            <label for="item_name">Item Name <span class="text-danger">*</span></label>
                            <input type="text" name="item_name" id="item_name" class="form-control"
                                value="{{ old('item_name') }}" placeholder="Enter Item Name">
                            @error('item_name')
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

                        <div class="col-md-4 form-group">
                            <label for="daily_availibility">Daily Avalibility <span class="text-danger">*</span></label>
                            <select name="daily_availibility" id="daily_availibility" class="form-control">
                                <option value="" selected disabled> --Select Avalibility-- </option>
                                @php
                                    $avalibility = config('constant.item.daily_availablity');
                                @endphp
                                @foreach ($avalibility as $available)
                                    <option value="{{ $available }}"
                                        {{ old('daily_availibility') == $available ? 'selected' : '' }}>
                                        {{ ucfirst($available) }} </option>
                                @endforeach
                            </select>
                            @error('daily_availibility')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-4 form-group">
                            <label for="item_type">Item Type <span class="text-danger">*</span></label>
                            <select name="item_type" id="item_type" class="form-control">
                                <option value="" selected disabled> --Select Item Type-- </option>
                                @php
                                    $type = config('constant.item.type');
                                @endphp
                                @foreach ($type as $data)
                                    <option value="{{ $data }}"
                                        {{ old('item_type') == $data ? 'selected' : '' }}>
                                        {{ ucfirst($data) }} </option>
                                @endforeach
                            </select>
                            @error('item_type')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-4 form-group">
                            <label for="available_days">Available Days <span class="text-danger">*</span></label>
                            <select name="available_days[]" id="available_days" class="form-control" multiple>
                                <option value="" selected disabled> --Select Available Days-- </option>
                                @php
                                    $days = config('constant.item.days');
                                @endphp
                                @foreach ($days as $day)
                                    <option value="{{ $day }}"
                                        {{ old('available_days') == $day ? 'selected' : '' }}>
                                        {{ ucfirst($day) }} </option>
                                @endforeach
                            </select>
                            @error('available_days')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-4 form-group">
                            <label for="is_featured">Featured <span class="text-danger">*</span></label>
                            <select name="is_featured" id="is_featured" class="form-control">
                                <option value="" selected disabled> --Select Featured-- </option>
                                <option value="1" {{ old('is_featured') === 1 ? 'selected' : '' }}>Yes</option>
                                <option value="0" {{ old('is_featured') === 0 ? 'selected' : '' }}>No</option>
                            </select>
                            @error('is_featured')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>




                        <div class="col-md-12 form-group">
                            <label for="name">Description <span class="text-danger">*</span></label>
                            <textarea name="item_desciption" id="item_desciption"></textarea>
                            @error('name')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="sb_btn text-end">
                        <button type="submit" class="btn btn-primary">Create Item</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
@endsection
@push('script')
    <script>
        var getSubCategoryUrl = "{{ route('get_sub_category') }}";
        var getChildCategoryUrl = "{{ route('get_child_category') }}";
    </script>
    <script src="{{ asset('public/assets/js/common.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/ckeditor/4.9.0/ckeditor.js"></script>
    <script>
        CKEDITOR.replace('item_desciption');
    </script>
@endpush
