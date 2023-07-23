<?php

use Illuminate\Routing\Router;

Admin::routes();

Route::group([
    "prefix" => config("admin.route.prefix"),
    "namespace" => config("admin.route.namespace"),
    "middleware" => config("admin.route.middleware"),
    "as" => config("admin.route.as"),
], function (Router $router) {
    $router->get("/", "HomeController@index")->name("home");
    $router->any("get_total","HomeController@getTotal"); //统计信息

    $router->any("upload_file","UploadFileController@uploadFile"); //图片上传
    $router->any("upload/img","UploadFileController@uploadMultipleImg"); //图片上传
    $router->any("upload/deleteimg","UploadFileController@deleteUploadImg"); //图片上传
    $router->any("upload/get_service_data","UploadFileController@getServiceData"); //获取服务包

    /*商品*/
    $router->resource("store/store_product", StoreProductController::class);
    $router->resource("store/store_category", StoreCategoryController::class);

    /*内容*/
    $router->resource("content/article", ArticleController::class);
    $router->resource("content/article_category", ArticleCategoryController::class);

    /*订单*/
    $router->resource("orders/store_order", StoreOrderController::class);
    $router->any("orders/store_order/toship","StoreOrderController@toShip"); //发货
    $router->any("orders/store_order/toreduce","StoreOrderController@toReduce"); //未发货商品退款
    $router->any("orders/store_order/service_audit","StoreOrderController@serviceAudit"); //审核服务
    $router->any("orders/store_order/getexpress","StoreOrderController@getExpress"); //查看物流
    $router->resource("orders/store_product_reply", StoreProductReplyController::class);

    /*用户*/
    $router->resource("users/userlist", UserListController::class);
    $router->resource("users/user_address", UserAddressController::class);

    /*财务*/
    $router->resource("bill/user_bill", UserBillController::class);
    $router->resource("bill/user_extract", UserExtractController::class);

    /*营销*/
    $router->resource("markting/store_coupon", StoreCouponController::class);
    $router->resource("markting/store_coupon_issue", StoreCouponIssueController::class);
    $router->resource("markting/store_coupon_order", StoreCouponOrderController::class);
    $router->any("markting/store_coupon_toreduce","StoreCouponOrderController@toReduce"); //退款
    $router->resource("markting/store_coupon_user", StoreCouponUserController::class);

    /*设置*/
    $router->resource("seting/store_article", StoreArticleController::class);
    $router->resource("seting/store_ads", StoreAdsController::class);
    $router->resource("seting/system_config", SystemConfigController::class);

    /*数据统计*/
    $router->resource("data/chart_order", ChartOrderController::class);
    $router->resource("data/chart_product", ChartProductController::class);
    $router->resource("data/data_operation", DataOperationController::class);
    $router->any("data/reportexport","DataOperationController@export"); //运营中心报表导出
    $router->any("data/reportchartproduct","ChartProductController@export"); //产品报表导出


    // $router->resource("store_cart", StoreCartController::class);
   // $router->resource("store_search", StoreSearchController::class);
    //$router->resource("user_notice", UserNoticeController::class);
    //$router->resource("user_notice_see", UserNoticeSeeController::class);
    //$router->resource("user_relation", UserRelationController::class);
    //$router->resource("user_visit", UserVisitController::class);
});
