<?php

namespace App\Models;

use App\Http\Controllers\Api\BaseController;
use App\Http\Controllers\Api\NumberPrivacyController;
use App\Jobs\UpdatePlantform;
use Carbon\Carbon;
use Encore\Admin\Traits\DefaultDatetimeFormat;
use Hanson\LaravelAdminWechat\Models\WechatUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class storeOrder extends Model
{
    use SoftDeletes;
    use DefaultDatetimeFormat;
    protected $table = "store_order";
    protected $fillable = [];
    protected $guarded = [];
    protected $appends = [];
    protected $hidden = [];

    public function user()
    {
        return $this->belongsTo(WechatUser::class,'uid');
    }
    public static function getNewOrderId()
    {
        $totime = date("Y-m-d H:i:s",strtotime('+1 day'));
        //$count = (int) self::where('created_at','>=',date("Y-m-d H:i:s"))->where('created_at','<',$totime)->count();
        return 'wx'.date('YmdHis',time()).(microtime(true) % 1) * 1000 .mt_rand(0, 9999);
    }
    /*退款单号*/
    public function outReturnNo()
    {
        return 'R'.date('YmdHis').(microtime(true) % 1) * 1000 .mt_rand(0, 9999);
    }
    /*商户订单号，需保持唯一性(只能是字母或者数字，不能包含有符号)*/
    public function partnerTradeNo($uid)
    {
        return $uid.date('YmdHis').(microtime(true) % 1) * 1000 .mt_rand(0, 9999);
    }
    public function getUserOrderNumber($userid){
        $pending_payment = self::where('uid',$userid)->where('status',1)->count();
        $pending_shipping = self::where('uid',$userid)->where('status',2)->count();
        $pending_receipt = self::where('uid',$userid)->where('status',3)->count();
        $pending_service = self::where('uid',$userid)->where('status',5)->count();
        $pending_review = self::where('uid',$userid)->where('status',6)->count();
        return compact('pending_payment','pending_shipping','pending_receipt','pending_service','pending_review');
    }
    public function getMasterOrderNumber($userid){
        $processing = self::where('master_id',$userid)->where('status',8)->where('need_service',1)->count();
        $pending = self::where('master_id',$userid)->where('status',9)->where('need_service',1)->count();
        $completed = self::where('master_id',$userid)->whereIn('status',[6,10])->where('need_service',1)->count();
        $rejected = self::where('master_id','<>',$userid)->where('reject_master_id',$userid)->whereIn('status',[5,8,9,6,10])->where('need_service',1)->count();
        return compact('processing','pending','completed','rejected');
    }
    public function getMasterOrderCount($userid,$type=1){
        if($type == 1){//接单量
            $count = self::where('master_id',$userid)->whereIn('status',[8,9,6,10])->where('need_service',1)->count();
        }else{//拒单量
            $count = self::where('master_id','<>',$userid)->where('reject_master_id',$userid)->whereIn('status',[5,8,9,6,10])->where('need_service',1)->count();
        }
        return $count;
    }
    public function createOrder($orderNo, $userid,$addressRow,$cartInfo,$priceGroup,$payPrice,$payPostage,$couponid,$couponPrice,$apointment_time,$other=[]){
        try{
            $paid = 0;//未付款
            $cartIds = [];
            $totalNum = 0;
            foreach ($cartInfo as $cart){
                $cartIds[] = $cart['id'];
                $totalNum += $cart['cart_num'];
            }
            $cartIdstr = implode(',',$cartIds);
            $orderData = [
                'order_id' => $orderNo,
                'uid' => $userid,
                'real_name' => $addressRow->real_name,
                'user_phone' => $addressRow->phone,
                'user_address' => $addressRow->province_name.$addressRow->city_name.$addressRow->district_name,
                'province' => $addressRow->province,
                'city' => $addressRow->city,
                'district' => $addressRow->district,
                'addr_detail' => $addressRow->detail,
                'cart_id'=>$cartIdstr,
                'total_num'=>$totalNum,
                'total_price'=>$priceGroup['totalPrice'],
                'total_postage'=>$priceGroup['storePostage'],
                'coupon_id'=>$couponid,
                'coupon_price'=>$couponPrice,
                'pay_price'=>$payPrice,
                'pay_postage'=>$payPostage,
                'paid'=>$paid,
                'status'=>1,//待付款
                'pay_type'=>'wechat',
                'apointment_time'=>$apointment_time,
                'created_at' => date('Y-m-d H:i:s'),
            ];
            if($other){
                $orderData = $orderData+$other;
            }
            $orderId = self::insertGetId($orderData);
             if($orderId){
                 foreach ($cartInfo as $cart)
                 {
                    // BaseController::editStore($cart['product_id'],-1,$cart['cart_num'],$cart['attr_id']);
                     //购物车状态修改
                     storeCart::where('id',$cart['id'])->update(['is_pay'=>1,'updated_at'=>date('Y-m-d H:i:s')]);
                 }
                 //保存购物车商品信息
                 storeOrderProduct::setCartInfo($orderId,$cartInfo);
             }
            storeOrderStatus::status($orderId,'cache_key_create_order','订单生成');
             return $orderId;
        } catch (\Exception $exception) {
            $data = ["msg" => $exception->getMessage(), 'line' => $exception->getLine()];
            Log::error('Create',$data);
            return 0;
        }
    }
    public function createCouponOrder($orderNo, $userid,$cartInfo,$payPrice,$other=[]){
        try{
            $paid = 0;//未付款
            $orderData = [
                'order_id' => $orderNo,
                'uid' => $userid,
                'cart_id'=>0,
                'total_num'=>$cartInfo['total_num'],
                'total_price'=>$payPrice,
                'total_postage'=>0,
                'pay_price'=>$payPrice,
                'paid'=>$paid,
                'status'=>1,//待付款
                'pay_type'=>'wechat',
                'created_at' => date('Y-m-d H:i:s'),
            ];
            if($other){
                $orderData = $orderData+$other;
            }
            $orderId = self::insertGetId($orderData);
             if($orderId){
                 //保存购物车商品信息
                 storeOrderProduct::setCouponInfo($orderId,$cartInfo);
             }
            storeOrderStatus::status($orderId,'cache_key_create_order','代金券订单生成');
             return $orderId;
        } catch (\Exception $exception) {
            $data = ["msg" => $exception->getMessage(), 'line' => $exception->getLine()];
            Log::error('Create',$data);
            return 0;
        }
    }
    public function getOrderPriceGroup($cartInfo,$addrId){
        $totalPrice = self::getOrderSumPrice($cartInfo,'truePrice');//获取订单总金额
        $productPrice = self::getOrderProPrice($cartInfo,'truePrice');//获取订单产品总金额
        $servicePrice = self::getOrderServicePrice($cartInfo);//获取订单产品总金额
        $storePostage = self::getOrderPostagePrice($cartInfo,$addrId);//获取邮费
        return compact('storePostage','totalPrice','productPrice','servicePrice');
    }

    public static function getOrderSumPrice($cartInfo,$key='truePrice'){
        $SumPrice = 0;
        $ServicePrice = 0;
        foreach ($cartInfo as $cart){
            $SumPrice = bcadd($SumPrice,bcmul($cart['cart_num'],$cart[$key],2),2);
            if($cart['seviceInfo']){
                $ServicePrice = bcadd($ServicePrice,bcmul($cart['cart_num'],$cart['seviceInfo']['price'],2),2);
            }
        }
        return bcadd($SumPrice,$ServicePrice,2);
    }
    public static function getOrderProPrice($cartInfo,$key='truePrice'){
        $SumPrice = 0;
        foreach ($cartInfo as $cart){
            $SumPrice = bcadd($SumPrice,bcmul($cart['cart_num'],$cart[$key],2),2);
        }
        return $SumPrice;
    }

    public static function getOrderServicePrice($cartInfo){
        $ServicePrice = 0;
        foreach ($cartInfo as $cart){
            if($cart['seviceInfo']){
                $ServicePrice = bcadd($ServicePrice,bcmul($cart['cart_num'],$cart['seviceInfo']['price'],2),2);
            }
        }
        return $ServicePrice;
    }

    public static function getOrderPostagePrice($cartInfo,$addrId){
        $storePostage = 0;
        $address = [];
        if($addrId>0){
            $address = userAddress::where('id',$addrId)->first();
            foreach ($cartInfo as $cart){
                $proinfo = $cart['productInfo'];
                if($proinfo['postage'] > 0){//运费模板
                    $postagerow = storeExpressPostage::getRowByid($proinfo['postage']);
                    $postage_gradient = $proinfo['postage_gradient'];
                    if($postagerow){
                        $postage_fee = $postagerow['whole_postage'];
                        if($address && $postagerow['other_postage']){
                            foreach ($postagerow['other_postage'] as $poskey=>$posval){
                                if($posval['city_id'] == null){
                                    if ($posval['province_id'] == $address['province']){
                                        $postage_fee = $posval['postage_fee'];
                                        continue;
                                    }
                                }elseif($posval['province_id'] == $address['province'] && $posval['city_id'] == $address['city']){
                                    $postage_fee = $posval['postage_fee'];
                                    continue;
                                }
                            }
                        }
                        if($proinfo['postage_num'] == 2 && $postage_gradient){ //系数梯度
                            $numberarr = [];
                            foreach ($postage_gradient as $grakey=>$graval) {
                                $numberarr[$grakey] = $graval['buy_number'];
                            }
                            $granumber = self::NextNumberArray($cart['cart_num'],$numberarr);

                            foreach ($postage_gradient as $grakey=>$graval){
                                if($cart['cart_num'] >= $granumber && $granumber == $graval['buy_number']){
                                    if($cart['cart_num'] <= $graval['buy_number']){
                                        $ceil = ceil($cart['cart_num']/$graval['buy_number']);
                                    }else{
                                        $ceil =  floor($cart['cart_num']/$graval['buy_number']);
                                    }
                                    $postage_fee = $ceil*$graval['gradient']*$postage_fee;
                                    break;
                                }
                            }
                        }
                        $storePostage = bcadd($storePostage,$postage_fee,2);
                    }
                }
            }
        }
        return $storePostage;
    }

    /**
     * 订单列表
     * @param $userid
     * @param $type
     * @param int $limit
     * @return mixed
     */
    public function getMyOrderList($userid,$type,$limit=15,$keyword){
        $where = [];
        if($type == 1){//待付款
            $where['status'] = 1;
        }elseif ($type == 2){//待发货
            $where['status'] = 2;
        }elseif ($type == 3){//待收货
            $where['status'] = 3;
        }elseif ($type == 4){//待服务
            $where['status'] = 5;
        }elseif ($type == 5){//待评价
            $where['status'] = 6;
        }
        $field = ['id','order_id','uid','master_id','real_name','user_phone','user_address','addr_detail','total_num','total_price','total_postage','product_price','service_price','pay_price','pay_postage',
            'coupon_id','coupon_price','need_service','paid','pay_time','status','refund_status','refund_reason_time','refund_reason','refund_price',
            'is_shipping','shipping_time','delivery_code','ship_no','apointment_time','give_id','give_time','arrival_time','service_finished','service_finished_time','service_isverify','remark','pro_type',
            'master_level','level_amount','base_wage','policy_subsidy','created_at'];
        $list = self::where('uid',$userid)->where($where)
            ->when($keyword, function ($query) use ($keyword) {
                $query->where('order_id','like', '%' . $keyword . '%')->orWhere('user_phone',$keyword)->orWhere('real_name', 'like', '%' . $keyword . '%');
            })
            ->orderBy('created_at','desc')
            ->select($field)
            ->paginate($limit);
        $list->each(function ($item, $key) {
            $item = self::tidyOrder($item);
        });
        return $list;
    }
    /**
     * 接单列表
     * @param $userid
     * @param $type
     * @param int $limit
     * @return mixed
     */
    public function getAcceptOrderList($userid,$limit=15){
        $field = ['id','order_id','uid','master_id','real_name','user_phone','user_address','addr_detail','total_num','total_price','total_postage','product_price','service_price','pay_price','pay_postage',
            'coupon_id','coupon_price','need_service','paid','pay_time','status','refund_status','refund_reason_time','refund_reason','refund_price','pro_type',
            'is_shipping','shipping_time','delivery_code','ship_no','apointment_time','give_id','give_time','arrival_time','service_finished','service_finished_time','service_isverify','review_reason','review_time','remark','created_at'];
        $list = self::where('give_id',$userid)->where('status',5)->where('need_service',1)
            ->orderBy('created_at','desc')
            ->select($field)
            ->paginate($limit);
        $list->each(function ($item, $key)use($userid) {
            $item = self::masterTidyOrder($item,$userid);
        });
        return $list;
    }
    /**
     * 订单详情
     * @param $orderid
     * @return mixed
     */
    public function getMasterOrderInfoById($orderid,$userid){
        $field = ['id','order_id','uid','master_id','real_name','user_phone','user_address','addr_detail','total_num','total_price','total_postage','product_price','service_price','pay_price','pay_postage',
            'coupon_id','coupon_price','need_service','paid','pay_time','status','refund_status','refund_reason_time','refund_reason','refund_price','pro_type',
            'is_shipping','shipping_time','delivery_code','ship_no','apointment_time','give_id','give_time','arrival_time','service_finished','service_finished_time','service_isverify','review_reason','review_time','remark',
            'master_level','level_amount','base_wage','policy_subsidy','reject_master_id','reject_master_level','reject_level_amount','reject_base_wage','reject_policy_subsidy','reject_time','created_at'];
        $row = self::where('id',$orderid)
            ->select($field)
            ->first();
        if($row){
            $row = self::masterTidyOrder($row,$userid);
        }
        return $row;
    }
    /**
     * 师傅端--我的订单
     * @param $userid
     * @param $type
     * @param int $limit
     * @return mixed
     */
    public function getMasterOrderList($userid,$type=0,$limit=15,$keyword){
        $field = ['id','order_id','uid','master_id','real_name','user_phone','user_address','addr_detail','total_num','total_price','total_postage','product_price','service_price','pay_price','pay_postage',
            'coupon_id','coupon_price','need_service','paid','pay_time','status','refund_status','refund_reason_time','refund_reason','refund_price','pro_type',
            'is_shipping','shipping_time','delivery_code','ship_no','apointment_time','give_id','give_time','arrival_time','service_finished','service_finished_time','service_isverify','review_reason','review_time','remark',
            'master_level','level_amount','base_wage','policy_subsidy','reject_master_id','reject_master_level','reject_level_amount','reject_base_wage','reject_policy_subsidy','reject_time','created_at'];
        if($type == 0){
            /*$list = self::where(function($query)use($userid,$keyword) {
                $query->where('master_id',$userid)->whereIn('status',[8,9,6,10])->where('need_service',1)
                    ->when($keyword, function ($query) use ($keyword) {
                        $query->where('order_id','like', '%' . $keyword . '%')->orWhere('user_phone',$keyword)->orWhere('real_name', 'like', '%' . $keyword . '%');
                    });
            })->orwhere(function($query)use($userid,$keyword){
                $query->where('master_id','<>',$userid)->where('reject_master_id',$userid)->whereIn('status',[5,8,9,6,10])->where('need_service',1)
                    ->when($keyword, function ($query) use ($keyword) {
                        $query->where('order_id','like', '%' . $keyword . '%')->orWhere('user_phone',$keyword)->orWhere('real_name', 'like', '%' . $keyword . '%');
                    });
            })->select($field)->paginate($limit);*/
            $list = self::where('master_id', $userid)->whereIn('status',[8,9,6,10])->where('need_service', 1)
                ->when($keyword, function ($query) use ($keyword) {
                    $query->where('order_id','like', '%' . $keyword . '%')->orWhere('user_phone',$keyword)->orWhere('real_name', 'like', '%' . $keyword . '%');
                })
                ->orderBy('created_at', 'desc')
                ->select($field)
                ->paginate($limit);
        }elseif($type == 1 || $type == 2){
            $where = [];
            if($type == 1){//进行中
                $where = [
                    ['status', '=', '8'],
                ];
            }elseif ($type == 2){//待审核
                $where = [
                    ['status', '=', '9'],
                ];
            }
            $list = self::where('master_id', $userid)->where($where)->where('need_service', 1)
                ->when($keyword, function ($query) use ($keyword) {
                    $query->where('order_id','like', '%' . $keyword . '%')->orWhere('user_phone',$keyword)->orWhere('real_name', 'like', '%' . $keyword . '%');
                })
                ->orderBy('created_at', 'desc')
                ->select($field)
                ->paginate($limit);
        }elseif ($type == 3){ //已完成
            $list = self::where('master_id',$userid)->whereIn('status',[6,10])->where('need_service',1)
                ->when($keyword, function ($query) use ($keyword) {
                    $query->where('order_id','like', '%' . $keyword . '%')->orWhere('user_phone',$keyword)->orWhere('real_name', 'like', '%' . $keyword . '%');
                })
                ->orderBy('created_at','desc')
                ->select($field)
                ->paginate($limit);
        }elseif ($type == 4){ //拒单
            $list = self::where('master_id','<>',$userid)->where('reject_master_id',$userid)->whereIn('status',[5,8,9,6,10])->where('need_service',1)
                ->when($keyword, function ($query) use ($keyword) {
                    $query->where('order_id','like', '%' . $keyword . '%')->orWhere('user_phone',$keyword)->orWhere('real_name', 'like', '%' . $keyword . '%');
                })
                ->orderBy('created_at','desc')
                ->select($field)
                ->paginate($limit);
        }
        $list->each(function ($item, $key)use($userid) {
            $item = self::masterTidyOrder($item,$userid);
        });
        return $list;
    }

    /**
     * 订单详情
     * @param $orderid
     * @return mixed
     */
    public function getOrderInfoById($orderid){
        $field = ['id','order_id','uid','master_id','real_name','user_phone','user_address','addr_detail','total_num','total_price','total_postage','product_price','service_price','pay_price','pay_postage',
            'coupon_id','coupon_price','need_service','paid','pay_time','status','refund_status','refund_reason_time','refund_reason','refund_price','pro_type',
            'is_shipping','shipping_time','delivery_code','ship_no','apointment_time','give_id','give_time','arrival_time','service_finished','service_finished_time','service_isverify','remark',
            'master_level','level_amount','base_wage','policy_subsidy','created_at'];
        $row = self::where('id',$orderid)
            ->select($field)
            ->first();
        if($row){
            $row = self::tidyOrder($row);
        }
        return $row;
    }

    public static function masterTidyOrder($order,$userid)
    {
        /*状态*/
        switch ($order['status']) {
            case 5:
                $order['status_title'] = '待服务';
                break;
            case 6:
                $order['status_title'] = '待评价';
                break;
            case 7:
                $order['status_title'] = '已退款';
                break;
            case 8:
                $order['status_title'] = '进行中';
                if($order['service_isverify'] == 2){
                    $order['status_title'] = '审核不通过';
                }
                break;
            case 9:
                $order['status_title'] = '待审核';
                /*if($order['service_isverify'] == 2){
                    $order['status_title'] = '审核不通过';
                }*/
                break;
            case 10:
                $order['status_title'] = '已完成';
                break;
        }
        if($order['master_id'] != $userid && $order['reject_master_id'] == $userid){
            $order['status_title'] = '已拒单';
            $leveltotal = bcadd($order['reject_base_wage'],$order['reject_level_amount'],2);
            $order['reject_total_service_pay'] = bcadd($leveltotal,$order['reject_policy_subsidy'],2);;
        }
        /*产品信息*/
        $order['cart_info'] = [];
        $cart_info = [];
        $cartInfo = storeOrderProduct::getCartInfoList($order['id']);
        foreach ($cartInfo as $key => $val){
            $cart_info[$key] = json_decode($val['cart_info'],true);
        }
        /*规定服务时间*/
        $service_day = 0;
        $service_base_wage = 0;
        foreach ($cart_info as $pro){
            if($pro['productInfo']['type'] == 2){
                $service_day += $pro['productInfo']['service_day'];
                $service_base_wage += $pro['productInfo']['service_base_wage'];
            }
            if($pro['seviceInfo']){
                $service_day += $pro['seviceInfo']['service_day'];
                $service_base_wage += $pro['seviceInfo']['service_base_wage'];
            }
        }
        $order['service_set_time'] = $service_day;
        $order['service_base_wage'] = $service_base_wage;
        $order['cart_info'] = $cart_info;
        $order['coupon_title'] = '';
       /* $order['coupon_price'] = 0;
        $order['use_min_price'] = 0;*/
        /*优惠券*/
        /*if($order['coupon_id'] > 0){
            $couponInfo = storeCouponUser::where('id',$order['coupon_id'])->first();
            $order['coupon_title'] = $couponInfo['coupon_title'];
            $order['coupon_price'] = $couponInfo['coupon_price'];
            $order['use_min_price'] = $couponInfo['use_min_price'];
        }*/
        /*服务费*/
        $total_service_pay = 0;
        if($order['status'] == 5 && $order['give_id'] > 0){
            $giveUserInfo = WechatUser::getUserById($order['give_id']);
            $give_level_amount = userMasterLevel::where('id',$giveUserInfo['master_level'])->value('amount');
            //$other = $giveUserInfo['base_wage'] + $giveUserInfo['policy_subsidy'];
            $other = $service_base_wage + $giveUserInfo['policy_subsidy'];
            $total_service_pay = bcadd($give_level_amount,$other,2);
        }
        if($order['master_id'] > 0){
            //$total_service_pay = $order['level_amount'] + $order['base_wage'] + $order['policy_subsidy'];
            $other = $order['base_wage'] + $order['policy_subsidy'];
            $total_service_pay = bcadd($order['level_amount'],$other,2);
        }
        $order['total_service_pay'] = $total_service_pay;
        $finish = [];
        /*完成服务*/
        if($order['service_finished']){
            $service_finished = json_decode($order['service_finished'],true);
            $finish['site_photo'] = explode(',',$service_finished['site_photo']);
            $finish['customer_photo'] = explode(',',$service_finished['customer_photo']);
        }
        $order['service_finished'] = $finish;
        /*评价*/
        $order['order_review'] = [];
        if($order['need_service'] = 1 && $order['status'] == 10){//已评价
            $review = storeProductReply::getRowByOid($order['id']);
            if($review){
                $review['comment'] = json_decode($review['comment'],true);
                $order['order_review'] = $review['comment'];
            }
        }
        /*隐私号码*/
        $masterUserInfo = WechatUser::getUserById($userid);
        $masterPhone = $masterUserInfo['mobile'];
        $userphone = $order['user_phone'];
        $privacy = Cache::get('privacy_'.$userphone);
        $subsId = Cache::get('privacy_subsId_'.$userphone);
        $newNumberPrivacy = new NumberPrivacyController();
        /*查询号码的绑定关系*/
        if($privacy && $subsId){
            $relation = $newNumberPrivacy->querySubscriptionDetail($subsId,$privacy);
            Log::error('relation:'.json_encode($relation));
            if($relation['Code'] == 'OK' && $relation['Message'] == 'OK'){
                $order['user_phone'] = $privacy;
            }else{
                $order['user_phone'] = self::updatePhoneNoX($masterPhone,$userphone,$subsId,$privacy);
            }
        }else{
            $order['user_phone'] = self::updatePhoneNoX($masterPhone,$userphone);
        }
        return $order;
    }

    public function updatePhoneNoX($masterPhone,$userphone,$subsId='',$privacy=''){
        Log::error('$masterPhone'.$masterPhone);
        Log::error('$userphone'.$userphone);
        $newNumberPrivacy = new NumberPrivacyController();
        $time =  Carbon::tomorrow()->toDateTimeString();
        $groupId = '2000027920101';
        $phoneNoA = $masterPhone;
        $expiration = $time;
        $phoneNoB = $userphone;
        /*绑定AXG关系*/
        $result = $newNumberPrivacy->bindAxg($groupId,$phoneNoA,$expiration,$phoneNoB);
        Log::error('绑定AXG关系:'.json_encode($result));
        if($result['Code'] == 'OK' && $result['Message'] == 'OK'){
            $SecretNo = $result['SecretBindDTO']['SecretNo'];
            $SubsId = $result['SecretBindDTO']['SubsId'];
            Cache::put('privacy_'.$userphone,$SecretNo);
            Cache::put('privacy_subsId_'.$userphone,$SubsId);
            return $SecretNo;
        }else if ($result['Code'] == 'isv.NO_AVAILABLE_NUMBER' || $result['Code'] == 'isv.ILLEGAL_ARGUMENT'){
            /*删除绑定关系*/
            if($subsId && $privacy){
                $newNumberPrivacy->UnbindSubscription($groupId,$userphone);
            }
            /*修改G号码组*/
            $update = $newNumberPrivacy->operateAxgGroup($groupId,$userphone);
            Log::error('修改G号码组:'.json_encode($update));
            if($update['Code'] == 'OK' && $update['Message'] == 'OK'){
                $result = $newNumberPrivacy->bindAxg($groupId,$phoneNoA,$expiration,$phoneNoB);
                Log::info('$result'.json_encode($result));
                if($result['Code'] == 'OK' && $result['Message'] == 'OK'){
                    $SecretNo = $result['SecretBindDTO']['SecretNo'];
                    $SubsId = $result['SecretBindDTO']['SubsId'];
                    Cache::put('privacy_'.$userphone,$SecretNo);
                    Cache::put('privacy_subsId_'.$userphone,$SubsId);
                    return $SecretNo;
                }
            }
        }
        return $userphone;
    }
    public static function tidyOrder($order)
    {
        /*状态*/
        switch ($order['status']) {
            case 0:
                $order['status_title'] = '已取消';
                break;
            case 1:
                $order['status_title'] = '待付款';
                break;
            case 2:
                $order['status_title'] = '待发货';
                break;
            case 3:
                $order['status_title'] = '待收货';
                break;
            case 5:
                $order['status_title'] = '待服务';
                break;
            case 6:
                $order['status_title'] = '待评价';
                break;
            case 7:
                $order['status_title'] = '已退款';
                break;
            case 8:
                $order['status_title'] = '服务中';
                break;
            case 9:
                $order['status_title'] = '待审核';
                break;
            case 10:
                $order['status_title'] = '已完成';
                break;
        }
        /*产品信息*/
        $need_service= $order['need_service'];
        $order['cart_info'] = [];
        $cart_info = [];
        $cartInfo = storeOrderProduct::getCartInfoList($order['id']);
        foreach ($cartInfo as $key => $val){
            $cart_info[$key] = json_decode($val['cart_info'],true);
        }
        $order['cart_info'] = $cart_info;
        $order['coupon_title'] = '';
       /* $order['coupon_price'] = 0;
        $order['use_min_price'] = 0;*/
        /*优惠券*/
        if($order['coupon_id']){
            /*$couponInfo = storeCouponUser::where('id',$order['coupon_id'])->first();
            $order['coupon_title'] = $couponInfo['coupon_title'];
            $order['coupon_price'] = $couponInfo['coupon_price'];
            $order['use_min_price'] = $couponInfo['use_min_price'];*/
        }
        /*物流信息*/
        $order['ship_company'] = '';
        if($order['is_shipping'] == 1){
            $order['ship_company'] = storeIntegral::where('code',$order['delivery_code'])->value('name');
        }
        /*待服务 派单师傅信息*/
        if($order['give_id'] > 0){
            $master = WechatUser::getUserById($order['give_id']);
            $order['give_master_avatar'] = $master['avatar'];
            $order['give_master_name'] = $master['name'];
        }
        /*服务信息*/
        if($order['master_id'] > 0){
            $master = WechatUser::getUserById($order['master_id']);
            $order['master_avatar'] = $master['avatar'];
            $order['master_name'] = $master['name'];
            $order['master_level_name'] = userMasterLevel::where('id',$order['master_level'])->value('level_title');
            $totalpay =  $order['base_wage'] + $order['policy_subsidy'];
            $order['master_total_service'] = bcadd($order['level_amount'],$totalpay,2);
        }
        /*完成服务*/
        $finish = [];
        /*完成服务*/
        if($order['service_finished']){
            $service_finished = json_decode($order['service_finished'],true);
            $finish['site_photo'] = array_values(explode(',',$service_finished['site_photo']));
            if($service_finished['customer_photo'] !== null)$finish['customer_photo'] = array_values(explode(',',$service_finished['customer_photo']));
        }
        $order['service_finished'] = $finish;
        /*评价*/
        $order['order_review'] = [];
        if($order['need_service'] = 1 && $order['status'] == 10){//已评价
            $review = storeProductReply::getRowByOid($order['id']);
            if($review){
                $review['comment'] = json_decode($review['comment'],true);
                $order['order_review'] = $review['comment'];
            }
        }
        $order['need_service'] = $need_service;
        return $order;
    }
    //返回数组中的下一个相关数字
    public static function NextNumberArray($Number, $NumberRangeArray){
        $w = 0;
        $c = -1;
        $abstand = 0;
        $l = count($NumberRangeArray);
        for($pos=0; $pos < $l; $pos++){
            $n = $NumberRangeArray[$pos];
            $abstand = ($n < $Number) ? $Number - $n : $n - $Number;
            if ($c == -1){
                $c = $abstand;
                continue;
            }
            else if ($abstand < $c){
                $c = $abstand;
                $w = $pos;
            }
        }
        return $NumberRangeArray[$w];
    }

    /**
     * 微信支付 为 0元时
     * @param $order_id
     * @param $uid
     * @return bool
     */
    public static function jsPayPrice($order_id,$cartInfo){
        $res = self::paySuccess($order_id,$cartInfo);//支付为0时
        return $res;
    }
    /**
     * //TODO 支付成功后
     * @param $orderId
     * @param $paytype
     * @param $notify
     * @return bool
     */
    public static function paySuccess($orderId,$cartInfo)
    {
        $orderInfo = storeOrder::where('id',$orderId)->first();
        if(isset($cartInfo['pro_type']) && $cartInfo['pro_type'] == 2){//代金券产品
            $status = 10;//已完成
            $data = [];
            $data['paid'] = 1;
            $data['pay_time'] = date('Y-m-d H:i:s');
            $data['status'] = $status;
            $data['updated_at'] = date('Y-m-d H:i:s');
            $res = self::where('id',$orderId)->update($data);//订单改为支付
            /*发放优惠券*/
            for($i=0; $i < $cartInfo['total_num']; $i++){
                /*发布的优惠券剩余减1*/
                storeCouponIssue::updateCount($cartInfo['id']);
                storeCouponUser::createNewRow($orderInfo->uid,$cartInfo['cid'],$cartInfo,4,$cartInfo['id'],$orderId);
            }
            storeOrderStatus::status($orderId,'pay_success','用户付款成功，优惠券发放完成');
            /*消息通知*/
            $title = '订单支付成功';
            $content = '您已成功购买代金券，请去个人中心查看我的优惠券 \n订单号:'.$orderInfo->order_id.' \n支付金额:￥'.$orderInfo->pay_price;
            userNotice::sendNotice($orderInfo->uid,$orderId,$orderInfo->order_id,$title,$content,1);
        }else{
            $status = 2;//待发货
            /*减库存 增加销量*/
            foreach ($cartInfo as $cart)
            {
                BaseController::editStore($cart['product_id'],-1,$cart['cart_num'],$cart['attr_id']);
            }
            /*如果产品仅为服务包，则订单状态为待服务*/
            $onlyService = 1;
            foreach ($cartInfo as $cart) {
                if($cart['productInfo']['type'] == 1){//是否存在普通商品
                    $onlyService = 0;
                    break;
                }
            }
            if($onlyService == 1){
                $status = 5;
            }
            $data = [];
            $data['paid'] = 1;
            $data['pay_time'] = date('Y-m-d H:i:s');
            $data['status'] = $status;
            $data['updated_at'] = date('Y-m-d H:i:s');
            if($orderInfo['need_service'] == 1 && $status == 5) {
                /* $master = WechatUser::autoGetMasterId($orderInfo['oprate_id'], $orderInfo['province'], $orderInfo['city']);
                 $data['give_id'] = $master['id'];
                 $data['give_time'] = date('y-m-d H:i:s');
                 $data['plantfrom'] = 2;*///订单分配给师傅端
                $data['give_id'] = 0;
                $data['plantfrom'] = 1;//订单分配给平台
            }
            $res = self::where('id',$orderId)->update($data);//订单改为支付
            //dispatch(new UpdatePlantform($orderId)); //师傅规定时间未接单，返回平台去分配
            storeOrderStatus::status($orderId,'pay_success','用户付款成功');
            /*消息通知*/
            $orderInfo = storeOrder::where('id',$orderId)->first();
            $title = '订单支付成功';
            $content = '您有一个新订单 \n订单号:'.$orderInfo->order_id.' \n支付金额:￥'.$orderInfo->pay_price;
            userNotice::sendNotice($orderInfo->uid,$orderId,$orderInfo->order_id,$title,$content,1);
        }
        return $res;
    }
    public static function cacheOrderInfo($uid,$cartInfo, $priceGroup,$other = [],$cacheTime = 3600)
    {
        $key = md5(time());
        Cache::put('user_order_'.$uid.$key,compact('cartInfo','priceGroup','other'),$cacheTime);
        return $key;
    }
    public static function getCacheOrderInfo($uid,$key)
    {
        $cacheName = 'user_order_'.$uid.$key;
        if(!Cache::has($cacheName)) return null;
        return Cache::get($cacheName);
    }
    public static function clearCacheOrderInfo($uid,$key)
    {
        Cache::forget('user_order_'.$uid.$key);
    }
    /**
     * 统计
     * @param $mer_id
     * @param $start_time
     * @param $endday
     * @param string $type
     * @return mixed
     */
    public function getOrderTotal($operate_id,$start_time,$endday,$type='sum'){
        $where = [];
        if($operate_id > 0){
            $where['operate_id'] = $operate_id;
        }
        if($type == 'willship'){
            $model = self::where($where)->where('paid',1)->where('status',2);
        }else if($type == 'ship'){
            $model = self::where($where)->where('paid',1)->where('status',2);
        }else if($type == 'reviewed'){
            $model = self::where($where)->where('paid',1)->where('status',9);
        }else if($type == 'totalreviewed'){
            $model = self::where($where)->where('paid',1)->where('status',9);
        }else{
            $model = self::where($where)->where('paid',1)->where('status','<>',7);
        }
        switch ($type){
            case 'willship':
                return $model->whereDate('created_at','>=',$start_time)->whereDate('created_at','<=',$endday)->count();
            case 'ship':
                return $model->count();
            case 'reviewed':
                return $model->whereDate('created_at','>=',$start_time)->whereDate('created_at','<=',$endday)->count();
            case 'totalreviewed':
                return $model->count();
            case 'sum':
                return $model->whereDate('created_at','>=',$start_time)->whereDate('created_at','<=',$endday)->sum('pay_price');
            case 'sumtoday':
                return $model->whereDate('created_at',$endday)->sum('pay_price');
                break;
            case 'counttoday':
                $list = $model->whereDate('created_at',$endday)->select('id','uid')->groupBy('uid')->get()->toArray();
                return count($list);
                break;
            default:
                $list = $model->whereDate('created_at','>=',$start_time)->whereDate('created_at','<=',$endday)->select('id','uid')->groupBy('uid')->get()->toArray();
                return count($list);
        }
    }
    /**
     * 统计
     * @param $mer_id
     * @param $start_time
     * @param $endday
     * @param string $type
     * @return mixed
     */
    public function getOrderStatusTotal($status,$operate_id,$start_time,$endday,$type='sum'){
        $where = [];
        if($operate_id > 0){
            $where['operate_id'] = $operate_id;
        }
        if($status != 11){
            $where['status'] = $status;
        }
        switch ($type){
            case 'sum':
                return self::where($where)->whereDate('created_at','>=',$start_time)->whereDate('created_at','<=',$endday)->sum('pay_price');
            case 'count':
                return self::where($where)->whereDate('created_at','>=',$start_time)->whereDate('created_at','<=',$endday)->count();
            case 'total_num':
                return self::where($where)->whereDate('created_at','>=',$start_time)->whereDate('created_at','<=',$endday)->sum('total_num');
            case 'coupon_price':
                return self::where($where)->whereDate('created_at','>=',$start_time)->whereDate('created_at','<=',$endday)->sum('coupon_price');
            case 'sumtoday':
            return self::where($where)->whereDate('created_at',$endday)->sum('pay_price');
            break;
            case 'counttoday':
                $list = self::where($where)->whereDate('created_at',$endday)->select('id','uid')->groupBy('uid')->get()->toArray();
                return count($list);
                break;
            default:
                $list = self::where($where)->whereDate('created_at','>=',$start_time)->whereDate('created_at','<=',$endday)->select('id','uid')->groupBy('uid')->get()->toArray();
                return count($list);
        }
    }
}
