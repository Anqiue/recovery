<?php

namespace App\Listeners;

use App\Models\userAddress;
use App\Models\userMasterApplication;
use Hanson\LaravelAdminWechat\Events\DecryptUserInfo;
use Hanson\LaravelAdminWechat\Models\WechatUser;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AfterSaveMaster
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\Hanson\LaravelAdminWechat\Events\DecryptUserInfo  $event
     * @return void
     */
    public function handle(DecryptUserInfo $event)
    {
        $userinfo = $event->wechatUser;
        try {
            Log::info(11111222);
            $mobile = $userinfo['mobile'];
            $master = userMasterApplication::where('mobile',$mobile)->where('status',3)->first();
            if($master && $userinfo['application_id'] == 0){
                $operateId = userAddress::masterArea($master['province'],$master['city']);
                WechatUser::where('id',$userinfo['id'])->update([
                    'name'=>$master['name'],
                    'application_id'=>$master['id'],
                    'operate_id'=>$operateId,
                    'updated_at'=>date('Y-m-d H:i:s')
                ]);
            }
        }catch (\Exception $exception) {
            Log::error('师傅端注册后错误：'.$exception->getLine().'-'.$exception->getMessage());
        }
    }
}
