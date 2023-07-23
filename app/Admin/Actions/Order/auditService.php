<?php

namespace App\Admin\Actions\Order;

use App\Models\storeOrder;
use App\Models\storeOrderStatus;
use App\Models\userBill;
use App\Models\userNotice;
use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class auditService extends RowAction
{
    public $name = '审核服务';

    public function handle(Model $model)
    {
        $id = $this->getKey();
        $type = 1;
        $reason = '';
        $orderinfo = storeOrder::where('id',$id)->where('status',9)->first();
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
        $res = storeOrder::where('id',$id)->update($update);
        if($res){
            $totalpay =  $orderinfo['base_wage'] + $orderinfo['policy_subsidy'];
            $master_total_service = bcadd($orderinfo['level_amount'],$totalpay,2);
            //$master_total_service = bcmul($total_service_pay,$orderinfo['total_num'],2);
            if($type == 1) {//通过
                /*给师傅结算服务费用*/
                userBill::insertRow(1,'服务费收入',$orderinfo['master_id'],'service_income','income',$master_total_service,$id,'服务审核完成，发放服务费');
                /*用户端通知消息*/
                $title = '师傅服务完成通知';
                $content = '您的订单已由师傅完成服务，请对师傅服务进行评价。 \n订单号:' . $orderinfo['order_id'] . ' \n订单金额:￥' . $orderinfo['pay_price'] . ' \n服务完成时间:' . $orderinfo['service_finished_time'];
                userNotice::sendNotice($orderinfo['uid'], $id, $orderinfo['order_id'], $title, $content, 1);
                /*师傅端通知消息*/
                $title = '服务完成审核通过';
                $content = '您已完成此订单客户上门服务 \n订单号:' . $orderinfo['order_id'] . ' \n审核时间:' . $date;
                userNotice::sendNotice($orderinfo['master_id'], $id, $orderinfo['order_id'], $title, $content, 2);
                /*订单状态记录*/
                storeOrderStatus::status($id, 'sure_service_audit', '服务审核通过', $adminRow->id);
            }else{//不通过
                /*师傅端通知消息*/
                $title = '服务完成审核不通过';
                $content = '您的服务完成审核不通过，不通过原因：'.$reason.' \n请尽快重新提交审核。' .' \n订单号:' . $orderinfo['order_id'] . ' \n订单金额:￥' . $orderinfo['pay_price'] . ' \n服务费用:￥' . $master_total_service . ' \n审核时间:' . $date;
                userNotice::sendNotice($orderinfo['master_id'], $id, $orderinfo['order_id'], $title, $content, 2);
                /*订单状态记录*/
                storeOrderStatus::status($id, 'reduce_service_audit', '服务审核不通过:'.$reason, $adminRow->id);
            }
            return $this->response()->success('审核成功.')->refresh();
        }else{
            return $this->response()->error('审核失败,请稍后重试！')->refresh();
        }
    }
   /* public function form()
    {
        $this->radio('status', '状态')->options(['1'=>'通过','2'=>'不通过'])->default(1);
        $this->textarea('reason','理由');
    }*/
    public function dialog()
    {
        $this->confirm('确定服务审核通过吗？');
    }
}
