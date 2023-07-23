<?php
namespace App\Models;

use Encore\Admin\Traits\DefaultDatetimeFormat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class storeVisit extends Model{
    use SoftDeletes;
    use DefaultDatetimeFormat;
    protected $table="store_visit";
    protected $fillable=[];
    protected $guarded=[];
    protected $appends=[];
    protected $hidden=[];

    /**
     * 统计
     * @param $mer_id
     * @param $start_time
     * @param $endday
     * @param string $type
     * @return mixed
     */
    public function getVisitTotal($start_time,$endday,$type='sum'){
        switch ($type){
            case 'counttoday':
                return self::whereDate('created_at',$endday)->count();
                break;
            default:
                return self::whereDate('created_at','>=',$start_time)->whereDate('created_at','<=',$endday)->count();
        }
    }
}
