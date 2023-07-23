<?php

namespace App\Admin\Actions\Master;

use Encore\Admin\Actions\RowAction;
use Hanson\LaravelAdminWechat\Models\WechatUser;
use Illuminate\Database\Eloquent\Model;

class masterInfo extends RowAction
{
    public $name = '师傅申请详情';

    /**
     * @return  string
     */
    public function href()
    {
        $applicationId = WechatUser::where('id',$this->getKey())->value('application_id');
        return "/admin/users/user_master_application/".$applicationId;
    }

}
