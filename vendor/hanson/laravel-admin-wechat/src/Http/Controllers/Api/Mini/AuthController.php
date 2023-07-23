<?php

namespace Hanson\LaravelAdminWechat\Http\Controllers\Api\Mini;

use Hanson\LaravelAdminWechat\Events\DecryptMobile;
use Hanson\LaravelAdminWechat\Events\DecryptUserInfo;
use Hanson\LaravelAdminWechat\Models\WechatUser;
use Hanson\LaravelAdminWechat\Services\MiniService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function checkToken()
    {
        $wechatUser = Auth::guard('mini')->user();

        return ok($wechatUser);
    }

    public function login(Request $request, MiniService $service)
    {
        $request->validate([
            'code' => 'required',
            'app_id' => 'required',
        ]);

        $session = $service->session($appId = $request->get('app_id'), $request->get('code'));

        $type = 1;
        if($appId == 'wx06dca285f93fa366'){
            $type = 2;//师傅
        }
        $wechatUser = config('admin.extensions.wechat.wechat_user', WechatUser::class)::query()->updateOrCreate([
            'openid' => $session['openid'],
            'app_id' => $appId,
        ],[
            'type' => $type,
        ]);

        $token = auth('mini')->login($wechatUser);
        return ok([
            'access_token' => 'bearer '.$token,
            'expires_in' => auth('mini')->factory()->getTTL() * 60,
            'wechat_user' => $wechatUser,
        ]);
    }

    protected function decryptMobile(Request $request, MiniService $service)
    {
        $request->validate([
            'iv' => 'required',
            'encrypted_data' => 'required',
            'app_id' => 'required',
        ]);

        $wechatUser = Auth::guard('mini')->user();

        $decryptedData = $service->decrypt($request->get('app_id'), $wechatUser->openid, $request->get('iv'), $request->get('encrypted_data'));
       // event(new DecryptMobile($decryptedData, $wechatUser));
        if($decryptedData && isset($decryptedData['phoneNumber'])){
            $wechatUser->update([
                'mobile' => $decryptedData['phoneNumber'],
            ]);
        }
        //Log::info('mobileinfo:'.json_encode($wechatUser));
        if($request->get('app_id') == 'wx06dca285f93fa366'){
            event(new DecryptUserInfo($decryptedData, $wechatUser));
        }

        return ok($decryptedData);
    }

    protected function decryptUserInfo(Request $request, MiniService $service)
    {
        $request->validate([
            'iv' => 'required',
            'encrypted_data' => 'required',
            'app_id' => 'required',
        ]);

        $wechatUser = Auth::guard('mini')->user();

        $decryptedData = $service->decrypt($request->get('app_id'), $wechatUser->openid, $request->get('iv'), $request->get('encrypted_data'));
        $wechatUser->update([
            'nickname' => $decryptedData['nickName'],
            'country' => $decryptedData['country'],
            'province' => $decryptedData['province'],
            'city' => $decryptedData['city'],
            'gender' => $decryptedData['gender'],
            'avatar' => $decryptedData['avatarUrl'],
        ]);

        //event(new DecryptUserInfo($decryptedData, $wechatUser));

        return ok($decryptedData);
    }
}
