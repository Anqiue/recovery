<?php

namespace App\Models;

use Encore\Admin\Traits\DefaultDatetimeFormat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class storeCoupon extends Model
{
    use SoftDeletes;
    use DefaultDatetimeFormat;
    protected $table = "store_coupon";
    protected $fillable = [];
    protected $guarded = [];
    protected $appends = [];
    protected $hidden = [];

    public function coupon_allow(): BelongsToMany
    {
        $pivotTable = 'store_coupon_allow';

        return $this->belongsToMany(storeProduct::class,$pivotTable,'coupon_id','product_id');
    }

    public function getRowById($id){
        return self::where('id',$id)->where('status',1)->first();
    }
}
