<div class="box box-info">
    <form class="form-horizontal">
        <div class="box-body">
            <div class="form-group">
                <label class="col-sm-2 control-label">订单状态:</label>
                <div class="col-sm-10">
                    <input type="hidden" name="status" value="{{$status}}" class="status">
                    <div class="col-xs-8 order_status">
                        <button type="button" class="layui-btn layui-btn-sm @if($status != 11) layui-btn-primary @endif " id="11">全部</button>
                        <button type="button" class="layui-btn layui-btn-sm @if($status != 0) layui-btn-primary @endif "id="0">已取消</button>
                        <button type="button" class="layui-btn layui-btn-sm @if($status != 1) layui-btn-primary @endif" id="1">待付款</button>
                        <button type="button" class="layui-btn layui-btn-sm @if($status != 3) layui-btn-primary @endif" id="2">待发货</button>
                        <button type="button" class="layui-btn layui-btn-sm @if($status != 4) layui-btn-primary @endif" id="3">待收货</button>
                        <button type="button" class="layui-btn layui-btn-sm @if($status != 5) layui-btn-primary @endif" id="5">待服务</button>
                        <button type="button" class="layui-btn layui-btn-sm @if($status != 6) layui-btn-primary @endif" id="6">待评价</button>
                        <button type="button" class="layui-btn layui-btn-sm @if($status != 7) layui-btn-primary @endif" id="7">已退款</button>
                        <button type="button" class="layui-btn layui-btn-sm @if($status != 8) layui-btn-primary @endif" id="8">服务进行中</button>
                        <button type="button" class="layui-btn layui-btn-sm @if($status != 9) layui-btn-primary @endif" id="9">待审核</button>
                        <button type="button" class="layui-btn layui-btn-sm @if($status != 10) layui-btn-primary @endif" id="10">已完成</button>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">查询日期:</label>
                <div class="col-sm-10">
                    <div class="col-xs-4">
                        <input type="text" class="layui-input" placeholder="选择日期范围" id="test1">
                        <input type="hidden" class="startdate" name="startdate" value="{{$startdate}}"/>
                        <input type="hidden" class="enddate" name="enddate" value="{{$enddate}}"/>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label"></label>
                <div class="col-sm-10">
                    <div class="col-xs-4">
                        <button type="submit" class="btn btn-info pull-left">查询</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
<div class="totalreport">
<div class="row report">
    <div class="col-lg-3 col-xs-6">
        <div class="small-box bg-aqua" title="订单数量">
            <div class="inner">
                <p>订单数量</p>
                <h3 class="totalpri">{{$data['totalOrderCount']}}</h3>
            </div>
            <div class="icon">
                <i class="ion ion-stats-bars"></i>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-xs-6">
        <div class="small-box bg-green">
            <div class="inner">
                <p>使用优惠卷金额（元）</p>
                <h3 class="count">￥{{$data['totalCouponPrice']}}</h3>
            </div>
            <div class="icon">
                <i class="ion ion-stats-bars"></i>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-xs-6">
        <div class="small-box bg-yellow">
            <div class="inner">
                <p>在线支付总金额（元）</p>
                <h3 class="wechatfans">￥{{$data['totalOrderPrice']}}</h3>
            </div>
            <div class="icon">
                <i class="ion ion-stats-bars"></i>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-xs-6">
        <div class="small-box bg-red">
            <div class="inner">
                <p>订单商品数量</p>
                <h3 class="merfans">{{$data['totalOrderTotalNum']}}</h3>
            </div>
            <div class="icon">
                <i class="ion ion-stats-bars"></i>
            </div>
        </div>
    </div>

</div>
</div>
<div class="ordersys">
<div class="row ordersys-header">
    <div class="col-sm-8">
        <p>订单</p>
    </div>
</div>
<div class="row">
    <div class="col-sm-10" style="margin: 0 auto;float: none;">
        <canvas id="myChart4" width="200px" height="100px"></canvas>
    </div>
</div>
</div>
<div class="ordersys" style="margin-top: 20px">
<div class="row ordersys-header">
    <div class="col-sm-8">
        <p>运营中心订单</p>
    </div>
</div>
<div class="row">
    <div class="col-sm-10" style="margin: 0 auto;float: none;">
        <canvas id="myChart" width="200px" height="100px"></canvas>
    </div>
