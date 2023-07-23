<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class storeOrderProduct extends Model
{
    use SoftDeletes;
    protected $table = "store_order_product";
    protected $fillable = [];
    protected $guarded = [];
    protected $appends = [];
    protected $hidden = [];

    public static function setCartInfo($oid,array $cartInfo)
    {
        foreach ($cartInfo as $cart){
            $group = [];
            $group['oid'] = $oid;
            $group['cart_id'] = $cart['id'];
            $group['product_id'] = $cart['productInfo']['id'];
            $group['product_setvice_id'] = $cart['product_setvice_id'];
            $group['number'] = $cart['cart_num'];
            $group['cart_info'] = json_encode($cart);
            self::insert($group);
        }
        return true;
    }
    public static function setCouponInfo($oid,$cartInfo)
    {
        $group = [];
        $group['oid'] = $oid;
        $group['cart_id'] = 0;
        $group['product_id'] = $cartInfo['id'];
        $group['pro_type'] = 2;
        $group['product_setvice_id'] = 0;
        $group['number'] = $cartInfo['total_num'];
        $group['cart_info'] = json_encode($cartInfo);
        return self::insert($group);
    }

    public function getCartInfoList($oid){
        return self::where('oid',$oid)->select('id','product_id','number','product_setvice_id','cart_info')->get()->toArray();
    }
}
