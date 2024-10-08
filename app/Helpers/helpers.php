<?php

use App\Models\ProductChildCategory;
use Illuminate\Support\Facades\DB;

if (!function_exists("generateOTP")) {
    function generateOTP($digit = 6)
    {
        if ($digit < 1) {
            throw new InvalidArgumentException('The number of digits must be at least 1');
        }

        $otp = '';
        for ($i = 0; $i < $digit; $i++) {
            $otp .= mt_rand(0, 9);
        }

        return $otp;
    }
}

if (!function_exists('getNameLetters')) {
    function getNameLetters($name)
    {
        $initials = preg_replace_callback('/\b\w/u', function ($matches) {
            return strtoupper($matches[0]);
        }, $name);
        return str_replace(' ', '', $initials);
    }
}

if (!function_exists("generateRestaurantId")) {
    function generateRestaurantId($storeName, $storeMobileNo)
    {
        // Prefix
        $prefix = 'RESTO';

        // Combine store name and mobile number
        $combined = $storeName . $storeMobileNo;

        // Generate a hash of the combined string
        $hash = strtoupper(substr(md5($combined), 0, 10));

        // Generate the unique ID
        $uniqueId = $prefix . $hash;

        // Ensure the ID is 15 characters long
        if (strlen($uniqueId) < 15) {
            $uniqueId = str_pad($uniqueId, 15, '0');
        } elseif (strlen($uniqueId) > 15) {
            $uniqueId = substr($uniqueId, 0, 15);
        }

        return $uniqueId;
    }
}


function getCategoryName($category_id)
{
    $category = DB::table('product_categories')->where('id', $category_id)->first('name');
    return $category->name;
}
function getSubCategoryName($sub_category_id)
{
    $category = DB::table('product_sub_categories')->where('id', $sub_category_id)->first('sub_cat_name');
    return $category->sub_cat_name;
}
 function getChildCategoryName($child_category_id)
{
    $category = ProductChildCategory::find($child_category_id);
    return $category->child_cat_name;
}

function extactImageOldPath(string $full_path, string $folder)
{
    return substr(parse_url($full_path, PHP_URL_PATH), strpos(parse_url($full_path, PHP_URL_PATH), $folder));
}
