@extends('partials.app')
@section('title', 'Restaurant View')
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
                        <a href="{{ route('resto_pending_list') }}">Restaurant</a>
                    </li>
                    <li class="separator">
                        <i class="icon-arrow-right"></i>
                    </li>
                    <li class="nav-item">
                        <a href="#">Restaurant View</a>
                    </li>
                </ul>
            </div>
        </div>

        <div class="card mx-4">
            <div class="card-header">
                <div class="d-flex justify-content-between">

                    <div>
                        <h5><a href="{{ route('admin_user_list') }}"><i class="fas fa-arrow-alt-circle-left"
                                    title="Back To User List"></i></a> Restaurant Status : @if ($user->resto_rider_status === 'approved')
                                <span class="badge bg-success">Approved</span>
                            @elseif($user->resto_rider_status === 'pending' || $user->resto_rider_status === 'cancelled')
                                <span class="badge bg-danger">{{ ucfirst($user->resto_rider_status) }}</span>
                            @endif
                        </h5>
                    </div>

                    @if ($user->resto_rider_status != 'approved')
                        <div>
                            <button class="btn btn-primary btn-sm statusBtn" data-userid="{{ $user->uuid }}"
                                data-btn="all_approve">Approve</button>
                            <button class="btn btn-danger btn-sm statusRejectBtn" data-userid="{{ $user->uuid }}"
                                data-btn="all_reject">Reject</button>
                        </div>
                    @endif

                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h4>1. Personal Details</h4>
                        <table class="table table-hover">
                            <tr>
                                <th>Full Name :</th>
                                <td>{{ $user->name }}</td>
                            </tr>
                            <tr>
                                <th>Email :</th>
                                <td><a href="mailto:{{ $user->email }}"
                                        style="text-decorations:none; color:inherit;">{{ $user->email }}</a></td>
                            </tr>
                            <tr>
                                <th>Mobile :</th>
                                <td><a href="tel:{{ $user->mobile_no }}"
                                        style="text-decorations:none; color:inherit;">{{ $user->mobile_no }}</a></td>
                            </tr>

                            <tr>
                                <th>Email Verification :</th>
                                <td>{!! $user->email_verified_at != null
                                    ? "<span class='badge bg-success'>Verified</span>"
                                    : "<span class='badge bg-danger'>Not Verified</span>" !!}</td>
                            </tr>
                            <tr>
                                <th>Mobile Verification :</th>
                                <td>{!! $user->mobile_verified_at != null
                                    ? "<span class='badge bg-success'>Verified</span>"
                                    : "<span class='badge bg-danger'>Not Verified</span>" !!}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h4>2. Restaurant Details</h4>
                        <table class="table table-hover">
                            <tr>
                                <th>Restaurant Name :</th>
                                <td>{{ $user->restoDetails?->store_name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Restaurant Email :</th>
                                <td>{{ $user->restoDetails?->store_email ?? '-' }}
                                </td>
                            </tr>
                            <tr>
                                <th>Restaurant Mobile :</th>
                                <td>{{ $user->restoDetails?->store_mobile_no ?? '-' }}
                                </td>
                            </tr>

                            <tr>
                                <th>Restaurant Address :</th>
                                <td>{!! $user->restoDetails?->store_address !!}, {{ $user->restoDetails?->store_city }},
                                    {{ $user->restoDetails?->store_state }}, {{ $user->restoDetails?->store_pincode }}
                                </td>
                            </tr>
                            <tr>
                                <th>Restaurant Website :</th>
                                <td><a href="{{ $user->restoDetails?->website }}" target="_blank"
                                        style="text-decorations:none; color:inherit;">{{ $user->restoDetails?->website ?? '-' }}</a>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-12">
                        <h4>3. KYC Details</h4>
                        <table class="table table-hover">

                            <tr>
                                <th>Aadhar Verification :</th>
                                <td>
                                    @if ($user->aadhar_verified != 0)
                                        <span class="badge bg-success">Verified</span>
                                    @else
                                        <span class="badge bg-danger">Not Verified</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($user->aadhar_verified != 0)
                                        {{ $user->aadharVerification?->aadhar_no }}
                                    @endif
                                </td>
                                <td></td>
                            </tr>
                            <tr>
                                <th>PAN Verification :</th>
                                <td>
                                    @if ($user->pan_verified != 0)
                                        <span class="badge bg-success">Verified</span>
                                    @else
                                        <span class="badge bg-danger">Not Verified</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($user->pan_verified != 0)
                                        {{ strtoupper($user->panVerification?->pan_no) }}
                                    @endif
                                </td>
                                <td></td>
                            </tr>
                            <tr>
                                <th>FSSAI Verification :</th>
                                <td>
                                    @php
                                        $fssai_image_url = 'javascript:void(0)';
                                        $fssai_image = null;
                                        if (
                                            isset($user->restoVerifications) &&
                                            !is_null($user->restoVerifications->fssai_image)
                                        ) {
                                            $fssai_image = $user->restoVerifications->fssai_image;
                                            $fssai_image_url = asset(
                                                'storage/app/public/' . $user->restoVerifications->fssai_image,
                                            );
                                        }
                                    @endphp
                                    @if ($user->fssai_verified != 0)
                                        <span class="badge bg-success">Verified</span> |
                                        <a class="badge bg-warning" href="{{ $fssai_image_url }}"
                                            {{ $fssai_image != null ? 'target="_blank"' : '' }}>View</a>
                                    @else
                                        <span class="badge bg-danger">Not Verified</span>
                                        <a class="badge bg-warning" href="{{ $fssai_image_url }}"
                                            {{ $fssai_image != null ? 'target="_blank"' : '' }}>View</a>
                                    @endif
                                </td>
                                <td>
                                    @if (isset($user->restoVerifications) && $user->restoVerifications->fssai_verification == 'pending')
                                        <button type="button" class="btn btn-primary btn-sm statusBtn"
                                            data-userid="{{ $user->uuid }}" data-btn="fssai_approve">Verify</button>
                                    @elseif(isset($user->restoVerifications) && $user->restoVerifications->fssai_verification == 'verified')
                                        {{-- {{ $user->restoVerifications?->gst_no }} --}}
                                    @elseif (isset($user->restoVerifications) && $user->restoVerifications->fssai_verification == 'cancelled')
                                        {!! $user->restoVerifications?->fssai_cancellation_reason !!}
                                    @else
                                        <span>Not Uploaded.</span>
                                    @endif
                                </td>
                                <td>
                                    @if (isset($user->restoVerifications) && $user->restoVerifications->fssai_verification == 'pending')
                                        <button type="button" class="btn btn-danger btn-sm statusRejectBtn"
                                            data-userid="{{ $user->uuid }}" data-btn="fssai_reject">Reject</button>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>GST Verification :</th>
                                <td>
                                    @php
                                        $gst_image_url = 'javascript:void(0)';
                                        $gst_image = null;
                                        if (
                                            isset($user->restoVerifications) &&
                                            !is_null($user->restoVerifications->gst_image)
                                        ) {
                                            $gst_image = $user->restoVerifications->gst_image;
                                            $gst_image_url = asset(
                                                'storage/app/public/' . $user->restoVerifications->gst_image,
                                            );
                                        }
                                    @endphp
                                    @if ($user->gst_verified != 0)
                                        <span class="badge bg-success">Verified</span> |
                                        <a class="badge bg-warning" href="{{ $gst_image_url }}"
                                            {{ $gst_image != null ? 'target="_blank"' : '' }}>View</a>
                                    @else
                                        <span class="badge bg-danger">Not Verified</span>
                                        <a class="badge bg-warning" href="{{ $gst_image_url }}"
                                            {{ $gst_image != null ? 'target="_blank"' : '' }}>View</a>
                                    @endif
                                </td>
                                <td>
                                    @if (isset($user->restoVerifications) && $user->restoVerifications->gst_verification == 'pending')
                                        <button type="button" class="btn btn-primary btn-sm statusBtn"
                                            data-userid="{{ $user->uuid }}" data-btn="gst_approve">Verify</button>
                                    @elseif(isset($user->restoVerifications) && $user->restoVerifications->gst_verification == 'verified')
                                        {{ $user->restoVerifications?->gst_no }}
                                    @elseif (isset($user->restoVerifications) && $user->restoVerifications->gst_verification == 'cancelled')
                                        {!! $user->restoVerifications?->gst_cancellation_reason !!}
                                    @else
                                        <span>Not Uploaded.</span>
                                    @endif
                                </td>
                                <td>
                                    @if (isset($user->restoVerifications) && $user->restoVerifications->gst_verification == 'pending')
                                        <button type="button" class="btn btn-danger btn-sm statusRejectBtn"
                                            data-userid="{{ $user->uuid }}" data-btn="gst_reject">Reject</button>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- Model Box --}}
    <div class="modal fade" id="cancellationModal" tabindex="-1" aria-labelledby="cancellationModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cancellationModalLabel">New message</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="javascript:void(0)" id="cancellation_reason_model">
                        <div class="mb-3">
                            <label for="reason" class="col-form-label">Cancellation Reason:</label>
                            <textarea class="form-control" id="reason"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary">Submit</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('script')
    <script>
        $(document).ready(function() {
            $(".statusBtn").on("click", function() {
                if (confirm("Are you sure process this action.")) {
                    let approveBtn = $(this)
                    approveRequest(approveBtn)
                } else {
                    toast("Cancelled request.", 'error')
                }
            })

            $(".statusRejectBtn").on("click",function(){
                if (confirm("Are you sure reject this action.")) {
                    let rejectBtn = $(this)
                    // approveRequest(approveBtn)
                } else {
                    toast("Cancelled request.", 'error')
                }
            })
        });

        function approveRequest(btn) {
            let user_id = $(btn).attr("data-userid");
            let btnType = $(btn).attr("data-btn");
            $.ajax({
                type: "post",
                url: "{{ route('resto_approve') }}",
                data: {
                    "user_id": user_id,
                    "btn_type": btnType
                },
                success: function(response) {
                    toast("Details successfully approved.")
                    setTimeout(() => {
                        window.location.href = `{{ route('resto_pending_list') }}`
                    }, 4000);
                }
            });
        }
    </script>
@endpush
