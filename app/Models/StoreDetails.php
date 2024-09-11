<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreDetails extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $connection = 'mysql'; // Main database connection
}
