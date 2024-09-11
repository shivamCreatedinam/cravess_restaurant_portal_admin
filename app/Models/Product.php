<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = [];
    protected $connection = 'resto_mysql';
    protected $with = ['store', 'category', 'sub_category', 'sub_child_category'];

    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'category_id', 'id');
    }

    public function sub_category()
    {
        return $this->belongsTo(ProductSubCategory::class, 'sub_category_id', 'id');
    }

    public function sub_child_category()
    {
        return $this->belongsTo(ProductChildCategory::class, 'child_category_id', 'id');
    }
    public function store()
    {
        return $this->belongsTo(StoreDetails::class, 'restaurant_id', 'user_id');
    }
}
