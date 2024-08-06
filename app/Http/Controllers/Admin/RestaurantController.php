<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StoreVerification;
use App\Models\User;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Http\Request;

class RestaurantController extends Controller
{
    use ApiResponseTrait;
    public function __construct()
    {
    }

    public function pending_list(Request $request)
    {
        if ($request->ajax()) {

            // Page Length
            $pageNumber = ($request->start / $request->length) + 1;
            $pageLength = $request->length;
            $skip = ($pageNumber - 1) * $pageLength;
            $search = $request->search['value'];
            // $order = $request->order[0]['column'];
            $dir = $request->order[0]['dir'];
            // $column = $request->columns[$order]['data'];

            $users = User::with('restoDetails')->where(['role' => 'store', 'resto_rider_status' => 'pending'])->orderBy('created_at', $dir);

            if ($search) {
                $users->where(function ($q) use ($search) {
                    $q->orWhere('name', 'like', '%' . $search . '%');
                    $q->orWhere('email', 'like', '%' . $search . '%');
                    $q->orWhere('mobile_no', 'like', '%' . $search . '%');
                });
            }
            $total = $users->count();
            $users = $users->skip($skip)->take($pageLength)->get();
            $return = [];
            foreach ($users as $key => $user) {

                $action_buttons = "<a href='" . route('resto_view_pending', ['user_id' => $user->uuid]) . "' title='View' class='btn btn-sm btn-primary'>View</a>";

                // fetch trade status
                $return[] = [
                    'id' => $key + 1,
                    'name' => $user->name,
                    'email' => $user->email,
                    'mobile_no' => $user->mobile_no,
                    'store_name' => $user->restoDetails?->store_name ?? null,
                    'actions' => $action_buttons,
                ];
            }
            return response()->json([
                'draw' => $request->draw,
                'recordsTotal' => $total,
                'recordsFiltered' => $total,
                'data' => $return,
            ]);
        }
        return view('restaurant.pending_list');
    }


    public function index(Request $request)
    {
        if ($request->ajax()) {

            // Page Length
            $pageNumber = ($request->start / $request->length) + 1;
            $pageLength = $request->length;
            $skip = ($pageNumber - 1) * $pageLength;
            $search = $request->search['value'];
            // $order = $request->order[0]['column'];
            $dir = $request->order[0]['dir'];
            // $column = $request->columns[$order]['data'];

            $users = User::where(['role' => 'store', 'resto_rider_status' => 'approved'])->orderBy('created_at', $dir);

            if ($search) {
                $users->where(function ($q) use ($search) {
                    $q->orWhere('name', 'like', '%' . $search . '%');
                    $q->orWhere('email', 'like', '%' . $search . '%');
                    $q->orWhere('mobile_no', 'like', '%' . $search . '%');
                });
            }
            $total = $users->count();
            $users = $users->skip($skip)->take($pageLength)->get();
            $return = [];
            foreach ($users as $key => $user) {

                $action_buttons = "<a href='" . route('resto_view', ['user_id' => $user->uuid]) . "' title='View' class='btn btn-sm btn-primary'>View</a>&nbsp;&nbsp;<a href='" . route('resto_edit', ['user_id' => $user->uuid]) . "' title='Edit' class='btn btn-sm btn-success'>Edit </a>";

                // fetch trade status
                $return[] = [
                    'id' => $key + 1,
                    'name' => $user->name,
                    'email' => $user->email,
                    'mobile_no' => $user->mobile_no,
                    'actions' => $action_buttons,
                ];
            }
            return response()->json([
                'draw' => $request->draw,
                'recordsTotal' => $total,
                'recordsFiltered' => $total,
                'data' => $return,
            ]);
        }
        return view('restaurant.index');
    }


    public function restoViewPending($user_id)
    {
        $data["user"] = User::find($user_id);
        return view('restaurant.view_pending', $data);
    }

    public function restoView($user_id)
    {
        $data["user"] = User::find($user_id);
        return view('restaurant.view', $data);
    }

    public function restoEdit($user_id)
    {
        $data["resto"] = User::find($user_id);
        return view('restaurant.edit', $data);
    }

    public function restoApprove(Request $request)
    {
        try {
            $btn_type = $request->btn_type;
            $user_id = $request->user_id;
            if ($btn_type == "all_approve") {
                User::find($user_id)->update([
                    "resto_rider_status" => "approved",
                    "fssai_verified" => 1,
                    "gst_verified" => 1,
                ]);
                StoreVerification::where("user_id", $user_id)->update([
                    "gst_verification" => "verified",
                    "fssai_verification" => "verified",
                ]);
            }
            if ($btn_type == "gst_approve") {
                User::find($user_id)->update([
                    "gst_verified" => 1,
                ]);
                StoreVerification::where("user_id", $user_id)->update([
                    "gst_verification" => "verified",
                ]);
            }

            if ($btn_type == "fssai_approve") {
                User::find($user_id)->update([
                    "fssai_verified" => 1,
                ]);
                StoreVerification::where("user_id", $user_id)->update([
                    "fssai_verification" => "verified",
                ]);
            }
        } catch (Exception $e) {
            return errorResponse($e->getMessage());
        }
    }
}
