<?php

namespace App\Models;

use Encore\Admin\Traits\DefaultDatetimeFormat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class userBank extends Model
{
    use SoftDeletes;
    use DefaultDatetimeFormat;
    protected $table = "user_bank";
    protected $fillable = [];
    protected $guarded = [];
    protected $appends = [];
    protected $hidden = [];
}
