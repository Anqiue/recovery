<?php

namespace App\Admin\Controllers;

use App\Exports\report\collectReportExport;
use App\Http\Controllers\Controller;
use App\Models\storeOrder;
use Carbon\Carbon;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Hanson\LaravelAdminWechat\Models\WechatUser;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class DataOperationController extends Controller
{
    public function index(Content $content,Request $request)
    {
        $today = date('Y-m-d');
        $start = Carbon::now()->subDays(7)->startOfDay()->toDateString();
        $startdate = $request->startdate?:$start;
        $enddate = $request->enddate?:$today;
        $collectReport = $this->getTotalData($startdate,$enddate);
        return $content->title('运营中心报表')
            ->description('报表')
            ->view('admin.report.operation_report',compact('collectReport','startdate','enddate'));
    }
    /*运营中心汇总数据*/
    public function getTotalData($startdate,$enddate)
    {
        $userModel = config('admin.database.users_model');
        $list = $userModel::where('type',2)->get()->toArray();
        $result = [];
        $data = [];
        $total['all_total_price'] = 0;
        $total['all_total_num'] = 0;
        $total['all_total_foreman'] = 0;
        $total['all_total_master'] = 0;

        if($list){
            foreach ($list as $key=>$val){
                $total_price = storeOrder::where('operate_id',$val['id'])->whereIn('status',[2,3,5,6,8,9,10])->whereBetween('created_at',[$startdate.' 00:00:00',$enddate.' 23:59:59'])->sum('pay_price');
                $total_num = storeOrder::where('operate_id',$val['id'])->whereIn('status',[2,3,5,6,8,9,10])->whereBetween('created_at',[$startdate.' 00:00:00',$enddate.' 23:59:59'])->count();
                $total_foreman = WechatUser::where('type',2)->where('master_type',1)->where('operate_id',$val['id'])->where('application_id','>',0)->count();
                $total_master = WechatUser::where('type',2)->where('master_type',0)->where('operate_id',$val['id'])->where('application_id','>',0)->count();
                $data[$key]['name'] = $val['name'];
                $data[$key]['total_price'] = $total_price;
                $data[$key]['total_num'] = $total_num;
                $data[$key]['total_foreman'] = $total_foreman;
                $data[$key]['total_master'] = $total_master;
                $total['all_total_price'] += $total_price;
                $total['all_total_num'] += $total_num;
                $total['all_total_foreman'] += $total_foreman;
                $total['all_total_master'] += $total_master;
            }
        }
        $result['datalist'] = $data;
        $result['total'] = $total;
        return $result;
    }

    public function export(Request $request)
    {
        $today = date('Y-m-d');
        $start = Carbon::now()->subDays(7)->startOfDay()->toDateString();
        $startdate = $request->startdate?:$start;
        $enddate = $request->enddate?:$today;
        $collectReport = $this->getTotalData($startdate,$enddate);
        return Excel::download(new collectReportExport($collectReport), 'report-'.date('YmdHis').'.xlsx',null,['Set-Cookie' => 'fileDownload=true; path=/']);
    }
}
