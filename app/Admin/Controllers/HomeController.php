<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\storeOrder;
use App\Models\storeVisit;
use App\Models\userExtract;
use App\Models\userMasterApplication;
use Carbon\Carbon;
use Encore\Admin\Controllers\Dashboard;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Hanson\LaravelAdminWechat\Models\WechatConfig;
use Hanson\LaravelAdminWechat\Models\WechatUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index(Content $content,Request $request)
    {
        $adminRow = Auth::guard('admin')->user();
        $type = $adminRow['type'];//1-平台 2-运营中心
        $oprated_id = 0;
        if($type == 2){
            $oprated_id = $adminRow['id'];
        }
        $start_time=Carbon::now()->startOfMonth()->toDateString();
        $today = date('Y-m-d');
        $data = [];
        $data['willShipOrders'] = storeOrder::getOrderTotal($oprated_id,$start_time,$today,'willship');
        $data['shipOrders'] = storeOrder::getOrderTotal($oprated_id,$start_time,$today,'ship');
        $data['reviewedOrders'] = storeOrder::getOrderTotal($oprated_id,$start_time,$today,'reviewed');
        $data['totalreviewdOrders'] = storeOrder::getOrderTotal($oprated_id,$start_time,$today,'totalreviewed');

        $data['totalOrderPrice'] = storeOrder::getOrderTotal($oprated_id,$start_time,$today,'sum');
        $data['totalOrderPrice_today'] = storeOrder::getOrderTotal($oprated_id,'',$today,'sumtoday');

        $data['totalOrderUserCount'] = storeOrder::getOrderTotal($oprated_id,$start_time,$today,'count');
        $data['totalOrderUserCount_today'] = storeOrder::getOrderTotal($oprated_id,'',$today,'counttoday');

        $data['mer_fans'] = storeVisit::getVisitTotal($start_time,$today,'count');//访客量
        $data['mer_todayfans'] = storeVisit::getVisitTotal($start_time,$today,'counttoday');//今日访问量
        /*提现申请*/
        $data['today_extract'] = userExtract::getTodayExtract($oprated_id,$start_time,$today,'today');
        $data['total_extract'] = userExtract::getTodayExtract($oprated_id,$start_time,$today,'total');

        $daycode = $request->input('daycode');
        $daycode = (!$daycode)?'2':$daycode;
        $chartdata = $this->getChartTotalData($oprated_id,$daycode);
        return $content->title('总体概览')
            ->description('Dashboard')
            ->view('admin.home',compact('type','daycode','start_time','today','data','chartdata'));
    }

    public function getChartTotalData($operate_id,$daycode){
        $opwhere = [];
        if($operate_id > 0){
            $opwhere['operate_id'] = $operate_id;
        }
        //总订单数 成交订单数 总收入
        //按时间的订单数分析  总订单和成交订单
        if($daycode == 1){
            $where = $this->searchDate(7);
        }else if($daycode == 2){
            $where = $this->searchDate(15);

        }else if($daycode == 3){
            $where = $this->searchMonth(6);
        }else{
            $where = $this->searchMonth(12);
        }
        foreach ($where as $key=>$value){
            $con = [
                ['created_at', '>=', $value['start']],
                ['created_at', '<=', $value['end']],
            ];
            $data1[$key] = storeOrder::where($opwhere)->where($con)->count();
            $data2[$key] = storeOrder::getOrderTotal($operate_id,$value['start'],$value['end'],'count');
            $data3[$key] = storeOrder::getOrderTotal($operate_id,$value['start'],$value['end'],'sum');
            if($daycode != 1 && $daycode != 2 && $daycode != 3){
                $nowYear = date('Y');
                $arrayYear = substr($value['start'],0,4);
                if($nowYear != $arrayYear){
                    $nameTemp = substr($value['start'],0,7);
                }else{
                    $nameTemp = substr($value['start'],5,2);
                    $nameTemp = intval($nameTemp).'月';
                }
                $name[$key] = trim($nameTemp);
            }else{
                $nameTemp = substr($value['start'],5,6);
                $name[$key] = trim($nameTemp);
            }
        }
        $data = [];
        $data['data1'] = implode('#',$data1);
        $data['data2'] = implode('#',$data2);
        $data['data3'] = implode('#',$data3);
        $data['name'] = implode('#',$name);
        return $data;
    }

    //获取查询条件  周
    public function searchWeek($week)
    {
        $where = [];
        for ($i=0,$j=$week-1;$i<$week;$i++,$j--){
            $where[$i]['start'] = Carbon::now()->subWeek($j)->startOfWeek();
            $where[$i]['end'] = Carbon::now()->subWeek($j)->endOfWeek();
        }
        return $where;
    }
    //获取查询条件  月
    public function searchMonth($month)
    {
        $where = [];
        for ($i=0,$j=$month-1;$i<$month;$i++,$j--){
            $where[$i]['start'] = Carbon::now()->subMonths($j)->startOfMonth();
            $where[$i]['end'] = Carbon::now()->subMonths($j)->endOfMonth();
        }
        return $where;
    }
    //获取查询条件  天
    public function searchDate($day)
    {
        $where = [];
        for ($i=0,$j=$day-1;$i<$day;$i++,$j--){
            $where[$i]['start'] = Carbon::now()->subDays($j)->startOfDay()->toDateTimeString();
            $where[$i]['end'] = Carbon::now()->subDays($j)->endOfDay()->toDateTimeString();
        }
        return $where;
    }

    function getMonthNum( $start_time, $end_time){
        $date1 = explode('-',$start_time);
        $date2 = explode('-',$end_time);
        if($date1[0] == $date2[0]){ //同年
            if($date1[1] == $date2[1]){ //同月
                $month = 0;
                $day = $date2[2]-$date1[2];
            }else{
                //不同月
                $month = $date2[1]-$date1[1];
                if($date1[2]>$date2[2]){
                    //开始天大于结束天：计算天数，月份减一
                    $day = date('t',strtotime($start_time))-($date1[2]-$date2[2]);
                    $month-=1;
                }else{
                    //结束天大于开始天
                    $day = $date2[2]-$date1[2];
                }
            }
        }else{
            $month = 12;
            //不同年
            if($date1[1] == $date2[1]){ //同月
                if($date1[2]>$date2[2]){
                    //开始天大于结束天：计算天数，月份减一
                    $day = date('t',strtotime($start_time))-($date1[2]-$date2[2]);
                    $month-=1;
                }else{
                    //结束天大于开始天
                    $day = $date2[2]-$date1[2];
                }
            }else{
                //不同月
                if($date1[1]>$date2[1]){
                    //开始月大于结束月
                    $month -= $date1[1]-$date2[1];
                    if($date1[2]>$date2[2]){
                        //开始天大于结束天：计算天数，月份减一
                        $day = date('t',strtotime($start_time))-($date1[2]-$date2[2]);
                        $month-=1;
                    }else{
                        //结束天大于开始天
                        $day = $date2[2]-$date1[2];
                    }
                }else{
                    //结束月大于开始月
                    $month += $date2[1]-$date1[1];
                    if($date1[2]>$date2[2]){
                        //开始天大于结束天：计算天数，月份减一
                        $day = date('t',strtotime($start_time))-($date1[2]-$date2[2]);
                        $month-=1;
                    }else{
                        //结束天大于开始天
                        $day = $date2[2]-$date1[2];
                    }
                }
            }
        }
        return [
            'start_time'=>$start_time,
            'end_time'=>$end_time,
            'month'=>$month,
            'day'=>$day,
        ];
    }
    public function getTotal(Request $request){
        $adminRow = Auth::guard('admin')->user();
        $type = $adminRow['type'];//1-平台 2-运营中心
        $oprated_id = 0;
        if($type == 2){
            $oprated_id = $adminRow['id'];
        }
        $start = $request->input('start');
        $end = $request->input('end');
        $data = [];
        $data['totalOrderPrice'] = storeOrder::getOrderTotal($oprated_id,$start,$end,'sum');
        $data['totalOrderUserCount'] = storeOrder::getOrderTotal($oprated_id,$start,$end,'count');
        $data['willShipOrders'] = storeOrder::getOrderTotal($oprated_id,$start,$end,'willship');
        $data['reviewedOrders'] = storeOrder::getOrderTotal($oprated_id,$start,$end,'reviewed');
        $data['mer_fans'] = storeVisit::getVisitTotal($start,$end,'count');
        /*提现申请*/
        $data['today_extract'] = userExtract::getTodayExtract($oprated_id,$start,$end,'today');
        //$data['chartdata'] = $this->getChartTotalData($mid,$start,$end);
        return response($data);
    }
}
