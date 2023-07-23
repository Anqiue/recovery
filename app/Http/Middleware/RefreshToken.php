<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;


class RefreshToken extends BaseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {

        try {
            /**
             *TODO 获取用户信息的方法可封装起来
             *对应的放回参数可根据个人习惯进行自定义
             */
            $token = JWTAuth::getToken();
            if ($token) JWTAuth::setToken($token);
            if (Auth::guard('mini')->user() == null){
                return response(['code' => 400, 'msg' => '无此用户']);
            }else{
                $user = Auth::guard('mini')->user();
                request()->offsetSet('wechat_user_id', $user['id']);
            }
        } catch (TokenExpiredException $e) {
            return response(['code' => 400, 'msg' => '登录过期，请重新登录']);
            /* return response()->json([
                 'errcode' => 400001,
                 'errmsg' => 'token 过期'
             ]);*/
            // 此处捕获到了 token 过期所抛出的 TokenExpiredException 异常，我们在这里需要做的是刷新该用户的 token 并将它添加到响应头中
            /*try {
                // 刷新用户的 token
                $token = $this->auth->refresh();
                // 使用一次性登录以保证此次请求的成功
                Auth::guard('mini')->onceUsingId($this->auth->manager()->getPayloadFactory()->buildClaimsCollection()->toPlainArray()['sub']);
            } catch (JWTException $exception) {
                return response(['code' => 400, 'msg' => '登录过期，请重新登录']);
                // 如果捕获到此异常，即代表 refresh 也过期了，用户无法刷新令牌，需要重新登录。
                //throw new UnauthorizedHttpException('jwt-auth', $exception->getMessage());
            }*/
            // 在响应头中返回新的 token
           // return $this->setAuthenticationHeader($next($request), $token);

        } catch (TokenInvalidException $e) {
            return response(['code' => 400, 'msg' => '登录过期，请重新登录']);
        } catch (JWTException $e) {
            return response(['code' => 400, 'msg' => '登录过期，请重新登录']);
        }
        return $next($request);
    }
}