</div>
</div>
<style>
    #app{
        display: inline-block;
        width: 100%;
    }
    .content-wrapper .content{
        background: #fff;
        margin: 15px;
        padding: 10px;
        border-radius: 15px;
    }
    .content-wrapper .tip{
        margin: 0 -10px 10px;
    }
    .tip p{
        margin: 0;
        line-height: 35px;
    }
    #test1{
        padding: 0 20px;
        border-radius: 16px;
    }
    .content-wrapper .report{
        margin: 0 -5px;
    }
    .content-wrapper .report .small-box{
        border-radius: 5px;
    }
    .ordersys{
        border: 1px solid #d5d5d5;
        border-radius: 15px;
        padding: 10px;
    }
    .ordersys-header{
        border-bottom: 1px dotted;
        margin: 0;
    }
    .layui-btn-sm{
        margin: 5px 2px;
    }
    .layui-btn+.layui-btn{
        margin: 0;
    }
</style>
<script>
    $(function () {
        setDateRange();
        chartjs('{{$chartdata['data1']}}','{{$chartdata['data2']}}','{{$chartdata['data3']}}','{{$chartdata['name']}}');
        piechartjs('{{$oprateddata['chartdata']}}','{{$oprateddata['name']}}','{{$oprateddata['color']}}');
        $('.order_status button').each(function(){
            $( this ).bind("click" , function(){//绑定当前点击的按钮
                var id = $( this).attr("id");//获取它的id属性值
                $('.status').val(id);
                $(this).removeClass('layui-btn-primary').siblings().addClass('layui-btn-primary');
            });
        })
    });

    function setDateRange(start = '{{$startdate}}', dateval = '{{$enddate}}') {
        layui.use('laydate', function (laydate) {
            laydate.render({
                elem: '#test1' //指定元素
                , type: 'date'
                , trigger: 'click'
                , range: true //开启日期范围，默认使用“-”分割
                , min: '2022-07-01'
                , value: start + ' - ' + dateval
                , done: function (value, date, endDate) {
                    $('.startdate').val(date.year + '-' + date.month + '-' + date.date);
                    $('.enddate').val(endDate.year + '-' + endDate.month + '-' + endDate.date);
                }
                , change: function (value, date, endDate) {
                    $('.startdate').val(date.year + '-' + date.month + '-' + date.date);
                    $('.enddate').val(endDate.year + '-' + endDate.month + '-' + endDate.date);
                }
            });
        });
    }
</script>
<script>
    function chartjs(data1,data2,data3,name) {
        console.log(1111);
        var namex = name.split("#");
        var data1 = data1.split("#");
        var data2 = data2.split("#");
        var data3 = data3.split("#");

        var barChartData={
            labels:namex,
            datasets:[
                {
                    label: '订单总数',
                    type: 'bar',
                    data: data1,
                    backgroundColor: 'rgba(255,99,132,1)',
                    borderColor: 'rgba(255,99,132,1)',
                    borderWidth: 1
                },
                {
                    label: '订单成交数',
                    type: 'bar',
                    data: data2,
                    backgroundColor: 'rgba(75,192,192,1)',
                    borderColor: 'rgba(75,192,192,1)',
                    borderWidth: 1
                },
                {
                    label: '订单金额',
                    type:'line',
                    data: data3,
                    backgroundColor:'rgba(54, 162, 235, 0.1)',
                    borderColor:'rgba(255,99,132,1)',
                    borderWidth: 1
                }
            ]
        };
        var ctx4 = document.getElementById("myChart4").getContext('2d');
        var myChart4 = new Chart(ctx4, {
            type: 'bar',
            data: barChartData,
            options: {
                responsive: true, legend: {position: 'top'},
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero:true
                        }
                    }]
                },
                title: {
                    display: true, text: "订单分析"
                }
            }
        });
    }
    function piechartjs(data,name,color) {
        var namex = name.split("#");
        var data1 = data.split("#");
        var color = color.split("|");
        var ctx = document.getElementById('myChart').getContext('2d');
        var myChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: namex,
                datasets: [{
                    label: '运营中心订单量',
                    data: data1,
                    borderColor:'gray',
                    backgroundColor:color,
                    borderWidth: 1
                }]
            },
        });
    }
</script>
