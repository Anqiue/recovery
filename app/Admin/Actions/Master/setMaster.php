<?php

namespace App\Admin\Actions\Master;

use App\Models\userMasterLevel;
use Encore\Admin\Actions\RowAction;
use Hanson\LaravelAdminWechat\Models\WechatUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class setMaster extends RowAction
{
    public $name = '师傅设置';

    public function handle(Model $model,Request $request)
    {
        try{
            $id = $this->getKey();
            $pid = $request->pid?:0;
            $master_level = $request->master_level;
            $master_status = $request->master_status;
            //$base_wage = $request->base_wage;
            $policy_subsidy = $request->policy_subsidy;
            if(!$policy_subsidy)return $this->response()->error('参数错误');
            WechatUser::where('id',$id)->update([
                'master_level'=>$master_level,
                'pid'=>$pid,
                'master_status'=>$master_status,
               // 'base_wage'=>$base_wage,
                'policy_subsidy'=>$policy_subsidy,
                'updated_at'=>date('Y-m-d H:i:s'),
            ]);
            return $this->response()->success('设置成功.')->refresh();
        }catch(\Exception $e){
            return $this->response()->error($e->getMessage());
        }
    }
    public function form()
    {
        $data = $this->data();
        $this->radio('master_status', '状态')->options(['1'=>'空闲','2'=>'忙碌'])->default($data['master_status']);
        $this->select('pid', '所属工长')->options(WechatUser::where('pid',0)->where('type',2)->where('master_type',1)->pluck('name','id'))->default($data['pid']);
        $this->select('master_level', '师傅等级')->options(userMasterLevel::where('status',1)->pluck('level_title','id'))->default($data['master_level']);
        //$this->text('base_wage', '基础工价/元')->default($data['base_wage']);
        $this->text('policy_subsidy', '政策补贴/元')->default($data['policy_subsidy']);
    }
    /**
     * The data of the form.
     *
     * @return array
     */
    public function data()
    {
        $id = $this->getKey();
        $row = WechatUser::where('id',$id)->first();
        return [
            'pid' => $row->pid,
            'master_level' =>$row->master_level,
            'master_status' =>$row->master_status,
            //'base_wage' =>$row->base_wage,
            'policy_subsidy' =>$row->policy_subsidy,
        ];
    }
}
