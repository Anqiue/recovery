<?php

namespace App\Admin\Actions\Master;

use App\Models\userMasterApplication;
use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class auditApplication extends RowAction
{
    public $name = '审核';

    public function handle(Model $model, Request $request)
    {
        $id = $this->getKey();
        $status = $request->status;
        $reason = $request->reason?:'';
        $data['status'] = $status;
        $data['reason'] = $reason;
        $res = userMasterApplication::where('id',$id)->update($data);
        if($res){
            return $this->response()->success('审核成功.')->refresh();
        }else{
            return $this->response()->error('审核失败,请稍后重试！')->refresh();
        }
    }
    public function form()
    {
        $this->radio('status', '状态')->options(['1'=>'通过','2'=>'不通过'])->default(1);
        $this->textarea('reason','理由');
    }
}
