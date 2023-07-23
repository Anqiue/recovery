<?php

namespace App\Admin\Actions\Master;

use App\Models\storeOrder;
use App\Models\userBill;
use App\Models\userExtract;
use App\Models\userNotice;
use Encore\Admin\Actions\RowAction;
use Hanson\LaravelAdminWechat\Models\WechatMerchant;
use Hanson\LaravelAdminWechat\Models\WechatUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class withdrawalAudit extends RowAction
{
    public $name = '提现审核';

    public function handle(Model $model, Request $request)
    {
        $id = $this->getKey();
        $row = userExtract::where('id',$id)->first();
        $adminRow = Auth::guard('admin')->user();
        if(!$row) return $this->response()->error('提现申请不存在！');
        $status = $request->status;
        $reason = $request->reason?:'';
        if($status == '-1'){//不通过
            $data['adminid'] = $adminRow->id;
            $data['status'] = $status;
            $data['fail_msg'] = $reason;
            $data['fail_time'] = date('Y-m-d H:i:s');
            $data['updated_at'] = date('Y-m-d H:i:s');
            $res = userExtract::where('id',$id)->update($data);
        }else{//通过
            $res = 0;
            $userInfo = WechatUser::getUserById($row['uid']);
            $mchId = WechatMerchant::where('id',1)->value('mch_id');
            $app = \Hanson\LaravelAdminWechat\Facades\MerchantService::getInstanceByMchId($mchId);
            $partnerTradeNo = storeOrder::partnerTradeNo($row['uid']);//系统内部退款单号
            $result = $app->transfer->toBalance([
                'partner_trade_no' => $partnerTradeNo, // 商户订单号，需保持唯一性(只能是字母或者数字，不能包含有符号)
                'openid' => $userInfo['openid'],
                'check_name' => 'NO_CHECK', // NO_CHECK：不校验真实姓名, FORCE_CHECK：强校验真实姓名
                're_user_name' => $userInfo['name'], // 如果 check_name 设置为FORCE_CHECK，则必填用户真实姓名
                'amount' => $row['extract_price']*100, // 企业付款金额，单位为分
                'desc' => '提现', // 企业付款操作说明信息。必填
            ]);
            Log::info('提现：'.json_encode($result));
            if($result['return_code'] == 'SUCCESS' && $result['result_code'] == 'SUCCESS'){
                /*提现状态修改*/
                $data['adminid'] = $adminRow->id;
                $data['status'] = $status;
                $data['updated_at'] = date('Y-m-d H:i:s');
                $res = userExtract::where('id',$id)->update($data);
                /*账单记录*/
                userBill::insertRow(0,'提现',$row['uid'],'withdrawal_success','ithdrawal',$row['extract_price'],0,'提现成功');
            }
        }
        if($res){
            if($status == '-1'){
                /*师傅端通知消息*/
                $title = '你的提现申请被拒绝';
                $content = '您好，你的提现申请被拒绝,拒绝理由：'.$reason.' \n提现金额:'.$row['apointment_time'];
            }else{
                /*师傅端通知消息*/
                $title = '你的提现申请已通过';
                $content = '您好，你的提现申请已通过，请注意查收！ \n提现金额:'.$row['apointment_time'];
            }
            userNotice::sendNotice($row['uid'],0,0,$title,$content,2);
            return $this->response()->success('审核成功.')->refresh();
        }else{
            return $this->response()->error('审核失败,请稍后重试！')->refresh();
        }
    }
    public function form()
    {
        $this->radio('status', '状态')->options(['1'=>'通过','-1'=>'不通过'])->default(1);
        $this->textarea('reason','理由');
    }
}
