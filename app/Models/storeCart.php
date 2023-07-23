<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class storeCart extends Model
{
    use SoftDeletes;
    protected $table = "store_cart";
    protected $fillable = [];
    protected $guarded = [];
    protected $appends = [];
    protected $hidden = [];

    /**
     * 加入购物车
     * @param $userid
     * @param $proid
     * @param int $cart_num
     * @param int $attrid
     * @param int $serviceId
     * @return mixed
     */
    public function setCart($userid,$proid,$cart_num = 1,$attrid = 0,$serviceId=0,$is_new=0,$type='cart'){
        if($cart_num < 1) $cart_num = 1;
        $row = self::where('uid',$userid)
            ->where('product_id',$proid)
            ->where('attr_id',$attrid)
            ->where('product_setvice_id',$serviceId)
            ->where('is_pay',0)
            ->where('is_new',$is_new)
            ->lockForUpdate()->first();
        if($row){
            if($type == 'cart'){
                $updata['cart_num'] = $row->cart_num + $cart_num;
            }else{
                $updata['cart_num'] = $cart_num;
            }
            $updata['updated_at'] = date('Y-m-d H:i:s');
            self::where('id',$row->id)->update($updata);
            return $row->id;
        }else{
            $data = [];
            $data['uid'] = $userid;
            $data['product_id'] = $proid;
            $data['attr_id'] = $attrid;
            $data['product_setvice_id'] = $serviceId;
            $data['cart_num'] = $cart_num;
            $data['is_new'] = $is_new;
            $data['created_at'] = date('Y-m-d H:i:s');
            return self::insertGetId($data);
        }
    }

    public function getUserProductCartList($userid,$cartIds='',$status=0){
        $productInfoField = ['id','cate_id','product_name','image','slider_image','price','ot_price','good_sku','postage','postage_num','postage_gradient','asso_service_id','service_day','service_base_wage','sales','stock','is_show','min_buy','max_buy','type'];
        $model = self::where('uid',$userid)->where('is_pay',0);
        if(!$cartIds) $model->where('is_new',0);
        if($cartIds) $model->whereIn('id',$cartIds);
        $list = $model->select('id','uid','product_id','attr_id','product_setvice_id','cart_num','is_pay','is_new')->get()->toArray();
        $valid = $invalid = [];
        if(!count($list)) return compact('valid','invalid');
        foreach ($list as $k=>$cart) {
            $product = storeProduct::getProById($cart['product_id'],$productInfoField);
            if(!empty($product)) $product = $product->toArray();
            $cart['productInfo'] = $product;
            //商品不存在
            if (!$product) {
                self::where('id', $cart['id'])->delete();
                //商品删除或无库存
            }else if (!$product['is_show']|| !$product['stock']) {
                $invalid[] = $cart;
                //商品属性不对应并且没有seckill_id
            }else{
                $cart['seviceInfo'] = [];
                if($cart['product_setvice_id']){
                    $cart['seviceInfo'] = storeProduct::getProById($cart['product_setvice_id'],$productInfoField);
                }
                $asso_service_id = $cart['productInfo']['asso_service_id'];
                if ($cart['attr_id']) {
                    $attrInfo = [];
                    $specname = '';
                    $goodsku = json_decode($product['good_sku'],true);
                    if($goodsku['type'] == 'many'){
                        foreach ($goodsku['sku'] as $val){
                            if($val['id'] == $cart['attr_id']) {
                                foreach ($goodsku['attrs'] as $akey=> $attr) {
                                    $specname .= $akey . ':' . $val[$akey] . ';';
                                }
                                $attrInfo = $val;
                                if(isset($val['asso_service_id']) && $val['asso_service_id']){
                                    $asso_service_id = $val['asso_service_id'];
                                }
                            }
                        }
                    }
                    $cart['productInfo']['asso_service_id'] = $asso_service_id;
                    if($specname)$attrInfo['specname'] = $specname;
                    //商品没有对应的属性
                    if (!$attrInfo || !$attrInfo['stock'])
                        $invalid[] = $cart;
                    else {
                        $cart['productInfo']['attrInfo'] = $attrInfo;
                        $cart['truePrice'] = (float)$attrInfo['price'];
                        $cart['trueStock'] = $attrInfo['stock'];
                        $valid[] = $cart;
                    }
                } else {
                    $cart['truePrice'] = (float)$cart['productInfo']['price'];
                    $cart['trueStock'] = $cart['productInfo']['stock'];
                    $valid[] = $cart;
                }
            }
        }
        foreach ($valid as $k=>$cart){
            if($cart['trueStock'] < $cart['cart_num']){
                $cart['cart_num'] = $cart['trueStock'];
                self::where('id',$cart['id'])->update(['cart_num'=>$cart['cart_num']]);
                $valid[$k] = $cart;
            }
        }
        return compact('valid','invalid');
    }
    /**
     * 修改数量
     * @param $cartid
     * @param $cartNum
     * @param $userid
     * @return mixed
     */
    public function changeUserCartNum($cartid,$cartNum,$userid){
        $updata['cart_num'] = $cartNum;
        $updata['updated_at'] = date('Y-m-d H:i:s');
        return self::where('uid',$userid)->where('id',$cartid)->update($updata);
    }

    /**
     * 删除
     * @param $cartid
     * @param $userid
     * @return mixed
     */
    public function removeUserCart($cartid,$userid){
        return self::where('uid',$userid)->where('id',$cartid)->delete();
    }
    /**
     * 清空
     * @param $userid
     * @return mixed
     */
    public function removeUserAllCart($userid){
        return self::where('uid',$userid)->delete();
    }

    /**
     * @param $cartid
     * @param $userid
     */
    public function addServiceforCart($cartid,$userid,$serviceId){
        $updata['product_setvice_id'] = $serviceId;
        $updata['updated_at'] = date('Y-m-d H:i:s');
        return self::where('uid',$userid)->where('id',$cartid)->update($updata);
    }
}
