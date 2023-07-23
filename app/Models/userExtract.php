<?php

namespace App\Models;

use Encore\Admin\Traits\DefaultDatetimeFormat;
use Hanson\LaravelAdminWechat\Models\WechatUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class userExtract extends Model
{
    use SoftDeletes;
    use DefaultDatetimeFormat;
    protected $table = "user_extract";
    protected $fillable = [];
    protected $guarded = [];
    protected $appends = [];
    protected $hidden = [];

    public function user()
    {
        return $this->belongsTo(WechatUser::class,'uid');
    }

    public function insertRow($uid,$price=0){
        $data = [];
        $data['uid'] = $uid;
        $data['extract_price'] = $price;
        $data['mark'] = '申请提现';
        $data['status'] = 0;
        $data['created_at'] = date('Y-m-d H:i:s');
        return self::insertGetId($data);
    }

    public function getTodayExtract($operate_id,$start_time,$endday,$type='today'){
        $operate_ids = [];
        if($operate_id > 0){
            $userids = WechatUser::where('operate_id',$operate_id)->where('type',2)->select('id')->get()->toArray();
            if ($userids){
                foreach ($userids as $key=> $ids){
                    $operate_ids[$key] = $ids['id'];
                }
            }
        }
        if($operate_ids){
            $model = self::whereIn('uid',$operate_ids)->where('status',0);
        }else{
            $model = self::where('status',0);
        }
        switch ($type){
            case 'today':
                return $model->whereDate('created_at','>=',$start_time)->whereDate('created_at','<=',$endday)->count();
            default:
                return $model->count();
        }
    }
}
