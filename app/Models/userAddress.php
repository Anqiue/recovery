<?php

namespace App\Models;

use Encore\Admin\Traits\DefaultDatetimeFormat;
use Hanson\LaravelAdminWechat\Models\WechatUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class userAddress extends Model
{
    use SoftDeletes;
    use DefaultDatetimeFormat;

    protected $table = "user_address";
    protected $fillable = [];
    protected $guarded = [];
    protected $appends = [];
    protected $hidden = [];
    public function user()
    {
        return $this->belongsTo(WechatUser::class,'uid');
    }
    /**
     * 添加地址
     * @param $userid
     * @param $name
     * @param $mobile
     * @param $province
     * @param $city
     * @param $district
     * @param $detail
     * @param int $is_default
     * @return mixed
     */
    public function insertRow($userid,$name,$mobile,$province,$city,$district,$province_name,$city_name,$district_name,$detail,$is_default=0){
        if($is_default == 1){//如果原来有默认 则修改为0
            $row = self::where('uid',$userid)->where('is_default',1)->first();
            if($row){
                $update = [];
                $update['is_default'] = 0;
                $update['updated_at'] = date('Y-m-d H:i:s');
                self::where('id',$row->id)->update($update);
            }
        }
        /*插入数据*/
        $newdata = [];
        $newdata['uid'] = $userid;
        $newdata['real_name'] = $name;
        $newdata['phone'] = $mobile;
        $newdata['province'] = $province;
        $newdata['city'] = $city;
        $newdata['district'] = $district;
        $newdata['province_name'] = $province_name;
        $newdata['city_name'] = $city_name;
        $newdata['district_name'] = $district_name;
        $newdata['detail'] = $detail;
        $newdata['is_default'] = $is_default;
        $newdata['created_at'] = date('Y-m-d H:i:s');
        return self::insertGetId($newdata);
    }
    /**
     * 添加微信地址
     * @param $userid
     * @param $name
     * @param $mobile
     * @param $province
     * @param $city
     * @param $district
     * @param $detail
     * @param int $is_default
     * @return mixed
     */
    public function insertWechatRow($userid,$name,$mobile,$provinceName,$cityName,$countyName,$detail,$is_default=0){
        $province = DB::table('china_area')->where('name',$provinceName)->value('code');
        $city = DB::table('china_area')->where('name',$cityName)->value('code');
        $district = DB::table('china_area')->where('name',$countyName)->value('code');
        /*插入数据*/
        $newdata = [];
        $newdata['uid'] = $userid;
        $newdata['real_name'] = $name;
        $newdata['phone'] = $mobile;
        $newdata['province'] = $province;
        $newdata['city'] = $city;
        $newdata['district'] = $district;
        $newdata['province_name'] = $provinceName;
        $newdata['city_name'] = $cityName;
        $newdata['district_name'] = $countyName;
        $newdata['detail'] = $detail;
        $newdata['is_default'] = $is_default;
        $newdata['created_at'] = date('Y-m-d H:i:s');
        return self::insertGetId($newdata);
    }

    /**
     * 添加地址
     * @param $userid
     * @param $name
     * @param $mobile
     * @param $province
     * @param $city
     * @param $district
     * @param $detail
     * @param int $is_default
     * @return mixed
     */
    public function updateRow($userid,$addrid,$name,$mobile,$province,$city,$district,$detail,$is_default=0){
        if($is_default == 1){//如果原来有默认 则修改为0
            $row = self::where('id','<>',$addrid)->where('uid',$userid)->where('is_default',1)->first();
            if($row){
                $update = [];
                $update['is_default'] = 0;
                $update['updated_at'] = date('Y-m-d H:i:s');
                self::where('id',$row->id)->update($update);
            }
        }
        /*更新数据*/
        $newdata = [];
        $newdata['real_name'] = $name;
        $newdata['phone'] = $mobile;
        $newdata['province'] = $province;
        $newdata['city'] = $city;
        $newdata['district'] = $district;
        $newdata['detail'] = $detail;
        $newdata['is_default'] = $is_default;
        $newdata['updated_at'] = date('Y-m-d H:i:s');
        return self::where('id',$addrid)->update($newdata);
    }

    /**
     * 我的地址列表
     * @param $userid
     * @return array
     */
    public function getMyList($userid){
        $list = self::where('uid',$userid)->orderBy('created_at','desc')->get();
        $list=count($list) ? $list->toArray() : [];
        return $list;
    }
    /**
     * 我的地址列表
     * @param $userid
     * @return array
     */
    public function updateaddr(){
        $list = self::orderBy('created_at','desc')->get()->toArray();
        foreach ($list as $key=>$val){
            $province_name = DB::table('china_area')->where('code',$val['province'])->value('name');
            $city_name = DB::table('china_area')->where('code',$val['city'])->value('name');
            $district_name = DB::table('china_area')->where('code',$val['district'])->value('name');
            self::where('id',$val['id'])->update([
                'province_name'=>$province_name,
                'city_name'=>$city_name,
                'district_name'=>$district_name,
            ]);
        }
    }

    /**
     * 获取地址
     * @param $userid
     * @return mixed
     */
    public function getAddrById($userid,$id){
        $row = self::where('uid',$userid)->where('id',$id)->first();
        return $row?$row:[];
    }
    /**
     * 获取默认地址
     * @param $userid
     * @return mixed
     */
    public function getDefaultAddr($userid){
        $row = self::where('uid',$userid)->where('is_default',1)->first();
        return $row?$row:[];
    }

    public function inUsersArea($addrid){
        $addr = self::where('id',$addrid)->first();
        $userModel = config('admin.database.users_model');
        $userList = $userModel::where('type',2)->select('id','username','area')->get()->toArray();
        $inArea = 0;
        foreach ($userList as $key=>$val){
            foreach ($val['area'] as $area){
                if($addr->province == $area['province_id'] && $addr->city == $area['city_id']){
                    /*运营中心是否存在对应师傅*/
                    $master = WechatUser::autoGetMasterId($val['id'],$area['province_id'],$area['city_id']);
                    if($master){
                        $inArea = $val['id'];
                        break;
                    }
                }
            }
        }
        return $inArea;
    }
    public function hasUsersArea($addrid){
        $addr = self::where('id',$addrid)->first();
        $userModel = config('admin.database.users_model');
        $userList = $userModel::where('type',2)->select('id','username','area')->get()->toArray();
        $inArea = 0;
        foreach ($userList as $key=>$val){
            foreach ($val['area'] as $area){
                if($addr->province == $area['province_id'] && $addr->city == $area['city_id']){
                    $inArea = $val['id'];
                    break;
                }
            }
        }
        return $inArea;
    }

    /**
     * 师傅所属运营中心
     * @param $province_id
     * @param $city_id
     * @return int
     */
    public function masterArea($province_id,$city_id){
        $userModel = config('admin.database.users_model');
        $userList = $userModel::where('type',2)->select('id','username','area')->get()->toArray();
        $inArea = 0;
        foreach ($userList as $key=>$val){
            foreach ($val['area'] as $area){
                if($province_id == $area['province_id'] && $city_id == $area['city_id']){
                    $inArea = $val['id'];
                    break;
                }
            }
        }
        return $inArea;
    }
}
