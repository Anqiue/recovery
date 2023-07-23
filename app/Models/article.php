<?php

namespace App\Models;

use Carbon\Carbon;
use Encore\Admin\Traits\DefaultDatetimeFormat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Config;

class article extends Model
{
    use SoftDeletes;
    use DefaultDatetimeFormat;
    protected $table = "article";
    protected $fillable = [];
    protected $guarded = [];
    protected $appends = [];
    protected $hidden = [];
    public function getCreatedAtAttribute($value){
        return Carbon::createFromFormat('Y-m-d H:i:s', $value)->diffForHumans();
    }
    /*获取一条推荐文章*/
    public function getRecommend(){
        $row = self::where('status',1)->where('recommend',1)
            ->orderBy('sort','asc')
            ->orderBy('created_at','desc')
            ->select('id','cid','title','image','synopsis','browse')
            ->first();
        if($row){
            $row['image'] = Config::get('app.oss_cdn').$row['image'];
        }
        return $row?$row:[];
    }

    /**
     * 获取详情
     * @param $id
     * @return array
     */
    public function getRowById($id){
        $row = self::where('id',$id)->where('status',1)
            ->select('id','cid','title','author','image','synopsis','browse','content','created_at')
            ->lockForUpdate()
            ->first();
        if($row){
            $row['image'] = Config::get('app.oss_cdn').$row['image'];
        }
        return $row?$row:[];
    }

    public function getArticleByCid($cid,$limit){
        $list = self::where('status',1)->where('cid',$cid)
            ->orderBy('sort','asc')
            ->orderBy('created_at','desc')
            ->select('id','cid','title','image','synopsis','browse','created_at')
            ->limit($limit)
            ->get();
        if($list){
            $list->each(function ($item, $key) {
                $item['image'] = Config::get('app.oss_cdn').$item['image'];
            });
        }
        $list=count($list) ? $list->toArray() : [];
        return $list;
    }

    public function getListByCid($cid,$limit=15){
        $list = self::where('status',1)->where('cid',$cid)
            ->orderBy('sort','asc')
            ->orderBy('created_at','desc')
            ->select('id','cid','title','image','synopsis','browse','created_at')
            ->paginate($limit);
        if($list){
            $list->each(function ($item, $key) {
                $item['image'] = Config::get('app.oss_cdn').$item['image'];
            });
        }
        return $list;
    }
    public function getListByKeyword($keyword,$limit=15){
        $list = self::where('status',1)->where('title', 'like', '%'.$keyword.'%')
            ->orderBy('sort','asc')
            ->orderBy('created_at','desc')
            ->select('id','cid','title','image','synopsis','browse','created_at')
            ->paginate($limit);
        if($list){
            $list->each(function ($item, $key) {
                $item['image'] = Config::get('app.oss_cdn').$item['image'];
            });
        }
        return $list;
    }
}
