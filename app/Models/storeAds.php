<?php

namespace App\Models;

use Encore\Admin\Traits\DefaultDatetimeFormat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Config;

class storeAds extends Model
{
    use SoftDeletes;
    use DefaultDatetimeFormat;

    protected $table = "store_ads";
    protected $fillable = [];
    protected $guarded = [];
    protected $appends = [];
    protected $hidden = [];

    /**
     * 获取单条广告
     * @param $mid
     * @param $position
     * @return mixed
     */
    public function getRow($position){
        $time = date('Y-m-d H:i:s',time());
        $row = self::where('status',1)
            ->where('position',$position)
            ->where('start_time','<=',$time)
            ->where('end_time','>',$time)
            ->orderBy('sort','asc')
            ->orderBy('id','desc')
            ->select('id','title','image','link')
            ->first();
        if($row)$row['image'] =  Config::get('app.oss_cdn').$row['image'];
        //if($row) $row['image'] = asset('upload/'.$row['image']);
        return $row?:'';
    }

    /**
     * 获取多条广告
     * @param $mid
     * @param $position
     * @return mixed
     */
    public function getList($position=1){
        $time = date('Y-m-d H:i:s',time());
        $list = self::where('status',1)
            ->where('position',$position)
            ->where('start_time','<=',$time)
            ->where('end_time','>',$time)
            ->orderBy('sort','asc')
            ->orderBy('id','desc')
            ->select('id','title','image','file','link','type')
            ->get();
        if($list){
            $list->each(function ($item, $key) {
                //$item['image'] = Config::get('app.oss_cdn').$item['image'];
                if($item['type'] == 1){//图片
                    $item['image'] = Config::get('app.oss_cdn').$item['image'];
                }else{//视频
                    $item['image'] = Config::get('app.oss_cdn').$item['image'];
                    $item['file'] = Config::get('app.oss_cdn').$item['file'];
                }
            });
        }
        $list=count($list) ? $list->toArray() : [];
        return $list;
    }
}
