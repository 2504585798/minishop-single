<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    public $table = "images";
    protected $guarded = [];
    public $timestamps = false;
}