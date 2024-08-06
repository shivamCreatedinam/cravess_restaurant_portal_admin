<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreMedia extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function getStoreImageAttribute($value)
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

}
