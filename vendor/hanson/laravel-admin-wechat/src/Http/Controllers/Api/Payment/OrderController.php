<?php

namespace Hanson\LaravelAdminWechat\Http\Controllers\Api\Payment;

use App\Http\Controllers\Api\BaseController;
use App\Models\storeCouponIssue;
use App\Models\storeCouponUser;
use App\Models\storeOrder;
use App\Models\storeOrderProduct;
use App\Models\storeOrderStatus;
use App\Models\userNotice;
use App\Jobs\UpdatePlantform;
use Carbon\Carbon;
use Hanson\LaravelAdminWechat\Events\OrderPaid;
use Hanson\LaravelAdminWechat\Facades\MerchantService;
use Hanson\LaravelAdminWechat\Models\WechatOrder;
use Hanson\LaravelAdminWechat\Models\WechatUser;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function paidNotify()
    {
        $app = MerchantService::getInstanceByMchId(request('mch_id'));

        $app->handlePaidNotify(function ($message, $fail) {
            $order = WechatOrder::query()->where([
                'mch_id' => request('mch_id'),
                'out_trade_no' => $message['out_trade_no'],
            ])->first();

            if (!$order || $order->paid_at) {
                return  true;
            }

            if ($message['return_code'] === 'SUCCESS') { // return_code 表示通信状态，不代表支付状态
                // 用户是否支付成功
                if ($message['result_code'] === 'SUCCESS') {
                    $order->update(['paid_at' => Carbon::parse($message['time_end'])->toDateTimeString()]);
                    if($order['link_order_id']) {
                        $orderInfo = storeOrder::where('id',$order['link_order_id'])->first();
                        if($order['pro_type'] == 2){//购买代金券
                            $status = 10;//已完成
                            $data = [];
                            $data['paid'] = 1;
                            $data['pay_time'] = Carbon::parse($message['time_end'])->toDateTimeString();
                            $data['status'] = $status;
                            $data['updated_at'] = date('Y-m-d H:i:s');
                            storeOrder::where('id',$order['link_order_id'])->update($data);//订单改为支付
                            /*发放优惠券*/
                            $cartInfo = storeOrderProduct::getCartInfoList($order['link_order_id']);
                            for($i=0; $i < $orderInfo['total_num']; $i++){
                                /*发布的优惠券剩余减1*/
                                foreach ($cartInfo as $key => $val){
                                    $info = json_decode($val['cart_info'],true);
                                    storeCouponIssue::updateCount($info['id']);
                                    storeCouponUser::createNewRow($orderInfo['uid'],$info['cid'],$info,4,$info['id'],$order['link_order_id']);
                                }
                            }
                            storeOrderStatus::status($order['link_order_id'],'pay_success','用户付款成功，优惠券发放完成');
                            /*消息通知*/
                            $title = '订单支付成功';
                            $content = '您已成功购买代金券，请去个人中心查看我的优惠券 \n订单号:'.$order['out_trade_no'].' \n支付金额:￥'.$orderInfo['pay_price'];
                            userNotice::sendNotice($order['wechat_user_id'],$order['link_order_id'],$order['out_trade_no'],$title,$content,1);
                        }else{
                            storeOrderStatus::status($order['link_order_id'], 'pay_success', '微信支付成功');
                            $status = 2;//待发货
                            /*如果产品仅为服务包，则订单状态为待服务*/
                            $onlyService = 1;
                            $cartInfo = storeOrderProduct::getCartInfoList($order['link_order_id']);
                            $cart_info = [];
                            foreach ($cartInfo as $key => $val){
                                $info = json_decode($val['cart_info'],true);
                                /*减库存 增加销量*/
                                BaseController::editStore($info['product_id'],-1,$info['cart_num'],$info['attr_id']);
                                $cart_info[$key] = $info;
                            }

                            foreach ($cart_info as $cart) {
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
                            $data['pay_time'] = Carbon::parse($message['time_end'])->toDateTimeString();
                            $data['status'] = $status;
                            $data['updated_at'] = date('Y-m-d H:i:s');
                            if($orderInfo['need_service'] == 1 && $status == 5) {
                                /* $master = WechatUser::autoGetMasterId($orderInfo['operate_id'], $orderInfo['province'], $orderInfo['city']);
                                 $data['give_id'] = $master['id'];
                                 $data['give_time'] = date('y-m-d H:i:s');
                                 $data['plantfrom'] = 2;//订单分配给师傅端*/
                                $data['give_id'] =0;
                                $data['plantfrom'] =1;
                            }
                            storeOrder::where('id',$order['link_order_id'])->update($data);
                            //dispatch(new UpdatePlantform($order['link_order_id'])); //师傅规定时间未接单，返回平台去分配
                            /*消息通知*/
                            $title = '订单支付成功';
                            $content = '您有一个新订单 \n订单号:'.$order['out_trade_no'].' \n支付金额:￥'.$orderInfo['pay_price'];
                            userNotice::sendNotice($order['wechat_user_id'],$order['link_order_id'],$order['out_trade_no'],$title,$content,1);
                        }
                    }
                   event(new OrderPaid($order));
                }
            } else {
                return $fail('通信失败，请稍后再通知我');
            }
        });
    }
}
