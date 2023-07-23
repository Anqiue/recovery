<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Order\auditService;
use App\Admin\Actions\Order\assignMaster;
use App\Admin\Actions\Order\toShip;
use App\Admin\Actions\Product\storeOrderDelete;
use App\Http\Controllers\Api\BaseController;
use App\Models\storeCouponIssue;
use App\Models\storeCouponUser;
use App\Models\storeIntegral;
use App\Models\storeOrder;
use App\Models\storeOrderProduct;
use App\Models\storeOrderStatus;
use App\Models\systemConfig;
use App\Models\userBill;
use App\Models\userNotice;
use App\Services\ExpressQuery;
use Encore\Admin\Layout\Content;
use Encore\Admin\Widgets\Table;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Hanson\LaravelAdminWechat\Models\WechatMerchant;
use Hanson\LaravelAdminWechat\Models\WechatUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cookie;
use Encore\Admin\Grid\Displayers\Actions;
use Illuminate\Support\Facades\DB;

class StoreCouponOrderController extends AdminController
{
    use ExpressQuery;
    protected $title = "订单列表";

    protected function grid()
    {
        Cookie::queue('orders_page', null , -1);
        $page = \request('page');
        $page = isset($page)?$page:1;
        Cookie::queue('orders_page', $page, 60);
        $userModel = config('admin.database.users_model');
        $grid = new Grid(new storeOrder());
        $grid->model()->where('pro_type', 2);
        $grid->model()->orderBy("id", "desc");
        //$grid->disableExport();
        $grid->filter(function($filter)use($userModel){
            // 去掉默认的id过滤器
           // $filter->disableIdFilter();
            $filter->equal('status', '订单状态')->radio([
                ''   => '全部',
                0    => '已取消',
                1    => '待付款',
                7    => '已退款',
                10   => '已完成',
            ]);
            $filter->equal('order_id', '订单号');
            $filter->like('real_name', '下单姓名');
            $filter->equal('user_phone', '下单手机号');
            $filter->equal('uid', '会员ID');
            $filter->between('created_at', '下单时间')->datetime();
        });
        $grid->column("id", "ID");
        $grid->column('order_id', '订单号')->modal('订单产品', function ($model) {
            $oid = $model->id;
            $storeProuct = storeOrderProduct::where('oid',$oid)->get();
            if($storeProuct){
                $storeProuct->each(function ($item, $key) {
                    $cart_info = json_decode($item['cart_info'],true);
                    $item['product_id'] = $cart_info['id'];
                    $item['product_name'] = $cart_info['title'];
                    $item['price'] = $cart_info['price'];
                    $item['cart_num'] = $cart_info['total_num'];
                });
            }
            $storeProuct = $storeProuct->map(function ($storeProuct) {
                return $storeProuct->only(['product_id','product_name','price','cart_num']);
            });
            return new Table(['ID', '产品名','价格','数量'], $storeProuct->toArray());
        });
        $grid->column("user.nickname", "用户")->display(function ($username){
            return $username.'['.$this->uid.']';
        });
        $grid->column("user.mobile", "手机号");
       // $grid->column("real_name", "姓名");
        //$grid->column("user_phone", "电话");
        $grid->column("total_num", "总数")->sortable();
        $grid->column("use_num", "已使用数")->display(function (){
            $count = storeCouponUser::where('uid',$this->uid)->where('oid',$this->id)->where('status',1)->count();
            return $count;
        });
        $grid->column("pay_price", "支付金额")->sortable();
        $grid->column("status", "订单状态")->using(['0'=>'已取消','1'=>'待付款','2'=>'待发货','3'=>'待收货','5'=>'待服务','6'=>'待评价','7'=>'已退款','8'=>'服务进行中','9'=>'待审核','10'=>'已完成'])->label([
            0 => 'danger',
            1 => 'warning',
            2 => 'info',
            3 => 'success',
            5 => 'success',
            6 => 'primary',
            7 => 'default',
            8 => 'default',
            9 => 'warning',
            10 => 'success',
        ]);;
        $grid->column("created_at", "创建时间")->sortable();
        $grid->disableCreateButton();
        $grid->batchActions(function ($batch) {
            $batch->disableDelete();
            $batch->add(new storeOrderDelete());
        });
        $grid->actions(function ($actions) {
            // 去掉删除
            $actions->disableDelete();
            // 去掉编辑
            $actions->disableEdit();
            // 去掉查看
            // $actions->disableView();
        });
        //$grid->setActionClass(Actions::class);
        return $grid;
    }

    protected function detail($id)
    {
        $show = new Show(storeOrder::findOrFail($id));
        $show->field("id", "编号");
        $show->field("order_id", "订单号");
        $show->field("uid", "用户id");
        return $show;
    }

