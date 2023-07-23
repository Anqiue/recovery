<?php

namespace App\Models;

use Encore\Admin\Traits\DefaultDatetimeFormat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class storeIntegralCategory extends Model
{
    use SoftDeletes;
    use DefaultDatetimeFormat;
    protected $table = "store_integral_category";
    protected $fillable = [];
    protected $guarded = [];
    protected $appends = [];
    protected $hidden = [];
}
