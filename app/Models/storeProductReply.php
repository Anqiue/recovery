<?php

namespace App\Models;

use Encore\Admin\Traits\DefaultDatetimeFormat;
use Hanson\LaravelAdminWechat\Models\WechatUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class storeProductReply extends Model
{
    use SoftDeletes;
    use DefaultDatetimeFormat;
    protected $table = "store_product_reply";
    protected $fillable = [];
    protected $guarded = [];
    protected $appends = [];
    protected $hidden = [];
    public function user()
    {
        return $this->belongsTo(WechatUser::class,'uid');
    }
    public function getRowByOid($oid){
        return self::where('oid',$oid)->select('id','comment','created_at')->first();
    }
}
