<?php

namespace App\Admin\Actions\Master;

use Encore\Admin\Actions\RowAction;
use Hanson\LaravelAdminWechat\Models\WechatUser;
use Illuminate\Database\Eloquent\Model;

class setForeman extends RowAction
{
    public $name = '设置工长';

    public function handle(Model $model)
    {
        $id = $this->getKey();
        try{
            $userInfo = WechatUser::where('id',$id)->first();
            if(!$userInfo)return $this->response()->error('用户信息不存在.');
            WechatUser::where('id',$id)->update([
                'master_type'=>1,
                'updated_at'=>date('Y-m-d H:i:s'),
            ]);
            return $this->response()->success('设置成功.')->refresh();
        }catch(\Exception $e){
            return $this->response()->error('操作失败');
        }
    }
    public function dialog()
    {
        $this->confirm('确定设置此人为工长？');
    }
}
