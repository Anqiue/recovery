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
                                        <a href="/admin/markting/store_coupon_order?page={{$page}}" data-z-type="primary" type="button" class="ant-btn ant-btn-primary">
                                            <span>列表</span>
                                        </a>
                                    </div>
                                    @if($order['status'] == 10 && $refund_number > 0)
                                    <div class="ant-space-item" style="">
                                        <button type="button" class="ant-btn ant-btn-default" onclick="setReduce({{$refund_number}})">
                                            <span>退款</span>
                                        </button>
                                    </div>
                                    @endif
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
                                        <span class="ant-descriptions-item-label">优惠券名称</span>
                                        <span class="ant-descriptions-item-content">
                                        <div class="ant-space ant-space-horizontal ant-space-align-center" style="gap: 8px;">
                                            <div class="ant-space-item" style="">{{$cart['title']}}</div>
                                            <div class="ant-space-item" style=""> </div>
                                        </div>
                                    </span>
                                    </div>
                                </td>
                            </tr>
                            <tr class="ant-descriptions-row">
                                <td class="ant-descriptions-item" colspan="1">
                                    <div class="ant-descriptions-item-container">
                                        <span class="ant-descriptions-item-label">产品单价</span>
                                        <span class="ant-descriptions-item-content">
                                        <div class="ant-statistic">
                                            <div class="ant-statistic-content">
                                                <span class="ant-statistic-content-value">
                                                   {{$cart['price']}}
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
                                            {{$cart['total_num']}}
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
                            </tr>
                            <tr class="ant-descriptions-row">
                                <td class="ant-descriptions-item" colspan="1">
                                    <div class="ant-descriptions-item-container"><span
                                                class="ant-descriptions-item-label">已使用</span><span
                                                class="ant-descriptions-item-content">{{$usenumber}}</span></div>
                                </td>
                            </tr>
                            <tr class="ant-descriptions-row">
                                <td class="ant-descriptions-item" colspan="1">
                                    <div class="ant-descriptions-item-container">
                                        <span class="ant-descriptions-item-label">订单状态</span>
                                        <span class="ant-descriptions-item-content">{{$order['status_title']}}</span>
                                    </div>
                                </td>
                            </tr>
                            @if($order['refund_number'] >0)
                            <tr class="ant-descriptions-row">
                                <td class="ant-descriptions-item" colspan="1">
                                    <div class="ant-descriptions-item-container">
                                        <span class="ant-descriptions-item-label">退款数量</span>
                                        <span class="ant-descriptions-item-content">{{$order['refund_number']}}</span>
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
                            </tbody>
                        </table>
                    </div>
                </div>
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
<script>
    /*退款*/
    function setReduce(number) {
        layer.prompt({title: '输入退款数量，并确认',value:number, formType: 0}, function(text, index){
            layer.confirm('确认退款后，退款金额直接返回客户账户，确定退款吗？', {
                btn: ['确定','取消'] //按钮
            }, function(){
                var params = {
                    'orderid':'{{$order['id']}}',
                    'number':text,
                    '_token':'{{csrf_token()}}'
                };
                const getContentHost = '/admin/markting/store_coupon_toreduce';
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
</script>
