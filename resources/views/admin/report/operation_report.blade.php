<div class="row">
    <div class="col-xs-12">
        <div class="box box-info">
            <form class="form-horizontal">
                <div class="box-body">
                    <div class="form-group">
                        <label class="col-sm-2 control-label">查询日期:</label>
                        <div class="col-sm-10">
                            <div class="col-xs-4">
                                <select class="form-control select_function" name="select_function" style="width: 100%;">
                                    <option selected="selected" value="1">按下单日期</option>
                                </select>
                            </div>
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

        <div class="nav-tabs-custom">
            <ul class="nav nav-tabs">
                <input type="hidden" class="selecttype" name="selecttype" value="1"/>
                <li class="active"><a href="#tab_1" data-toggle="tab" aria-expanded="true">运营中心</a></li>
                <li class="pull-right">
                    <button class="btn btn-sm btn-flat btn-primary" data-loading-text="loading..."  onclick="downloading()"><i class="fa fa-download"></i>&nbsp;&nbsp;导出</button>
                </li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane active" id="tab_1">
                    <div class="box-body">
                        <table id="example2" class="table table-bordered table-hover">
                            <thead>
                            <tr>
                                <th>运营中心名称</th>
                                <th>总订单量</th>
                                <th>总销售额</th>
                                <th>工长总数</th>
                                <th>师傅总数</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($collectReport['datalist'] as $data)
                            <tr>
                                <td>{{$data['name']}}</td>
                                <td>{{$data['total_num']}}</td>
                                <td>{{$data['total_price']}}</td>
                                <td>{{$data['total_foreman']}}</td>
                                <td>{{$data['total_master']}}</td>
                            </tr>
                            @endforeach
                            </tbody>
                            <tfoot>
                            <tr>
                                <th>小计</th>
                                <th>{{$collectReport['total']['all_total_num']}}</th>
                                <th>{{$collectReport['total']['all_total_price']}}</th>
                                <th>{{$collectReport['total']['all_total_foreman']}}</th>
                                <th>{{$collectReport['total']['all_total_master']}}</th>
                            </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

            </div>

        </div>
    </div>
</div>
<script>
    $(function () {
        setDateRange();
    });
     function setDateRange(start='{{$startdate}}',dateval='{{$enddate}}') {
         layui.use('laydate', function(laydate){
             laydate.render({
                 elem: '#test1' //指定元素
                 ,type: 'date'
                 ,trigger: 'click'
                 ,range: true //开启日期范围，默认使用“-”分割
                 ,min:'2022-03-01'
                 ,value:start+' - '+dateval
                 ,done: function(value, date, endDate){
                     $('.startdate').val(date.year+'-'+date.month+'-'+date.date);
                     $('.enddate').val(endDate.year+'-'+endDate.month+'-'+endDate.date);
                 }
                 ,change: function(value, date, endDate){
                     $('.startdate').val(date.year+'-'+date.month+'-'+date.date);
                     $('.enddate').val(endDate.year+'-'+endDate.month+'-'+endDate.date);
                 }
             });
         });
     }
    // 下载事件
    function downloading() {
        var startdate = $('.startdate').val();
        var enddate = $('.enddate').val();
        $.fileDownload("{{ url('admin/data/reportexport') }}", {
            httpMethod: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                startdate: startdate,
                enddate: enddate,
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
