<?php

namespace App\Http\Controllers\Api;

use App\Models\article;
use App\Models\articleCategory;
use App\Models\storeAds;
use App\Models\storeCouponUser;
use App\Models\storeOrder;
use App\Models\storeOrderStatus;
use App\Models\systemConfig;
use App\Models\userBill;
use App\Models\userExtract;
use App\Models\userMasterApplication;
use App\Models\userMasterLevel;
use App\Models\userNotice;
use App\Models\userRelation;
use App\Models\userVisit;
use Hanson\LaravelAdminWechat\Models\WechatUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Log;

/**
 * @group store
 * @authenticated
 */
class MasterApiController extends BaseController
{
    /**
     * 入口
     * @response  {}
     * @response  200 {"msg":"wrong msg"}
     */
    public function enter(Request $request)
    {
        try {
            $enter = storeAds::getRow(2); //首页入口图
            return response(['code' => 200, 'msg' => 'success','data'=>$enter]);
        } catch (Exception $exception) {
            return response(['code' => 400, 'msg' => $exception->getLine().'-'.$exception->getMessage()]);
        }
    }
    /**
     * 用户协议
     * @response  {}
     * @response  200 {"msg":"wrong msg"}
     */
    public function userAgreement(Request $request)
    {
        try {
            $data = article::where('id',3)->select('title','content')->first();
            return response(['code' => 200,'msg' => 'success','data'=>$data]);
        } catch (Exception $exception) {
            return response(['code' => 400, 'msg' => $exception->getLine().'-'.$exception->getMessage()]);
        }
    }
    /**
     * 等级规则
     * @response  {}
     * @response  200 {"msg":"wrong msg"}
     */
    public function masterLevelRule(Request $request)
    {
        try {
            $data = article::where('id',5)->select('title','content')->first();
            return response(['code' => 200,'msg' => 'success','data'=>$data]);
        } catch (Exception $exception) {
            return response(['code' => 400, 'msg' => $exception->getLine().'-'.$exception->getMessage()]);
        }
    }
    /**
     * 师傅状态
     * @bodyParam Authorization string required
     * @response  {}
     * @response  200 {"msg":"wrong msg"}
     */
    public function masterStatus(Request $request)
    {
        try {
            $userid = $request->wechat_user_id;
            $wechat_link = systemConfig::where('id',1)->value('kefu_link');
            $data['wechat_link'] =$wechat_link;
            $user = Auth::guard('mini')->user();
            $mobile = $user['mobile'];
            $application =userMasterApplication::getRowByMobile($mobile);
            if(!$application)return response(['code' => 200, 'msg' => '你不是该平台入住师傅','is_master'=>0]);
            if($application && $application->status == 0)return response(['code' => 200, 'msg' => '管理员审核中','is_master'=>1,'data'=>$data]);
            if($application && $application->status == 2)return response(['code' => 200, 'msg' => '平台入驻失败','is_master'=>2,'data'=>$data]);
            if($application && $application->status == 1)return response(['code' => 200, 'msg' => '管理员审核成功，待签订合同','is_master'=>3,'data'=>$data]);
            if($application && $application->status == 3)return response(['code' => 200, 'msg' => '入驻成功','is_master'=>4,'data'=>$data]);
        } catch (Exception $exception) {
            return response(['code' => 400, 'msg' => $exception->getLine().'-'.$exception->getMessage()]);
        }
    }
    /**
     * 个人中心
     * @bodyParam Authorization string required
     * @response  {}
     * @response  200 {"msg":"wrong msg"}
     */
    public function my(Request $request)
    {
        try {
            $userid = $request->wechat_user_id;
            $user = Auth::guard('mini')->user();
            $mobile = $user['mobile'];
            $data = [];
            $data['nickname'] = $user['nickname'] =='微信用户'?'用户'.substr($user['mobile'],-4):$user['nickname'];
            $data['avatar'] = $user['avatar'];
            $data['star'] = $user['star'];
            $data['master_status'] = $user['master_status'];
            $data['master_level'] = $user['master_level'];
            $data['order_accept'] = storeOrder::getMasterOrderCount($userid,1);
            $data['order_reject'] = storeOrder::getMasterOrderCount($userid,2);;
            $data['order'] = storeOrder::getMasterOrderNumber($userid);
            $wechat_link = systemConfig::where('id',1)->value('kefu_link');
            $data['wechat_link'] =$wechat_link;
            return response(['code' => 200, 'msg' => 'success','data'=>$data]);
        } catch (Exception $exception) {
            return response(['code' => 400, 'msg' => $exception->getLine().'-'.$exception->getMessage()]);
        }
    }

