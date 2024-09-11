<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductSubCategory extends Model
{
    use HasFactory;
    protected $connection = 'mysql'; // Main database connection
    protected $guarded = ['id'];

    public function getSubIconAttribute($value)
    {
        if ($value) {
            return url('storage/app/public/' . $value);
        }

        return null;
    }

    public function getSubBannerImageAttribute($value)
    {
        if ($value) {
            return url('storage/app/public/' . $value);
        }

        return null;
    }

    public function category()
    {
        return $this->belongsTo(ProductCategory::class);
    }
}
