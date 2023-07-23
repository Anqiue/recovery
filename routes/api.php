<?php

use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\StoreApiController;
use App\Http\Controllers\Api\MasterApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::prefix('store')->group(function () {
    Route::post('index', [StoreApiController::class, 'index'])->middleware('refresh'); //首页
    Route::post('activity_list', [StoreApiController::class, 'activityList']); //活动产品列表
    Route::post('activity_info', [StoreApiController::class, 'activityInfo']); //优惠券详情
    Route::post('get_new_coupon', [StoreApiController::class, 'getNewcomerCoupon'])->middleware('refresh'); //领取新人优惠券
    Route::post('world', [StoreApiController::class, 'world']); //世界
    Route::post('worldProduct', [StoreApiController::class, 'worldProduct']); //分类下产品
    Route::post('module_product', [StoreApiController::class, 'moduleProduct']); //不同模块下的产品
    Route::post('search', [StoreApiController::class, 'searchPage'])->middleware('refresh'); //商品搜索页面
    Route::post('search_pro', [StoreApiController::class, 'searchProduct'])->middleware('refresh'); //商品搜索
    Route::post('delete_key', [StoreApiController::class, 'deleteSearchKey'])->middleware('refresh'); //删除搜索记录
    Route::post('video', [StoreApiController::class, 'video'])->middleware('refresh'); //视频
    Route::post('video_visit', [StoreApiController::class, 'videoVisit'])->middleware('refresh'); //视频浏览记录
    Route::post('case_category', [StoreApiController::class, 'caseCategory'])->middleware('refresh'); //案例
    Route::post('case_product', [StoreApiController::class, 'caseProduct'])->middleware('refresh'); //案例产品
    Route::post('caseinfo', [StoreApiController::class, 'caseInfo'])->middleware('refresh'); //案例详情
    Route::post('coupons_list', [StoreApiController::class, 'couponsList'])->middleware('refresh'); //优惠券领取列表
    Route::post('coupons_receive', [StoreApiController::class, 'couponsReceive'])->middleware('refresh'); //优惠券领取
    Route::post('storeinfo', [StoreApiController::class, 'storeInfo'])->middleware('refresh'); //产品详情
    Route::post('is_service', [StoreApiController::class, 'isService'])->middleware('refresh'); //根据地址判断是否提供上门服务
    Route::post('collect', [StoreApiController::class, 'Collect'])->middleware('refresh'); //收藏
    Route::post('cancelcollect', [StoreApiController::class, 'cancelCollect'])->middleware('refresh'); //取消收藏
    Route::post('set_cart', [StoreApiController::class, 'setCart'])->middleware('refresh'); //加入购物车
    Route::post('get_cart_list', [StoreApiController::class, 'getCartList'])->middleware('refresh'); //购物车页面
    Route::post('change_cart_num', [StoreApiController::class, 'changeCartNum'])->middleware('refresh'); //修改购物车数量
    Route::post('remove_cart', [StoreApiController::class, 'removeCart'])->middleware('refresh'); //删除购物车产品
    Route::post('remove_all', [StoreApiController::class, 'removeCartAll'])->middleware('refresh'); //清空购物车
    Route::post('add_service', [StoreApiController::class, 'AddService'])->middleware('refresh'); //添加服务
    Route::post('now_buy', [StoreApiController::class, 'nowBuy'])->middleware('refresh'); //立即购买
    Route::post('confirm_order', [StoreApiController::class, 'confirmOrder'])->middleware('refresh'); //订单页面
    Route::post('coupon_confirm_order', [StoreApiController::class, 'couponConfirmOrder'])->middleware('refresh'); //代金券订单页面
    Route::post('coupon_suborder', [StoreApiController::class, 'couponSubmitOrder'])->middleware('refresh'); //代金券提交订单
    Route::post('suborder', [StoreApiController::class, 'submitOrder'])->middleware('refresh'); //提交订单
    Route::post('select_coupon', [StoreApiController::class, 'selectCoupon'])->middleware('refresh'); //优惠券选择
    Route::post('orderCanService', [StoreApiController::class, 'orderCanService'])->middleware('refresh'); //地址变化 判断服务是否提供
    Route::post('orderGetUpdate', [StoreApiController::class, 'orderGetUpdate'])->middleware('refresh'); //数量改变获得优惠券 和 邮费
    Route::post('pay_finish', [StoreApiController::class, 'orderPayFinish'])->middleware('refresh'); //订单支付成功页面
});

Route::prefix('user')->group(function () {
    Route::post('about_us', [AuthApiController::class, 'aboutUs']); //关于我们
    Route::post('upload_image', [AuthApiController::class, 'uploadImage']); //上传图片
    Route::post('updateaddrlist', [AuthApiController::class, 'updateAddrList']); //上传图片
});

