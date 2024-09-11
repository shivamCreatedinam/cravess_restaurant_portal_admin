<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductCategory extends Model
{
    use HasFactory;
    protected $connection = 'mysql'; // Main database connection
    protected $guarded = ['id'];

    public function getIconAttribute($value)
    {
        if ($value) {
            return url('storage/app/public/' . $value);
        }

        return null;
    }

    public function getBannerImageAttribute($value)
    {
        if ($value) {
            return url('storage/app/public/' . $value);
        }

        return null;
    }

    public function subCategory()
    {
        return $this->hasMany(ProductSubCategory::class);
    }
}