    /**
     * 个人资料
     * @bodyParam Authorization string required
     * @response  {}
     * @response  200 {"msg":"wrong msg"}
     */
    public function myInfo(Request $request)
    {
        try {
            $userid = $request->wechat_user_id;
            $user = WechatUser::getUserById($userid);
            //$mobile = $user['mobile'];
            $application =userMasterApplication::getRowById($user['application_id']);
            $data = [];
            $data['nickname'] = $user['nickname'] =='微信用户'?'用户'.substr($user['mobile'],-4):$user['nickname'];
            $data['realname'] = $user['name'];
            $data['avatar'] = $user['avatar'];
            $data['mobile'] = $user['mobile'];
            $data['gender'] = $user['gender'];
            $data['address'] = $application->province_name.'-'.$application->city_name.'-'.$application->district_name.'-'.$application->detail;
            return response(['code' => 200, 'msg' => 'success','data'=>$data]);
        } catch (Exception $exception) {
            return response(['code' => 400, 'msg' => $exception->getLine().'-'.$exception->getMessage()]);
        }
    }
    /**
     * 资讯
     * @response  {}
     * @response  200 {"msg":"wrong msg"}
     */
    public function News(Request $request)
    {
        try {
            $data = [];
            $data['recommend'] = article::getRecommend(); //推荐文章
            $data['list'] = articleCategory::indexList(2); //首页列表
            $bannerlist = storeAds::getList(4); //首页轮播图
            $data['bannerlist'] = $bannerlist;
            return response(['code' => 200, 'msg' => 'success','data'=>$data]);
        } catch (Exception $exception) {
            return response(['code' => 400, 'msg' => $exception->getLine().'-'.$exception->getMessage()]);
        }
    }
    /**
     * 搜索资讯
     * @response  {}
     * @response  200 {"msg":"wrong msg"}
     */
    public function newsSearch(Request $request)
    {
        try {
            $keyword = $request->keyword;
            $limit = $request->limit?:15;
            if(!$keyword)return response(['code' => 400, 'msg' => '请输入关键字搜索']);
            $data = [];
            $data = article::getListByKeyword($keyword,$limit);
            return response(['code' => 200, 'msg' => 'success','data'=>$data]);
        } catch (Exception $exception) {
            return response(['code' => 400, 'msg' => $exception->getLine().'-'.$exception->getMessage()]);
        }
    }
    /**
     * 资讯列表
     * @response  {}
     * @response  200 {"msg":"wrong msg"}
     */
    public function newsList(Request $request)
    {
        try {
            $cid = $request->cid;
            $limit = $request->limit?:15;
            if(!$cid)return response(['code' => 400, 'msg' => '传递参数错误']);
            $data = [];
            $data['catname'] = articleCategory::getCatName($cid); //分类名称
            $data['list'] = article::getListByCid($cid,$limit);
            return response(['code' => 200, 'msg' => 'success','data'=>$data]);
        } catch (Exception $exception) {
            return response(['code' => 400, 'msg' => $exception->getLine().'-'.$exception->getMessage()]);
        }
    }
    /**
     * 资讯详情
     * @response  {}
     * @response  200 {"msg":"wrong msg"}
     */
    public function newsInfo(Request $request){
        try {
            $id = $request->id;
            if(!$id)return response(['code' => 400, 'msg' => '传递参数错误']);
            $data = [];
            $data = article::getRowById($id);
            //访问量+1
            $browerCount = $data->browse + 1;
            article::where('id', $id)->update([
                'browse' => $browerCount,
                'updated_at'   => date('Y-m-d H:i:s'),
            ]);
            return response(['code' => 200, 'msg' => 'success','data'=>$data]);
        } catch (Exception $exception) {
            return response(['code' => 400, 'msg' => $exception->getLine().'-'.$exception->getMessage()]);
        }
    }
    /**
     * 我的消息
     * @bodyParam Authorization string required
     * @response  {}
     * @response  200 {"msg":"wrong msg"}
     */
    public function myNotice(Request $request)
    {
        try {
            $userid = $request->wechat_user_id;
            $type = $request->type?:0;
            $limit = $request->limit?:15;
            $data = userNotice::getMyListByUid($userid,$type,$limit,2);
            $total = userNotice::getTotal($userid,2);
            return response(['code' => 200, 'msg' => 'success','data'=>$data,'total'=>$total]);
        } catch (Exception $exception) {
            return response(['code' => 400, 'msg' => $exception->getLine().'-'.$exception->getMessage()]);
        }
    }
    /**
     * 我的消息详情
     * @bodyParam Authorization string required
     * @response  {}
     * @response  200 {"msg":"wrong msg"}
     */
    public function myNoticeInfo(Request $request)
    {
        try {
            $userid = $request->wechat_user_id;
            $noticeid = $request->noticeid;
            if(!$noticeid)return response(['code' => 400, 'msg' => '传递参数错误']);
            $data = userNotice::getRowById($noticeid);
            /*设置已读*/
            userNotice::where('id',$noticeid)->update(['is_read'=>1,'updated_at'=>date('Y-m-d H:i:s')]);
            return response(['code' => 200, 'msg' => 'success','data'=>$data]);
        } catch (Exception $exception) {
            return response(['code' => 400, 'msg' => $exception->getLine().'-'.$exception->getMessage()]);
        }
    }
    /**
     * 我的消息删除
     * @bodyParam Authorization string required
     * @response  {}
     * @response  200 {"msg":"wrong msg"}
     */
    public function myNoticeDelete(Request $request)
    {
        try {
            $userid = $request->wechat_user_id;
            $noticeid = $request->noticeid;
            if(!$noticeid)return response(['code' => 400, 'msg' => '传递参数错误']);
            $data = userNotice::deleteNotice($noticeid);
            if($data){
                return response(['code' => 200, 'msg' => 'success']);
            }else{
                return response(['code' => 400, 'msg' => '删除失败']);
            }
        } catch (Exception $exception) {
            return response(['code' => 400, 'msg' => $exception->getLine().'-'.$exception->getMessage()]);
        }
    }
    /**
     * 接单列表
     * @bodyParam Authorization string required
     * @response  {}
     * @response  200 {"msg":"wrong msg"}
     */
    public function acceptOrderList(Request $request)
    {
        try {
            $userid = $request->wechat_user_id;
            $user = Auth::guard('mini')->user();
            $limit = $request->limit?:15;
            //$operate_id = $user['operate_id'];//运营中心id
            $data['list'] = storeOrder::getAcceptOrderList($userid,$limit);
            $wechat_link = systemConfig::where('id',1)->value('kefu_link');
            $data['wechat_link'] =$wechat_link;
            return response(['code' => 200, 'msg' => 'success','data'=>$data]);
        } catch (Exception $exception) {
            return response(['code' => 400, 'msg' => $exception->getLine().'-'.$exception->getMessage()]);
        }
    }
    /**
     * 拒单理由
     * @bodyParam Authorization string required
     * @response  {}
     * @response  200 {"msg":"wrong msg"}
     */
    public function rejectReason(Request $request){
        try {
            $data = systemConfig::first();
            $reject_reason= explode("\r\n",trim($data['reject_reason']));
            return response(['code' => 200, 'msg' => 'success','data'=>$reject_reason]);
        } catch (Exception $exception) {
            return response(['code' => 400, 'msg' => $exception->getLine().'-'.$exception->getMessage()]);
        }
    }
    /**
     * 接单
     * @bodyParam Authorization string required
     * @response  {}
     * @response  200 {"msg":"wrong msg"}
     */
    public function acceptOrder(Request $request){
        try {
            $userid = $request->wechat_user_id;
            $orderid = $request->orderid;
            if(!$orderid)return response(['code' => 400, 'msg' => '传递参数错误']);
            $row = storeOrder::getMasterOrderInfoById($orderid,$userid);
            if($row['status'] == 5 && $row['master_id'] == 0){
                $userInfo = WechatUser::getUserById($userid);
                $level_amount = userMasterLevel::where('id',$userInfo['master_level'])->value('amount');
                $base_wage = $row['service_base_wage'];
                $data['master_id'] = $userid;
                $data['master_level'] = $userInfo['master_level'];
                $data['level_amount'] = $level_amount;
                //$data['base_wage'] = $userInfo['base_wage'];
                $data['base_wage'] = $base_wage;
                $data['policy_subsidy'] = $userInfo['policy_subsidy'];
                $data['status'] = 8;//已接单，服务中
                $data['arrival_time'] = date('Y-m-d H:i:s');
                $data['updated_at'] = date('Y-m-d H:i:s');
                $res = storeOrder::where('id',$orderid)->update($data);
                if($res){
                    $user = Auth::guard('mini')->user();
                    $nickname = $user['name'];
                    /*用户端通知消息*/
                    $title = '师傅已接单';
                    $content = '您的订单已被师傅【'.$nickname.'】成功接单，请等待师傅联系上门服务 \n订单号:'.$row['order_id'].' \n订单金额:￥'.$row['pay_price'].' \n服务费用:'.$row['service_price'].' \n预约时间:'.$row['apointment_time'];
                    userNotice::sendNotice($row['uid'],$orderid,$row['order_id'],$title,$content,1);
                    /*师傅端通知消息*/
                    $title = '您已接单';
                    $content = '您的已成功接单，请尽快联系客户上门服务 \n订单号:'.$row['order_id'].' \n订单金额:￥'.$row['pay_price'].' \n服务费用:'.$row['service_price'].' \n预约时间:'.$row['apointment_time'];
                    userNotice::sendNotice($userid,$orderid,$row['order_id'],$title,$content,2);
                    /*订单状态记录*/
                    storeOrderStatus::status($orderid,'accept_order','师傅【'.$nickname.'】接单',$userid);
                    return response(['code' => 200, 'msg' => 'success']);
                }else{
                    return response(['code' => 400, 'msg' => '请稍后重试']);
                }
            }else{
                return response(['code' => 400, 'msg' => '订单无需服务']);
            }

        } catch (Exception $exception) {
            return response(['code' => 400, 'msg' => $exception->getLine().'-'.$exception->getMessage()]);
        }
    }
    /**
     * 拒单
     * @bodyParam Authorization string required
     * @response  {}
     * @response  200 {"msg":"wrong msg"}
     */
    public function rejectOrder(Request $request){
        try {
            $userid = $request->wechat_user_id;
            $orderid = $request->orderid;
            $reason = $request->reason;
            if(!$orderid)return response(['code' => 400, 'msg' => '传递参数错误']);
            if(!$reason)return response(['code' => 400, 'msg' => '请输入拒单理由']);
            $row = storeOrder::getMasterOrderInfoById($orderid,$userid);
            if($row['status'] == 5 && $row['master_id'] == 0){
                $userInfo = WechatUser::getUserById($userid);
                $level_amount = userMasterLevel::where('id',$userInfo['master_level'])->value('amount');
                $data['reject_master_id'] = $userid;
                $data['reject_reason'] = $reason;//拒单理由
                $data['give_id'] = 0;
                $data['plantfrom'] = 1;//拒单后 订单分配至平台
                $data['reject_master_level'] = $userInfo['master_level'];
                $data['reject_level_amount'] = $level_amount;
                //$data['reject_base_wage'] = $userInfo['base_wage'];
                $data['reject_base_wage'] = $row['service_base_wage'];
                $data['reject_policy_subsidy'] = $userInfo['policy_subsidy'];
                $data['reject_time'] = date('Y-m-d H:i:s');
                $data['updated_at'] = date('Y-m-d H:i:s');

                $res = storeOrder::where('id',$orderid)->update($data);
                $nickname = $userInfo['name'];
                /*用户端通知消息*/
                $title = '师傅已拒单';
                $content = '您的订单已被师傅【'.$nickname.'】拒单，请等待系统再次分配师傅上门服务或者联系客服，谢谢！ \n订单号:'.$row['order_id'].' \n订单金额:￥'.$row['pay_price'].' \n服务费用:'.$row['service_price'].' \n预约时间:'.$row['apointment_time'];
                userNotice::sendNotice($row['uid'],$orderid,$row['order_id'],$title,$content,1);
                /*师傅端通知消息*/
                $title = '您已拒单';
                $content = '您的已成功拒单 \n订单号:'.$row['order_id'].' \n订单金额:￥'.$row['pay_price'].' \n服务费用:'.$row['service_price'].' \n预约时间:'.$row['apointment_time'];
                userNotice::sendNotice($userid,$orderid,$row['order_id'],$title,$content,2);
                /*订单状态记录*/
                $message = '师傅【'.$nickname.'】据单;理由：'.$reason;
                storeOrderStatus::status($orderid,'reject_order',$message,$userid);
                return response(['code' => 200, 'msg' => 'success']);
            }else{
                return response(['code' => 400, 'msg' => '订单无需服务']);
            }
        } catch (Exception $exception) {
            return response(['code' => 400, 'msg' => $exception->getLine().'-'.$exception->getMessage()]);
        }
    }
    /**
     * 我的订单
     * @bodyParam Authorization string required
     * @response  {}
     * @response  200 {"msg":"wrong msg"}
     */
    public function myOrder(Request $request)
    {
        try {
            $userid = $request->wechat_user_id;
            $type = $request->type?:0;
            $limit = $request->limit?:15;
            $keyword = $request->keyword;
            $data['list'] = storeOrder::getMasterOrderList($userid,$type,$limit,$keyword);
            $wechat_link = systemConfig::where('id',1)->value('kefu_link');
            $data['wechat_link'] =$wechat_link;
            return response(['code' => 200, 'msg' => 'success','data'=>$data]);
        } catch (Exception $exception) {
            return response(['code' => 400, 'msg' => $exception->getLine().'-'.$exception->getMessage()]);
        }
    }
    /**
     * 订单详情
     * @bodyParam Authorization string required
     * @response  {}
     * @response  200 {"msg":"wrong msg"}
     */
    public function myOrderInfo(Request $request)
    {
        try {
            $userid = $request->wechat_user_id;
            $orderid = $request->orderid;
            if(!$orderid)return response(['code' => 400, 'msg' => '传递参数错误']);
            $data = storeOrder::getMasterOrderInfoById($orderid,$userid);
            return response(['code' => 200, 'msg' => 'success','data'=>$data]);
        } catch (Exception $exception) {
            return response(['code' => 400, 'msg' => $exception->getLine().'-'.$exception->getMessage()]);
        }
    }
    /**
     * 完成服务
     * @bodyParam Authorization string required
     * @response  {}
     * @response  200 {"msg":"wrong msg"}
     */
    public function finishService(Request $request)
    {
        try {
            $userid = $request->wechat_user_id;
            $orderid = $request->orderId;
            $site_photo = $request->site_photo;
            $customer_photo = $request->customer_photo;
            Log::info('orderid:'.$orderid);
            Log::info('site_photo:'.json_encode($site_photo));
            Log::info('customer_photo:'.json_encode($customer_photo));
            if(!$orderid || !$site_photo)return response(['code' => 400, 'msg' => '传递参数错误']);
            $row = storeOrder::where('id',$orderid)->where('status',8)->first();
            if(!$row)return response(['code' => 400, 'msg' => '订单不存在或暂不能完成服务']);
            $service_finished = [];
            $service_finished['site_photo'] = $site_photo;
            $service_finished['customer_photo'] = $customer_photo;
            $update = [];
            $update['status'] = 9;//服务待审核
            $update['service_finished'] = json_encode($service_finished);
            $update['service_finished_time'] = date('Y-m-d H:i:s');
            $update['updated_at'] = date('Y-m-d H:i:s');
            $res = storeOrder::where('id',$orderid)->update($update);
            if($res){
                storeOrderStatus::status($orderid,'finish_service','师傅完成订单，提交服务完成申请',$userid);
                return response(['code' => 200, 'msg' => 'success']);
            }else{
                return response(['code' => 200, 'msg' => '请稍后重试']);
            }
        } catch (Exception $exception) {
            return response(['code' => 400, 'msg' => $exception->getLine().'-'.$exception->getMessage()]);
        }
    }
    /**
     * 我的钱包
     * @bodyParam Authorization string required
     * @response  {}
     * @response  200 {"msg":"wrong msg"}
     */
    public function myPurse(Request $request)
    {
        try {
            $userid = $request->wechat_user_id;
            $type = $request->type?:0;
            $limit = $request->limit?:15;
            $datestart = $request->datestart?:'';
            $dateend = $request->dateend?:'';
            $data = userBill::getMyPurse($userid,$type,$limit,$datestart,$dateend);
            $balance = userBill::getPurseBalance($userid);
            return response(['code' => 200, 'msg' => 'success','data'=>$data,'balance'=>$balance]);
        } catch (Exception $exception) {
            return response(['code' => 400, 'msg' => $exception->getLine().'-'.$exception->getMessage()]);
        }
    }
    /**
     * 我的余额
     * @bodyParam Authorization string required
     * @response  {}
     * @response  200 {"msg":"wrong msg"}
     */
    public function myBalance(Request $request)
    {
        try {
            $userid = $request->wechat_user_id;
            $data = userBill::getPurseBalance($userid);
            return response(['code' => 200, 'msg' => 'success','data'=>$data]);
        } catch (Exception $exception) {
            return response(['code' => 400, 'msg' => $exception->getLine().'-'.$exception->getMessage()]);
        }
    }
    /**
     * 提现
     * @bodyParam Authorization string required
     * @response  {}
     * @response  200 {"msg":"wrong msg"}
     */
    public function withdrawApplication(Request $request)
    {
        try {
            $userid = $request->wechat_user_id;
            $price = $request->price;
            if(!$price)return response(['code' => 400, 'msg' => '传递参数错误']);
            if($price <= 0)return response(['code' => 400, 'msg' => '提现金额必须大于0']);
            $balance = userBill::getPurseBalance($userid);
            if($price > $balance)return response(['code' => 400, 'msg' => '提现余额不足']);
            $res = userExtract::insertRow($userid,$price);
            if($res){
                /*师傅端通知消息*/
                $title = '提现申请提交';
                $content = '您的提现申请已提交，提现金额：￥'.$price.'\n请等待后台审核！如有疑问，请联系客服。';
                userNotice::sendNotice($userid,0,0,$title,$content,2);
                return response(['code' => 200, 'msg' => 'success']);
            }else{
                return response(['code' => 400, 'msg' => '提现申请失败']);
            }
        } catch (Exception $exception) {
            return response(['code' => 400, 'msg' => $exception->getLine().'-'.$exception->getMessage()]);
        }
    }
}
