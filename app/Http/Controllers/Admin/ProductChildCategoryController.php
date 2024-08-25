<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductChildCategory;
use App\Traits\ApiResponseTrait;
use App\Traits\ImageUploadTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProductChildCategoryController extends Controller
{
    use ImageUploadTrait;
    use ApiResponseTrait;
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

            $categories = ProductChildCategory::query()->orderBy('created_at', $dir);

            if ($search) {
                $categories->where(function ($q) use ($search) {
                    $q->orWhere('name', 'like', '%' . $search . '%');
                });
            }
            $total = $categories->count();
            $categories = $categories->skip($skip)->take($pageLength)->get();
            $return = [];
            foreach ($categories as $key => $category) {
                // dd($category->category);
                $action_buttons = "<a href='" . route('edit_child_category', ['id' => $category->id]) . "' title='View' class='btn btn-sm btn-primary'>Edit</a>";
                $action_buttons .= "&nbsp;<a href='" . route('delete_child_category', ['id' => $category->id]) . "' title='View' class='btn btn-sm btn-danger'>Delete</a>";

                $icon = $category->child_cat_icon ?? asset('public/assets/img/dummy.png');
                $image = "<img src='" . $icon . "' alt='category image' class='img-fluid' width='64' height='64'>";

                $status = $category->child_cat_status === 1 ? "<span class='badge text-bg-success'>Active</span>" : "<span class='badge text-bg-danger'>Disable</span>";
                // fetch trade status
                $return[] = [
                    'id' => $key + 1,
                    'icon' => $image,
                    'category' => @getCategoryName($category->category_id) ?? null,
                    'sub_cat_name' => @getSubCategoryName($category->subcategory_id) ?? null,
                    'child_cat_name' => $category->child_cat_name ?? null,
                    'status' => $status,
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
        return view('product_child_category.index');
    }
    public function createPage()
    {
        $data['categories'] = DB::table('product_categories')->where('status', 1)->get();
        $data['sub_categories'] = DB::table('product_sub_categories')->where('sub_status', 1)->get();
        return view('product_child_category.add', $data);
    }

    public function getSubCategory(Request $request)
    {
        try {
            $category_id = $request->category_id;
            $subCats = DB::table('product_sub_categories')->where(['category_id' => $category_id, 'sub_status' => 1])->get();

            $html = "<option value='' selected disabled>-- Select Sub Category --</option>";
            foreach ($subCats as $sub_cat) {
                $html .= "<option value='" . $sub_cat->id . "'>" . $sub_cat->sub_cat_name . "</option>";
            }
            $data = [
                "options" => $html
            ];
            return $this->successResponse($data, 'Sub Category Fetched', 200);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }

    public function getChildCategory(Request $request)
    {
        try {
            $category_id = $request->category_id;
            $sub_category_id = $request->sub_category_id;
            $childCats = ProductChildCategory::where(['category_id' => $category_id,"subcategory_id"=>$sub_category_id, 'child_cat_status' => 1])->get();
            $html = "<option value='' selected disabled>-- Select Child Category --</option>";
            foreach ($childCats as $child_cat) {
                $html .= "<option value='" . $child_cat->id . "'>" . $child_cat->child_cat_name . "</option>";
            }
            $data = [
                "options" => $html
            ];
            return $this->successResponse($data, 'Child Category Fetched', 200);
        } catch (Exception $e) {
            $this->errorResponse($e->getMessage());
        }
    }



    public function storeChildCategory(Request $request)
    {
        $request->validate([
            "name" => "required|string",
            "category_id" => "required",
            "sub_category_id" => "required",
            "status" => "required|boolean",
            "icon" => "nullable|mimes:png,jpg,jpeg,gif|max:500",
        ]);

        // dd($request->all());

        try {
            $icon = null;
            $path = "child_category_icon";
            if ($request->hasFile("icon")) {
                // if (!is_null($user->icon)) {
                //     $this->deleteImage($user->icon);
                // }
                $icon = $this->uploadImage($request->file('icon'), $path);
            }


            ProductChildCategory::create([
                "category_id" => $request->category_id,
                "subcategory_id" => $request->sub_category_id,
                "child_cat_name" => $request->name,
                "child_cat_status" => $request->status,
                "child_cat_icon" => $icon,
                "created_by" => Auth::user()->uuid,
            ]);

            return redirect()->route('child_cat_list')->with('success', ucfirst($request->name) . " successfully created.");
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function editChildCategory($id)
    {
        try {
            $data['child_category'] = ProductChildCategory::find($id);
            $data['categories'] = DB::table('product_categories')->where('status', 1)->get();
            $data['sub_categories'] = DB::table('product_sub_categories')->where(['category_id' => $data['child_category']->category_id, 'sub_status' => 1])->get();
            return view('product_child_category.edit', $data);
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function updateChildCategory(Request $request)
    {

        // dd($request->all());

        try {
            $request->validate([
                "child_cat_id" => "required|exists:product_child_categories,id",
                "sub_category_id" => "required",
                "category_id" => "required",
                "name" => "required|string",
                "status" => "required|boolean",
                "icon" => "nullable|mimes:png,jpg,jpeg,gif|max:500",
            ]);
            $category = ProductChildCategory::find($request->child_cat_id);

            $old_icon = null;
            if (!is_null($category->child_cat_icon)) {
                $old_icon = extactImageOldPath($category->child_cat_icon, "child_category_icon");
            }
            $icon = null;
            $path = "child_category_icon";
            if ($request->hasFile("icon")) {
                if (!is_null($category->child_cat_icon)) {
                    $this->deleteImage($old_icon);
                }
                $icon = $this->uploadImage($request->file('icon'), $path);
            }

            $category->update([
                "category_id" => $request->category_id,
                "subcategory_id" => $request->sub_category_id,
                "child_cat_name" => $request->name,
                "child_cat_status" => $request->status,
                "child_cat_icon" => $icon ?? $old_icon,
            ]);

            return redirect()->route('child_cat_list')->with('success', ucfirst($request->name) . " successfully updated.");
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function deleteChildCategory($id)
    {
        $category = ProductChildCategory::find($id);

        if (!is_null($category->child_cat_icon)) {
            $old_icon = extactImageOldPath($category->child_cat_icon, "child_category_icon");
            if (!is_null($old_icon)) {
                $this->deleteImage($old_icon);
            }
        }

        $category->delete();
        return redirect()->route('child_cat_list')->with('success', "Child Category successfully deleted.");
    }
}
