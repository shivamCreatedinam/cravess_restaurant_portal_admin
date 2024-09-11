<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductChildCategory extends Model
{
    use HasFactory;
    protected $connection = 'mysql'; // Main database connection
    protected $guarded = [];

    public function getChildCatIconAttribute($value)
    {
        if ($value) {
            return url('storage/app/public/' . $value);
        }
        return null;
        // return url('public/assets/img/dummy.png');
    }
}
