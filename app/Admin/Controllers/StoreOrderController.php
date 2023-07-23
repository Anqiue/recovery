<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Order\auditService;
use App\Admin\Actions\Order\assignMaster;
use App\Admin\Actions\Order\toShip;
use App\Admin\Actions\Product\storeOrderDelete;
use App\Http\Controllers\Api\BaseController;
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

class StoreOrderController extends AdminController
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
        $grid->model()->where('pro_type', 1);
        $grid->model()->orderBy("id", "desc");
        //$grid->disableExport();
        $grid->filter(function($filter)use($userModel){
            // 去掉默认的id过滤器
           // $filter->disableIdFilter();
            $filter->equal('status', '订单状态')->radio([
                ''   => '全部',
                0    => '已取消',
                1    => '待付款',
                2    => '待发货',
                3    => '待收货',
                5    => '待服务',
                6    => '待评价',
                7    => '已退款',
                8    => '服务进行中',
                9    => '待审核',
                10   => '已完成',
            ]);
            $filter->equal('order_id', '订单号');
            $filter->like('real_name', '下单姓名');
            $filter->equal('user_phone', '下单手机号');
            $filter->equal('uid', '会员ID');
            $filter->equal('operate_id', '运营中心管理员')->select($userModel::where('type',2)->pluck('name','id'));
            $filter->between('created_at', '下单时间')->datetime();
        });
        $grid->column("id", "ID");
        $grid->column('order_id', '订单号')->modal('订单产品', function ($model) {
            $oid = $model->id;
            $storeProuct = storeOrderProduct::where('oid',$oid)->get();
            if($storeProuct){
                $storeProuct->each(function ($item, $key) {
                    $cart_info = json_decode($item['cart_info'],true);
                    $item['product_name'] = $cart_info['productInfo']['product_name'];
                    $item['image'] = $cart_info['productInfo']['image'];
                    $item['spec'] = '';
                    if($cart_info['attr_id'] > 0){
                        $item['spec'] = $cart_info['productInfo']['attrInfo']['specname'];
                    }
                    $item['price'] = $cart_info['truePrice'];
                    $item['stock'] = $cart_info['trueStock'];
                    $item['service'] = '';
                    if($cart_info['product_setvice_id'] > 0){
                        $item['service'] = $cart_info['seviceInfo']['product_name'].'['.$cart_info['seviceInfo']['id'].']'.'- ￥'.$cart_info['seviceInfo']['price'];
                    }
                    $item['cart_num'] = $cart_info['cart_num'];
                });
            }
            $storeProuct = $storeProuct->map(function ($storeProuct) {
                return $storeProuct->only(['product_id','product_name', 'spec','price','stock','service','cart_num']);
            });
            return new Table(['ID', '产品名','属性','价格','库存','服务','数量'], $storeProuct->toArray());
        });
        $grid->column("user.nickname", "用户")->display(function ($username){
            return $username.'['.$this->uid.']';
        });
        $grid->column("operate_id", "运营中心")->display(function ($opid)use($userModel){
            $name = '';
            if($opid > 0){
                $name = $userModel::where('id',$opid)->value('name');
            }
            return $name;
        });
        $grid->column("real_name", "姓名");
        $grid->column("user_phone", "电话");
        $grid->column("total_num", "总数")->sortable();
        $grid->column("total_price", "商品总价")->sortable();
        $grid->column("total_postage", "邮费")->sortable();
        $grid->column("pay_price", "实际支付金额")->sortable();
        $grid->column("coupon_price", "优惠券")->sortable();
        $grid->column("service_master", "服务师傅")->display(function (){
            $text = '无需服务';
            if($this->need_service == 1){
                if($this->master_id > 0){
                    $text = WechatUser::getFieldsById($this->master_id);
                }elseif ($this->give_id > 0){
                    $text = '待接单:'.WechatUser::getFieldsById($this->give_id);
                }elseif ($this->status == 5){
                    $text = '待指定';
                }else{
                    $text = '';
                }
            }
            return $text;
        });
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
            if ($actions->row->status == 2 || $actions->row->status == 3){
                $actions->add(new toShip());
            }
            if (($actions->row->status == 5 && $actions->row->master_id == 0 && $actions->row->give_id == 0 && $actions->row->plantfrom == 1) || $actions->row->status == 8|| $actions->row->status == 9){
                $actions->add(new assignMaster());
            }
            if ($actions->row->status == 9){
                $actions->add(new auditService());
            }
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
        $ordertype = 1;//平台
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
        /*快递公司*/
        $shiplist = storeIntegral::where('status',1)->orderBy('sort','asc')->get()->toArray();
        $shiplist = count($shiplist)?$shiplist:[];
        return $content->title('订单详情')
            ->description('显示')
            ->view('admin.order.order-detail', compact('ordertype','order','user','shiplist','oprate','page'));
    }

    /**
     * 发货
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function toShip(Request $request){
        $orderid = $request->orderid;
        $ship_code = $request->ship_code;
        $ship_no = $request->ship_no;
        $type = $request->type;
        if(!$orderid || !$ship_code || !$ship_no)return response()->json(['code'=>'0','msg'=>'参数错误']);
        $status = 2;
        if($type == 2)$status = 3;
        $orderinfo = storeOrder::where('id',$orderid)->where('status',$status)->first();
        if(!$orderinfo)return response()->json(['code'=>'0','msg'=>'该订单不需要发货']);
        $adminRow = Auth::guard('admin')->user();
        $res = storeOrder::where('id',$orderid)->update([
            'status'=>3,
            'is_shipping'=>1,
            'delivery_code'=>$ship_code,
            'ship_no'=>$ship_no,
            'shipping_time'=>date('Y-m-d H:i:s'),
            'updated_at'=>date('Y-m-d H:i:s'),
        ]);
        $shipname = storeIntegral::where('code',$ship_code)->value('name');
        if($res){
            $title = '订单发货成功';
            $content = '您有订单已发货 \n订单号:'.$orderinfo->order_id.' \n支付金额:￥'.$orderinfo->pay_price.' \n配送物流:'.$shipname.' \n物流单号:'.$ship_no;
            userNotice::sendNotice($orderinfo->uid,$orderid,$orderinfo->order_id,$title,$content,1);
            storeOrderStatus::status($orderid,'order_ship','员工'.$adminRow->name.'系统后台操作发货',$adminRow->id);
            return response()->json(['code'=>'1','msg'=>'success']);
        }else{
            return response()->json(['code'=>'0','msg'=>'请稍后重试']);
        }
    }
    /**
     * 退款
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function toReduce(Request $request){
        $orderid = $request->orderid;
        $reason = $request->remark;
        if(!$orderid || !$reason)return response()->json(['code'=>'0','msg'=>'参数错误']);
        $orderinfo = storeOrder::where('id',$orderid)->where('status',2)->first();
        if(!$orderinfo)return response()->json(['code'=>'0','msg'=>'该订单不能退款']);
        $adminRow = Auth::guard('admin')->user();
        DB::beginTransaction();
        $date = date('Y-m-d H:i:s');
        if($orderinfo['pay_price'] > 0){
            $mchId = WechatMerchant::where('id',1)->value('mch_id');
            $app = \Hanson\LaravelAdminWechat\Facades\MerchantService::getInstanceByMchId($mchId);
            $out_return_no = storeOrder::outReturnNo();//系统内部退款单号
            $result = $app->refund->byOutTradeNumber($orderinfo['order_id'], $out_return_no, $orderinfo['pay_price']*100, $orderinfo['pay_price']*100, [
                // 可在此处传入其他参数，详细参数见微信支付文档
                'refund_desc' => '订单退款',
            ]);
            if($result['return_code'] == 'SUCCESS'){
                /*修改订单状态*/
                $updata = [];
                $updata['status'] = 7;//已退款
                $updata['refund_status'] = 2;//已退款
                $updata['refund_reason'] = $reason;
                $updata['refund_reason_time'] = date('Y-m-d H:i:s');
                $updata['refund_price'] = $orderinfo['pay_price'];
                $updata['updated_at'] = date('Y-m-d H:i:s');
                $res = storeOrder::where('id',$orderinfo['id'])->update($updata);
                /*库存增加*/
                $cartInfo = storeOrderProduct::getCartInfoList($orderinfo['id']);
                foreach ($cartInfo as $key => $val){
                    $info = json_decode($val['cart_info'],true);
                    BaseController::editStore($info['product_id'],1,$info['cart_num'],$info['attr_id']);
                }
                /*优惠券回退*/
                if($orderinfo['coupon_id']){
                    $arr = explode(',',$orderinfo['coupon_id']);
                    foreach ($arr as $couponId){
                        DB::table('store_coupon_user')->where('id',$couponId)->where('status',1)->update(['status'=>0,'updated_at'=>date('Y-m-d H:i:s')]);
                    }
                }
                /*消息发送*/
                $title = '订单退款成功';
                $content = '您的订单已成功退款 \n订单号:'.$orderinfo['order_id'].' \n退款金额:￥'.$orderinfo['pay_price'].' \n退款时间:'.date('Y-m-d H:i:s');
                userNotice::sendNotice($orderinfo['uid'],$orderid,$orderinfo['order_id'],$title,$content,1);
                storeOrderStatus::status($orderid,'order_refund','员工'.$adminRow->name.'系统后台操作退款成功',$adminRow->id);
                DB::commit();
                return response()->json(['code'=>'1','msg'=>'success']);
            }else{
                DB::rollBack();
                return response()->json(['code'=>'0','msg'=>'退款失败'.$result['return_msg']]);
            }
        }
    }
    /**
     * 服务审核
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function serviceAudit(Request $request){
        $orderid = $request->orderid;
        $type = $request->type?:1;
        $reason = $request->reason?:'';
        if(!$orderid || !$type)return response()->json(['code'=>'0','msg'=>'参数错误']);
        $orderinfo = storeOrder::where('id',$orderid)->where('status',9)->first();
        if(!$orderinfo)return response()->json(['code'=>'0','msg'=>'该订单无需审核']);
        $adminRow = Auth::guard('admin')->user();
        $date = date('Y-m-d H:i:s');
        $update = [];
        if($type == 1) {
            /*审核通过 待评价*/
            $update['status'] = 6;
        }else{
            /*审核不通过 重新上传图片*/
            $update['status'] = 8;
        }
        $update['service_isverify'] = $type;
        $update['review_reason'] = $reason;
        $update['review_adminid'] = $adminRow->id;
        $update['review_time'] = $date;
        $update['updated_at'] = $date;
        $res = storeOrder::where('id',$orderid)->update($update);
        if($res){
            $totalpay =  $orderinfo['base_wage'] + $orderinfo['policy_subsidy'];
            $master_total_service = bcadd($orderinfo['level_amount'],$totalpay,2);
            if($type == 1) {//通过
                /*给师傅结算服务费用*/
                userBill::insertRow(1,'服务费收入',$orderinfo['master_id'],'service_income','income',$master_total_service,$orderid,'服务审核完成，发放服务费');
                /*用户端通知消息*/
                $title = '师傅服务完成通知';
                $content = '您的订单已由师傅完成服务，请对师傅服务进行评价。 \n订单号:' . $orderinfo['order_id'] . ' \n订单金额:￥' . $orderinfo['pay_price'] . ' \n服务完成时间:' . $orderinfo['service_finished_time'];
                userNotice::sendNotice($orderinfo['uid'], $orderid, $orderinfo['order_id'], $title, $content, 1);
                /*师傅端通知消息*/
                $title = '服务完成审核通过';
                $content = '您已完成此订单客户上门服务 \n订单号:' . $orderinfo['order_id'] . ' \n审核时间:' . $date;
                userNotice::sendNotice($orderinfo['master_id'], $orderid, $orderinfo['order_id'], $title, $content, 2);
                /*订单状态记录*/
                storeOrderStatus::status($orderid, 'sure_service_audit', '服务审核通过', $adminRow->id);
            }else{//不通过
                /*师傅端通知消息*/
                $title = '服务完成审核不通过';
                $content = '您的服务完成审核不通过，不通过原因：'.$reason.' \n请尽快重新提交审核。' .' \n订单号:' . $orderinfo['order_id'] . ' \n审核时间:' . $date;
                userNotice::sendNotice($orderinfo['master_id'], $orderid, $orderinfo['order_id'], $title, $content, 2);
                /*订单状态记录*/
                storeOrderStatus::status($orderid, 'reduce_service_audit', '服务审核不通过:'.$reason, $adminRow->id);
            }
            return response()->json(['code'=>'1','msg'=>'success']);
        }else{
            return response()->json(['code'=>'0','msg'=>'审核失败']);
        }
    }
    /**
     * 查看物流
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getExpress(Request $request){
        $orderid = $request->orderid;
        if(!$orderid)return response()->json(['code'=>'0','msg'=>'参数错误']);
        $orderinfo = storeOrder::where('id',$orderid)->where('is_shipping',1)->first();
        if(!$orderinfo)return response()->json(['code'=>'0','msg'=>'该订单不存在快递单号']);
        $cacheName = $orderinfo['order_id'].$orderinfo['ship_no'];
        $result = Cache::get($cacheName);
        if($result === null || 1==1) {
            $app_code = systemConfig::where('id',1)->value('system_express_app_code');
            $result = $this->setNo($orderinfo['ship_no'])->setAppCode($app_code)->query();
            if (is_array($result) &&
                isset($result['result']) &&
                isset($result['result']['deliverystatus']) &&
                $result['result']['deliverystatus'] >= 3)
                $cacheTime = 0;
            else
                $cacheTime = 1800;
            if ($cacheTime > 0) {
                Cache::put($cacheName, $result, $cacheTime);
            } else {
                Cache::put($cacheName, $result);
            }
        }
        return response()->json(['code'=>'1','msg'=>'success','express'=>$result]);
    }
}
