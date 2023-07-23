<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\UpdatePlantform;
use App\Models\article;
use App\Models\storeCouponUser;
use App\Models\storeOrder;
use App\Models\storeOrderProduct;
use App\Models\storeOrderStatus;
use App\Models\storeProductReply;
use App\Models\systemConfig;
use App\Models\userAddress;
use App\Models\userFeedback;
use App\Models\userMasterApplication;
use App\Models\userNotice;
use App\Models\userRelation;
use App\Models\userVisit;
use App\Services\ExpressQuery;
use Exception;
use Hanson\LaravelAdminWechat\Facades\OrderService;
use Hanson\LaravelAdminWechat\Models\WechatMerchant;
use Hanson\LaravelAdminWechat\Models\WechatUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * @group user
 * @authenticated
 */
class AuthApiController extends BaseController
{
    use ExpressQuery;
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
            $data = [];
            $data['nickname'] = $user['nickname'] =='微信用户'?'用户'.substr($user['mobile'],-4):$user['nickname'];
            $data['avatar'] = $user['avatar'];
            $data['couponCount'] = storeCouponUser::getMyCouponCount($userid);
            $data['collectCount'] = userRelation::getTotalCount($userid);
            $data['visitCount'] = userVisit::getTotalCount($userid);
            $data['order'] = storeOrder::getUserOrderNumber($userid);
            $data['is_master'] = userMasterApplication::isSubmitApplication($userid,$user['mobile']);
            $wechat_link = systemConfig::where('id',1)->value('kefu_link');
            $data['wechat_link'] =$wechat_link;

