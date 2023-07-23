<?php

namespace App\Models;

use Encore\Admin\Traits\DefaultDatetimeFormat;
use Hanson\LaravelAdminWechat\Models\WechatUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class storeCouponUser extends Model
{
    use SoftDeletes;
    use DefaultDatetimeFormat;
    protected $table = "store_coupon_user";
    protected $fillable = [];
    protected $guarded = [];
    protected $appends = [];
    protected $hidden = [];

    public function coupon()
    {
        return $this->belongsTo(storeCoupon::class,'cid');
    }

    public function user()
    {
        return $this->belongsTo(WechatUser::class,'uid');
    }

    public function createNewRow($userid,$cid,$couponinfo,$type,$issue_id=0,$oid=0){
        $time = date('Y-m-d H:i:s');
        if($couponinfo['use_status'] == 1){//使用日期
            $usetime = $couponinfo['coupon_start'];
            $endtime = $couponinfo['coupon_end'];
        }else{//使用天数
            $usetime = date('Y-m-d H:i:s',time());
            $endtime = date('Y-m-d H:i:s',strtotime($time)+$couponinfo['coupon_day']*24*60*60);
        }
        $coupondata = [];
        $coupondata['cid'] = $cid;
        $coupondata['uid'] = $userid;
        $coupondata['issue_id'] = $issue_id;
        $coupondata['oid'] = $oid;
        $coupondata['coupon_title'] = $couponinfo['title'];
        $coupondata['coupon_subtitle'] = $couponinfo['subtitle'];
        $coupondata['coupon_price'] = $couponinfo['coupon_price'];
        $coupondata['use_min_price'] = $couponinfo['use_min_price'];
        $coupondata['use_count'] = $couponinfo['use_count'];
        $coupondata['type'] = $couponinfo['type'];
        $coupondata['act_type'] = $type;
        $coupondata['use_time'] = $usetime;
        $coupondata['end_time'] = $endtime;
        $coupondata['created_at'] = date('Y-m-d H:i:s');
        return self::insertGetId($coupondata);
    }

    /**
     * 优惠券是否被领取并且使用
     * @param $userid
     * @param $id
     */
    public function isUsedCoupon($userid,$id){
        return self::where('uid',$userid)->where('issue_id',$id)->count();
    }
    /**
     * 优惠券是否已有效购买
     * @param $userid
     * @param $id
     */
    public function isBuyCoupon($userid,$id){
        return self::where('uid',$userid)->where('issue_id',$id)->whereIn('status',[0,1])->where('act_type',4)->count();
    }
    /**
     * 已售
     * @param $userid
     * @param $id
     */
    public function salseCoupon($id){
        return self::where('issue_id',$id)->whereIn('status',[0,1])->where('act_type',4)->count();
    }

    /*获取可用优惠券总数*/
    public function getMyCouponCount($userid){
        $time = date('Y-m-d H:i:s',time());
        return self::where('uid',$userid)->where('end_time','>',$time)->where('status',0)->count();
    }
    /*获取优惠券数*/
    public function getCouponCount($userid){
        $time = date('Y-m-d H:i:s',time());
        $data['all']=self::where('uid',$userid)->count();
        $data['canuse']=self::where('uid',$userid)->where('status',0)->count();
        $data['expried']=self::where('uid',$userid)->where('status',2)->count();
        $data['already']=self::where('uid',$userid)->where('status',1)->count();
        return $data;
    }

    /*获取优惠券列表*/
    public function getCouponList($userid,$status=0,$limit=15){
        $where = [];
        if($status == 1){
            $where['status'] = 0;
        }elseif ($status == 2){
            $where['status'] = 2;
        }elseif ($status == 3){
            $where['status'] = 1;
        }
        $list = self::where('uid',$userid)->where($where)
            ->orderBy('created_at','desc')
            ->select('id','issue_id','coupon_title','coupon_subtitle','coupon_price','use_min_price','use_time','end_time','act_type','type','use_count','status')
            ->paginate($limit);
        if($list){
            $list->each(function ($item, $key) {
                $item['created_at'] = $item['use_time'];
            });
        }
        return $list;
    }
    /*设置优惠券过期状态*/
    public function setCouponExpired($userid){
        $time = date('Y-m-d H:i:s',time());
        $list = self::where('uid',$userid)->where('end_time','<',$time)->where('status',0)->get()->toArray();
        if($list){
            foreach ($list as $key=>$val){
                self::updateStatus($val['id'],2);
            }
        }
    }

    /*订单可用优惠券*/
    public function beUsableCoupon($uid,$price,$cartInfo){
        $time = date('Y-m-d H:i:s',time());
        $list = self::where('uid',$uid)->where('status',0)
            ->where('use_min_price','<=',$price)
            ->whereDate('use_time','<=',$time)->whereDate('end_time','>=',$time)
            ->orderBy('created_at','desc')
            ->select('id','cid','coupon_title','coupon_price','use_min_price','use_time','end_time','type','use_count','created_at')
            ->get();
        $list=count($list) ? $list->toArray() : [];
        /*是否适用产品*/
        if($list){
            foreach ($list as $key=>$val){
                $list[$key]['canuse']=1;
                $list[$key]['overlay']=0;
                $canuse = 1;
                $couponInfo = storeCoupon::getRowByid($val['cid']);
                if($couponInfo && $couponInfo['pro_limit_type'] == 2){
                    $productIdArr = DB::table('store_coupon_allow')->where('coupon_id',$val['cid'])->select('id','product_id')->get()->toArray();
                    $proids = [];
                    foreach ($productIdArr as $prokey=> $pro){
                        $proids[$prokey] = $pro->product_id;
                    }
                    if(!$proids)continue;
                    $count = 0;
                    $totalprice = 0;
                    foreach ($cartInfo as $cart){
                        if(in_array($cart['product_id'],$proids)){
                            $totalprice += $cart['truePrice']*$cart['cart_num'];
                            $count ++;
                        }
                    }
                    if($count <= 0 || $val['use_min_price'] > $totalprice){
                        $canuse = 0;
                    }
                }
                if($canuse == 0){
                    unset($list[$key]);continue;
                }
            }
        }
        $list=count($list) ? array_values($list) : [];
        return $list;
    }
    /*订单累计可用优惠券*/
    public function canUseCoupon($couponlist,$totalprice,$couponid,$couponPrice,$mincouponPrice){
        /*是否适用产品*/
        $remainPrice = $totalprice - $couponPrice;
        if($couponid){
            $arrCoupon = explode(',',$couponid);
            $count = count($arrCoupon);
            //if($count == 1){
                $couponInfo = self::getRowByid($arrCoupon[0]);
                $type = $couponInfo['type'];
                $use_count = $couponInfo['use_count'];
                foreach ($couponlist as $key=>&$val){
                    $val['canuse'] = 0;
                    $val['overlay'] = 0;
                    if($val['type'] == $type){
                        $val['canuse'] = 1;
                        if($remainPrice >0 && $remainPrice >= $val['use_min_price'] && $use_count > $count){
                            $val['overlay'] = 1;
                        }
                    }
                }
           /* }else{

            }*/
        }
        return $couponlist;
    }
    /*获取优惠券信息*/
    public function getRowByid($id){
        $time = date('Y-m-d H:i:s',time());
        return self::where('id',$id)->where('status',0)->where('end_time','>=',$time)->first();
    }

    /*使用优惠券*/
    public function useCoupon($id){
        self::updateStatus($id,1);
    }
    public function updateStatus($id,$status=2){
        $updata = [];
        $updata['status'] = $status;
        $updata['updated_at'] = date('Y-m-d H:i:s');
        self::where('id',$id)->update($updata);
    }
}
