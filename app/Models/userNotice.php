<?php

namespace App\Models;

use Encore\Admin\Traits\DefaultDatetimeFormat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class userNotice extends Model
{
    use SoftDeletes;
    use DefaultDatetimeFormat;
    protected $table = "user_notice";
    protected $fillable = [];
    protected $guarded = [];
    protected $appends = [];
    protected $hidden = [];

    public function sendNotice($uid,$orderId,$orderno,$title,$content,$type=1){
        $data = [];
        $data['uid'] = $uid;
        $data['order_id'] = $orderId;
        $data['order_no'] = $orderno;
        $data['type'] = $type;//1-用户 2-师傅
        $data['title'] = $title;
        $data['content'] = $content;
        $data['send_time'] = date('Y-m-d');
        $data['created_at'] = date('Y-m-d H:i:s');
        self::insert($data);
    }

    /*获取我的消息*/
    public function getMyListByUid($userid,$is_read,$limit=15,$type=1){
        $where = [];
        if($is_read == 1){//已读
            $where['is_read'] = 1;
        }elseif ($is_read == 2){//未读
            $where['is_read'] = 0;
        }
        $list = self::where('uid',$userid)->where('type',$type)->where($where)
            ->orderBy('created_at','desc')
            ->select('id','order_id','order_no','title','send_time','is_read')
            ->paginate($limit);
        if($list){
            $list->each(function ($item, $key) {
                if($item['is_read'] == 1){
                    $item['is_read_item'] = '已读';
                }else{
                    $item['is_read_item'] = '未读';
                }
            });
        }
        return $list;
    }

    public function getTotal($userid,$type){
        $total = self::where('uid',$userid)->where('type',$type)->count();
        $readtotal = self::where('uid',$userid)->where('type',$type)->where('is_read',1)->count();
        $unreadtotal = self::where('uid',$userid)->where('type',$type)->where('is_read',0)->count();
        return compact('total','readtotal','unreadtotal');
    }
    /**
     * 获取详情
     * @param $id
     * @return mixed
     */
    public function getRowById($id){
        return self::where('id',$id)->select('id','order_id','order_no','title','content','send_time','is_read')->first();
    }

    public function deleteNotice($id){
        return self::where('id',$id)->delete();
    }
}
