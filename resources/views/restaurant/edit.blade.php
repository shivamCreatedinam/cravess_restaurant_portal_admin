@extends('partials.app')
@section('title', 'Restaurant Edit')
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
                        <a href="{{ route('resto_list') }}">Restaurants</a>
                    </li>
                    <li class="separator">
                        <i class="icon-arrow-right"></i>
                    </li>
                    <li class="nav-item">
                        <a href="#">Restaurant Edit</a>
                    </li>
                </ul>
            </div>
        </div>

        <div class="card mx-4">
            <div class="card-header">
                <h5><a href="{{ route('resto_list') }}"><i class="fas fa-arrow-alt-circle-left"
                            title="Back To Restaurants List"></i></a> Restaurant Edit</h5>
            </div>
            <div class="card-body">

                @include('status')

                <div class="editFormDiv">
                    <h5>1. <u>Update Personal Details : </u></h5>
                    <form action="javascript:void(0)" method="post">
                        @csrf
                        <input type="hidden" name="user_id" id="user_id" value="{{ $resto->uuid }}">
                        <div class="row">
                            <div class="col-md-3 form-group">
                                <label for="name">Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="name" class="form-control"
                                    value="{{ $resto->name }}" placeholder="Enter Name">
                                @error('name')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-3 form-group">
                                <label for="email">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" id="email" class="form-control"
                                    value="{{ $resto->email }}" placeholder="Enter Email">
                                @error('email')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-3 form-group">
                                <label for="mobile_no">Mobile <span class="text-danger">*</span></label>
                                <input type="text" name="mobile_no" id="mobile_no" class="form-control"
                                    value="{{ $resto->mobile_no }}" placeholder="Enter Mobile Number">
                                @error('mobile_no')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                        </div>
                        <hr>
                        <h5>2. <u>Update Restaurant Details : </u></h5>
                        <div class="row">
                            <div class="col-md-3 form-group">
                                <label for="store_name">Store Name <span class="text-danger">*</span></label>
                                <input type="text" name="store_name" id="store_name" class="form-control"
                                    value="{{ $resto->restoDetails?->store_name }}" placeholder="Enter Store Name">
                                @error('store_name')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-3 form-group">
                                <label for="store_type">Store Type <span class="text-danger">*</span></label>
                                <select name="store_type" id="store_type" class="form-control" required>
                                    <option value="" disabled>-- Select Store type --</option>
                                    <option value="veg" {{ $resto->restoDetails?->store_type =='veg' ?'selected':'' }}>Veg</option>
                                    <option value="non_veg" {{ $resto->restoDetails?->store_type =='non_veg' ?'selected':'' }}>Non Veg</option>
                                    <option value="both" {{ $resto->restoDetails?->store_type =='both' ?'selected':'' }}>Both</option>
                                </select>
                                @error('store_type')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-3 form-group">
                                <label for="store_mobile_no">Store Mobile No <span class="text-danger">*</span></label>
                                <input type="text" name="store_mobile_no" id="store_mobile_no" class="form-control"
                                    value="{{ $resto->restoDetails?->store_mobile_no }}" placeholder="Enter Store Mobile No">
                                @error('store_mobile_no')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-3 form-group">
                                <label for="store_phone_no">Store Phone No</label>
                                <input type="text" name="store_phone_no" id="store_phone_no" class="form-control"
                                    value="{{ $resto->restoDetails?->store_phone_no }}" placeholder="Enter Store Phone No">
                                @error('store_phone_no')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-3 form-group">
                                <label for="store_address">Store Address <span class="text-danger">*</span></label>
                                <input type="text" name="store_address" id="store_address" class="form-control"
                                    value="{{ $resto->restoDetails?->store_address }}" placeholder="Enter Store Address">
                                @error('store_address')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-3 form-group">
                                <label for="store_city">Store City <span class="text-danger">*</span></label>
                                <input type="text" name="store_city" id="store_city" class="form-control"
                                    value="{{ $resto->restoDetails?->store_city }}" placeholder="Enter Store City">
                                @error('store_city')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-3 form-group">
                                <label for="store_state">Store State <span class="text-danger">*</span></label>
                                <input type="text" name="store_state" id="store_state" class="form-control"
                                    value="{{ $resto->restoDetails?->store_state }}" placeholder="Enter Store State">
                                @error('store_state')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-3 form-group">
                                <label for="store_pincode">Store Pincode <span class="text-danger">*</span></label>
                                <input type="text" name="store_pincode" id="store_pincode" class="form-control"
                                    value="{{ $resto->restoDetails?->store_pincode }}" placeholder="Enter Store Pincode">
                                @error('store_pincode')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="col-md-6 form-group">
                                <label for="store_desc">Store Description </label>
                                <textarea type="text" name="store_desc" id="store_desc" class="form-control" placeholder="Enter Store Description">{{ $resto->restoDetails?->store_desc }}</textarea>
                                @error('store_desc')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>


                        </div>

                        <div class="sb_btn text-end">
                            <button type="submit" class="btn btn-primary">Update</button>
                        </div>
                    </form>
                </div>

                <hr>

                <div class="btns">
                    <h5><u>Update Status & KYC Details : </u></h5>
                    <div class="row text-center">

                        <div class="col-md-2 form-group">
                            <label for="">Restaurant Status</label><br>
                            <input type="checkbox" {{ $resto->user_status == 'active' ? 'checked' : '' }}
                                data-toggle="toggle" data-width="100" data-height="75" data-onlabel="Active"
                                data-offlabel="Block" data-onstyle="success" data-offstyle="danger"
                                data-type="user_status">
                        </div>

                        <div class="col-md-2 form-group">
                            <label for="">Restaurant BAN</label><br>
                            <input type="checkbox" {{ $resto->user_status == 'ban' ? 'checked' : '' }}
                                data-toggle="toggle" data-width="100" data-height="75" data-onlabel="BAN"
                                data-offlabel="Not BAN" data-onstyle="success" data-offstyle="danger"
                                data-type="user_ban">
                        </div>

                        <div class="col-md-2 form-group">
                            <label for="">Email Verification</label><br>
                            <input type="checkbox" {{ $resto->email_verified_at != null ? 'checked' : '' }}
                                data-toggle="toggle" data-width="100" data-height="75" data-onlabel="Verified"
                                data-offlabel="Not Verify" data-onstyle="success" data-offstyle="danger"
                                data-type="email_verify">
                        </div>

                        <div class="col-md-2 form-group">
                            <label for="">Mobile Verification</label><br>
                            <input type="checkbox" {{ $resto->mobile_verified_at != null ? 'checked' : '' }}
                                data-toggle="toggle" data-width="100" data-height="75" data-onlabel="Verified"
                                data-offlabel="Not Verify" data-onstyle="success" data-offstyle="danger"
                                data-type="mobile_verify">
                        </div>

                        <div class="col-md-2 form-group">
                            <label for="">Aadhar Verification</label><br>
                            <input type="checkbox" {{ $resto->aadhar_verified != 0 ? 'checked' : '' }}
                                data-toggle="toggle" data-width="100" data-height="75" data-onlabel="Verified"
                                data-offlabel="Not Verify" data-onstyle="success" data-offstyle="danger"
                                data-type="aadhar_verify">
                        </div>

                        <div class="col-md-2 form-group">
                            <label for="">PAN Verification</label><br>
                            <input type="checkbox" {{ $resto->pan_verified != 0 ? 'checked' : '' }} data-toggle="toggle"
                                data-width="100" data-height="75" data-onlabel="Verified" data-offlabel="Not Verify"
                                data-onstyle="success" data-offstyle="danger" data-type="pan_verify">
                        </div>

                        <div class="col-md-2 form-group">
                            <label for="">GST Verification</label><br>
                            <input type="checkbox" {{ $resto->gst_verified != 0 ? 'checked' : '' }} data-toggle="toggle"
                                data-width="100" data-height="75" data-onlabel="Verified" data-offlabel="Not Verify"
                                data-onstyle="success" data-offstyle="danger" data-type="gst_verify">
                        </div>

                        <div class="col-md-2 form-group">
                            <label for="">FSSAI Verification</label><br>
                            <input type="checkbox" {{ $resto->fssai_verified != 0 ? 'checked' : '' }}
                                data-toggle="toggle" data-width="100" data-height="75" data-onlabel="Verified"
                                data-offlabel="Not Verify" data-onstyle="success" data-offstyle="danger"
                                data-type="fssai_verify">
                        </div>

                        <div class="col-md-2 form-group">
                            <label for="">2FA Enable</label><br>
                            <input type="checkbox" {{ $resto->google2fa_enable == 'yes' ? 'checked' : '' }}
                                data-toggle="toggle" data-width="100" data-height="75" data-onlabel="Enable"
                                data-offlabel="Disable" data-onstyle="success" data-offstyle="danger" data-type="2fa">
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('script')
    <script>
        $(document).ready(function() {
            // Attach event listener to elements with data-toggle="toggle"
            $('input[type="checkbox"]').change(function() {
                let type = $(this).data('type');
                updateStatus(this, type);
            });

            // Initialize toggle buttons if needed
            // $('[data-toggle="toggle"]').bootstrapToggle();
        });

        function updateStatus(input, type, other = null) {
            let inputData = $(this)
            let user_id = $('#user_id').val()
            $.ajax({
                type: "post",
                url: "{{ route('admin_user_status_update') }}",
                data: {
                    "status_type": type,
                    "user_id": user_id,
                    "_token": "{{ csrf_token() }}"
                },
                success: function(response) {
                    console.log(response);
                },
                error: function(xhr, status, error) {
                    console.error(xhr.responseText);
                }
            });
        }
    </script>
@endpush
