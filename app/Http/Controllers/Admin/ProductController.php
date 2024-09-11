<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductChildCategory;
use App\Traits\ImageUploadTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    use ImageUploadTrait;


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

            $products = Product::query()->orderBy('created_at', $dir);

            if ($search) {
                $products->where(function ($q) use ($search) {
                    $q->orWhere('item_name', 'like', '%' . $search . '%');
                });
            }
            $total = $products->count();
            $products = $products->skip($skip)->take($pageLength)->get();
            $return = [];
            foreach ($products as $key => $product) {


                $status = $product->status === 1 ? "<span class='badge text-bg-success'>Active</span>" : "<span class='badge text-bg-danger'>Disable</span>";
                $img_btn = "<a href='" . route('product_image_upload', ['product_id', $product->id]) . "' class='btn btn-primary btn-sm rounded'>Images</a>";
                // fetch trade status
                $return[] = [
                    'id' => $key + 1,
                    'item_name' => $product->item_name,
                    'category' => @getCategoryName($product->category_id) ?? null,
                    'sub_cat_name' => @getSubCategoryName($product->sub_category_id) ?? null,
                    'child_cat_name' => @getChildCategoryName($product->child_category_id) ?? null,
                    'item_type' => ucfirst($product->item_type) ?? null,
                    'daily_availibility' => ucfirst($product->daily_availibility) ?? null,
                    'images' => $img_btn,
                    'status' => $status,
                    'actions' => null,
                ];
            }
            return response()->json([
                'draw' => $request->draw,
                'recordsTotal' => $total,
                'recordsFiltered' => $total,
                'data' => $return,
            ]);
        }
        return view('products.index');
    }

    public function addPage()
    {
        $data['categories'] = DB::table('product_categories')->where('status', 1)->get();
        $data['sub_categories'] = DB::table('product_sub_categories')->where('sub_status', 1)->get();
        $data['child_categories'] = ProductChildCategory::where('child_cat_status', 1)->get();

        return view('products.add', $data);
    }

    public function storeItem(Request $request)
    {
        $request->validate([
            'category_id' => 'required',
            'sub_category_id' => 'required',
            'child_category_id' => 'required',
            'item_name' => 'required|string',
            'status' => 'required',
            'is_featured' => 'required',
            'item_desciption' => 'required',
            'daily_availibility' => 'required',
            'item_type' => 'required',
            'available_days' => 'required',
        ]);
        try {

            Product::create([
                "restaurant_id" => Auth::user()->uuid,
                "category_id" => $request->category_id,
                "sub_category_id" => $request->sub_category_id,
                "child_category_id" => $request->child_category_id,
                "item_name" => $request->item_name,
                "description" => $request->item_desciption,
                "status" => $request->status,
                "is_featured" => $request->is_featured,
                "daily_availibility" => $request->daily_availibility,
                "item_type" => $request->item_type,
                "available_days" => json_encode($request->available_days, true),
            ]);
            return redirect()->back()->with('success', ucfirst($request->item_name) . " successfully created.");
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function productImageUpload($product_id)
    {
        return view('products.image', compact('product_id'));
    }

    public function productImageUploadPost(Request $request) {}
}
