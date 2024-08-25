@extends('partials.app')
@section('title', 'Products List')
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
                        <a href="{{ route('child_cat_list') }}">Product List</a>
                    </li>

                </ul>
            </div>
        </div>

        <div class="card mx-4">
            <div class="card-header">
                @include('status')
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between my-4">
                    <h4 class="card-title">Products List</h4>
                    <a href="{{route('product_add_page')}}" class="btn btn-primary btn-sm rounded"><i class="fa fa-plus"></i> Add New Product</a>
                </div>

                {{-- <input type="checkbox" checked data-toggle="toggle" data-width="100" data-height="75" data-onlabel="Active" data-offlabel="Block" data-onstyle="success" data-offstyle="danger"> --}}


                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover text-center" id="productTable"
                        style="width: 100%">
                        <thead>
                            <tr>
                                <th class="text-center">#</th>
                                <th class="text-center">Item Name</th>
                                <th class="text-center">Category Name</th>
                                <th class="text-center">Sub Category Name</th>
                                <th class="text-center">Child Category Name</th>
                                <th class="text-center">Item Type</th>
                                <th class="text-center">Daily Availibility</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Images</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>


                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection


@push('script')
    <script>
        $(document).ready(function() {
            var table = $('#productTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                // order: [
                //     [0, "desc"]
                // ],
                ajax: {
                    url: "{{ route('product_list') }}",
                    type: 'GET'
                },
                columns: [{
                        data: 'id',
                        name: 'id'
                    },
                    {
                        data: 'item_name',
                        name: 'item_name'
                    },
                    {
                        data: 'category',
                        name: 'category'
                    },
                    {
                        data: 'sub_cat_name',
                        name: 'sub_cat_name'
                    },
                    {
                        data: 'child_cat_name',
                        name: 'child_cat_name'
                    },
                    {
                        data: 'item_type',
                        name: 'item_type'
                    },
                    {
                        data: 'daily_availibility',
                        name: 'daily_availibility'
                    },
                    {
                        data: 'status',
                        name: 'status',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'images',
                        name: 'images'
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false
                    },
                ]
            });
        });
    </script>
@endpush
