<div class="totalreport">
<div class="row tip">
    <div class="col-sm-4">
        <p><font style="vertical-align: inherit;">在所选时间段内的统计数据：</font></p>
    </div>
    <div class="col-sm-8" style="text-align: right">
        <div class="layui-inline">
            <input type="text" class="layui-input" placeholder="选择日期范围" id="test1">
        </div>
    </div>
</div>
<div class="row report">
    <div class="col-lg-3 col-xs-6">
        <div class="small-box bg-blue" title="订单">
            <div class="inner">
                <p>订单待发货</p>
                <h3 class="totalship">{{$data['willShipOrders']}}</h3>
                <p>总:<span class="countship">{{$data['shipOrders']}}</span></p>

            </div>
            <div class="icon">
                <i class="ion ion-pie-graph"></i>
            </div>
            @if($type == 1)
            <a  href="/admin/orders/store_order?&id=&status=2&order_id=&real_name=&user_phone=&uid=&operate_id=&created_at%5Bstart%5D=&created_at%5Bend%5D=" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
            @else
            <a  href="/admin/operate/order?&id=&status=2&order_id=&real_name=&user_phone=&uid=&operate_id=&created_at%5Bstart%5D=&created_at%5Bend%5D=" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
            @endif
        </div>
    </div>
    <div class="col-lg-3 col-xs-6">
        <div class="small-box bg-olive" title="订单">
            <div class="inner">
                <p>订单待审核</p>
                <h3 class="bereview">{{$data['reviewedOrders']}}</h3>
                <p>总:<span class="revieworders">{{$data['totalreviewdOrders']}}</span></p>

            </div>
            <div class="icon">
                <i class="ion ion-pie-graph"></i>
            </div>
            @if($type == 1)
            <a  href="/admin/orders/store_order?&id=&status=9&order_id=&real_name=&user_phone=&uid=&operate_id=&created_at%5Bstart%5D=&created_at%5Bend%5D=" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
            @else
                <a  href="/admin/operate/order?&id=&status=9&order_id=&real_name=&user_phone=&uid=&operate_id=&created_at%5Bstart%5D=&created_at%5Bend%5D=" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
            @endif
        </div>
    </div>
    <div class="col-lg-3 col-xs-6">
        <div class="small-box bg-aqua" title="交易额(元)">
            <div class="inner">
                <p>总交易额(元)</p>
                <h3 class="totalpri">￥{{$data['totalOrderPrice']}}</h3>
                <p>今日:￥<span class="totaypri">{{$data['totalOrderPrice_today']}}</span></p>
            </div>
            <div class="icon">
                <i class="ion ion-bag"></i>
            </div>
            @if($type == 1)
                <a  href="/admin/orders/store_order" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
            @else
                <a  href="/admin/operate/order" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
            @endif
        </div>
    </div>

    <div class="col-lg-3 col-xs-6">
        <div class="small-box bg-green">
            <div class="inner">
                <p>交易用户(人)</p>
                <h3 class="count">{{$data['totalOrderUserCount']}}</h3>
                <p>今日：<span class="todaycount">{{$data['totalOrderUserCount_today']}}</span></p>
            </div>
            <div class="icon">
                <i class="ion ion-stats-bars"></i>
            </div>
            @if($type == 1)
                <a  href="/admin/orders/store_order" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
            @else
                <a  href="/admin/operate/order" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
            @endif
        </div>
    </div>
    <div class="col-lg-3 col-xs-6">
        <div class="small-box bg-red">
            <div class="inner">
                <p>访客量(人)</p>
                <h3 class="merfans">{{$data['mer_fans']}}</h3>
                <p>今日：<span class="merfanstoday">{{$data['mer_todayfans']}}</span></p>
            </div>
            <div class="icon">
                <i class="ion ion-pie-graph"></i>
            </div>
            <a href="" class="small-box-footer"> <i class="fa fa-arrow-circle-right"></i></a>
        </div>
    </div>
    @if($type == 1)
    <div class="col-lg-3 col-xs-6">
        <div class="small-box bg-red">
            <div class="inner">
                <p>提现申请(待处理)</p>
                <h3 class="extract">{{$data['today_extract']}}</h3>
                <p>总申请：<span class="extracttotal">{{$data['total_extract']}}</span></p>
            </div>
            <div class="icon">
                <i class="ion ion-pie-graph"></i>
            </div>
            <a href="/admin/bill/user_extract" class="small-box-footer"> <i class="fa fa-arrow-circle-right"></i></a>
        </div>
    </div>
    @endif
</div>
</div>
<div class="ordersys">
<div class="row ordersys-header">
    <div class="col-sm-8">
        <p>订单</p>
    </div>
    <div class="col-sm-4">
    <form action="/admin" method="get">
        <div class="row col-md-5">
            <select onchange="this.form.submit()" name="daycode">
                <option value="1" @if($daycode == 1) selected @endif>近7日</option>
                <option value="2" @if($daycode == 2) selected @endif>近15天</option>
                <option value="3" @if($daycode == 3) selected @endif>近半年</option>
                <option value="4" @if($daycode == 4) selected @endif>近一年</option>
            </select>
        </div>
    </form>
    </div>
</div>
<div class="row">
    <div class="col-sm-10" style="margin: 0 auto;float: none;">
        <canvas id="myChart4" width="200px" height="100px"></canvas>
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
    .small-box p,.small-box h3{
        margin: 0;
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
</style>
<script>
    $(function () {
        $('input[type="checkbox"].minimal, input[type="radio"].minimal').iCheck({
            checkboxClass: 'icheckbox_minimal-blue',
            radioClass   : 'iradio_minimal-blue'
        })
        setDateRange('{{$start_time}}');
        chartjs('{{$chartdata['data1']}}','{{$chartdata['data2']}}','{{$chartdata['data3']}}','{{$chartdata['name']}}')
    });
    function setDateRange(starttime,dateval='{{$today}}') {
        layui.use('laydate', function(laydate){
            laydate.render({
                elem: '#test1' //指定元素
                ,type: 'date'
                ,trigger: 'click'
                ,range: true //开启日期范围，默认使用“-”分割
                ,max:'{{$today}}'
                ,value:starttime+' - '+dateval
                ,done: function(value, date, endDate){
                    var start = date.year+'-'+date.month+'-'+date.date;
                    var end = endDate.year+'-'+endDate.month+'-'+endDate.date;
                    console.log(start);
                    console.log(end);
                    const getContentHost = '/admin/get_total';
                    $.post(getContentHost, {
                        'start': start,
                        'end': end,
                        '_token': '{{csrf_token()}}'
                    }, function (data) {
                        var totalOrderPrice = data.totalOrderPrice;
                        var totalOrderUserCount = data.totalOrderUserCount;
                        var mer_fans = data.mer_fans;
                        //var chartdata = data.chartdata;
                        $('.totalpri').text('￥'.totalOrderPrice);
                        $('.count').text(totalOrderUserCount);
                        $('.merfans').text(mer_fans);
                        $('.totalship').text(data.willShipOrders);
                        $('.bereview').text(data.reviewedOrders);
                        $('.extract').text(data.today_extract);
                        $('.master').text(data.today_master);
                        //chartjs(chartdata.data1,chartdata.data2,chartdata.data3,chartdata.name);
                    });
                }
                ,change: function(value, date, endDate){
                    /*$('.startdate').val(date.year+'-'+date.month+'-'+date.date);
                    $('.enddate').val(endDate.year+'-'+endDate.month+'-'+endDate.date);*/
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
                    label: '订单收入金额',
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
</script>
