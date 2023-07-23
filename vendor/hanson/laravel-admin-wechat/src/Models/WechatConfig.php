<?php


namespace Hanson\LaravelAdminWechat\Models;


use Encore\Admin\Traits\DefaultDatetimeFormat;
use Illuminate\Database\Eloquent\Model;

class WechatConfig extends Model
{
    use DefaultDatetimeFormat;
    protected $guarded = [];

    protected $appends = ['type_readable'];

    public function getTypeReadableAttribute()
    {
        return [1 => '公众号', 2 => '小程序'][$this->attributes['type']];
    }
}