            /*优惠券过期设置*/
            storeCouponUser::setCouponExpired($userid);
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
            $user = Auth::guard('mini')->user();
            $data = [];
            $data['nickname'] = $user['nickname'] =='微信用户'?'用户'.substr($user['mobile'],-4):$user['nickname'];
            $data['avatar'] = $user['avatar'];
            $data['mobile'] = $user['mobile'];
            $data['gender'] = $user['gender'];
            return response(['code' => 200, 'msg' => 'success','data'=>$data]);
        } catch (Exception $exception) {
            return response(['code' => 400, 'msg' => $exception->getLine().'-'.$exception->getMessage()]);
        }
    }
    /**
     * 我的浏览记录
     * @bodyParam Authorization string required
     * @response  {}
     * @response  200 {"msg":"wrong msg"}
     */
    public function myVisit(Request $request)
    {
        try {
            $userid = $request->wechat_user_id;
            $type = $request->type?:1; //1-产品 2-视频 3-案例
            $datetype = $request->datetype?:1; //1-今天 2：一周内 3：一月内  4-全部
            $limit = $request->limit?:15;
            $data = userVisit::getVisitList($userid,$type,$datetype,$limit);
            $other = [];
            /*地址*/
            $defaultaddr = userAddress::getDefaultAddr($userid);
            $other['defaultAddr'] = $defaultaddr;
            /*默认地址是否提供服务*/
            $other['is_service'] = 0;
            if($defaultaddr){
                $isset = userAddress::inUsersArea($defaultaddr->id);
                if($isset > 0)$other['is_service'] = 1;
            }
            return response(['code' => 200, 'msg' => 'success','data'=>$data,'other'=>$other]);
        } catch (Exception $exception) {
            return response(['code' => 400, 'msg' => $exception->getLine().'-'.$exception->getMessage()]);
        }
    }
    /**
     * 我的优惠券数量
     * @bodyParam Authorization string required
     * @response  {}
     * @response  200 {"msg":"wrong msg"}
     */
    public function myCouponCount(Request $request)
    {
        try {
            $userid = $request->wechat_user_id;
            $data = storeCouponUser::getCouponCount($userid);
            return response(['code' => 200, 'msg' => 'success','data'=>$data]);
        } catch (Exception $exception) {
            return response(['code' => 400, 'msg' => $exception->getLine().'-'.$exception->getMessage()]);
        }
    }
    /**
     * 我的优惠券
     * @bodyParam Authorization string required
     * @response  {}
     * @response  200 {"msg":"wrong msg"}
     */
    public function myCoupons(Request $request)
    {
        try {
            $userid = $request->wechat_user_id;
            $status = $request->status?:0; //0-全部 1-可使用 2-已过期 3-已使用
            $limit = $request->limit?:15;
            $data = storeCouponUser::getCouponList($userid,$status,$limit);
            $wechat_link = systemConfig::where('id',1)->value('kefu_link');
            return response(['code' => 200, 'msg' => 'success','data'=>$data,'wechat_link'=>$wechat_link]);
        } catch (Exception $exception) {
            return response(['code' => 400, 'msg' => $exception->getLine().'-'.$exception->getMessage()]);
        }
    }
    /**
     * 我的收藏
     * @bodyParam Authorization string required
     * @response  {}
     * @response  200 {"msg":"wrong msg"}
     */
    public function myCollect(Request $request)
    {
        try {
            $userid = $request->wechat_user_id;
            $type = $request->type?:1; //1--普通商品、2-视频 3-案例
            $limit = $request->limit?:15;
            $data = userRelation::getCollectList($userid,$type,$limit);
            $other = [];
            /*地址*/
            $defaultaddr = userAddress::getDefaultAddr($userid);
            $other['defaultAddr'] = $defaultaddr;
            /*默认地址是否提供服务*/
            $other['is_service'] = 0;
            if($defaultaddr){
                $isset = userAddress::inUsersArea($defaultaddr->id);
                if($isset > 0)$other['is_service'] = 1;
            }
            return response(['code' => 200, 'msg' => 'success','data'=>$data,'other'=>$other]);
        } catch (Exception $exception) {
            return response(['code' => 400, 'msg' => $exception->getLine().'-'.$exception->getMessage()]);
        }
    }
    /**
     * 我的地址
     * @bodyParam Authorization string required
     * @response  {}
     * @response  200 {"msg":"wrong msg"}
     */
    public function myAddr(Request $request)
    {
        try {
            $userid = $request->wechat_user_id;
            $data = userAddress::getMyList($userid);
            return response(['code' => 200, 'msg' => 'success','data'=>$data]);
        } catch (Exception $exception) {
            return response(['code' => 400, 'msg' => $exception->getLine().'-'.$exception->getMessage()]);
        }
    }
    /**
     * 我的地址
     * @bodyParam Authorization string required
     * @response  {}
     * @response  200 {"msg":"wrong msg"}
     */
    public function updateAddrList(Request $request)
    {
        try {
            $userid = $request->wechat_user_id;
            $data = userAddress::updateaddr();
            return response(['code' => 200, 'msg' => 'success','data'=>1]);
        } catch (Exception $exception) {
            return response(['code' => 400, 'msg' => $exception->getLine().'-'.$exception->getMessage()]);
        }
    }
    /**
     * 删除地址
     * @bodyParam Authorization string required
     * @response  {}
     * @response  200 {"msg":"wrong msg"}
     */
    public function deleteAddr(Request $request)
    {
        try {
            $userid = $request->wechat_user_id;
            $addrid = $request->addrid;
            if(!$addrid)return response(['code' => 400, 'msg' => '传递参数错误']);
            $row = userAddress::where('id',$addrid)->where('uid',$userid)->first();
            if(!$row)return response(['code' => 400, 'msg' => '地址不存在']);
            $del = userAddress::where('id',$addrid)->delete();
            if($del){
                return response(['code' => 200, 'msg' => 'success']);
            }else{
                return response(['code' => 200, 'msg' => '删除失败']);
            }
        } catch (Exception $exception) {
            return response(['code' => 400, 'msg' => $exception->getLine().'-'.$exception->getMessage()]);
        }
    }
    /**
     * 添加我的地址
     * @bodyParam Authorization string required
     * @response  {}
     * @response  200 {"msg":"wrong msg"}
     */
    public function addmyAddr(Request $request)
    {
        try {
            $userid = $request->wechat_user_id;
            $name = $request->name;
            $mobile = $request->mobile;
            $province = $request->province;
            $city = $request->city;
            $district = $request->district;
            $province_name = $request->province_name;
            $city_name = $request->city_name;
            $district_name = $request->district_name;
            $detail = $request->detail;
            $is_default = $request->is_default?:0;
            if(!$name || !$mobile || !$province || !$city || !$district || !$detail)return response(['code' => 400, 'msg' => '传递参数错误']);
            $rowid = userAddress::insertRow($userid,$name,$mobile,$province,$city,$district,$province_name,$city_name,$district_name,$detail,$is_default);
            if($rowid){
                return response(['code' => 200, 'msg' => 'success']);
            }else{
                return response(['code' => 200, 'msg' => '插入失败']);
            }
        } catch (Exception $exception) {
            return response(['code' => 400, 'msg' => $exception->getLine().'-'.$exception->getMessage()]);
        }
    }
    /**
     * 添加微信地址
     * @bodyParam Authorization string required
     * @response  {}
     * @response  200 {"msg":"wrong msg"}
     */
    public function addWechaatAddr(Request $request)
    {
        try {
            $userid = $request->wechat_user_id;
            $name = $request->userName;
            $mobile = $request->telNumber;
            $provinceName = $request->provinceName;
            $cityName = $request->cityName;
            $countyName = $request->countyName;
            $detail = $request->detailInfo;
            if(!$name || !$mobile || !$provinceName || !$cityName || !$countyName || !$detail)return response(['code' => 400, 'msg' => '传递参数错误']);
            $rowid = userAddress::insertWechatRow($userid,$name,$mobile,$provinceName,$cityName,$countyName,$detail);
            if($rowid){
                return response(['code' => 200, 'msg' => 'success']);
            }else{
                return response(['code' => 200, 'msg' => '插入失败']);
            }
        } catch (Exception $exception) {
            return response(['code' => 400, 'msg' => $exception->getLine().'-'.$exception->getMessage()]);
        }
    }
    /**
     * 地址详情
     * @bodyParam Authorization string required
     * @response  {}
     * @response  200 {"msg":"wrong msg"}
     */
    public function addrInfo(Request $request)
    {
        try {
            $userid = $request->wechat_user_id;
            $addrid = $request->addrid;
            if(!$addrid)return response(['code' => 400, 'msg' => '传递参数错误']);
            $row = userAddress::getAddrById($userid,$addrid);
            return response(['code' => 200, 'msg' => 'success','data'=>$row]);
        } catch (Exception $exception) {
            return response(['code' => 400, 'msg' => $exception->getLine().'-'.$exception->getMessage()]);
        }
    }
    /**
     * 修改我的地址
     * @bodyParam Authorization string required
     * @response  {}
     * @response  200 {"msg":"wrong msg"}
     */
    public function updatemyAddr(Request $request)
    {
        try {
            $userid = $request->wechat_user_id;
            $addrid = $request->addrid;
            $name = $request->name;
            $mobile = $request->mobile;
            $province = $request->province;
            $city = $request->city;
            $district = $request->district;
            $detail = $request->detail;
            $is_default = $request->is_default?:0;
            if(!$addrid || !$name || !$mobile || !$province || !$city || !$district || !$detail)return response(['code' => 400, 'msg' => '传递参数错误']);
            $row = userAddress::where('id',$addrid)->where('uid',$userid)->first();
            if(!$row)return response(['code' => 400, 'msg' => '地址不存在']);
            $update = userAddress::updateRow($userid,$addrid,$name,$mobile,$province,$city,$district,$detail,$is_default);
            if($update){
                return response(['code' => 200, 'msg' => 'success']);
            }else{
                return response(['code' => 200, 'msg' => '更新失败']);
            }
        } catch (Exception $exception) {
            return response(['code' => 400, 'msg' => $exception->getLine().'-'.$exception->getMessage()]);
        }
    }
    /**
     * 留言类型
     * @bodyParam Authorization string required
     * @response  {}
     * @response  200 {"msg":"wrong msg"}
     */
    public function feedbackType(Request $request){
        try {
            $userid = $request->wechat_user_id;
            $data = systemConfig::first();
            $message_type = explode("\r\n",trim($data['message_type']));
            return response(['code' => 200, 'msg' => 'success','data'=>$message_type]);
        } catch (Exception $exception) {
            return response(['code' => 400, 'msg' => $exception->getLine().'-'.$exception->getMessage()]);
        }
    }

    /**
     * 上传文件
     * @bodyParam file string required
     * @bodyParam Authorization string required
     * @response  {}
     * @response  200 {"msg":"wrong msg"}
     */
    public function uploadImage(Request $request){
        try {
            $file = $request->file;
            if(!$file)return response(['code' => 400, 'msg' => '传递参数错误']);
            $file = $this->addCos('front',$file);
            //$file = $this->addImages($file);
            if($file){
                return response(['code' => 200,'msg' => 'success','url'=>$file]);
            }else {
                return response(['code' => 400, 'msg' => '上传失败']);
            }
        } catch (Exception $exception) {
            return response(['code' => 400, 'msg' => $exception->getLine().'-'.$exception->getMessage()]);
        }
    }
    /**
     * 关于我们
     * @bodyParam Authorization string required
     * @response  {}
     * @response  200 {"msg":"wrong msg"}
     */
    public function aboutUs(Request $request){
        try {
            $data = article::where('id',2)->select('title','content')->first();
            return response(['code' => 200,'msg' => 'success','data'=>$data]);
        } catch (Exception $exception) {
            return response(['code' => 400, 'msg' => $exception->getLine().'-'.$exception->getMessage()]);
        }
    }
    /**
     * 留言类型
     * @bodyParam Authorization string required
     * @response  {}
     * @response  200 {"msg":"wrong msg"}
     */
    public function feedback(Request $request){
        try {
            $userid = $request->wechat_user_id;
            $type = $request->type;
            $message = $request->message;
            $imgs = $request->img;
            if(!$type)return response(['code' => 400, 'msg' => '留言类型必须']);
            if(!$message)return response(['code' => 400, 'msg' => '请输入留言内容']);
            $user = Auth::guard('mini')->user();
            $user_type = $user['type'];
            if($imgs){
                $imgs = json_encode($imgs);
            }
            $msg = userFeedback::insertMessage($userid,$type,$message,$imgs,$user_type);
            if($msg){
                return response(['code' => 200, 'msg' => 'success']);
            }else{
                return response(['code' => 400, 'msg' => '留言失败']);
            }
        } catch (Exception $exception) {
            return response(['code' => 400, 'msg' => $exception->getLine().'-'.$exception->getMessage()]);
        }
    }

    /**
     * 师傅申请
     * @bodyParam Authorization string required
     * @response  {}
     * @response  200 {"msg":"wrong msg"}
     */
    public function masterApplication(Request $request){
        try {
            $userid = $request->wechat_user_id;
            $name = $request->name;
            $mobile = $request->mobile;
            $province = $request->province;
            $city = $request->city;
            $district = $request->district;
            $detail = $request->detail;
            $idcard = $request->idcard;
            $idcard_front = $request->idcard_front;
            $idcard_reverse = $request->idcard_reverse;
            if(!$name || !$mobile || !$province || !$city || !$district || !$detail || !$idcard || !$idcard_front || !$idcard_reverse)return response(['code' => 400, 'msg' => '参数错误']);
            /*if(!$this->isValidCard($idcard)){
                return response(['code' => 400, 'msg' => '身份证格式错误,请输入正确的身份证.']);
            }*/
            $msg = userMasterApplication::insertApplication($userid,$name,$mobile,$province,$city,$district,$detail,$idcard,$idcard_front,$idcard_reverse);
            if($msg){
                return response(['code' => 200, 'msg' => 'success']);
            }else{
                return response(['code' => 400, 'msg' => '申请失败']);
            }
        } catch (Exception $exception) {
            return response(['code' => 400, 'msg' => $exception->getLine().'-'.$exception->getMessage()]);
        }
    }
    /**
     * 详情
     * @bodyParam Authorization string required
     * @response  {}
     * @response  200 {"msg":"wrong msg"}
     */
    public function masterApplicationInfo(Request $request){
        try {
            $userid = $request->wechat_user_id;
            $row = userMasterApplication::getRowByUid($userid);
            return response(['code' => 200, 'msg' => 'success','data'=>$row]);
        } catch (Exception $exception) {
            return response(['code' => 400, 'msg' => $exception->getLine().'-'.$exception->getMessage()]);
        }
    }
    /**
     * 师傅申请编辑
     * @bodyParam Authorization string required
     * @response  {}
     * @response  200 {"msg":"wrong msg"}
     */
    public function masterApplicationEdit(Request $request){
        try {
            $userid = $request->wechat_user_id;
            $applicationid = $request->applicationid;
            $name = $request->name;
            $mobile = $request->mobile;
            $province = $request->province;
            $city = $request->city;
            $district = $request->district;
            $detail = $request->detail;
            $idcard = $request->idcard;
            $idcard_front = $request->idcard_front;
            $idcard_reverse = $request->idcard_reverse;
            if(!$applicationid || !$name || !$mobile || !$province || !$city || !$district || !$detail || !$idcard || !$idcard_front || !$idcard_reverse)return response(['code' => 400, 'msg' => '参数错误']);
            /*if(!$this->isValidCard($idcard)){
                return response(['code' => 400, 'msg' => '身份证格式错误,请输入正确的身份证.']);
            }*/
            $msg = userMasterApplication::editApplication($applicationid,$userid,$name,$mobile,$province,$city,$district,$detail,$idcard,$idcard_front,$idcard_reverse);
            if($msg){
                return response(['code' => 200, 'msg' => 'success']);
            }else{
                return response(['code' => 400, 'msg' => '申请失败']);
            }
        } catch (Exception $exception) {
            return response(['code' => 400, 'msg' => $exception->getLine().'-'.$exception->getMessage()]);
        }
    }
    /**
     * 师傅申请状态
     * @bodyParam Authorization string required
     * @response  {}
     * @response  200 {"msg":"wrong msg"}
     */
    public function masterApplicationStatus(Request $request)
    {
        try {
            $userid = $request->wechat_user_id;
            $user = Auth::guard('mini')->user();
            $mobile = $user['mobile'];
            $application =userMasterApplication::getRowByMobile($mobile);
            //$application =userMasterApplication::getRowByUid($userid);
            if(!$application) return response(['code' => 400, 'msg' => '未申请']);
            $wechat_link = systemConfig::where('id',1)->value('kefu_link');
            $data['wechat_link'] =$wechat_link;
            if(!$application)$is_master = 0;//未入驻
            if($application && $application['status'] == 0)$is_master=1;//管理员审核中
            if($application && $application['status'] == 2)$is_master=2;//管理员审核失败
            if($application && $application['status'] == 1)$is_master=3;//管理员审核成功，待签订合同
            if($application && $application['status'] == 3)$is_master=4;//入驻成功
            $data['is_master'] = $is_master;
            $data['reason'] = $application['reason'];
            $data['id'] = $application['id'];
            $data['name'] = $application['name'];
            $data['mobile'] = $application['mobile'];
            $data['province'] = $application['province'];
            $data['city'] = $application['city'];
            $data['district'] = $application['district'];
            $data['detail'] = $application['detail'];
            $data['idcard'] = $application['idcard'];
            $data['idcard_front'] = $application['idcard_front'];
            $data['idcard_reverse'] = $application['idcard_reverse'];
            return response(['code' => 200, 'msg' => 'success','data'=>$data]);
        } catch (Exception $exception) {
            return response(['code' => 400, 'msg' => $exception->getLine().'-'.$exception->getMessage()]);
        }
    }
    /**
     * 入驻申请合同
     * @bodyParam Authorization string required
     * @response  {}
     * @response  200 {"msg":"wrong msg"}
     */
    public function masterApplicationContract(Request $request)
    {
        try {
            $userid = $request->wechat_user_id;
            $data = article::where('id',4)->select('title','content')->first();
            return response(['code' => 200, 'msg' => 'success','data'=>$data]);
        } catch (Exception $exception) {
            return response(['code' => 400, 'msg' => $exception->getLine().'-'.$exception->getMessage()]);
        }
    }
    /**
     * 签订合同
     * @bodyParam Authorization string required
     * @response  {}
     * @response  200 {"msg":"wrong msg"}
     */
    public function masterSignContract(Request $request){
        try {
            $userid = $request->wechat_user_id;
            $user = Auth::guard('mini')->user();
            $mobile = $user['mobile'];
            if(!$mobile)return response(['code' => 400, 'msg' => '手机号未绑定']);
            $application =userMasterApplication::getRowByMobile($mobile);
            if(!$application)return response(['code' => 400, 'msg' => '未找到相关申请记录']);
            if($application['status'] == 0)return response(['code' => 400, 'msg' => '申请待审核']);
            if($application['status'] == 2)return response(['code' => 400, 'msg' => '审核失败，请重新提交申请']);
            if($application['status'] == 3)return response(['code' => 400, 'msg' => '合同已签订']);
            $update = [];
            $update['status'] = 3;
            $update['sign'] = 1;
            $update['updated_at'] = date('Y-m-d H:i:s');
            $res = userMasterApplication::where('id',$application['id'])->update($update);
            if($res){
                $master = WechatUser::where('mobile',$mobile)->where('type',2)->where('application_id',0)->first();
                if($master){
                    $operateId = userAddress::masterArea($application['province'],$application['city']);
                    WechatUser::where('id',$master['id'])->update([
                        'name'=>$application['name'],
                        'application_id'=>$application['id'],
                        'operate_id'=>$operateId,
                        'updated_at'=>date('Y-m-d H:i:s')
                    ]);
                }
                return response(['code' => 200, 'msg' => 'success']);
            }else{
                return response(['code' => 400, 'msg' => '请稍后重试']);
            }
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
            $data = userNotice::getMyListByUid($userid,$type,$limit,1);
            return response(['code' => 200, 'msg' => 'success','data'=>$data]);
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
            $keyword = $request->keyword?:'';
            $data['list'] = storeOrder::getMyOrderList($userid,$type,$limit,$keyword);
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
            $data['info'] = storeOrder::getOrderInfoById($orderid);
            $wechat_link = systemConfig::where('id',1)->value('kefu_link'); //客服链接
            $data['wechat_link'] =$wechat_link;
            $shop_intro = systemConfig::where('id',1)->value('shop_intro'); //购买须知
            $data['shop_intro'] = $shop_intro;
            return response(['code' => 200, 'msg' => 'success','data'=>$data]);
        } catch (Exception $exception) {
            return response(['code' => 400, 'msg' => $exception->getLine().'-'.$exception->getMessage()]);
        }
    }
    /**
     * 取消订单
     * @bodyParam Authorization string required
     * @response  {}
     * @response  200 {"msg":"wrong msg"}
     */
    public function cancelOrder(Request $request)
    {
        try {
            $userid = $request->wechat_user_id;
            $orderid = $request->orderid;
            if(!$orderid)return response(['code' => 400, 'msg' => '传递参数错误']);
            $row = storeOrder::getOrderInfoById($orderid);
            if(!$row)return response(['code' => 400, 'msg' => '订单不存在']);
            if($row['status'] !== 1)throw new Exception('订单不能取消');
            $res = storeOrder::where('id',$orderid)->update([
                'status'=>0,
                'updated_at'=>date('Y-m-d H:i:s'),
            ]);
            if($res){
                /*优惠券回退*/
                if($row['coupon_id']){
                    $arr = explode(',',$row['coupon_id']);
                    foreach ($arr as $couponId){
                        DB::table('store_coupon_user')->where('id',$couponId)->where('status',1)->update(['status'=>0,'updated_at'=>date('Y-m-d H:i:s')]);
                    }
                }
                storeOrderStatus::status($orderid,'cancel_order','用户取消订单',$userid);
                return response(['code' => 200, 'msg' => 'success']);
            }else{
                return response(['code' => 400, 'msg' => '取消失败，请稍后重试']);
            }
        } catch (Exception $exception) {
            return response(['code' => 400, 'msg' => $exception->getLine().'-'.$exception->getMessage()]);
        }
    }
    /**
     * 支付订单
     * @bodyParam Authorization string required
     * @response  {}
     * @response  200 {"msg":"wrong msg"}
     */
    public function payOrder(Request $request)
    {
        try {
            $userid = $request->wechat_user_id;
            $orderid = $request->orderid;
            if(!$orderid)return response(['code' => 400, 'msg' => '传递参数错误']);
            $row = storeOrder::getOrderInfoById($orderid);
            if(!$row)return response(['code' => 400, 'msg' => '订单不存在']);
            if($row['status'] !== 1)throw new Exception('订单不需要支付');
            DB::beginTransaction();
            $sitename = systemConfig::where('id',1)->value('site_name');
            $mch_id = WechatMerchant::where('id',1)->value('mch_id');
            $data['mch_id'] = $mch_id;
            $data['body'] = $sitename;
            $data['out_trade_no'] = $row['order_id'];
            $data['total_fee'] = $row['pay_price']* 100;
            $data['openid'] = WechatUser::getFieldsById($userid,'openid');
            $data['link_order_id'] = $row['id'];
            $data['wechat_user_id'] = $userid;
            $result = OrderService::jsConfig($mch_id,'JSAPI',$data);
            DB::commit();
            return response(['code' => 200, 'msg' => '微信付款','data'=>$result]);
        } catch (Exception $exception) {
            DB::rollBack();
            return response(['code' => 400, 'msg' => $exception->getLine().'-'.$exception->getMessage()]);
        }
    }
    /**
     * 提醒发货
     * @bodyParam Authorization string required
     * @response  {}
     * @response  200 {"msg":"wrong msg"}
     */
    public function remindOrder(Request $request)
    {
        try {
            $userid = $request->wechat_user_id;
            $orderid = $request->orderid;
            if(!$orderid)return response(['code' => 400, 'msg' => '传递参数错误']);
            $row = storeOrder::getOrderInfoById($orderid);
            if(!$row)return response(['code' => 400, 'msg' => '订单不存在']);
            if($row['status'] == 2 && $row['is_shipping'] == 0){
                $data = [];
                $data['remind_ship'] = 1;
                $data['remind_time'] = date('Y-m-d H:i:s');
                $data['updated_at'] = date('Y-m-d H:i:s');
                $res = storeOrder::where('id',$orderid)->update($data);
                if($res){
                    storeOrderStatus::status($orderid,'order_remid',date('Y-m-d H:i:s').':用户提醒发货');
                    return response(['code' => 200, 'msg' => 'success']);
                }else{
                    return response(['code' => 400, 'msg' => '提醒失败，请稍后再试']);
                }
            }else{
                return response(['code' => 400, 'msg' => '订单无需发货']);
            }
        } catch (Exception $exception) {
            DB::rollBack();
            return response(['code' => 400, 'msg' => $exception->getLine().'-'.$exception->getMessage()]);
        }
    }
    /**
     * 退款
     * @bodyParam Authorization string required
     * @response  {}
     * @response  200 {"msg":"wrong msg"}
     */
    public function refundOrder(Request $request)
    {
        try {
            $userid = $request->wechat_user_id;
            $orderid = $request->orderid;
            $reason = $request->reason;
            if(!$orderid)return response(['code' => 400, 'msg' => '传递参数错误']);
            if(!$reason)return response(['code' => 400, 'msg' => '请输入退款理由']);
            $orderinfo = storeOrder::where('id',$orderid)->where('uid',$userid)->first();
            if(!$orderinfo)return response(['code' => 400, 'msg' => '订单不存在']);
            if($orderinfo['status'] != 2)return response(['code' => 400, 'msg' => '订单不能取消']);
            $order_userinfo = WechatUser::getUserById($userid);
            /*退款*/
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
                    userNotice::sendNotice($userid,$orderid,$orderinfo['order_id'],$title,$content,1);
                    storeOrderStatus::status($orderid,'order_refund','用户申请退款成功');
                    DB::commit();
                    return response(['code' => 200, 'msg' => 'success']);
                }else{
                    DB::rollBack();
                    return response(['code' => 400, 'msg' => '退款失败:'.$result['return_msg']]);
                }
            }
        } catch (Exception $exception) {
            DB::rollBack();
            return response(['code' => 400, 'msg' => $exception->getLine().'-'.$exception->getMessage()]);
        }
    }
    /**
     * 退款理由
     * @bodyParam Authorization string required
     * @response  {}
     * @response  200 {"msg":"wrong msg"}
     */
    public function refundReason(Request $request){
        try {
            $userid = $request->wechat_user_id;
            $data = systemConfig::first();
            $store_reason= explode("\r\n",trim($data['store_reason']));
            return response(['code' => 200, 'msg' => 'success','data'=>$store_reason]);
        } catch (Exception $exception) {
            return response(['code' => 400, 'msg' => $exception->getLine().'-'.$exception->getMessage()]);
        }
    }
    /**
     * 确认收货
     * @bodyParam Authorization string required
     * @response  {}
     * @response  200 {"msg":"wrong msg"}
     */
    public function orderReceipt(Request $request){
        try {
            $userid = $request->wechat_user_id;
            $orderid = $request->orderid;
            if(!$orderid)return response(['code' => 400, 'msg' => '传递参数错误']);
            $row = storeOrder::where('uid',$userid)->where('id',$orderid)->first();
            if(!$row)return response(['code' => 400, 'msg' => '订单不存在']);
            if($row['status'] == 3){//待收货
                $update = [];
                $update['status'] = 5;//已收货，待服务
                $update['plantfrom'] = 1;//订单分配给平台
                $update['give_id'] = 0;
                //$update['plantfrom'] = 2;//订单分配给师傅端
                /*自动分配给师傅*/
                if($row['need_service'] == 1){
                   /* $master = WechatUser::autoGetMasterId($row['operate_id'],$row['province'],$row['city']);
                    $masterid = $master?$master['id']:0;
                    if($masterid == 0){
                        $update['plantfrom'] = 1;//后台指定师傅
                    }
                    $update['give_id'] = $masterid;
                    $update['give_time'] = date('y-m-d H:i:s');*/
                    /*师傅消息发送*/
                    /*if($masterid > 0){
                        $title = '订单待接单';
                        $content = '您有新订单待接单，请及时处理 \n订单号:'.$row['order_id'];
                        userNotice::sendNotice($masterid,$orderid,$row['order_id'],$title,$content,2);
                    }*/
                    /*用户消息发送*/
                    $title = '订单确认收货，待接单';
                    $content = '您的订单已确认收货，等待师傅上门服务 \n订单号:'.$row['order_id'];
                    userNotice::sendNotice($userid,$orderid,$row['order_id'],$title,$content,1);
                    storeOrderStatus::status($orderid,'order_receipt','用户确认收货，等待师傅接单');
                }else{/*无需服务，则已完成*/
                    $update['status'] = 10;//已完成
                    $update['plantfrom'] = 0;
                    /*用户消息发送*/
                    $title = '订单确认收货通知';
                    $content = '您的订单已确认收货，订单已完成 \n订单号:'.$row['order_id'];
                    userNotice::sendNotice($userid,$orderid,$row['order_id'],$title,$content,1);
                    storeOrderStatus::status($orderid,'order_receipt_finish','用户确认收货，订单已完成');
                }
                $update['updated_at'] = date('y-m-d H:i:s');
                $res = storeOrder::where('id',$orderid)->update($update);
                //dispatch(new UpdatePlantform($orderid)); //师傅规定时间未接单，返回平台去分配
                if($res){
                    return response(['code' => 200, 'msg' => 'success']);
                }else{
                    return response(['code' => 400, 'msg' => '确认失败，请稍后重试']);
                }
            }else{
                return response(['code' => 400, 'msg' => '订单无需确认']);
            }
        } catch (Exception $exception) {
            return response(['code' => 400, 'msg' => $exception->getLine().'-'.$exception->getMessage()]);
        }
    }
    /**
     * 查看物流
     * @bodyParam Authorization string required
     * @response  {}
     * @response  200 {"msg":"wrong msg"}
     */
    public function orderExpress(Request $request){
        try {
            $userid = $request->wechat_user_id;
            $orderid = $request->orderid;
            if(!$orderid)return response(['code' => 400, 'msg' => '传递参数错误']);
            $row = storeOrder::where('uid',$userid)->where('id',$orderid)->where('is_shipping',1)->first();
            if(!$row)return response(['code' => 400, 'msg' => '该订单不存在快递单号']);
            $cacheName = $row['order_id'].$row['ship_no'];
            $result = Cache::get($cacheName);
            if($result === null || 1==1) {
                $app_code = systemConfig::where('id',1)->value('system_express_app_code');
                $result = $this->setNo($row['ship_no'])->setAppCode($app_code)->query();
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
            return response(['code' => 200, 'msg' => 'success','express'=>$result]);
        } catch (Exception $exception) {
            return response(['code' => 400, 'msg' => $exception->getLine().'-'.$exception->getMessage()]);
        }
    }
    /**
     * 评价问题
     * @bodyParam Authorization string required
     * @response  {}
     * @response  200 {"msg":"wrong msg"}
     */
    public function orderEvaluationAsk(Request $request){
        try {
            $userid = $request->wechat_user_id;
            $data = systemConfig::first();
            $service_review= explode("\r\n",trim($data['service_review']));
            return response(['code' => 200, 'msg' => 'success','data'=>$service_review]);
        } catch (Exception $exception) {
            return response(['code' => 400, 'msg' => $exception->getLine().'-'.$exception->getMessage()]);
        }
    }
    /**
     * 评价师傅
     * @bodyParam Authorization string required
     * @response  {}
     * @response  200 {"msg":"wrong msg"}
     */
    public function orderEvaluation(Request $request){
        try {
            $userid = $request->wechat_user_id;
            $orderid = $request->orderid;
            $comment = $request->comment;
            if(!$orderid || !$comment)return response(['code' => 400, 'msg' => '传递参数错误']);
            $row = storeOrder::getOrderInfoById($orderid);
            if(!$row)return response(['code' => 400, 'msg' => '订单不存在']);
            if($row['master_id'] > 0 && $row['status'] == 6){//待评价
                $data['uid'] = $userid;
                $data['oid'] = $orderid;
                $data['master_id'] = $row['master_id'];
                $data['comment'] = json_encode($comment);
                $data['created_at'] = date('Y-m-d H:i:s');
                $res = storeProductReply::insert($data);
                if($res){
                    storeOrder::where('id',$orderid)->update([
                        'status'=>10,//已完成
                        'updated_at'=>date('y-m-d H:i:s')
                    ]);
                    return response(['code' => 200, 'msg' => 'success']);
                }else{
                    return response(['code' => 400, 'msg' => '评价失败，请稍后重试']);
                }
            }else{
                return response(['code' => 400, 'msg' => '订单无需评价']);
            }
        } catch (Exception $exception) {
            return response(['code' => 400, 'msg' => $exception->getLine().'-'.$exception->getMessage()]);
        }
    }
}
