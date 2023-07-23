<?php

namespace App\Admin\Actions\Order;

use App\Models\storeIntegral;
use App\Models\storeOrder;
use App\Models\storeOrderStatus;
use App\Models\userNotice;
use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class toShip extends RowAction
{
    public $name = '发货';

    public function handle(Model $model,Request $request)
    {
        $orderid = $this->getKey();
        $delivery_code = $request->delivery_code;
        $ship_no = $request->ship_no;
        if(!$delivery_code || !$ship_no)return $this->response()->error('参数错误');
        $orderinfo = storeOrder::where('id',$orderid)->where('status',2)->first();
        if(!$orderinfo)return $this->response()->error('该订单不需要发货');
        $adminRow = Auth::guard('admin')->user();
        $res = storeOrder::where('id',$orderid)->update([
            'status'=>3,
            'is_shipping'=>1,
            'delivery_code'=>$delivery_code,
            'ship_no'=>$ship_no,
            'shipping_time'=>date('Y-m-d H:i:s'),
            'updated_at'=>date('Y-m-d H:i:s'),
        ]);
        $shipname = storeIntegral::where('code',$delivery_code)->value('name');
        if($res){
            $title = '订单发货成功';
            $content = '您有订单已发货 \n订单号:'.$orderinfo->order_id.' \n支付金额:￥'.$orderinfo->pay_price.' \n配送物流:'.$shipname.' \n物流单号:'.$ship_no;
            userNotice::sendNotice($orderinfo->uid,$orderid,$orderinfo->order_id,$title,$content,1);
            storeOrderStatus::status($orderid,'order_ship','员工'.$adminRow->name.'系统后台操作发货',$adminRow->id);
            return $this->response()->success('发货成功.')->refresh();
        }else{
            return $this->response()->error('请稍后重试');
        }
    }

    public function form()
    {
        $data = $this->data();
        $shiplist = storeIntegral::where('status',1)->orderBy('sort','asc')->pluck('name','code');
        $this->select('delivery_code', '物流公司')->options($shiplist)->rules('required')->default($data['delivery_code']);
        $this->text('ship_no', '快递单号')->rules('required')->default($data['ship_no']);
    }
    /**
     * The data of the form.
     *
     * @return array
     */
    public function data()
    {
        $id = $this->getKey();
        $row = storeOrder::where('id',$id)->first();
        return [
            'delivery_code' => $row->delivery_code,
            'ship_no' =>$row->ship_no,
        ];
    }
}
