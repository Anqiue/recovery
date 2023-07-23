<?php

namespace App\Models;

use Encore\Admin\Traits\DefaultDatetimeFormat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class storeCategory extends Model
{
    use SoftDeletes;
    use DefaultDatetimeFormat;

    protected $table = "store_category";
    protected $fillable = [];
    protected $guarded = [];
    protected $appends = [];
    protected $hidden = [];

    /*世界列表页*/
    public function getContentList(){
        $list = self::where('status',1)
            ->orderBy('sort','asc')->orderBy('id','desc')
            ->select('id','cate_name','pic')->get();
        if($list){
            $list->each(function ($item, $key) {
                $item['pic'] = Config::get('app.oss_cdn').$item['pic'];
            });
        }
        $list=count($list) ? $list->toArray() : [];
        return $list;
    }
}
