<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Api\BaseController;
use App\Http\Controllers\Controller;
use App\Models\storeProduct;
use Illuminate\Http\Request;

class UploadFileController extends Controller
{
    public function uploadFile(Request $request)
    {
        $file = $request->file;
        if(!$file)return response(['code' => 400, 'msg' => '传递参数错误']);
       // $url = BaseController::addCos('attr',$file);
        $url = BaseController::addImages($file);
        if($url){
            return response(['code' => 200,'msg' => 'success','url'=>$url]);
        }else {
            return response(['code' => 400, 'msg' => '上传失败']);
        }
    }
    public function uploadMultipleImg(Request $request)
    {
        $files = $request->slider_image;
        if(!$files)return response(['code' => 400, 'msg' => '传递参数错误']);
        $slider_image = [];
        foreach ($files as $key=> $file){
            $slider_image[$key] = BaseController::addImages($file);
        }
        // $url = BaseController::addCos('multiple',$file);
        if($slider_image){
            return response(['code' => 200,'msg' => 'success','data'=>$slider_image]);
        }else {
            return response(['code' => 400, 'msg' => '上传失败']);
        }
    }
    public function deleteUploadImg(Request $request)
    {
        dd($request->all());
        $files = $request->slider_image;
        if(!$files)return response(['code' => 400, 'msg' => '传递参数错误']);
        $slider_image = [];
        foreach ($files as $key=> $file){
            $slider_image[$key] = BaseController::addImages($file);
        }
       // $url = BaseController::addCos('multiple',$file);
        if($slider_image){
            return response($slider_image);
        }else {
            return response(['code' => 400, 'msg' => '上传失败']);
        }
    }
    public function getServiceData(Request $request)
    {
       $data = storeProduct::where('is_show',1)->where('type',2)->select('product_name', 'id')->get()->toArray();
       // $url = BaseController::addCos('multiple',$file);
        if($data){
            return response(['code' => 200,'msg' => 'success','data'=>$data]);
        }else {
            return response(['code' => 400, 'msg' => 'fail']);
        }
    }
}