    public function show($id, Content $content)
    {
        $page = Cookie::get('orders_page');
        $order = storeOrder::findOrFail($id);
        $order = storeOrder::tidyOrder($order);
        $user = WechatUser::getUserById($order['uid']);
        $ordertype = 3;//优惠券
        $oprate = [];
        $opratelist = storeOrderStatus::where('oid',$id)->get()->toArray();
        foreach ($opratelist as $key=> $val){
            switch ($val['change_type']){
                default:
                    $oprate_title = $val['change_message'];
                    $oprate_time = $val['created_at'];
            }
            $oprate[$key]['oprate_title'] = $oprate_title;
            $oprate[$key]['oprate_time'] = $oprate_time;
        }
        $today = date('Y-m-d H:i:s');
        //$refund_number = $order['total_num'] - $order['refund_number'];
        $refund_number = storeCouponUser::where('oid',$id)->whereIn('status',[0,2])->count();
        $usenumber = storeCouponUser::where('oid',$id)->where('status',1)->count();
        return $content->title('订单详情')
            ->description('显示')
            ->view('admin.order.coupon-order-detail', compact('ordertype','order','refund_number','usenumber','user','oprate','page'));
    }
    /**
     * 退款
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function toReduce(Request $request){
        $orderid = $request->orderid;
        $number = $request->number;
        if(!$orderid || !$number || !is_numeric($number))return response()->json(['code'=>'0','msg'=>'参数错误']);
        $orderinfo = storeOrder::where('id',$orderid)->where('status',10)->first();
        if(!$orderinfo)return response()->json(['code'=>'0','msg'=>'该订单不能退款']);
        $refund_number = $orderinfo['total_num'] - $orderinfo['refund_number'];
        if($number >$refund_number)return response()->json(['code'=>'0','msg'=>'退款数不能大于'.$refund_number]);
        $adminRow = Auth::guard('admin')->user();
        DB::beginTransaction();
        $date = date('Y-m-d H:i:s');
        $cartInfo = storeOrderProduct::getCartInfoList($orderid);
        foreach ($cartInfo as $key => $val){
            $info = json_decode($val['cart_info'],true);
            $price = $info['price']; //单价
        }
        $refundprice = bcmul($price,$number,2);
        if($orderinfo['pay_price'] > 0){
            $mchId = WechatMerchant::where('id',1)->value('mch_id');
            $app = \Hanson\LaravelAdminWechat\Facades\MerchantService::getInstanceByMchId($mchId);
            $out_return_no = storeOrder::outReturnNo();//系统内部退款单号
            $result = $app->refund->byOutTradeNumber($orderinfo['order_id'], $out_return_no, $orderinfo['pay_price']*100, $refundprice*100, [
                // 可在此处传入其他参数，详细参数见微信支付文档
                'refund_desc' => '订单退款',
            ]);
            if($result['return_code'] == 'SUCCESS'){
                /*修改订单状态*/
                $real_refund_number =  $orderinfo['refund_number'] + $number;
                $status = 2;
                $updata = [];
                if($real_refund_number < $orderinfo['total_num']) {
                    $status = 3;//部分退款
                }elseif ($real_refund_number == $orderinfo['total_num']){
                    $updata['status'] = 7;//已退款
                }
                $realrefundprice = bcmul($price,$real_refund_number,2);
                $updata['refund_status'] = $status;//已退款
                $updata['refund_number'] = $real_refund_number;
                $updata['refund_reason'] = '代金券退款';
                $updata['refund_reason_time'] = date('Y-m-d H:i:s');
                $updata['refund_price'] = $realrefundprice;
                $updata['updated_at'] = date('Y-m-d H:i:s');
                $res = storeOrder::where('id',$orderinfo['id'])->update($updata);


                $cartInfo = storeOrderProduct::getCartInfoList($orderid);
                /*库存增加 优惠券回退*/
                for($i=0; $i < $number; $i++){
                    foreach ($cartInfo as $key => $val){
                        $info = json_decode($val['cart_info'],true);
                        storeCouponIssue::updateCount($info['id'],2);
                        $coupon =  storeCouponUser::where('uid',$orderinfo['uid'])->where('oid',$orderid)->where('cid',$info['cid'])->whereIn('status',[0,2])->orderBy('id','desc')->first();
                        if($coupon)storeCouponUser::where('id',$coupon['id'])->update(['status'=>3,'updated_at'=>date('Y-m-d H:i:s')]);
                    }
                }
                /*消息发送*/
                $title = '订单退款成功';
                $content = '您的订单已成功退款 \n订单号:'.$orderinfo['order_id'].' \n退款金额:￥'.$orderinfo['pay_price'].' \n退款时间:'.date('Y-m-d H:i:s');
                userNotice::sendNotice($orderinfo['uid'],$orderid,$orderinfo['order_id'],$title,$content,1);
                storeOrderStatus::status($orderid,'order_refund','员工'.$adminRow->name.'系统后台操作退款成功'.',退款数量：'.$number.',退款金额：￥'.$refundprice,$adminRow->id);
                DB::commit();
                return response()->json(['code'=>'1','msg'=>'success']);
            }else{
                DB::rollBack();
                return response()->json(['code'=>'0','msg'=>'退款失败'.$result['return_msg']]);
            }
        }
    }
}
