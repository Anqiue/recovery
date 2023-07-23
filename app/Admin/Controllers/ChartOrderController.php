<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\storeOrder;
use App\Models\storeVisit;
use Carbon\Carbon;
use Encore\Admin\Controllers\Dashboard;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Hanson\LaravelAdminWechat\Models\WechatConfig;
use Hanson\LaravelAdminWechat\Models\WechatUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChartOrderController extends Controller
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
        $startdate = $request->startdate?:$start_time;
        $enddate = $request->enddate?:$today;
        $status = $request->status?:11;
        $data = [];
        $data['totalOrderPrice'] = storeOrder::getOrderStatusTotal($status,$oprated_id,$start_time,$today,'sum');
        $data['totalOrderCount'] = storeOrder::getOrderStatusTotal($status,$oprated_id,$start_time,$today,'count');
        $data['totalOrderTotalNum'] = storeOrder::getOrderStatusTotal($status,$oprated_id,$start_time,$today,'total_num');
        $data['totalCouponPrice'] = storeOrder::getOrderStatusTotal($status,$oprated_id,$start_time,$today,'coupon_price');

       /* $daycode = $request->input('daycode');
        $daycode = (!$daycode)?'2':$daycode;*/
        $chartdata = $this->getChartTotalData($status,$oprated_id,$startdate,$enddate);

        $oprateddata = $this->getChartOpratedTotalData($status,$startdate,$enddate);
        return $content->title('订单统计')
            ->description('统计')
            ->view('admin.report.chart_order',compact('type','start_time','today','data','chartdata','oprateddata','startdate','enddate','status'));
    }

    public function getChartTotalData($status,$operate_id,$startdate,$enddate){
        $opwhere = [];
        if($operate_id > 0){
            $opwhere['operate_id'] = $operate_id;
        }
        if($status != 11){
            $opwhere['status'] = $status;
        }
        /*天数*/
        $origin  = date_create($startdate);
        $target = date_create($enddate);
        $interval = date_diff($origin, $target)->days;
        //总订单数 成交订单数 总收入
        //按时间的订单数分析  总订单和成交订单
        if($interval <= 15){
            $where = $this->searchDate($interval);
        }else if($interval >15 && $interval <= 45){
            $weekday = 7;
            $hasweek = floor($interval/$weekday);//下取整计算有几个星期
            $lessday = $interval%$weekday;//取余计算不足一星期的天数
            if($lessday > 0)$hasweek = $hasweek + 1;
            $where = $this->searchWeek($hasweek);
        }else{
            $origin  = date_create($startdate);
            $target = date_create($enddate);
            $months = date_diff($origin, $target)->m;
            $where = $this->searchMonth($months);
        }
        foreach ($where as $key=>$value){
            $con = [
                ['created_at', '>=', $value['start']],
                ['created_at', '<=', $value['end']],
            ];
            $data1[$key] = storeOrder::where($opwhere)->where($con)->count();
            $data2[$key] = storeOrder::getOrderStatusTotal($status,$operate_id,$value['start'],$value['end'],'count');
            $data3[$key] = storeOrder::getOrderStatusTotal($status,$operate_id,$value['start'],$value['end'],'sum');
            if($interval > 45){
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

    public function getChartOpratedTotalData($status,$startdate,$enddate){
        $opwhere = [];
        if($status != 11){
            $opwhere['status'] = $status;
        }
        $userModel = config('admin.database.users_model');
        $adminuserlist = $userModel::where('type',2)->select('id','name')->get()->toArray();
        $data4 = [];
        $name = [];
        $color = [];
        if($adminuserlist){
            $i = 0;
            foreach ($adminuserlist as $key=>$val){
                $ordercount = storeOrder::where($opwhere)->where('operate_id',$val['id'])
                    ->whereDate('created_at','>=',$startdate)->whereDate('created_at','<=',$enddate)
                    ->count();
                $name[$key] = trim($val['name']);
                $data4[$key] = $ordercount;
                $colorList = ['#fc8251','#5470c6','#91cd77','#ef6567','#f9c956','#75bedc'];
                $color[$key] = $colorList[$key%6];
                /*if($ordercount > 0){
                    $name[$i] = trim($val['name']);
                    $data4[$i] = $ordercount;
                    $i ++;
                }*/
            }
        }
        $data = [];
        $data['chartdata'] = implode('#',$data4);
        $data['name'] = implode('#',$name);
        $data['color'] = implode('|',$color);
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
}
