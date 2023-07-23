<?php

namespace App\Admin\Actions\Order;

use App\Models\storeIntegral;
use App\Models\storeOrder;
use App\Models\storeOrderProduct;
use App\Models\storeOrderStatus;
use App\Models\userMasterLevel;
use App\Models\userNotice;
use Cisco\Aliyunsms\Facades\Aliyunsms;
use Encore\Admin\Actions\RowAction;
use Hanson\LaravelAdminWechat\Models\WechatUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class assignMaster extends RowAction
{
    public $name = '指定师傅';

    public function handle(Model $model,Request $request)
    {
        $orderid = $this->getKey();
        $master_id = $request->master_id;
        if(!$master_id)return $this->response()->error('参数错误');
        $orderinfo = storeOrder::where('id',$orderid)->whereIn('status',[5,8,9])->first();
        if(!$orderinfo)return $this->response()->error('该订单不能指定师傅');
        $userInfo = WechatUser::getUserById($master_id);
        if(!$userInfo)return $this->response()->error('师傅信息不存在');
        $level_amount = userMasterLevel::where('id',$userInfo['master_level'])->value('amount');
        $service_level_amount = 0;
        $service_policy_subsidy = 0;
        $service_base_wage = 0;
        $cart_info = [];
        $cartInfo = storeOrderProduct::getCartInfoList($orderid);
        foreach ($cartInfo as $key => $val){
            $cart_info[$key] = json_decode($val['cart_info'],true);
        }
        foreach ($cart_info as $pro){
            if($pro['productInfo']['type'] == 2){
                $service_base_wage += $pro['productInfo']['service_base_wage']*$pro['cart_num'];
                $service_level_amount += $level_amount*$pro['cart_num'];
                $service_policy_subsidy += $userInfo['policy_subsidy']*$pro['cart_num'];
            }
            if($pro['seviceInfo']){
                $service_base_wage += $pro['seviceInfo']['service_base_wage']*$pro['cart_num'];
                $service_level_amount += $level_amount*$pro['cart_num'];
                $service_policy_subsidy += $userInfo['policy_subsidy']*$pro['cart_num'];
            }
        }
        $data['master_id'] = $master_id;
        $data['master_level'] = $userInfo['master_level'];
        $data['level_amount'] = $service_level_amount;
        $data['base_wage'] = $service_base_wage;
        $data['policy_subsidy'] = $service_policy_subsidy;
        $data['status'] = 8;//已派单，服务中
        $data['give_time'] = date('Y-m-d H:i:s');
        $data['arrival_time'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        $res = storeOrder::where('id',$orderid)->update($data);
        if($res){
            $nickname = $userInfo['name'];
            /*用户端通知消息*/
            $title = '师傅已接单';
            $content = '您的订单已被师傅【'.$nickname.'】成功接单，请等待师傅联系上门服务 \n订单号:'.$orderinfo['order_id'].' \n订单金额:￥'.$orderinfo['pay_price'].' \n预约时间:'.$orderinfo['apointment_time'];
            userNotice::sendNotice($orderinfo['uid'],$orderid,$orderinfo['order_id'],$title,$content,1);
            /*发送短信*/
            $tel = $orderinfo['user_phone'];
            $templateCode = 'SMS_266745109';
            $TemplateParam = [
                "time"    => $orderinfo['apointment_time'],
            ];
            $result = $this->sendSms($tel,$templateCode,$TemplateParam);
            //Log::info('短信发送1'.json_encode($result));
            /*师傅端通知消息*/
            $title = '后台指定师傅服务';
            $content = '您已被指定为客户服务，请尽快联系客户上门服务 \n订单号:'.$orderinfo['order_id'].' \n预约时间:'.$orderinfo['apointment_time'];
            userNotice::sendNotice($master_id,$orderid,$orderinfo['order_id'],$title,$content,2);
            /*发送短信*/
            $master_info = WechatUser::getUserById($master_id);
            $tel1 = $master_info['mobile'];
            $templateCode1 = 'SMS_266730076';
            $TemplateParam1 = [
                "time"    => $orderinfo['apointment_time'],
            ];
            $result1 = $this->sendSms($tel1,$templateCode1,$TemplateParam1);
            //Log::info('短信发送2'.json_encode($result1));
            /*订单状态记录*/
            storeOrderStatus::status($orderid,'accept_order','后台指定师傅【'.$nickname.'】接单',$master_id);
            return $this->response()->success('指派师傅成功.')->refresh();
        }else{
            return $this->response()->error('请稍后重试');
        }
    }
    /*发送短信*/
    public function sendSms($tel, $templateCode,$templateParam){
        $SignName    = "壹盒饰家";          //模板签名
        $send = Aliyunsms::sendSms(strval($tel), $SignName, $templateCode, $templateParam);
        if ($send->Code == 'OK') {
            return true;
        } else {
            return $send->Message;
        }
    }
    public function form()
    {
        $data = $this->data();
        $masterList = WechatUser::getMasterList($data['operate_id'],$data['province'],$data['city']);
        $this->select('master_id', '师傅名称')->options($masterList)->rules('required')->default($data['master_id']);
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
            'operate_id' => $row->operate_id,
            'province' => $row->province,
            'city' => $row->city,
            'master_id' => $row->master_id,
        ];
    }
}
