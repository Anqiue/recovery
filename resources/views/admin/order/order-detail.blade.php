<div class="eb-ticket">
    <main class="ant-layout-content">
        <div class="ant-card orderDetail">
            <div class="ant-card-head" style="line-height: 1; height: auto; border-bottom: none; padding: 30px 20px 0px;">
                <div class="ant-card-head-wrapper">
                    <div class="ant-card-head-title">
                        <div class="ant-space ant-space-vertical" style="width: 100%; gap: 8px;">
                            <div class="ant-space-item">
                                <div class="ant-space ant-space-horizontal ant-space-align-center" style="flex-wrap: wrap; gap: 8px;">
                                    <div class="ant-space-item" style="">
                                        <h4 class="ant-typography">{{$order['order_id']}}</h4></div>
                                    <div class="ant-space-item" style="">
                                        <span class="ant-typography ant-typography-secondary">{{$order['status_title']}}</span>
                                    </div>
                                    <div class="ant-space-item" style="">
                                        <div>
                                            <span class="ant-tag ant-tag-blue">小程序</span>
                                            <span class="ant-tag ant-tag-gold">微信支付</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="ant-card-extra">
                        <div class="ant-space ant-space-vertical" style="gap: 8px;">
                            <div class="ant-space-item">
                                <div class="ant-space ant-space-horizontal ant-space-align-center" style="flex-wrap: wrap; gap: 8px;">
                                    <div class="ant-space-item" style="">
                                        @if($ordertype == 1)
                                        <a href="/admin/orders/store_order?page={{$page}}" data-z-type="primary" type="button" class="ant-btn ant-btn-primary">
                                            <span>列表</span>
                                        </a>
                                        @else
                                        <a href="/admin/operate/order?page={{$page}}" data-z-type="primary" type="button" class="ant-btn ant-btn-primary">
                                            <span>列表</span>
                                        </a>
                                        @endif
                                    </div>
                                    @if($order['status'] == 2 && $order['is_shipping'] == 0)
                                    <div class="ant-space-item" style="">
                                        <button type="button" class="ant-btn ant-btn-default" onclick="setShip(1)">
                                            <span>发货</span>
                                        </button>
                                    </div>
                                    <div class="ant-space-item" style="">
                                        <button type="button" class="ant-btn ant-btn-default" onclick="setReduce()">
                                            <span>退款</span>
                                        </button>
                                    </div>
                                    @endif
                                    @if($order['status'] == 3)
                                    <div class="ant-space-item" style="">
                                        <button type="button" class="ant-btn ant-btn-default" onclick="setShip(2)">
                                            <span>发货</span>
                                        </button>
                                    </div>
                                    @endif
                                    @if($order['status'] == 9)
                                    <div class="ant-space-item" style="">
                                        <button type="button" class="ant-btn ant-btn-default" onclick="serviceAudit()">
                                            <span>服务审核</span>
                                        </button>
                                    </div>
                                    @endif
                                    {{--<div class="ant-space-item" style="">
                                        <button data-track-disable="true" type="button" class="ant-btn ant-btn-default disable">
                                            <span>强制退款</span>
                                        </button>
                                    </div>--}}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="ant-card-body" style="padding: 10px 20px;">
                <div class="ant-descriptions is-border is-gray">
                    <div class="ant-descriptions-header">
                        <div class="ant-descriptions-title">产品信息</div>
                    </div>
                    <div class="ant-descriptions-view">
                        <table>
                            @foreach($order['cart_info'] as $cart)
                            <tbody>
                            <tr class="ant-descriptions-row">
                                <td class="ant-descriptions-item" colspan="2">
                                    <div class="ant-descriptions-item-container">
                                        <span class="ant-descriptions-item-label">商品名称</span>
                                        <span class="ant-descriptions-item-content">
                                        <div class="ant-space ant-space-horizontal ant-space-align-center" style="gap: 8px;">
                                            <div class="ant-space-item" style="">{{$cart['productInfo']['product_name']}}
{{--                                                【 {{$cart['product_id']}} 】--}}
                                            </div>
                                            <div class="ant-space-item" style=""> </div>
                                            @if($ordertype == 1)
                                            <div class="ant-space-item" style="">
                                                <a href="/admin/store/store_product/{{$cart['product_id']}}/edit"
                                                   target="_blank" class="ant-typography" style="color: #0d6aad">商品详情</a>
                                            </div>
                                            @endif
                                        </div>
                                    </span>
                                    </div>
                                </td>
                            </tr>
                            <tr class="ant-descriptions-row">
                                @if($cart['attr_id'] > 0)
                                <td class="ant-descriptions-item" colspan="1">
                                    <div class="ant-descriptions-item-container">
                                        <span class="ant-descriptions-item-label">属性</span>
                                        <span class="ant-descriptions-item-content">{{$cart['productInfo']['attrInfo']['specname']}}</span>
                                    </div>
                                </td>
                                @endif
                                @if($cart['product_setvice_id'] > 0)
                                <td class="ant-descriptions-item" colspan="1">
                                    <div class="ant-descriptions-item-container">
                                        <span class="ant-descriptions-item-label">服务</span>
                                        <span class="ant-descriptions-item-content">
                                            {{$cart['seviceInfo']['product_name']}}- ￥{{$cart['seviceInfo']['price']}}
                                            @if($ordertype == 1)
                                            <a href="/admin/store/store_product/{{$cart['seviceInfo']['id']}}/edit"
                                               target="_blank" class="ant-typography" style="color: #0d6aad">详情</a>
                                            @endif
                                        </span>
                                    </div>
                                </td>
                                @endif
                            </tr>
                            <tr class="ant-descriptions-row">
                                <td class="ant-descriptions-item" colspan="1">
                                    <div class="ant-descriptions-item-container">
                                        <span class="ant-descriptions-item-label">产品单价</span>
                                        <span class="ant-descriptions-item-content">
                                        <div class="ant-statistic">
                                            <div class="ant-statistic-content">
                                                <span class="ant-statistic-content-value">
                                                   {{$cart['truePrice']}}
                                                </span>
                                                <span class="ant-statistic-content-suffix">元</span>
                                            </div>
                                        </div>
                                    </span>
                                    </div>
                                </td>
                                <td class="ant-descriptions-item" colspan="1">
                                    <div class="ant-descriptions-item-container">
                                        <span class="ant-descriptions-item-label">数量</span>
                                        <span class="ant-descriptions-item-content">
                                            {{$cart['cart_num']}}
                                        </span>
                                    </div>
                                </td>
                            </tr>
                            </tbody>
                            @endforeach
                        </table>
                    </div>
                </div>
                <div class="ant-descriptions  is-gray">
                    <div class="ant-descriptions-header">
                        <div class="ant-descriptions-title">会员信息</div>
                    </div>
                    <div class="ant-descriptions-view">
                        <table>
                            <tbody>
                            <tr class="ant-descriptions-row">
                                <td class="ant-descriptions-item" colspan="1">
                                    <div class="ant-descriptions-item-container">
                                        <span class="ant-descriptions-item-label">会员昵称</span>
                                        <span class="ant-descriptions-item-content">
                                            <div class="ant-space ant-space-horizontal ant-space-align-center"style="gap: 8px;">
                                                <div class="ant-space-item" style="">{{$user['nickname']}}
{{--                                                    【 {{$user['id']}} 】--}}
                                                </div>
                                                <div class="ant-space-item"> </div>
                                            </div>
                                        </span>
                                    </div>
                                </td>
                                <td class="ant-descriptions-item" colspan="1">
                                    <div class="ant-descriptions-item-container"><span
                                                class="ant-descriptions-item-label">购买者姓名</span><span
                                                class="ant-descriptions-item-content">{{$order['real_name']}}</span></div>
                                </td>
                            </tr>
                            <tr class="ant-descriptions-row">
                                <td class="ant-descriptions-item" colspan="1">
                                    <div class="ant-descriptions-item-container">
                                        <span class="ant-descriptions-item-label">购买者电话</span>
                                        <span class="ant-descriptions-item-content">{{$order['user_phone']}}</span>
                                    </div>
                                </td>
                                <td class="ant-descriptions-item" colspan="1">
                                    <div class="ant-descriptions-item-container">
                                        <span class="ant-descriptions-item-label">发货地址</span>
                                        <span class="ant-descriptions-item-content">{{$order['user_address']}}{{$order['addr_detail']}}</span>
                                    </div>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="ant-descriptions is-gray">
                    <div class="ant-descriptions-header">
                        <div class="ant-descriptions-title">订单信息</div>
                        {{--<div class="ant-descriptions-extra">
                            <div class="ant-space ant-space-horizontal ant-space-align-center" style="gap: 8px;">
                                <div class="ant-space-item">
                                    <button data-track-disable="true" data-z-type="link" type="button"
                                            class="ant-btn ant-btn-link ant-btn-sm"><span>打印订单</span></button>
                                </div>
                            </div>
                        </div>--}}
                    </div>
                    <div class="ant-descriptions-view">
                        <table>
                            <tbody>
                            <tr class="ant-descriptions-row">
                                <td class="ant-descriptions-item" colspan="1">
                                    <div class="ant-descriptions-item-container">
                                        <span class="ant-descriptions-item-label">订单ID</span><span
                                                class="ant-descriptions-item-content">{{$order['id']}}</span>
                                    </div>
                                </td>
                                <td class="ant-descriptions-item" colspan="1">
                                    <div class="ant-descriptions-item-container">
                                        <span class="ant-descriptions-item-label">购买人姓名</span>
                                        <span class="ant-descriptions-item-content">{{$order['real_name']}}</span>
                                    </div>
                                </td>
                            </tr>
                            <tr class="ant-descriptions-row">
                                <td class="ant-descriptions-item" colspan="1">
                                    <div class="ant-descriptions-item-container">
                                        <span class="ant-descriptions-item-label">手机号</span>
                                        <span class="ant-descriptions-item-content">{{$order['user_phone']}}</span>
                                    </div>
                                </td>
                                <td class="ant-descriptions-item" colspan="1">
                                    <div class="ant-descriptions-item-container"><span
                                                class="ant-descriptions-item-label">平台订单号</span><span
                                                class="ant-descriptions-item-content">{{$order['order_id']}}</span></div>
                                </td>
                            </tr>
                            <tr class="ant-descriptions-row">
                                <td class="ant-descriptions-item" colspan="1">
                                    <div class="ant-descriptions-item-container">
                                        <span class="ant-descriptions-item-label">购买数量</span>
                                        <span class="ant-descriptions-item-content">{{$order['total_num']}}</span>
                                    </div>
                                </td>
                                <td class="ant-descriptions-item" colspan="1">
                                    <div class="ant-descriptions-item-container">
                                        <span class="ant-descriptions-item-label">商品总价</span>
                                        <span class="ant-descriptions-item-content">
                                            <div class="ant-statistic">
                                                <div class="ant-statistic-content">
                                                    {{$order['total_price']}}元
                                                </div>
                                            </div>
                                        </span>
                                    </div>
                                </td>
                            </tr>
                            <tr class="ant-descriptions-row">
                                <td class="ant-descriptions-item" colspan="1">
                                    <div class="ant-descriptions-item-container">
                                        <span class="ant-descriptions-item-label">已支付</span>
                                        <span class="ant-descriptions-item-content">
                                            <div class="ant-statistic">
                                                <div class="ant-statistic-content">
                                                    <span class="ant-statistic-content-value">
                                                        @if($order['paid'] == 1)
                                                       {{$order['pay_price']}}
                                                        @else
                                                            0
                                                        @endif
                                                    </span>
                                                    <span class="ant-statistic-content-suffix">元</span>
                                                </div>
                                            </div>
                                        </span>
                                    </div>
                                </td>
                                <td class="ant-descriptions-item" colspan="1">
                                    <div class="ant-descriptions-item-container">
                                        <span class="ant-descriptions-item-label">优惠券</span>
                                        <span class="ant-descriptions-item-content">
                                            <div class="ant-statistic">
                                                <div class="ant-statistic-content">
                                                    <span class="ant-statistic-content-value">
                                                       {{$order['coupon_price']}}
                                                    </span>
                                                    <span class="ant-statistic-content-suffix">元</span>
                                                </div>
                                            </div>
                                        </span>
                                    </div>
                                </td>
                            </tr>
                            <tr class="ant-descriptions-row">
                                <td class="ant-descriptions-item" colspan="1">
                                    <div class="ant-descriptions-item-container"><span
                                                class="ant-descriptions-item-label">邮费</span><span
                                                class="ant-descriptions-item-content">{{$order['total_postage']}}元</span></div>
                                </td>
                            </tr>
                            <tr class="ant-descriptions-row">
                                <td class="ant-descriptions-item" colspan="1">
                                    <div class="ant-descriptions-item-container">
                                        <span class="ant-descriptions-item-label">订单状态</span>
                                        <span class="ant-descriptions-item-content">{{$order['status_title']}}</span>
                                    </div>
                                </td>
                                <td class="ant-descriptions-item" colspan="1">
                                    <div class="ant-descriptions-item-container">
                                        <span class="ant-descriptions-item-label">订单备注</span>
                                        <span class="ant-descriptions-item-content">{{$order['remark']}}</span>
                                    </div>
                                </td>
                            </tr>
                            @if($order['status'] == 7)
                            <tr class="ant-descriptions-row">
                                <td class="ant-descriptions-item" colspan="1">
                                    <div class="ant-descriptions-item-container">
                                        <span class="ant-descriptions-item-label">退款原因</span>
                                        <span class="ant-descriptions-item-content">{{$order['refund_reason']}}</span>
                                    </div>
                                </td>
                                <td class="ant-descriptions-item" colspan="1">
                                    <div class="ant-descriptions-item-container">
                                        <span class="ant-descriptions-item-label">退款金额</span>
                                        <span class="ant-descriptions-item-content">￥{{$order['refund_price']}}元</span>
                                    </div>
                                </td>
                            </tr>
                            <tr class="ant-descriptions-row">
                                <td class="ant-descriptions-item" colspan="1">
                                    <div class="ant-descriptions-item-container">
                                        <span class="ant-descriptions-item-label">退款时间</span>
                                        <span class="ant-descriptions-item-content">{{$order['refund_reason_time']}}</span>
                                    </div>
                                </td>
                            </tr>
                            @endif
                            @if($order['is_shipping'] == 1)
                            <tr class="ant-descriptions-row">
                                <td class="ant-descriptions-item" colspan="1">
                                    <div class="ant-descriptions-item-container"><span
                                                class="ant-descriptions-item-label">物流公司</span><span
                                                class="ant-descriptions-item-content">{{$order['ship_company']}}</span></div>
                                </td>
                                <td class="ant-descriptions-item" colspan="1">
                                    <div class="ant-descriptions-item-container">
                                        <span class="ant-descriptions-item-label">快递单号</span>
                                        <span class="ant-descriptions-item-content">{{$order['ship_no']}}</span>
                                        <span role="button" tabindex="0" class="check-detail"  style="color: #0d6aad" onclick="seeExpress({{$order['id']}})">
                                            &nbsp;&nbsp;查看物流
                                        </span>
                                    </div>
                                </td>
                            </tr>
                            <tr class="ant-descriptions-row">
                                <td class="ant-descriptions-item" colspan="1">
                                    <div class="ant-descriptions-item-container">
                                        <span class="ant-descriptions-item-label">发货时间</span>
                                        <span class="ant-descriptions-item-content">{{$order['shipping_time']}}</span>
                                    </div>
                                </td>
                            </tr>
                            @endif
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($order['master_id'] > 0)
                <div class="ant-descriptions  is-gray">
                    <div class="ant-descriptions-header">
                        <div class="ant-descriptions-title">服务信息</div>
                    </div>
                    <div class="ant-descriptions-view">
                        <table>
                            <tbody>
                            <tr class="ant-descriptions-row">
                                <td class="ant-descriptions-item" colspan="1">
                                    <div class="ant-descriptions-item-container">
                                        <span class="ant-descriptions-item-label">师傅名称</span>
                                        <span class="ant-descriptions-item-content">
                                            <div class="ant-space ant-space-horizontal ant-space-align-center"style="gap: 8px;">
                                                <div class="ant-space-item" style="">{{$order['master_name']}}【 {{$order['master_id']}} 】</div>
                                                <div class="ant-space-item"> </div>
                                            </div>
                                        </span>
                                    </div>
                                </td>
                                <td class="ant-descriptions-item" colspan="1">
                                    <div class="ant-descriptions-item-container"><span
                                            class="ant-descriptions-item-label">师傅接单等级</span><span
                                            class="ant-descriptions-item-content">{{$order['master_level_name']}}</span></div>
                                </td>
                            </tr>
                            <tr class="ant-descriptions-row">
                                <td class="ant-descriptions-item" colspan="1">
                                    <div class="ant-descriptions-item-container">
                                        <span class="ant-descriptions-item-label">等级补贴</span>
                                        <span class="ant-descriptions-item-content">￥{{$order['level_amount']}}</span>
                                    </div>
                                </td>
                                <td class="ant-descriptions-item" colspan="1">
                                    <div class="ant-descriptions-item-container">
                                        <span class="ant-descriptions-item-label">基础工价</span>
                                        <span class="ant-descriptions-item-content">￥{{$order['base_wage']}}</span>
                                    </div>
                                </td>
                            </tr>
                            <tr class="ant-descriptions-row">
                                <td class="ant-descriptions-item" colspan="1">
                                    <div class="ant-descriptions-item-container">
                                        <span class="ant-descriptions-item-label">政策补贴</span>
                                        <span class="ant-descriptions-item-content">￥{{$order['policy_subsidy']}}</span>
                                    </div>
                                </td>
                                <td class="ant-descriptions-item" colspan="1">
                                    <div class="ant-descriptions-item-container">
                                        <span class="ant-descriptions-item-label">服务总价</span>
                                        <span class="ant-descriptions-item-content">￥{{$order['master_total_service']}}</span>
                                    </div>
                                </td>
                            </tr>
                            <tr class="ant-descriptions-row">
                                <td class="ant-descriptions-item" colspan="1">
                                    <div class="ant-descriptions-item-container">
                                        <span class="ant-descriptions-item-label">完成时间</span>
                                        <span class="ant-descriptions-item-content">{{$order['service_finished_time']}}</span>
                                    </div>
                                </td>
                            </tr>
                            <tr class="ant-descriptions-row">
                                @if(isset($order['service_finished']['site_photo']))
                                <td class="ant-descriptions-item" colspan="1">
                                    <div class="ant-descriptions-item-container">
                                        <span class="ant-descriptions-item-label">完成凭证(完工区域照片)</span>
                                        <span class="ant-descriptions-item-content"  id="layer-photos-demo1">
                                            @foreach($order['service_finished']['site_photo'] as $siteimg)
                                                <a href="javascript:void(0)" onclick="imageClick(1)">
                                                    <img src="{{$siteimg}}" width="50px" layer-src="{{$siteimg}}" alt="场地合影" height="50px" style="margin-right:5px">
                                                </a>
                                            @endforeach
                                        </span>
                                    </div>
                                </td>
                                @endif
                                @if(isset($order['service_finished']['customer_photo']))
                                <td class="ant-descriptions-item" colspan="1">
                                    <div class="ant-descriptions-item-container">
                                        <span class="ant-descriptions-item-label">完成凭证(完工验收报告)</span>
                                        <span class="ant-descriptions-item-content" id="layer-photos-demo2">
                                            @foreach($order['service_finished']['customer_photo'] as $cusimg)
                                                <a href="javascript:void(0)" onclick="imageClick(2)">
                                                    <img src="{{$cusimg}}"  layer-src="{{$cusimg}}" alt="与客户合影" width="50px" height="50px" style="margin-right:5px">
                                                </a>
                                            @endforeach
                                        </span>
                                    </div>
                                </td>
                                @endif
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif
                <div class="ant-card">
                    <div class="ant-card-head" style="line-height: 36px; height: auto; padding: 20px 34px 0px;">
                        <div class="ant-card-head-wrapper">
                            <div class="ant-card-head-title">操作记录</div>
                        </div>
                    </div>
                    <div class="ant-card-body" style="padding: 10px 34px;">
                        <div class="ant-steps ant-steps-vertical ant-steps-dot">
                            @foreach($oprate as $oprateval)
                            <div class="ant-steps-item ant-steps-item-wait">
                                <div role="button" tabindex="0" class="ant-steps-item-container">
                                    <div class="ant-steps-item-tail"></div>
                                    <div class="ant-steps-item-icon">
                                        <span class="ant-steps-icon"> <span class="ant-steps-icon-dot"></span></span>
                                    </div>
                                    <div class="ant-steps-item-content">
                                        <div class="ant-steps-item-title">{{$oprateval['oprate_title']}}</div>
                                        <div class="ant-steps-item-description">
                                            <span style="white-space: nowrap;">{{$oprateval['oprate_time']}}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<div class="col-md-10" id="setship"  style="margin: 5%; display: none">
    <form class="form-horizontal" id="setroomForm">
        <div class="box-body">
            <div class="form-group">
                <label class="col-sm-2 control-label">订单号:</label>
                <div class="col-sm-10 product_name">
                    {{$order['order_id']}}
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">订单金额:</label>
                <div class="col-sm-10 totalprice">
                    {{$order['total_price']}} 元
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">商品优惠:</label>
                <div class="col-sm-10 couponprice">
                    {{$order['coupon_price']}} 元
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">邮费:</label>
                <div class="col-sm-10 levelprice">
                    {{$order['total_postage']}} 元
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">实际支付:</label>
                <div class="col-sm-10 pryprice">
                    {{$order['pay_price']}} 元
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">选择物流:</label>
                <div class="col-sm-10">
                    <div class="layui-inline">
                        <select class="form-control ship_code" style="width: 100%;">
                            @foreach($shiplist as $ship)
                                <option value="{{$ship['code']}}" @if($order['delivery_code'] == $ship['code']) selected="selected" @endif>{{$ship['name']}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">快递单号:</label>
                <div class="col-sm-8">
                    <input type="text" class="form-control ship_no" rows="3" placeholder="请输入快递单号"  name="ship_no" value="{{$order['ship_no']}}"></input>
                </div>
            </div>
        </div>
    </form>
</div>

<div class="user-order-logistics" id="express" style="overflow: hidden;display:none;">
    <section>
        <div class="product-info flex">
            <div class="picture"><img src="/vendor/express_icon.jpg"/></div>
            <div class="logistics-tip flex">
                <div class="company">物流公司：{{$order['ship_company']}}</div>
                <div class="number">物流单号：{{$order['ship_no']}}</div>
            </div>
        </div>
        <div class="logistics-info" style="background-color: inherit;">

            <div class="empty" style="display: none">
                <img src="/vendor/empty_address.png">
                <p>暂无查询记录</p>
            </div>

            <ul class="logistics flex"  style="display: none">

            </ul>

        </div>
    </section>
</div>
<li class='clearfix'><div class='right-wrapper fl'><div class='status'></div><div class='time'></div></div></li>

<style>
    .col-sm-10{
        line-height: 30px;
    }
    .col-sm-10 label{
        font-weight: 500;
        padding: 0 5px
    }
</style>
<script>
    function imageClick(type) {
        layer.photos({
            photos: '#layer-photos-demo'+type //格式见API文档手册页
            ,anim: 5 //0-6的选择，指定弹出图片动画类型，默认随机
        });
    }
    function seeExpress(oid) {
        layer.msg('加载中', {
            icon: 16
            ,shade: 0.01
        });
        setTimeout(function(){
            layer.closeAll('loading');
        }, 1000);
        var params = {
            'orderid':oid,
            '_token':'{{csrf_token()}}'
        };
        const getgetexpressHost = '/admin/orders/store_order/getexpress';
        $.post(getgetexpressHost, params, function (data) {
            var code = data.code;
            if(code == 1){
                var express = data.express;
                if(!express || express.status != 0){
                    $('.empty').show();
                    $('.logistics').hide();
                }else{
                    $('.empty').hide();
                    $('.logistics').show();
                    $('.logistics').html('');
                    var list = express.result.list;
                    var tmpl = $(".clearfix");
                    for(var i=0;i<list.length;i++){
                        var item = tmpl.clone();
                        item.find('.status').text(list[i].status);
                        item.find('.time').text(list[i].time);
                        $('.logistics').append(item);
                    }
                }
                //页面层
                layer.open({
                    type: 1 //Page层类型
                    ,skin: 'layui-layer-molv'
                    ,area: ['422px', '568px']
                    ,title: ['物流信息','font-size:18px']
                    ,shadeClose: true
                    ,shade: 0.3 //遮罩透明度
                    ,maxmin: true //允许全屏最小化
                    ,content:$("#express")
                });
            }else{
                layer.msg(data.msg, {icon: 2});
            }
        });
    };
</script>
<script>
    /*发货*/
    function setShip(type) {
        //页面层
        layer.open({
            type: 1 //Page层类型
            ,skin: 'layui-layer-molv'
            ,area: ['800px', '600px']
            ,title: ['发货','font-size:18px']
            ,btn: ['确定', '取消']
            ,shadeClose: true
            ,shade: 0.3 //遮罩透明度
            ,maxmin: true //允许全屏最小化
            ,content:$("#setship")
            ,success:function(){

            }
            ,yes:function(){
                var params = {
                    'orderid':'{{$order['id']}}',
                    'ship_code':$('.ship_code').val(),
                    'ship_no':$('.ship_no').val(),
                    'type':type,
                    '_token':'{{csrf_token()}}'
                };
                const getContentHost = '/admin/orders/store_order/toship';
                $.post(getContentHost, params, function (data) {
                    var code = data.code;
                    if(code == 1){
                        layer.msg('发货成功', {icon: 1},function(){
                            layer.closeAll();
                            window.location.reload();
                        });
                    }else{
                        layer.msg(data.msg, {icon: 2});
                    }
                });
            }
        });
    }
    /*退款*/
    function setReduce() {
        layer.prompt({title: '输入退款理由，并确认', formType: 2}, function(text, index){
            layer.confirm('确认退款后，退款金额直接返回客户账户，确定退款吗？', {
                btn: ['确定','取消'] //按钮
            }, function(){
                var params = {
                    'orderid':'{{$order['id']}}',
                    'remark':text,
                    '_token':'{{csrf_token()}}'
                };
                const getContentHost = '/admin/orders/store_order/toreduce';
                $.post(getContentHost, params, function (data) {
                    var code = data.code;
                    if(code == 1){
                        layer.msg('退款成功', {icon: 1},function(){
                            layer.close(index);
                            window.location.reload();
                        });
                    }else{
                        layer.msg(data.msg, {icon: 2});
                    }
                });
            }, function(){
                layer.closeAll();
            });

        });
    }
    /*服务审核*/
    function serviceAudit() {
        layer.confirm('确定师傅服务审核通过吗？', {
            btn: ['通过'] //按钮
        }, function () {
            var params = {
                'orderid': '{{$order['id']}}',
                'type': 1,
                '_token': '{{csrf_token()}}'
            };
            const getContentHost = '/admin/orders/store_order/service_audit';
            $.post(getContentHost, params, function (data) {
                var code = data.code;
                if (code == 1) {
                    layer.msg('审核成功', {icon: 1}, function () {
                        layer.close(index);
                        window.location.reload();
                    });
                } else {
                    layer.msg(data.msg, {icon: 2});
                }
            });
        }, function () {
            layer.prompt({title: '输入不通过理由', formType: 2}, function (text, index) {
                //alert(text);
                //layer.closeAll();
                var params = {
                    'orderid': '{{$order['id']}}',
                    'type': 2,
                    'reason': text,
                    '_token': '{{csrf_token()}}'
                };
                const getContentHost = '/admin/orders/store_order/service_audit';
                $.post(getContentHost, params, function (data) {
                    var code = data.code;
                    if (code == 1) {
                        layer.msg('审核成功', {icon: 1}, function () {
                            layer.close(index);
                            window.location.reload;
                        });
                    } else {
                        layer.msg(data.msg, {icon: 2});
                    }
                });
            })
        });
    }
</script>
