<?php

namespace Hanson\LaravelAdminWechat\Models;

use Encore\Admin\Traits\DefaultDatetimeFormat;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class WechatUser extends Authenticatable implements JWTSubject
{
    use DefaultDatetimeFormat;
    protected $guarded = [];

    /*protected $appends = ['gender_readable'];

    public function getGenderReadableAttribute()
    {
        if ($this->attributes['gender'] ?? false) {
            return [0 => '未知', 1 => '男', 2 => '女'][$this->attributes['gender']];
        }
    }*/

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
    /*获取昵称*/
    public function getNickNameById($id){
        return self::where('id',$id)->value('nickname');
    }
    /*获取用户详情*/
    public function getUserByOpenid($openid){
        return self::where('openid',$openid)->lockForUpdate()->first();
    }
    /*获取积分*/
    public function getFieldsById($id,$field = 'name'){
        return self::where('id',$id)->value($field);
    }
    /*获取用户详情*/
    public function getUserById($id){
        return self::where('id',$id)->lockForUpdate()->first();
    }

    public function autoGetMasterId($oprate_id,$province,$city){
        $row = self::where('wechat_users.operate_id',$oprate_id)->where('wechat_users.master_status',1)
            ->join('user_master_application as application', 'wechat_users.application_id', '=', 'application.id')
            ->where('application.province',$province)->where('application.city',$city)
            ->join('user_master_level as level','wechat_users.master_level','=','level.id')
            ->select('wechat_users.id')
            ->orderBy('level.grade','desc')
            ->first();
        return $row;
    }
    /*指定师傅列表*/
    public function getMasterList($oprate_id,$province,$city){
        $list = self::where('wechat_users.operate_id',$oprate_id)->where('wechat_users.master_status',1)
            ->join('user_master_application as application', 'wechat_users.application_id', '=', 'application.id')
            ->where('application.province',$province)->where('application.city',$city)
            ->join('user_master_level as level','wechat_users.master_level','=','level.id')
            ->select('wechat_users.id','wechat_users.name')
            ->orderBy('level.grade','desc')
            ->get()->toArray();
        $master = [];
        foreach ($list as $key=>$val){
            $master[$val['id']] = $val['name'];
        }
        return $master;
    }

    public function getMasterCount($operate_id){
        $where = [];
        if($operate_id > 0){
            $where['operate_id'] = $operate_id;
        }
        return self::where($where)->where('application_id','>',0)->where('type',2)->count();
    }
}
