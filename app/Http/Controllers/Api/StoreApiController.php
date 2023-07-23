<?php

namespace App\Http\Controllers\Api;

use App\Events\CreateOrderAfterEvent;
use App\Http\Controllers\Controller;
use App\Jobs\DelayOrder;
use App\Models\storeAds;
use App\Models\storeCart;
use App\Models\storeCase;
use App\Models\storeModel;
use App\Models\storeCategory;
use App\Models\storeCoupon;
use App\Models\storeCouponIssue;
use App\Models\storeCouponUser;
use App\Models\storeHotKeyword;
use App\Models\storeOrder;
use App\Models\storeOrderProduct;
use App\Models\storeProduct;
use App\Models\storeSearch;
use App\Models\storeVideo;
use App\Models\storeVisit;
use App\Models\systemConfig;
use App\Models\userAddress;
use App\Models\userRelation;
use App\Models\userVisit;
use Hanson\LaravelAdminWechat\Facades\OrderService;
use Hanson\LaravelAdminWechat\Models\WechatMerchant;
use Hanson\LaravelAdminWechat\Models\WechatUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Log;

/**
 * @group store
 * @authenticated
 */
class StoreApiController extends BaseController
{
    /**
     * 首页
     * @response  {}
     * @response  200 {"msg":"wrong msg"}
     */
    public function index(Request $request)
    {
        try {
            $userid = $request->wechat_user_id;
            $data = [];
            $enter = storeAds::getRow(2); //首页入口图
            $bannerlist = storeAds::getList(1); //首页轮播图
            $activity = systemConfig::getActivity(4);//首页活动专区
            $hotList = storeProduct::getHomeProductList('is_hot',4); //热销专区
            $discountList = storeProduct::getHomeProductList('is_discount',2);//优惠专区
            $diyList = storeProduct::getHomeProductList('is_diy',1);//定制服务
            $sampleList = storeProduct::getHomeProductList('is_sample',1);//小样专区
            $caseList = storeCase::getRecommList();//效果案例
            $videoList = storeVideo::getRecommList();//DIY视频
            $data['enterImg'] = $enter;
            $data['activityList'] = $activity;
            $data['bannerList'] = $bannerlist;
            $data['hotList'] = $hotList;
            $data['discountList'] = $discountList;
            $data['diyList'] = $diyList;
            $data['samsleList'] = $sampleList;
            $data['videoList'] = $videoList;
            $data['caseList'] = $caseList;
            $coupons = systemConfig::where('id',1)->value('wechat_first_coupon');
            $is_coupons = WechatUser::getFieldsById($userid,'is_coupons');
            $data['is_coupons'] = 0;
            if($is_coupons == 1 || !$coupons || $coupons == 0){
                $data['is_coupons'] = 1;
            }
            /*访问量添加*/
            if($userid){
                //增加访问量
                $isCount = storeVisit::where('uid',$userid)->whereDate('created_at',date('Y-m-d'))->lockForUpdate()->first();
                if($isCount){
                    $count = $isCount['count'] +1;
                    storeVisit::where('id',$isCount['id'])->update([
                        'count'=>$count,
                        'updated_at'=>date('Y-m-d H:i:s')
                    ]);
                }else{
                    storeVisit::insert([
                        'uid'=>$userid,
                        'count'=>1,
                        'created_at'=>date('Y-m-d H:i:s')
                    ]);
                }
            }
            return response(['code' => 200, 'msg' => 'success','data'=>$data]);
        } catch (Exception $exception) {
            return response(['code' => 400, 'msg' => $exception->getLine().'-'.$exception->getMessage()]);
        }
    }
    /**
     * 活动产品列表
     * @response  {}
     * @response  200 {"msg":"wrong msg"}
     */
    public function activityList(Request $request)
    {
        try {
            $couponid=$request->couponissue_id;
            $order = $request->order?:'complex';
            $dir = $request->dir?:'asc';
            if(!$couponid)return response(['code' => 400, 'msg' => '参数错误']);
            $data = storeCouponIssue::getCouponProList($couponid,$order,$dir);
            return response(['code' => 200, 'msg' => 'success','data'=>$data]);
        } catch (Exception $exception) {
            return response(['code' => 400, 'msg' => $exception->getLine().'-'.$exception->getMessage()]);
        }
    }
    /**
     * 优惠券详情
     * @response  {}
     * @response  200 {"msg":"wrong msg"}
     */
    public function activityInfo(Request $request)
    {
        try {
            $couponid=$request->couponissue_id;
            if(!$couponid)return response(['code' => 400, 'msg' => '参数错误']);
            $couponInfo = storeCouponIssue::getRowByid($couponid);
            if(!$couponInfo)return response(['code' => 400, 'msg' => '优惠券不存在或已过期']);
            $data['couponInfo'] = $couponInfo;
            $data['couponInfo']['sales'] = storeCouponUser::salseCoupon($couponid);
            $data['productlist'] = storeCouponIssue::getHomeCouponProList($couponid,'',2);
            $wechat_link = systemConfig::where('id',1)->value('kefu_link');
            $data['wechat_link'] =$wechat_link;
            return response(['code' => 200, 'msg' => 'success','data'=>$data]);
        } catch (Exception $exception) {
            return response(['code' => 400, 'msg' => $exception->getLine().'-'.$exception->getMessage()]);
        }
    }
    /**
     * 领取新人优惠券
     * @response  {}
     * @response  200 {"msg":"wrong msg"}
     */
    public function getNewcomerCoupon(Request $request)
    {
        try {
            $userid = $request->wechat_user_id;
            $coupons = systemConfig::where('id',1)->value('wechat_first_coupon');
            if(!$coupons || $coupons == 0)return response(['code' => 400, 'msg' => '无优惠券']);
            $is_coupons = WechatUser::getFieldsById($userid,'is_coupons');
            if($is_coupons >0)return response(['code' => 400, 'msg' => '已领取']);
            $couponArr = explode(',',$coupons);
            if($couponArr){
                $i = 0;
                foreach ($couponArr as $val){
                    $couponInfo = storeCoupon::where('id',$val)->first();
                    if(!$couponInfo)continue;
                    /*有效期*/
                    $time = date('Y-m-d H:i:s');
                    if($couponInfo->use_status == 1){
                        if($couponInfo->coupon_end < $time){
                            continue;
                        }
                    }
                    storeCouponUser::createNewRow($userid,$val,$couponInfo,3);
                    $i++;
                }
                /*设置用户新人优惠券已领取*/
                WechatUser::where('id',$userid)->update([
                    'is_coupons'=>1,
                    'updated_at'=>date('Y-m-d H:i:s'),
                ]);
                return response(['code' => 200, 'msg' => 'success','data'=>$i]);
            }
            return response(['code' => 400, 'msg' => '无优惠券']);
        } catch (Exception $exception) {
            return response(['code' => 400, 'msg' => $exception->getLine().'-'.$exception->getMessage()]);
        }
    }
    /**
     * 世界
     * @response  {}
     * @response  200 {"msg":"wrong msg"}
     */
    public function world(Request $request)
    {
        try {
            $data = storeCategory::getContentList();
            return response(['code' => 200, 'msg' => 'success','data'=>$data]);
        } catch (Exception $exception) {
            return response(['code' => 400, 'msg' => $exception->getLine().'-'.$exception->getMessage()]);
        }
    }
    /**
     * 不同模块下的产品
     * @bodyParam module int required 1
     * @response  {}
     * @response  200 {"msg":"wrong msg"}
     */
    public function moduleProduct(Request $request)
    {
        try {
            $userid = $request->wechat_user_id;
            $module = $request->module?:1;
            $order = $request->order?:'complex';
            $dir = $request->dir?:'asc';
            $data = storeProduct::getListByModule($module,$order,$dir);
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
     * 世界页分类下产品
     * @bodyParam catid int required 1
     * @response  {}
     * @response  200 {"msg":"wrong msg"}
     */
    public function worldProduct(Request $request)
    {
        try {
            $catid = $request->catid;
            $limit = $request->limit?:15;
            if(!$catid)return response(['code' => 400, 'msg' => '传递参数错误']);
            $data = storeProduct::getListByCatId($catid,$limit);
            return response(['code' => 200, 'msg' => 'success','data'=>$data]);
        } catch (Exception $exception) {
            return response(['code' => 400, 'msg' => $exception->getLine().'-'.$exception->getMessage()]);
        }
    }
    /**
     * 商品搜索页面
     * @response  {}
     * @response  200 {"msg":"wrong msg"}
     */
    public function searchPage(Request $request)
    {
        try {
            $userid = $request->wechat_user_id;
            $data = [];
            $data['recentSearch'] = storeSearch::getRecentSearch($userid);
            //$data['hotSearch'] = storeSearch::getHotSearch();
            $data['hotSearch'] = storeHotKeyword::getHotSearch();
            return response(['code' => 200, 'msg' => 'success','data'=>$data]);
        } catch (Exception $exception) {
            return response(['code' => 400, 'msg' => $exception->getLine().'-'.$exception->getMessage()]);
        }
    }
    /**
     * 商品搜索
     * @bodyParam catid int required 1
     * @response  {}
     * @response  200 {"msg":"wrong msg"}
     */
    public function searchProduct(Request $request)
    {
        try {
            $keyword = $request->keyword;
            $limit = $request->limit?:15;
            $userid = $request->wechat_user_id;
            if(!$keyword)return response(['code' => 400, 'msg' => '传递参数错误']);
            $data = storeProduct::getListByKeyword($keyword,$limit);
            /*添加入库*/
            storeSearch::insertKeyword($keyword,$userid);
            return response(['code' => 200, 'msg' => 'success','data'=>$data]);
        } catch (Exception $exception) {
            return response(['code' => 400, 'msg' => $exception->getLine().'-'.$exception->getMessage()]);
        }
    } /**
     * 商品搜索
     * @bodyParam catid int required 1
     * @response  {}
     * @response  200 {"msg":"wrong msg"}
     */
    public function deleteSearchKey(Request $request)
    {
        try {
            $userid = $request->wechat_user_id;
            storeSearch::deleteSearch($userid);
            return response(['code' => 200, 'msg' => 'success']);
        } catch (Exception $exception) {
            return response(['code' => 400, 'msg' => $exception->getLine().'-'.$exception->getMessage()]);
        }
    }
    /**
     * 视频
     * @response  {}
     * @response  200 {"msg":"wrong msg"}
     */
    public function video(Request $request)
    {
        try {
            $userid = $request->wechat_user_id;
            $data = storeVideo::getList($userid);
            return response(['code' => 200, 'msg' => 'success','data'=>$data]);
        } catch (Exception $exception) {
            return response(['code' => 400, 'msg' => $exception->getLine().'-'.$exception->getMessage()]);
        }
    }
    /**
     * 添加视频浏览记录
     * @response  {}
     * @response  200 {"msg":"wrong msg"}
     */
    public function videoVisit(Request $request)
    {
        try {
            $userid = $request->wechat_user_id;
            $videoid = $request->videoid;
            if(!$videoid)return response(['code' => 400, 'msg' => '传递参数错误']);
            userVisit::addVisit($userid,$videoid,2);
            return response(['code' => 200, 'msg' => 'success']);
        } catch (Exception $exception) {
            return response(['code' => 400, 'msg' => $exception->getLine().'-'.$exception->getMessage()]);
        }
    }
    /**
     * 案例分类
     * @response  {}
     * @response  200 {"msg":"wrong msg"}
     */
    public function caseCategory(Request $request)
    {
        try {
            $data = storeModel::getList();
            return response(['code' => 200, 'msg' => 'success','data'=>$data]);
        } catch (Exception $exception) {
            return response(['code' => 400, 'msg' => $exception->getLine().'-'.$exception->getMessage()]);
        }
    }
    /**
     * 案例产品
     * @bodyParam catid int required 1
     * @bodyParam userid int required 1
     * @response  {}
     * @response  200 {"msg":"wrong msg"}
     */
    public function caseProduct(Request $request)
    {
        try {
            $userid = $request->wechat_user_id;
            $catid = $request->catid;
            $limit = $request->limit?:15;
            if(!$catid)return response(['code' => 400, 'msg' => '传递参数错误']);
            $data = storeCase::getListByCatId($catid,$userid,$limit);
            return response(['code' => 200, 'msg' => 'success','data'=>$data]);
        } catch (Exception $exception) {
            return response(['code' => 400, 'msg' => $exception->getLine().'-'.$exception->getMessage()]);
        }
    }
    /**
     * 案例详情
     * @bodyParam catid int required 1
     * @bodyParam userid int required 1
     * @response  {}
     * @response  200 {"msg":"wrong msg"}
     */
    public function caseInfo(Request $request)
    {
        try {
            $userid = $request->wechat_user_id;
            $caseid = $request->caseid;
            if(!$caseid)return response(['code' => 400, 'msg' => '传递参数错误']);
            $data = storeCase::getCaseById($caseid,$userid);
            /*添加浏览记录*/
            userVisit::addVisit($userid,$caseid,3);
            return response(['code' => 200, 'msg' => 'success','data'=>$data]);
        } catch (Exception $exception) {
            return response(['code' => 400, 'msg' => $exception->getLine().'-'.$exception->getMessage()]);
        }
    }
    /**
     * 优惠券领取列表
     * @bodyParam catid int required 1
     * @bodyParam userid int required 1
     * @response  {}
     * @response  200 {"msg":"wrong msg"}
     */
    public function couponsList(Request $request)
    {
        try {
            $userid = $request->wechat_user_id;
            $data = storeCouponIssue::getCounponList($userid);
            return response(['code' => 200, 'msg' => 'success','data'=>$data]);
        } catch (Exception $exception) {
            return response(['code' => 400, 'msg' => $exception->getLine().'-'.$exception->getMessage()]);
        }
    }
    /**
     * 优惠券领取
     * @bodyParam catid int required 1
     * @bodyParam couponid int required 1
     * @response  {}
     * @response  200 {"msg":"wrong msg"}
     */
    public function couponsReceive(Request $request)
    {
        try {
            $userid = $request->wechat_user_id;
            $couponId = $request->couponid;
            if(!$couponId || !is_numeric($couponId)) return response(['code' => 400, 'msg' => '传递参数错误']);
            $time = date('Y-m-d H:i:s',time());
            $issueCouponInfo = storeCouponIssue::getRowByid($couponId);
            if(!$issueCouponInfo)return response(['code' => 400, 'msg' => '领取的优惠劵已领完或已过期!']);
            if(storeCouponUser::isUsedCoupon($userid,$couponId) > 0)return response(['code' => 400, 'msg' => '已领取过该优惠劵!']);
            DB::beginTransaction();
            if($issueCouponInfo['is_permanent']==0 && $issueCouponInfo['remain_count'] ==0)
                return response(['code' => 400, 'msg' => '该优惠劵已领完!']);
            $couponInfo = storeCoupon::getRowById($issueCouponInfo['cid']);
            $res = storeCouponUser::createNewRow($userid,$issueCouponInfo['cid'],$couponInfo,1,$couponId);
            if($res){
                /*发布的优惠券剩余减1*/
                storeCouponIssue::updateCount($couponId);
                DB::commit();
                return response(['code' => 200, 'msg' => 'success']);
            }else{
                DB::rollBack();
                return response(['code' => 400, 'msg' => '领取失败']);
            }
        } catch (Exception $exception) {
            DB::rollBack();
            return response(['code' => 400, 'msg' => $exception->getLine().'-'.$exception->getMessage()]);
        }
    }
    /**
     * 产品详情
     * @bodyParam proid int required 1
     * @response  {}
     * @response  200 {"msg":"wrong msg"}
     */
    public function storeInfo(Request $request)
    {
        try {
            $userid = $request->wechat_user_id;
            $proid = $request->proid;
            if(!$proid)return response(['code' => 400, 'msg' => '传递参数错误']);
            $data = storeProduct::getProById($proid,'*');
            if(!$data)return response(['code' => 400, 'msg' => '产品已售罄或不存在']);
            //访问量+1
            $browerCount = $data->browse + 1;
            storeProduct::where('id', $proid)->update([
                'browse' => $browerCount,
                'updated_at'   => date('Y-m-d H:i:s'),
            ]);
            /*添加浏览记录*/
            userVisit::addVisit($userid,$proid,1);
            /*轮播图*/
            $imgDetails = $data->slider_image;
            if (is_array($imgDetails)) {
                foreach ($imgDetails as $key => $value) {
                    $imgDetails[$key] = Config::get('app.oss_cdn').$value;
                    //$imgDetails[$key] = asset('upload/'.$value);;
                }
            }
            $data['slider_image'] = array_values($imgDetails);
            $asso_service_id = $data['asso_service_id'];
            $goodsku = json_decode($data['good_sku'],true);
            if($goodsku['type'] == 'many'){
                foreach ($goodsku['sku'] as $sk=> &$sval){
                    $sval['asso_service_info'] = [];
                    if(isset($sval['asso_service_id']) && $sval['asso_service_id'] >0){
                        $asso_service_id = $sval['asso_service_id'];
                        $sval['asso_service_info'] = storeProduct::getProById($sval['asso_service_id']);
                    }
                }
            }
            $data['good_sku'] = $goodsku;

            /*服务包*/
            if($asso_service_id > 0){
                $data['asso_service_info'] = storeProduct::getProById($asso_service_id);
            }
            $data['asso_service_id'] = $asso_service_id;
            /*地址*/
            $defaultaddr = userAddress::getDefaultAddr($userid);
            $data['defaultAddr'] = $defaultaddr;
            /*默认地址是否提供服务*/
            $data['is_service'] = 0;
            if($defaultaddr){
                $isset = userAddress::inUsersArea($defaultaddr->id);
                if($isset > 0)$data['is_service'] = 1;
            }
            /*是否收藏*/
            $data['is_collect'] = 0;
            $count = userRelation::where('uid',$userid)->where('type',1)->where('link_id',$proid)->count();
            if($count>0){
                $data['is_collect'] = 1;
            }
            $wechat_link = systemConfig::where('id',1)->value('kefu_link');
            $data['wechat_link'] =$wechat_link;
            return response(['code' => 200, 'msg' => 'success','data'=>$data]);
        } catch (Exception $exception) {
            return response(['code' => 400, 'msg' => $exception->getLine().'-'.$exception->getMessage()]);
        }
    }
    /**
     * 所选地址是否提供服务
     * @bodyParam addrid int required 1
     * @response  {}
     * @response  200 {"msg":"wrong msg"}
     */
    public function isService(Request $request)
    {
        try {
            $addrid = $request->addrid;
            if(!$addrid)return response(['code' => 400, 'msg' => '传递参数错误']);
            /*默认地址是否提供服务*/
            $is_service = 0;
            if($addrid){
                $isset = userAddress::inUsersArea($addrid);
                if($isset > 0)$is_service = 1;
            }
            return response(['code' => 200, 'msg' => 'success','is_service'=>$is_service]);
        } catch (Exception $exception) {
            return response(['code' => 400, 'msg' => $exception->getLine().'-'.$exception->getMessage()]);
        }
    }
    /**
     * 收藏
     * @bodyParam proid int required 1
     * @response  {}
     * @response  200 {"msg":"wrong msg"}
     */
    public function Collect(Request $request)
    {
        try {
            $userid = $request->wechat_user_id;
            $proid = $request->proid;
            $type = $request->type;
            if(!$proid || !$type)return response(['code' => 400, 'msg' => '传递参数错误']);
            $count = userRelation::where('uid',$userid)->where('link_id',$proid)->where('type',$type)->count();
            if($count > 0)return response(['code' => 400, 'msg' => '已收藏']);
            $collect = userRelation::addCollect($userid,$proid,$type);
            if($collect){
                return response(['code' => 200, 'msg' => 'success']);
            }else{
                return response(['code' => 400, 'msg' => '收藏失败']);
            }
        } catch (Exception $exception) {
            return response(['code' => 400, 'msg' => $exception->getLine().'-'.$exception->getMessage()]);
        }
    }
    /**
     * 取消收藏
     * @bodyParam proid int required 1
     * @response  {}
     * @response  200 {"msg":"wrong msg"}
     */
    public function cancelCollect(Request $request)
    {
        try {
            $userid = $request->wechat_user_id;
            $proid = $request->proid;
            $type = $request->type;
            if(!$proid || !$type)return response(['code' => 400, 'msg' => '传递参数错误']);
            $count = userRelation::where('uid',$userid)->where('link_id',$proid)->where('type',$type)->count();
            if($count == 0)return response(['code' => 400, 'msg' => '暂未收藏']);
            $collect = userRelation::cancelCollect($userid,$proid,$type);
            if($collect){
                return response(['code' => 200, 'msg' => 'success']);
            }else{
                return response(['code' => 400, 'msg' => '取消失败']);
            }
        } catch (Exception $exception) {
            return response(['code' => 400, 'msg' => $exception->getLine().'-'.$exception->getMessage()]);
        }
    }
    /**
     * 加入购物车
     * @bodyParam proid int required 1
     * @response  {}
     * @response  200 {"msg":"wrong msg"}
     */
    public function setCart(Request $request)
    {
        try {
            $userid = $request->wechat_user_id;
            $proid = $request->proid;
            $cartNum = $request->cartNum?:1;
            $attrid = $request->attrid?:0;
            $serviceId = $request->serviceId?:0;
            if(!$proid || !is_numeric($proid))return response(['code' => 400, 'msg' => '传递参数错误']);
            $row = storeProduct::getProById($proid);
            if(!$row)return response(['code' => 400, 'msg' => '该产品已下架或删除']);
            if(storeProduct::getProductStock($proid,$attrid) < $cartNum)return response(['code' => 400, 'msg' => '该产品库存不足']);
            $res = storeCart::setCart($userid,$proid,$cartNum,$attrid,$serviceId,0,'cart');
            if($res){
                return response(['code' => 200, 'msg' => 'success','cartId'=>$res]);
            }else{
                return response(['code' => 400, 'msg' => '加入购物车失败!']);
            }
        } catch (Exception $exception) {
            return response(['code' => 400, 'msg' => $exception->getLine().'-'.$exception->getMessage()]);
        }
    }
    /**
     * 购物车列表
     * @bodyParam proid int required 1
     * @response  {}
     * @response  200 {"msg":"wrong msg"}
     */
    public function getCartList(Request $request)
    {
        try {
            $userid = $request->wechat_user_id;
            $data = storeCart::getUserProductCartList($userid);
            return response(['code' => 200, 'msg' => 'success','data'=>$data]);
        } catch (Exception $exception) {
            return response(['code' => 400, 'msg' => $exception->getLine().'-'.$exception->getMessage()]);
        }
    }
    /**
     * 修改购物车数量
     * @bodyParam proid int required 1
     * @response  {}
     * @response  200 {"msg":"wrong msg"}
     */
    public function changeCartNum(Request $request)
    {
        try {
            $userid = $request->wechat_user_id;
            $cartid = $request->cartid;
            $cartNum = $request->cartNum?:1;
            if(!$cartid || !is_numeric($cartid) || !is_numeric($cartNum) || !is_numeric($cartNum))return response(['code' => 400, 'msg' => '传递参数错误']);
            $res = storeCart::changeUserCartNum($cartid,$cartNum,$userid);
            if($res){
                return response(['code' => 200, 'msg' => 'success']);
            }else{
                return response(['code' => 400, 'msg' => '修改失败!']);
            }
        } catch (Exception $exception) {
            return response(['code' => 400, 'msg' => $exception->getLine().'-'.$exception->getMessage()]);
        }
    }
    /**
     * 修改购物车数量
     * @bodyParam cartid int required 1
     * @response  {}
     * @response  200 {"msg":"wrong msg"}
     */
    public function removeCart(Request $request)
    {
        try {
            $userid = $request->wechat_user_id;
            $cartid = $request->cartid;
            if(!$cartid || !is_numeric($cartid))return response(['code' => 400, 'msg' => '传递参数错误']);
            $res = storeCart::removeUserCart($cartid,$userid);
            if($res){
                return response(['code' => 200, 'msg' => 'success']);
            }else{
                return response(['code' => 400, 'msg' => '删除失败!']);
            }
        } catch (Exception $exception) {
            return response(['code' => 400, 'msg' => $exception->getLine().'-'.$exception->getMessage()]);
        }
    }
    /**
     * 清空购物车
     * @bodyParam proid int required 1
     * @response  {}
     * @response  200 {"msg":"wrong msg"}
     */
    public function removeCartAll(Request $request)
    {
        try {
            $userid = $request->wechat_user_id;
            $res = storeCart::removeUserAllCart($userid);
            if($res){
                return response(['code' => 200, 'msg' => 'success']);
            }else{
                return response(['code' => 400, 'msg' => '删除失败!']);
            }
        } catch (Exception $exception) {
            return response(['code' => 400, 'msg' => $exception->getLine().'-'.$exception->getMessage()]);
        }
    }
    /**
     * 添加服务
     * @bodyParam catidid int required 1
     * @response  {}
     * @response  200 {"msg":"wrong msg"}
     */
    public function AddService(Request $request)
    {
        try {
            $userid = $request->wechat_user_id;
            $cartid = $request->cartid;
            $cartInfo = storeCart::where('id',$cartid)->first();
            if(!$cartInfo)return response(['code' => 400, 'msg' => '数据不存在！']);
            $field = ['id','product_name','image','stock','price','ot_price','good_sku','asso_service_id'];
            $proinfo = storeProduct::getProById($cartInfo->product_id,$field);
            if(!$proinfo)return response(['code' => 400, 'msg' => '产品不存在或已下架！']);
            $asso_service_id = $proinfo->asso_service_id;
            $goodsku = json_decode($proinfo['good_sku'],true);
            if($goodsku['type'] == 'many' && $cartInfo['attr_id']>0){
                foreach ($goodsku['sku'] as $sk=> $sval){
                    if($cartInfo['attr_id'] == $sval['id'] && $sval['asso_service_id']>0){
                        $asso_service_id = $sval['asso_service_id'];
                    }
                }
            }
            if($asso_service_id == 0)return response(['code' => 400, 'msg' => '无需添加服务！']);
            $serviceInfo = storeProduct::getProById($asso_service_id);
            if(!$serviceInfo)return response(['code' => 400, 'msg' => '服务不存在，无需添加服务！']);
            $res = storeCart::addServiceforCart($cartid,$userid,$asso_service_id);
            if($res){
                return response(['code' => 200, 'msg' => 'success','serviceInfo'=>$serviceInfo]);
            }else{
                return response(['code' => 400, 'msg' => '删除失败!']);
            }
        } catch (Exception $exception) {
            return response(['code' => 400, 'msg' => $exception->getLine().'-'.$exception->getMessage()]);
        }
    }
    /**
     * 立即购买
     * @bodyParam proid int required 1
     * @response  {}
     * @response  200 {"msg":"wrong msg"}
     */
    public function nowBuy(Request $request)
    {
        try {
            $userid = $request->wechat_user_id;
            $proid = $request->proid;
            $cartNum = $request->cartNum?:1;
            $attrid = $request->attrid?:0;
            $serviceId = $request->serviceId?:0;
            $addrId = $request->addressId?:0;
            if(!$proid || !is_numeric($proid))return response(['code' => 400, 'msg' => '传递参数错误']);
            $row = storeProduct::getProById($proid);
            if(!$row)return response(['code' => 400, 'msg' => '该产品已下架或删除']);
            if(storeProduct::getProductStock($proid,$attrid) < $cartNum)return response(['code' => 400, 'msg' => '该产品库存不足']);
            $is_service = 1;
            if($serviceId > 0 && $addrId){
                /*所选地址是否提供服务*/
                $isset = userAddress::inUsersArea($addrId);
                if($isset == 0)$is_service = 0;
            }
            if($is_service == 0)return response(['code' => 400, 'msg' => '所选区域暂不提供上门服务','is_service'=>$is_service]);
            $res = storeCart::setCart($userid,$proid,$cartNum,$attrid,$serviceId,1,'now');
            if($res){
                return response(['code' => 200, 'msg' => 'success','cartId'=>$res,'addrId'=>$addrId]);
            }else{
                return response(['code' => 400, 'msg' => '加入购物车失败!']);
            }
        } catch (Exception $exception) {
            return response(['code' => 400, 'msg' => $exception->getLine().'-'.$exception->getMessage()]);
        }
    }
    /**
     * 订单页面
    * @bodyParam cartId int required 1
     * @response  {}
     * @response  200 {"msg":"wrong msg"}
     */
    public function confirmOrder(Request $request)
    {
        try {
            $userid = $request->wechat_user_id;
            $cartIds = $request->cartIds;
            $addrId = $request->addressId?:0;
            if (!is_string($cartIds) || !$cartIds)return response(['code' => 400, 'msg' => '请提交购买的商品1']);
            $cartIds = explode(',',$cartIds);
            $cartGroup = storeCart::getUserProductCartList($userid, $cartIds,1);
            if (count($cartGroup['invalid'])) return response(['code' => 400, 'msg' => '存在失效产品']);
            if (!$cartGroup['valid']) return response(['code' => 400, 'msg' => '请提交购买的商品2']);
            $cartInfo = $cartGroup['valid'];
            $is_service = 0;
            if($addrId == 0){
                $defaultId = userAddress::where('uid',$userid)->where('is_default',1)->value('id');
                if($defaultId)$addrId = $defaultId;
            }
            $addressRow = [];
            /*是否需要服务*/
            $needservice = 0;
            foreach ($cartInfo as $cart){
                if($cart['product_setvice_id'] > 0 || $cart['productInfo']['type'] == 2){
                    $needservice = 1;
                    break;
                }
            }
            if($addrId > 0){
                $addressRow = userAddress::getAddrById($userid,$addrId);
                if($needservice == 1){
                    /*所选地址是否提供服务*/
                    $isset = userAddress::inUsersArea($addrId);
                    $is_service = $isset;
                }
            }
            //if($needservice == 1 && $is_service == 0)return response(['code' => 400, 'msg' => '所选区域存在不提供上门服务的产品，请重新选择','is_service'=>$is_service]);

            $priceGroup = storeOrder::getOrderPriceGroup($cartInfo,$addrId);

            /*优惠券*/
            $usableCoupon = storeCouponUser::beUsableCoupon($userid, $priceGroup['productPrice'],$cartInfo);
            $data['usableCoupon'] = $usableCoupon;
            $data['cartInfo'] = $cartInfo;
            $data['need_service'] = $needservice;
            $data['priceGroup'] = $priceGroup;
            $data['userAddress'] = $addressRow;
            $data['is_service'] = $is_service;
            $shop_intro = systemConfig::where('id',1)->value('shop_intro');
            $data['shop_intro'] = $shop_intro;
            $other = [];
            $other['is_service'] = $is_service;
            $other['need_service'] = $needservice;
            $key = storeOrder::cacheOrderInfo($userid,  $cartInfo, $priceGroup,$other);
            $service_day = systemConfig::where('id',1)->value('service_day');
            $data['serviceday'] =$service_day;
            $data['starttime'] =date("Y-m-d H:i:s",strtotime($service_day." day"));;
            $data['orderKey'] =$key;
            return response(['code' => 200, 'msg' => 'success','data'=>$data]);
        } catch (Exception $exception) {
            return response(['code' => 400, 'msg' => $exception->getLine().'-'.$exception->getMessage()]);
        }
    }
    /**
     * 代金券订单页面
    * @bodyParam cartId int required 1
     * @response  {}
     * @response  200 {"msg":"wrong msg"}
     */
    public function couponConfirmOrder(Request $request)
    {
        try {
            $userid = $request->wechat_user_id;
            $couponissue_id = $request->couponissue_id;
            $addrId = $request->addressId?:0;
            if (!$couponissue_id)return response(['code' => 400, 'msg' => '请提交购买的代金券']);
            $couponInfo = storeCouponIssue::getRowByid($couponissue_id);
            if(!$couponInfo)return response(['code' => 400, 'msg' => '优惠券不存在或已过期']);
            if($addrId == 0){
                $defaultId = userAddress::where('uid',$userid)->where('is_default',1)->value('id');
                if($defaultId)$addrId = $defaultId;
            }
            $addressRow = [];
            if($addrId > 0){
                $addressRow = userAddress::getAddrById($userid,$addrId);
            }
            $data['usableCoupon'] = [];
            $data['cartInfo'] = $couponInfo;
            $data['userAddress'] = $addressRow;
            $data['shop_intro'] = $couponInfo['content'];
            $other = [];
            $key = storeOrder::cacheOrderInfo($userid,  $couponInfo, [],$other);
            $data['orderKey'] =$key;
            return response(['code' => 200, 'msg' => 'success','data'=>$data]);
        } catch (Exception $exception) {
            return response(['code' => 400, 'msg' => $exception->getLine().'-'.$exception->getMessage()]);
        }
    }

    /**
     * 地址变化 判断服务是否提供
    * @bodyParam cartId int required 1
     * @response  {}
     * @response  200 {"msg":"wrong msg"}
     */
    public function orderCanService(Request $request)
    {
        try {
            $userid = $request->wechat_user_id;
            $cartIds = $request->cartIds;
            $addrId = $request->addressId;
            if (!is_string($cartIds) || !$cartIds)return response(['code' => 400, 'msg' => '请提交购买的商品1']);
            $cartIds = explode(',',$cartIds);
            $cartGroup = storeCart::getUserProductCartList($userid, $cartIds,1);
            if (count($cartGroup['invalid'])) return response(['code' => 400, 'msg' => '存在失效产品']);
            if (!$cartGroup['valid']) return response(['code' => 400, 'msg' => '请提交购买的商品2']);
            $cartInfo = $cartGroup['valid'];
            $is_service = 0;
            /*是否需要服务*/
            $needservice = 0;
            foreach ($cartInfo as $cart){
                if($cart['product_setvice_id'] > 0 || $cart['productInfo']['type'] == 2){
                    $needservice = 1;
                    break;
                }
            }
            if($addrId > 0 && $needservice == 1){
                $isset = userAddress::inUsersArea($addrId);
                $is_service = $isset;
            }
            $data['is_service'] =$is_service;
            if($needservice == 1 && $is_service == 0)return response(['code' => 400, 'msg' => '所选区域存在不提供上门服务的产品，请重新选择','data'=>$data]);
            if($is_service == 0){
                $is_service = userAddress::hasUsersArea($addrId);
                if($is_service == 0){
                    return response(['code' => 400, 'msg' => '所选区域存在不提供上门服务的产品，请重新选择','data'=>$data]);
                }
            }
            $priceGroup = storeOrder::getOrderPriceGroup($cartInfo,$addrId);
            $other = [];
            $other['is_service'] = $is_service;

            $other['need_service'] = $needservice;
            $key = storeOrder::cacheOrderInfo($userid,  $cartInfo, $priceGroup,$other);
            $data['orderKey'] =$key;
            return response(['code' => 200, 'msg' => 'success','data'=>$data]);
        } catch (Exception $exception) {
            return response(['code' => 400, 'msg' => $exception->getLine().'-'.$exception->getMessage()]);
        }
    }
    /**
     * 数量改变获得优惠券
    * @bodyParam cartId int required 1
     * @response  {}
     * @response  200 {"msg":"wrong msg"}
     */
    public function orderGetUpdate(Request $request)
    {
        try {
            $userid = $request->wechat_user_id;
            $cartIds = $request->cartIds;
            $addrId = $request->addressId?:0;
            if (!is_string($cartIds) || !$cartIds)return response(['code' => 400, 'msg' => '请提交购买的商品1']);
            $cartIds = explode(',',$cartIds);
            $cartGroup = storeCart::getUserProductCartList($userid, $cartIds,1);
            if (count($cartGroup['invalid'])) return response(['code' => 400, 'msg' => '存在失效产品']);
            if (!$cartGroup['valid']) return response(['code' => 400, 'msg' => '请提交购买的商品2']);
            $cartInfo = $cartGroup['valid'];
            $priceGroup = storeOrder::getOrderPriceGroup($cartInfo,$addrId);
            $is_service = 0;
            /*是否需要服务*/
            $needservice = 0;
            foreach ($cartInfo as $cart){
                if($cart['product_setvice_id'] > 0 || $cart['productInfo']['type'] == 2){
                    $needservice = 1;
                    break;
                }
            }
            if($addrId > 0 && $needservice == 1){
                $is_service = userAddress::inUsersArea($addrId);
            }
            $data['is_service'] =$is_service;
            /*优惠券*/
            $usableCoupon = storeCouponUser::beUsableCoupon($userid, $priceGroup['productPrice'],$cartInfo);
            $data['usableCoupon'] = $usableCoupon;
            $data['priceGroup'] = $priceGroup;
            $other = [];
            $other['is_service'] = $is_service;

            $other['need_service'] = $needservice;
            $key = storeOrder::cacheOrderInfo($userid,  $cartInfo, $priceGroup,$other);
            $data['orderKey'] =$key;
            return response(['code' => 200, 'msg' => 'success','data'=>$data]);
        } catch (Exception $exception) {
            return response(['code' => 400, 'msg' => $exception->getLine().'-'.$exception->getMessage()]);
        }
    }
    /**
     * 代金券提交订单
    * @bodyParam cartId int required 1
     * @response  {}
     * @response  200 {"msg":"wrong msg"}
     */
    public function couponSubmitOrder(Request $request)
    {
        try {
            $userid = $request->wechat_user_id;
            $orderKey = $request->orderKey;
            $number = $request->number?:1;
            $addrId = 0;
            $couponid = 0;
            if (!$orderKey)return response(['code' => 400, 'msg' => '参数错误']);
            $cacheGroup = storeOrder::getCacheOrderInfo($userid,$orderKey);
            if(!$cacheGroup)return response(['code' => 400, 'msg' => '订单已过期,请刷新当前页面!']);
            $cartInfo = $cacheGroup['cartInfo'];
            /*是否超过限量购买*/
            if($cartInfo['is_permanent']>0){
                $userCount = storeCouponUser::isBuyCoupon($userid,$cartInfo['id']);
                $total = $userCount + $number;
                if($total > $cartInfo['is_permanent']){
                    return response(['code' => 400, 'msg' => '该代金券购买次数已用完，不能继续购买!']);
                }
            }
            $cartInfo['total_num'] = $number;
            $cartInfo['pro_type'] = 2;
            $payPrice =(float)bcmul($cartInfo['price'],$number,2);
            /*优惠券*/
            DB::beginTransaction();
            /*下单*/
            $orderNo = storeOrder::getNewOrderId();
            $other['operate_id'] = 0; //运营中心管理员id
            $other['product_price'] = $payPrice; //产品总价
            $other['pro_type'] = 2; //代金券产品
            $orderId = storeOrder::createCouponOrder($orderNo, $userid,$cartInfo,$payPrice,$other);
            if($orderId){
                storeOrder::clearCacheOrderInfo($userid,$orderKey);
                $orderInfo = storeOrder::where('id',$orderId)->first();
                if(!$orderInfo) return response(['code' => 400, 'msg' => '支付订单不存在111!']);
                if($orderInfo['paid']) return response(['code' => 400, 'msg' => '该订单已支付!']);
                if($payPrice == 0){
                    if (storeOrder::jsPayPrice($orderId,$cartInfo)){
                        DB::commit();
                        return response(['code' => 200, 'msg' => '支付成功']);
                    }else{
                        DB::rollBack();
                        return response(['code' => 400, 'msg' => '支付失败']);
                    }
                }else{
                    $sitename = systemConfig::where('id',1)->value('site_name');
                    $mch_id = WechatMerchant::where('id',1)->value('mch_id');
                    $data['mch_id'] = $mch_id;
                    $data['body'] = $cartInfo['title'];
                    $data['out_trade_no'] = $orderNo;
                    $data['total_fee'] = bcmul($payPrice,100);
                    $data['openid'] = WechatUser::getFieldsById($userid,'openid');
                    $data['pro_type'] = 2;//代金券产品
                    $data['link_order_id'] = $orderId;
                    $data['wechat_user_id'] = $userid;
                    //$data['cart_info'] = json_encode($cartInfo);
                    $result = OrderService::jsConfig($mch_id,'JSAPI',$data);
                    dispatch(new DelayOrder($orderId)); //30分钟后未支付取消订单
                    DB::commit();
                    return response(['code' => 200, 'msg' => '微信付款','data'=>$result]);
                }
            }
            DB::rollBack();
            return response(['code' => 400, 'msg' => '下单失败']);
        } catch (Exception $exception) {
            DB::rollBack();
            return response(['code' => 400, 'msg' => $exception->getLine().'-'.$exception->getMessage().'-'.$exception->getFile()]);
        }
    }
    /**
     * 提交订单
    * @bodyParam cartId int required 1
     * @response  {}
     * @response  200 {"msg":"wrong msg"}
     */
    public function submitOrder(Request $request)
    {
        try {
            $userid = $request->wechat_user_id;
            $orderKey = $request->orderKey;
            $addrId = $request->addressId;
            $remark = $request->remark;
            $couponid = $request->couponid?:0;
            $apointment_time = $request->apointment_time;
            if (!$orderKey || !$addrId)return response(['code' => 400, 'msg' => '参数错误']);
            $cacheGroup = storeOrder::getCacheOrderInfo($userid,$orderKey);
            if(!$cacheGroup)return response(['code' => 400, 'msg' => '订单已过期,请刷新当前页面!']);
            $addressRow = userAddress::getAddrById($userid,$addrId);
            if(!$addressRow)return response(['code' => 400, 'msg' => '地址选择有误!']);
            $cartInfo = $cacheGroup['cartInfo'];
            $priceGroup = $cacheGroup['priceGroup'];
            $payPrice = (float)$priceGroup['totalPrice'];
            $payPostage = $priceGroup['storePostage'];
            $payPrice =(float)bcadd($payPrice,$payPostage,2);
            /*是否需要服务*/
            $needservice = 0;
            foreach ($cartInfo as $cart){
                if($cart['product_setvice_id'] > 0 || $cart['productInfo']['type'] == 2){
                    $needservice = 1;
                    break;
                }
            }
            if($needservice == 1 && !$apointment_time)return response(['code' => 400, 'msg' => '请选择上门服务时间']);
            $is_service = userAddress::inUsersArea($addrId);
            if($needservice == 1 && $is_service == 0){
                return response(['code' => 400, 'msg' => '所选区域存在不提供上门服务的产品，请重新选择']);
            }
            if($is_service == 0){
                $is_service = userAddress::hasUsersArea($addrId);
                if($is_service == 0){
                    return response(['code' => 400, 'msg' => '所选区域存在不提供上门服务的产品，请重新选择']);
                }
            }
            /*优惠券*/
            DB::beginTransaction();
            $couponPrice = 0;
            if($couponid){
                $arrCoupon = explode(',',$couponid);
                $count = count($arrCoupon);
                $onetype = 0;
                $twotype = 0;
                foreach ($arrCoupon as $couid){
                    $couponInfo = storeCouponUser::getRowByid($couid);
                    if(!$couponInfo)return response(['code' => 400, 'msg' => '选择的优惠劵无效!']);
                    if($couponInfo['type'] == 1){
                        $onetype += 1;
                    }else{
                        $twotype += 1;
                    }
                }
                if($onetype>0 && $twotype>0){//不同类型的优惠券不能同时使用
                    return response(['code' => 400, 'msg' => '选择的优惠券不能同时使用!']);
                }
                foreach ($arrCoupon as $couid){
                    $couponInfo = storeCouponUser::getRowByid($couid);
                    $couponPrice += $couponInfo['coupon_price'];
                    /*设置优惠券使用*/
                    storeCouponUser::useCoupon($couid);
                }
                $payPrice =(float)bcsub($payPrice,$couponPrice,2);
            }
            /*下单*/
            $orderNo = storeOrder::getNewOrderId();
            $other['operate_id'] = $is_service; //运营中心管理员id
            $other['need_service'] = $needservice; //运营中心管理员id
            $other['product_price'] = $priceGroup['productPrice']; //产品总价
            $other['service_price'] = $priceGroup['servicePrice']; //服务总价
            $other['remark'] = $remark; //订单备注
            $orderId = storeOrder::createOrder($orderNo, $userid,$addressRow,$cartInfo,$priceGroup,$payPrice,$payPostage,$couponid,$couponPrice,$apointment_time,$other);
            if($orderId){
                storeOrder::clearCacheOrderInfo($userid,$orderKey);
                $orderInfo = storeOrder::where('id',$orderId)->first();
                if(!$orderInfo) return response(['code' => 400, 'msg' => '支付订单不存在111!']);
                if($orderInfo['paid']) return response(['code' => 400, 'msg' => '该订单已支付!']);
                if($payPrice == 0){
                    if (storeOrder::jsPayPrice($orderId,$cartInfo)){
                        DB::commit();
                        return response(['code' => 200, 'msg' => '支付成功']);
                    }else{
                        DB::rollBack();
                        return response(['code' => 400, 'msg' => '支付失败']);
                    }
                }else{
                    $sitename = systemConfig::where('id',1)->value('site_name');
                    $mch_id = WechatMerchant::where('id',1)->value('mch_id');
                    $data['mch_id'] = $mch_id;
                    $data['body'] = $sitename;
                    $data['out_trade_no'] = $orderNo;
                    $data['total_fee'] = bcmul($payPrice,100);
                    $data['openid'] = WechatUser::getFieldsById($userid,'openid');
                    $data['pro_type'] = 1;//购物车产品
                    $data['link_order_id'] = $orderId;
                    $data['wechat_user_id'] = $userid;
                    //$data['cart_info'] = json_encode($cartInfo);
                    $result = OrderService::jsConfig($mch_id,'JSAPI',$data);
                    dispatch(new DelayOrder($orderId)); //30分钟后未支付取消订单
                    DB::commit();
                    return response(['code' => 200, 'msg' => '微信付款','data'=>$result]);
                }
            }
            DB::rollBack();
            return response(['code' => 400, 'msg' => '下单失败']);
        } catch (Exception $exception) {
            DB::rollBack();
            return response(['code' => 400, 'msg' => $exception->getLine().'-'.$exception->getMessage().'-'.$exception->getFile()]);
        }
    }
    /**
     * 优惠券选择
    * @bodyParam cartId int required 1
     * @response  {}
     * @response  200 {"msg":"wrong msg"}
     */
    public function selectCoupon(Request $request)
    {
        try {
            $userid = $request->wechat_user_id;
            $cartIds = $request->cartIds;
            $couponid = $request->couponid;
            $addrId = $request->addressId?:0;
            if (!is_string($cartIds) || !$cartIds)return response(['code' => 400, 'msg' => '参数错误']);
            $cartIds = explode(',',$cartIds);
            $cartGroup = storeCart::getUserProductCartList($userid, $cartIds,1);
            if (count($cartGroup['invalid'])) return response(['code' => 400, 'msg' => '存在失效产品']);
            if (!$cartGroup['valid']) return response(['code' => 400, 'msg' => '请提交购买的商品2']);
            $cartInfo = $cartGroup['valid'];
            $priceGroup = storeOrder::getOrderPriceGroup($cartInfo,$addrId);
            $is_service = 0;
            /*是否需要服务*/
            $needservice = 0;
            foreach ($cartInfo as $cart){
                if($cart['product_setvice_id'] > 0 || $cart['productInfo']['type'] == 2){
                    $needservice = 1;
                    break;
                }
            }
            if($addrId > 0 && $needservice == 1){
                $is_service = userAddress::inUsersArea($addrId);
            }
            $data['is_service'] =$is_service;
            /*优惠券*/
            $couponPrice = 0;
            $mincouponPrice = 0;
            if($couponid){
                $arrCoupon = explode(',',$couponid);
                $count = count($arrCoupon);
                $onetype = 0;
                $twotype = 0;
                foreach ($arrCoupon as $couid){
                    $couponInfo = storeCouponUser::getRowByid($couid);
                    $usecount = $couponInfo['use_count'];
                    if(!$couponInfo)return response(['code' => 400, 'msg' => '选择的优惠劵无效!']);
                    if($couponInfo['type'] == 1){
                        $onetype += 1;
                    }else{
                        $twotype += 1;
                    }
                }
                if($onetype>0 && $twotype>0){//不同类型的优惠券不能同时使用
                    return response(['code' => 400, 'msg' => '选择的优惠券不能同时使用!']);
                }
                if($count > $usecount){
                    return response(['code' => 400, 'msg' => '该优惠券最多累计使用'.$usecount.'张!']);
                }
                foreach ($arrCoupon as $couid){
                    $couponInfo = storeCouponUser::getRowByid($couid);
                    $couponPrice += $couponInfo['coupon_price'];
                    $mincouponPrice += $couponInfo['use_min_price'];
                }
                if($count > 1 &&$priceGroup['productPrice'] < $mincouponPrice){
                    return response(['code' => 400, 'msg' => '选择的优惠券不能同时使用!']);
                }
                $remainPrice = $priceGroup['productPrice'] - $couponPrice;
                if($count > 1 && $remainPrice < 0){
                    return response(['code' => 400, 'msg' => '选择的优惠券不能同时使用!']);
                }
            }
            $couponList = storeCouponUser::beUsableCoupon($userid, $priceGroup['productPrice'],$cartInfo);
            $usableCoupon = storeCouponUser::canUseCoupon($couponList,$priceGroup['productPrice'],$couponid,$couponPrice,$mincouponPrice);
            $data['usableCoupon'] = $usableCoupon;
            $data['priceGroup'] = $priceGroup;
            $other = [];
            $other['is_service'] = $is_service;

            $other['need_service'] = $needservice;
            $key = storeOrder::cacheOrderInfo($userid,  $cartInfo, $priceGroup,$other);
            $data['orderKey'] =$key;
            return response(['code' => 200, 'msg' => 'success','data'=>$data]);
        } catch (Exception $exception) {
            return response(['code' => 400, 'msg' => $exception->getLine().'-'.$exception->getMessage()]);
        }
    }
    /**
     * 订单支付成功页面
    * @bodyParam cartId int required 1
     * @response  {}
     * @response  200 {"msg":"wrong msg"}
     */
    public function orderPayFinish(Request $request)
    {
        try {
            $userid = $request->wechat_user_id;
            $orderId = $request->orderid;
            if(!$orderId)return response(['code' => 400, 'msg' => '参数错误']);
            $orderInfo = storeOrder::where('id',$orderId)->where('paid',1)->first();
            if(!$orderInfo)return response(['code' => 400, 'msg' => '订单不存在或未支付成功']);
            $data = [];
            $data['order_no'] = $orderInfo['order_id'];
            $data['pay_time'] = $orderInfo['pay_time'];
            $data['created_at'] = $orderInfo['created_at']->format('Y-m-d H:i:s');
            return response(['code' => 200, 'msg' => 'success','data'=>$data]);
        } catch (Exception $exception) {
            DB::rollBack();
            return response(['code' => 400, 'msg' => $exception->getLine().'-'.$exception->getMessage()]);
        }
    }


}
