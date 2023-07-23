<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class systemConfig extends Model
{
    use SoftDeletes;
    protected $table = "system_config";
    protected $fillable = [];
    protected $guarded = [];
    protected $appends = [];
    protected $hidden = [];
    public function getHomeActivityAttribute($value)
    {
        return array_values(json_decode($value, true) ?: []);
    }

    public function setHomeActivityAttribute($value)
    {
        $this->attributes['home_activity'] = json_encode(array_values($value));
    }

    public function getActivity($limit=4){
        $home_activity = systemConfig::where('id',1)->value('home_activity');
        $activity = [];
        if($home_activity){
            foreach ($home_activity as $key=>$val){
                $activity[$key]['title'] = $val['title'];
                $activity[$key]['couponissue_id'] = $val['coupon'];
                $productlist = storeCouponIssue::getHomeCouponProList($val['coupon'],$limit);
                if(!$productlist || $productlist==false){
                    unset($activity[$key]);
                    continue;
                }
                $activity[$key]['productlist'] = $productlist;
            }
        }
        return array_values($activity);
    }
}
