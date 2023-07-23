<?php

namespace App\Admin\Controllers;

use App\Exports\report\productReportExport;
use App\Http\Controllers\Controller;
use App\Models\storeCategory;
use App\Models\storeOrderProduct;
use App\Models\storeProduct;
use App\Models\userRelation;
use Carbon\Carbon;
use Encore\Admin\Layout\Content;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChartProductController extends Controller
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
        $where = [];
        if($status == 0){//已售罄商品
            $where=[['status','=',0]];
        }elseif ($status == 1){//警戒库存
            $where=[['status','<',3]];
        }
        $data = [];
        $data['totalCat'] = storeCategory::where('status',1)->count();
        $data['totalProduct'] = storeProduct::where('is_show',1)->where($where)->count();
        $data['totalstockpro'] = storeProduct::where('is_show',1)->where('stock',0)->count();
        $data['totalService'] = storeProduct::where('is_show',1)->where($where)->where('type',2)->count();
        $data['totalPro'] = storeProduct::where('is_show',1)->where($where)->where('type',1)->count();

        $collectReport = $this->getTotalData($status,$startdate,$enddate);

        return $content->title('产品统计')
            ->description('统计')
            ->view('admin.report.chart_product',compact('type','start_time','today','data','startdate','enddate','status','collectReport'));
    }
    /*运营中心汇总数据*/
    public function getTotalData($status,$startdate='',$enddate='')
    {
        $where = [];
        if($status == 0){//已售罄商品
            $where=[['status','=',0]];
        }elseif ($status == 1){//警戒库存
            $where=[['status','<',3]];
        }
        $list =  storeProduct::where('is_show',1)->where($where)
            //->whereDate('created_at','>=',$startdate)->whereDate('created_at','<=',$enddate)
            ->select('id','product_name','sales','browse')->get()->toArray();
        $result = [];
        $data = [];
        $total['all_total_price'] = 0;
        $total['all_total_num'] = 0;
        $total['all_total_collectNum'] = 0;
        $total['all_total_sales'] = 0;
        $total['all_total_browse'] = 0;

        if($list){
            foreach ($list as $key=>$val){
                /*销售金额*/
                $total_price = 0;
                $orderpro = storeOrderProduct::where('store_order_product.product_id',$val['id'])
                    ->join('store_order as order', 'store_order_product.oid', '=', 'order.id')
                    ->whereIn('order.status',[2,3,5,6,8,9,10])
                    ->select('store_order_product.id','store_order_product.cart_info')
                    ->get()->toArray();
                if($orderpro){
                    foreach ($orderpro as $pkey=>$pro){
                        $cartinfo = json_decode($pro['cart_info'],true);
                        $total_price += $cartinfo['truePrice'];//产品销售金额
                    }
                }
                /*收藏量*/
                $collectNum = count(userRelation::where('type',1)->where('link_id',$val['id'])->groupBy('uid')->get()->toArray());

                $data[$key]['id'] = $val['id'];
                $data[$key]['name'] = $val['product_name'];
                $data[$key]['sales'] = $val['sales'];
                $data[$key]['browse'] = $val['browse'];
                $data[$key]['total_price'] = $total_price;
                $data[$key]['collectNum'] = $collectNum;
                $total['all_total_price'] += $total_price;
                $total['all_total_collectNum'] += $collectNum;
                $total['all_total_sales'] += $val['sales'];
                $total['all_total_browse'] += $val['browse'];
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
        $status = $request->status?:11;
      /*  $startdate = $request->startdate?:$start;
        $enddate = $request->enddate?:$today;*/
        $collectReport = $this->getTotalData($status);
        return Excel::download(new productReportExport($collectReport), 'productreport-'.date('YmdHis').'.xlsx',null,['Set-Cookie' => 'fileDownload=true; path=/']);
    }
}
