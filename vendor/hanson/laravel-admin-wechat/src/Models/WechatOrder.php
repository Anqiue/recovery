<?php

namespace Hanson\LaravelAdminWechat\Models;

use Encore\Admin\Traits\DefaultDatetimeFormat;
use Illuminate\Database\Eloquent\Model;

class WechatOrder extends Model
{
    use DefaultDatetimeFormat;
    protected $guarded = [];
}
