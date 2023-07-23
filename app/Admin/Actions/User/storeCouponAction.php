<?php

namespace App\Admin\Actions\User;

use App\Models\storeCouponIssue;
use App\Models\storeCouponUser;
use Encore\Admin\Actions\BatchAction;
use Hanson\LaravelAdminWechat\Models\WechatUser;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use App\Models\storeCoupon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class storeCouponAction extends BatchAction
{
    public $name = '发送优惠券';
    protected $selector = '.grant_coupon';

    public function handle(Collection $collection, Request $request)
    {
        $couponid = $request->get('coupon');
        $coupon = storeCoupon::where('id',$couponid)->first();
        if($coupon->use_status == 1){
            $difference_time = $this->validity($coupon->coupon_end);//是否失效
            if($difference_time <=0){
                return $this->response()->error('该优惠券使用已过期');
            }
        }
        foreach ($collection as $model=>$value) {
            $userid = $value['id'];
            storeCouponUser::createNewRow($userid,$couponid,$coupon,2);
        }
        return $this->response()->success('优惠券已发送到对应账户中');
    }

    public function form()
    {
        $counpons = storeCoupon::where('status',1)->pluck('title','id');
        $this->select('coupon', '选择优惠券')->options($counpons)->rules('required');
    }
    public function html()
    {
        return <<<HTML
        <a class="btn btn-sm btn-success grant_coupon"><i class="fa fa-check-circle-o"></i> 发送优惠券</a>
HTML;
    }
    //获取时间差
    public function validity($time){
        $current_time = time();
        $difference_time = strtotime($time) - $current_time;
        return $difference_time;
    }
}
