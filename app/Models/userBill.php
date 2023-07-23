<?php

namespace App\Models;

use Carbon\Carbon;
use Encore\Admin\Traits\DefaultDatetimeFormat;
use Hanson\LaravelAdminWechat\Models\WechatUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class userBill extends Model
{
    use SoftDeletes;
    use DefaultDatetimeFormat;
    protected $table = "user_bill";
    protected $fillable = [];
    protected $guarded = [];
    protected $appends = [];
    protected $hidden = [];

    public function user()
    {
        return $this->belongsTo(WechatUser::class,'uid');
    }

    public static function insertRow($pm,$title,$uid,$category,$type,$number,$link_id = 0,$mark = '')
    {
        return self::insertGetId([
            'uid' =>$uid,
            'link_id' =>$link_id,
            'pm' =>$pm,
            'title' =>$title,
            'category' =>$category,
            'type' =>$type,
            'number' =>$number,
            'mark' =>$mark,
            'created_at' =>date('Y-m-d H:i:s')
        ]);
    }

    /**
     * 我的钱包列表
     * @param $userid
     * @param int $type
     * @param int $limit
     * @return mixed
     */
    public function getMyPurse($userid,$type=0,$limit=15,$datestart='',$dateend=''){
        $where = [];
        if($type == 1){//收入
            $where['pm'] = 1;
        }elseif($type == 2){//指出
            $where['pm'] = 0;
        }
        $model = self::where('uid',$userid);
        $model = self::validWhere($model,$datestart,$dateend);
        $list = $model->where($where)
            ->select('id','pm','title','number','created_at')
            ->orderBy('created_at','desc')
            ->paginate($limit);
        return $list;
    }

    public function validWhere($model,$datestart='',$dateend=''){
        $datestart = $datestart.' 00:00:00';
        $dateend = $dateend.' 23:59:59';
        $model->whereBetween('created_at',[$datestart,$dateend]);
        return $model;
    }
    /*钱包余额*/
    public function getPurseBalance($userid){
        $income = self::where('uid',$userid)->where('status',1)->where('pm',1)->sum('number');//总收入
        $expenditure = self::where('uid',$userid)->where('status',1)->where('pm',0)->sum('number');//总支出
        return bcsub($income,$expenditure,2);
    }
}
