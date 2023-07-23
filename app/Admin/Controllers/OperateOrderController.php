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
use App\Models\userBill;
use App\Models\userNotice;
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
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cookie;
use Encore\Admin\Grid\Displayers\Actions;
use Illuminate\Support\Facades\DB;

class OperateOrderController extends AdminController
{
    protected $title = "订单列表";

    protected function grid()
    {
        Cookie::queue('orders_page', null , -1);
        $page = \request('page');
        $page = isset($page)?$page:1;
        Cookie::queue('orders_page', $page, 60);
        $userModel = config('admin.database.users_model');
        $grid = new Grid(new storeOrder());
        $adminRow = Auth::guard('admin')->user();
        $grid->model()->where('operate_id', $adminRow['id']);
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
            //$filter->equal('operate_id', '运营中心管理员')->select($userModel::where('type',2)->pluck('name','id'));
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
        $ordertype = 2;//运营中心
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
}
