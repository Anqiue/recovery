<?php

namespace App\Admin\Actions\Coupon;

use App\Models\storeCouponIssue;
use Encore\Admin\Actions\RowAction;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

class Issue extends RowAction
{
    public $name = '发布优惠券';
    public function handle(Model $model, Request $request)
    {
        $id = $this->getKey();
        $start_time = $request->start_time;
        $end_time = $request->end_time;
        $total_count = $request->total_count;
        $is_permanent = $request->is_permanent;
        $price = $request->price;
        $status = $request->status;
        if(!is_numeric($total_count))return $this->response()->error('发布数量必须是数字');
        if(!$start_time || !$end_time)return $this->response()->error('领取时间必须');
        storeCouponIssue::insert([
            'cid'=>$id,
            'start_time'=>$start_time,
            'end_time'=>$end_time,
            'total_count'=>$total_count,
            'remain_count'=>$total_count,
            'price'=>$price,
            'is_permanent'=>$is_permanent,
            'status'=>$status,
            'created_at'=>date('Y-m-d H:i:s')
        ]);
        return $this->response()->success('发布优惠劵成功!')->refresh();
    }
    public function form()
    {
        $this->datetime('start_time', '领取开始时间')->required();
        $this->datetime('end_time', '领取结束时间')->required();
        $this->text('total_count', '发布数量')->required();
        $this->text('price', '价格')->default('0.00');
        $this->text('is_permanent', __('是否限量'))->default(0)->help('0：不限量；>0：限量领取或购买张数');
        $this->radio('status', __('状态'))->options(['0' => '关闭', '1'=> '开启'])->default('1');
    }
}
