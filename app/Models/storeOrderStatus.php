<?php

namespace App\Models;

use Encore\Admin\Traits\DefaultDatetimeFormat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class storeOrderStatus extends Model
{
    use SoftDeletes;
    use DefaultDatetimeFormat;
    protected $table = "store_order_status";
    protected $fillable = [];
    protected $guarded = [];
    protected $appends = [];
    protected $hidden = [];

    public function status($oid,$change_type,$change_message,$masterid=0)
    {
       $change_time = date('Y-m-d H:i:s');
        return self::insertGetId([
            'oid'=>$oid,
            'change_type'=>$change_type,
            'change_message'=>$change_message,
            'master_id'=>$masterid,
            'created_at'=>$change_time
        ]);
    }
}