Route::middleware('refresh')->prefix('user')->group(function () {
    Route::post('userCenter', [AuthApiController::class, 'my']); //个人中心
    Route::post('myinfo', [AuthApiController::class, 'myInfo']); //个人资料
    Route::post('myvisit', [AuthApiController::class, 'myVisit']); //我的浏览记录
    Route::post('couponcount', [AuthApiController::class, 'myCouponCount']); //我的优惠券数量
    Route::post('mycoupons', [AuthApiController::class, 'myCoupons']); //我的优惠券
    Route::post('mycollect', [AuthApiController::class, 'myCollect']); //我的收藏
    Route::post('myaddr', [AuthApiController::class, 'myAddr']); //我的地址
    Route::post('addwechataddr', [AuthApiController::class, 'addWechaatAddr']); //添加微信地址
    Route::post('addmyaddr', [AuthApiController::class, 'addmyAddr']); //添加我的地址
    Route::post('addrinfo', [AuthApiController::class, 'addrInfo']); //地址详情
    Route::post('updatemyaddr', [AuthApiController::class, 'updatemyAddr']); //修改我的地址
    Route::post('deleteaddr', [AuthApiController::class, 'deleteAddr']); //删除地址
    Route::post('feedbacktype', [AuthApiController::class, 'feedbacktype']); //留言类型
    Route::post('feedback', [AuthApiController::class, 'feedback']); //留言反馈
    Route::post('master_application', [AuthApiController::class, 'masterApplication']); //成为师傅
    Route::post('application_status', [AuthApiController::class, 'masterApplicationStatus']); //申请状态
    Route::post('contractinfo', [AuthApiController::class, 'masterApplicationContract']); //合同
    Route::post('sign_contract', [AuthApiController::class, 'masterSignContract']); //签订合同
    Route::post('application_info', [AuthApiController::class, 'masterApplicationInfo']); //申请详情
    Route::post('application_edit', [AuthApiController::class, 'masterApplicationEdit']); //申请修改
    Route::post('mynotice', [AuthApiController::class, 'myNotice']); //我的消息
    Route::post('noticeinfo', [AuthApiController::class, 'myNoticeInfo']); //我的消息详情
    Route::post('notice_del', [AuthApiController::class, 'myNoticeDelete']); //删除我的消息
    Route::post('myorder', [AuthApiController::class, 'myOrder']); //我的订单
    Route::post('orderInfo', [AuthApiController::class, 'myOrderInfo']); //订单详情
    Route::post('pay_order', [AuthApiController::class, 'payOrder']); //订单支付
    Route::post('cancel_order', [AuthApiController::class, 'cancelOrder']); //取消订单
    Route::post('remind_order', [AuthApiController::class, 'remindOrder']); //提醒发货
    Route::post('order_refund', [AuthApiController::class, 'refundOrder']); //退款
    Route::post('refund_reason', [AuthApiController::class, 'refundReason']); //退款理由
    Route::post('order_express', [AuthApiController::class, 'orderExpress']); //查看物流
    Route::post('order_receipt', [AuthApiController::class, 'orderReceipt']); //确认收货
    Route::post('evaluation_ask', [AuthApiController::class, 'orderEvaluationAsk']); //评价问题
    Route::post('order_evaluation', [AuthApiController::class, 'orderEvaluation']); //评价
});

Route::prefix('master')->group(function () {
    Route::post('user_agreement', [MasterApiController::class, 'userAgreement']); //用户协议
    Route::post('sign_contract', [MasterApiController::class, 'masterSignContract']); //签订合同
    Route::post('level_rule', [MasterApiController::class, 'masterLevelRule']); //等级规则
});
Route::middleware('refresh')->prefix('master')->group(function () {
    Route::post('enter', [MasterApiController::class, 'enter']); //首页
    Route::post('master_status', [MasterApiController::class, 'masterStatus']); //师傅状态
    Route::post('news', [MasterApiController::class, 'News']); //资讯
    Route::post('searchnews', [MasterApiController::class, 'newsSearch']); //搜索资讯
    Route::post('newslist', [MasterApiController::class, 'newsList']); //资讯列表
    Route::post('newsInfo', [MasterApiController::class, 'newsInfo']); //资讯详情
    Route::post('userCenter', [MasterApiController::class, 'my']); //个人中心
    Route::post('myinfo', [MasterApiController::class, 'myInfo']); //个人资料
    Route::post('mynotice', [MasterApiController::class, 'myNotice']); //我的消息
    Route::post('noticeinfo', [MasterApiController::class, 'myNoticeInfo']); //我的消息详情
    Route::post('notice_del', [MasterApiController::class, 'myNoticeDelete']); //删除我的消息
    Route::post('accept_order_list', [MasterApiController::class, 'acceptOrderList']); //接单列表
    Route::post('accept_order', [MasterApiController::class, 'acceptOrder']); //接单
    Route::post('reject_reason', [MasterApiController::class, 'rejectReason']); //拒单理由
    Route::post('reject_order', [MasterApiController::class, 'rejectOrder']); //拒单
    Route::post('myorder', [MasterApiController::class, 'myOrder']); //我的订单
    Route::post('orderInfo', [MasterApiController::class, 'myOrderInfo']); //订单详情
    Route::post('finishservice', [MasterApiController::class, 'finishService']); //完成服务
    Route::post('my_purse', [MasterApiController::class, 'myPurse']); //我的钱包
    Route::post('balance', [MasterApiController::class, 'myBalance']); //我的余额
    Route::post('withdraw', [MasterApiController::class, 'withdrawApplication']); //提现
});
