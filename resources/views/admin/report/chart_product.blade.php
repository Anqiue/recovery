<div class="box box-info">
    <form class="form-horizontal">
        <div class="box-body">
            <div class="form-group">
                <label class="col-sm-2 control-label">产品状态:</label>
                <div class="col-sm-10">
                    <input type="hidden" name="status" value="{{$status}}" class="status">
                    <div class="col-xs-8 order_status">
                        <button type="button" class="layui-btn layui-btn-sm @if($status != 11) layui-btn-primary @endif " id="11">全部商品</button>
                        <button type="button" class="layui-btn layui-btn-sm @if($status != 0) layui-btn-primary @endif "id="0">已售罄产品</button>
                        <button type="button" class="layui-btn layui-btn-sm @if($status != 1) layui-btn-primary @endif" id="1">警戒库存</button>
                    </div>
                </div>
            </div>
           {{-- <div class="form-group">
                <label class="col-sm-2 control-label">产品类型:</label>
                <div class="col-sm-10">
                    <input type="hidden" name="status" value="{{$status}}" class="status">
                    <div class="col-xs-8 order_status">
                        <button type="button" class="layui-btn layui-btn-sm @if($status != 11) layui-btn-primary @endif " id="11">全部商品</button>
                        <button type="button" class="layui-btn layui-btn-sm @if($status != 0) layui-btn-primary @endif "id="0">服务包</button>
                        <button type="button" class="layui-btn layui-btn-sm @if($status != 0) layui-btn-primary @endif "id="0">普通产品</button>
                    </div>
                </div>
            </div>--}}
            {{--<div class="form-group">
                <label class="col-sm-2 control-label">创建日期:</label>
                <div class="col-sm-10">
                    <div class="col-xs-4">
                        <input type="text" class="layui-input" placeholder="选择日期范围" id="test1">
                        <input type="hidden" class="startdate" name="startdate" value="{{$startdate}}"/>
                        <input type="hidden" class="enddate" name="enddate" value="{{$enddate}}"/>
                    </div>
                </div>
            </div>--}}
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
        <div class="small-box bg-aqua" title="商品分类数">
            <div class="inner">
                <p>商品分类数</p>
                <h3 class="totalpri">{{$data['totalCat']}}</h3>
            </div>
            <div class="icon">
                <i class="ion ion-stats-bars"></i>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-xs-6">
        <div class="small-box bg-green">
            <div class="inner">
                <p>商品总数</p>
                <h3 class="count">￥{{$data['totalProduct']}}</h3>
            </div>
            <div class="icon">
                <i class="ion ion-stats-bars"></i>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-xs-6">
        <div class="small-box bg-red">
            <div class="inner">
                <p>服务包</p>
                <h3 class="merfans">{{$data['totalService']}}</h3>
            </div>
            <div class="icon">
                <i class="ion ion-stats-bars"></i>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-xs-6">
        <div class="small-box bg-red">
            <div class="inner">
                <p>普通商品</p>
                <h3 class="merfans">{{$data['totalPro']}}</h3>
            </div>
            <div class="icon">
                <i class="ion ion-stats-bars"></i>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-xs-6">
        <div class="small-box bg-yellow">
            <div class="inner">
                <p>缺货商品</p>
                <h3 class="wechatfans">￥{{$data['totalstockpro']}}</h3>
            </div>
            <div class="icon">
                <i class="ion ion-stats-bars"></i>
            </div>
        </div>
    </div>

</div>
</div>

<div class="nav-tabs-custom">
    <ul class="nav nav-tabs">
        <input type="hidden" class="selecttype" name="selecttype" value="1"/>
        <li class="active"><a href="#tab_1" data-toggle="tab" aria-expanded="true">产品统计</a></li>
        <li class="pull-right">
            <button class="btn btn-sm btn-flat btn-primary" data-loading-text="loading..."  onclick="downloading()"><i class="fa fa-download"></i>&nbsp;&nbsp;导出</button>
        </li>
    </ul>
    <div class="tab-content">
        <div class="tab-pane active" id="tab_1">
            <div class="box-body">
                <table id="example2" class="table table-bordered table-hover dataTable">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>产品</th>
                        <th>销量</th>
                        <th>总销售额</th>
                        <th>浏览量</th>
                        <th>收藏量</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($collectReport['datalist'] as $key=> $data)
                        <tr>
                            <td>{{$data['id']}}</td>
                            <td>{{$data['name']}}</td>
                            <td>{{$data['sales']}}</td>
                            <td>{{$data['total_price']}}</td>
                            <td>{{$data['browse']}}</td>
                            <td>{{$data['collectNum']}}</td>
                        </tr>
                    @endforeach
                    </tbody>
                    <tfoot>
                    <tr>
                        <th></th>
                        <th>小计</th>
                        <th>{{$collectReport['total']['all_total_sales']}}</th>
                        <th>{{$collectReport['total']['all_total_price']}}</th>
                        <th>{{$collectReport['total']['all_total_browse']}}</th>
                        <th>{{$collectReport['total']['all_total_collectNum']}}</th>
                    </tr>
                    </tfoot>
                </table>
            </div>
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
    a.paginate_button{
        padding: 6px 12px;
        margin-left: -1px;
        line-height: 1.42857143;
        color: #777;
        text-decoration: none;
        background-color: #fff;
        border: 1px solid #ddd;
        cursor: pointer;
    }
    a.paginate_button.current{
        z-index: 3;
        color: #fff;
        cursor: default;
        background-color: #337ab7;
        border-color: #337ab7;
    }
</style>
<script>
    $(function () {
        setDateRange();
        $('.order_status button').each(function(){
            $( this ).bind("click" , function(){//绑定当前点击的按钮
                var id = $( this).attr("id");//获取它的id属性值
                $('.status').val(id);
                $(this).removeClass('layui-btn-primary').siblings().addClass('layui-btn-primary');
            });
        })
        $('#example2').DataTable({
            'paging'      : true,
            'lengthChange': true,
            'searching'   : true,
            'ordering'    : true,
            'info'        : true,
            'autoWidth'   : false
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
    // 下载事件
    function downloading() {
       // var startdate = $('.startdate').val();
       // var enddate = $('.enddate').val();
        $.fileDownload("{{ url('admin/data/reportchartproduct') }}", {
            httpMethod: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                status: "{{$status}}",
               // startdate: startdate,
               // enddate: enddate,
            },
            prepareCallback: function (url) {
                layer.load(0, {
                    shade: [0.1,'#fff'] //0.1透明度的白色背景
                });
                console.log("正在下载，请稍后...");
            },
            successCallback: function (url) {
                console.log("SUCCESS导出完成！");
                layer.closeAll('loading'); //关闭加载层
            },
            failCallback: function (html, url) {
                console.log("ERROR");
                layer.closeAll('loading'); //关闭加载层
            }
        });
    }
</script>
