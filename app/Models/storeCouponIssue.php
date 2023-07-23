<?php

namespace App\Models;

use Encore\Admin\Traits\DefaultDatetimeFormat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class storeCouponIssue extends Model
{
    use SoftDeletes;
    use DefaultDatetimeFormat;

    protected $table = "store_coupon_issue";
    protected $fillable = [];
    protected $guarded = [];
    protected $appends = [];
    protected $hidden = [];

    public function coupon()
    {
        return $this->belongsTo(storeCoupon::class,'cid');
    }

    /**
     * 获取优惠券领取列表
     * @param $userid
     * @return array
     */
    public function getCounponList($userid){
        $time = date('Y-m-d H:i:s',time());
        $list = self::where('store_coupon_issue.start_time','<=',$time)
            ->where('store_coupon_issue.end_time','>',$time)
            ->where('store_coupon_issue.status',1)
            ->join('store_coupon as coupon', 'store_coupon_issue.cid', '=', 'coupon.id')
            ->where('coupon.status',1)
            ->select('coupon.title','coupon.coupon_price','coupon.use_min_price','coupon.coupon_day','coupon.use_status','coupon.coupon_start','coupon.coupon_end',
                'store_coupon_issue.id','store_coupon_issue.cid','store_coupon_issue.total_count','store_coupon_issue.remain_count','store_coupon_issue.is_permanent')
            ->orderBy('coupon.sort','asc')->orderBy('store_coupon_issue.created_at','desc')
            ->get();
        $list=count($list) ? $list->toArray() : [];
        $list_coupon=[];
        if($list){
            foreach ($list as $k=>&$v){
                /*有效期*/
                $time = time();
                if($v['use_status'] == 1){
                    if(strtotime($v['coupon_end'].' 23:59:59')< $time){
                        continue;
                    }
                }
                $v['is_use'] = storeCouponUser::isUsedCoupon($userid,$v['id']);
                if(!$v['is_use']){
                    if($v['is_permanent'] == 0 && $v['total_count'] > 0 && $v['remain_count'] >0){
                        array_push($list_coupon,$v);
                    }else if($v['is_permanent'] == 1){
                        array_push($list_coupon,$v);
                    }
                }
            }
        }
        return $list;
    }

    /**
     * 获取
     * @param $couponId
     */
    public function getRowByid($couponId){
        $time = date('Y-m-d H:i:s',time());
        return self::where('store_coupon_issue.id',$couponId)->where('store_coupon_issue.start_time','<=',$time)
            ->where('store_coupon_issue.end_time','>',$time)
            ->where('store_coupon_issue.status',1)
            ->join('store_coupon as coupon', 'store_coupon_issue.cid', '=', 'coupon.id')
            ->where('coupon.status',1)
            ->select('store_coupon_issue.*','coupon.title','coupon.subtitle','coupon.coupon_price','coupon.use_min_price','coupon.coupon_day','coupon.use_status',
                'coupon.coupon_start','coupon.coupon_end','coupon.pro_limit_type','coupon.use_count','coupon.type','coupon.content')->lockForUpdate()
            ->first();
    }

    public function updateCount($id,$type=1){
        $row = self::getRowByid($id);
        $updata = [];
        if($type == 1){
            $updata['remain_count'] = $row->remain_count - 1;
        }else{
            $updata['remain_count'] = $row->remain_count + 1;
        }
        $updata['updated_at'] = date('Y-m-d H:i:s');
        self::where('id',$id)->update($updata);
    }

    /*根据ID获取优惠券*/
    public function getHomeCouponProList($id,$limit=4,$type=1){
        $info = self::getRowByid($id);
        if(!$info)return false;
        $productlist = [];
        if($info['pro_limit_type'] == 2) {
            $productIdArr = DB::table('store_coupon_allow')->where('coupon_id', $info['cid'])->select('id', 'product_id')->get()->toArray();
            foreach ($productIdArr as $prokey=> $pro){
                $proinfo = storeProduct::getProById($pro->product_id,['id','product_name','image','price','ot_price','sales','type','good_sku','asso_service_id']);
                if($proinfo){
                    $goodsku = json_decode($proinfo['good_sku'],true);
                    $asso_service_id = $proinfo['asso_service_id'];
                    if($goodsku['type'] == 'many'){
                        foreach ($goodsku['sku'] as $sk=> &$sval){
                            $sval['asso_service_info'] = [];
                            if(isset($sval['asso_service_id']) && $sval['asso_service_id'] >0){
                                $asso_service_id = $sval['asso_service_id'];
                                $sval['asso_service_info'] = storeProduct::getProById($sval['asso_service_id']);
                            }
                        }
                    }
                    $proinfo['good_sku'] = $goodsku;
                    /*服务包*/
                    $proinfo['asso_service_id'] = $asso_service_id;
                    $proinfo['asso_service_info'] = [];
                    if($asso_service_id && $asso_service_id > 0){
                        $proinfo['asso_service_info'] = storeProduct::getProById($asso_service_id);
                    }
                    $productlist[$prokey] = $proinfo;
                }
            }
            if($type ==1 && count($productlist) > $limit){
                $productlist = array_slice($productlist,0,$limit);
            }
        }else{
            if($type == 2){
                $limit = storeProduct::getCount();
            }
            $productlist = storeProduct::getHomeProductList('is_show',$limit);
        }
        return $productlist?array_values($productlist):[];
    }
    /*根据ID获取优惠券产品列表*/
    public function getCouponProList($id,$order,$dir){
        $info = self::getRowByid($id);
        if(!$info)return [];
        $proids = [];
        if($info['pro_limit_type'] == 2) {
            $productIdArr = DB::table('store_coupon_allow')->where('coupon_id', $info['cid'])->select('id', 'product_id')->get()->toArray();
            foreach ($productIdArr as $prokey=> $pro){
                $proids[$prokey] = $pro->product_id;
            }
        }
        $productlist = storeProduct::getListByCoupon($proids,$order,$dir);
        return $productlist;
    }
}
