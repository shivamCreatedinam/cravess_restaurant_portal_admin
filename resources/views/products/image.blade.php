@extends('partials.app')
@section('title', 'Add New Product')
@push('style')
    {{-- <link href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.7.2/dropzone.min.css" rel="stylesheet"> --}}
    <link rel="stylesheet" href="https://unpkg.com/dropzone@5/dist/min/dropzone.min.css" type="text/css" />

    </style>
@endpush
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
                        <a href="#">Upload Product Images</a>
                    </li>
                </ul>
            </div>
        </div>

        <div class="card mx-4">
            <div class="card-header">
                <h5><a href="{{route('product_list')}}"><i class="fas fa-arrow-alt-circle-left" title="Back To Product List"></i></a> Product
                    Images</h5>
            </div>
            <div class="card-body">

                @include('status')

                <form action="{{route('product_image_upload_post')}}" method="post" enctype="multipart/form-data"
                    id="image-upload" class="dropzone">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product_id }}">

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
    {{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.7.2/min/dropzone.min.js"></script> --}}
    <script src="https://unpkg.com/dropzone@5/dist/min/dropzone.min.js"></script>
    <script>
        Dropzone.autoDiscover = false;

        var dropzone = new Dropzone('#image-upload', {
            thumbnailWidth: 200,
            maxFilesize: 2,
            addRemoveLinks: true,
            dictCancelUpload: "Cancel",
            maxFiles:10,
            acceptedFiles: ".jpeg,.jpg,.png,.gif"
        });
    </script>
@endpush
