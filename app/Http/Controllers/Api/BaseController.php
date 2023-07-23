<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\storeProduct;
use Carbon\Carbon;
use Exception;
use Hanson\LaravelAdminWechat\Models\WechatUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class BaseController extends Controller
{
    /**
     * 上传cos
     * @param $file
     * @return mixed
     */
    public function addCos($filename,$file,$type=1){
        try {
            $file_url = '';
            $disk = Storage::disk('oss');
            if($type == 1){
                $file_content = $disk -> putfile($filename,$file);
                //第一个参数是你储存桶里想要放置文件的路径，第二个参数是文件对象
                if($file_content)$file_url = $disk->url($file_content);//获取到文件的线上地址
            }else{
                $file_content = $disk -> put($filename,$file);
                //第一个参数是你储存桶里想要放置文件的路径，第二个参数是文件对象
                if($file_content)$file_url = $disk->url($filename);//获取到文件的线上地址
            }
        } catch (\Exception $e) {
            //请求失败
            echo($e);
        }
        return $file_url;
    }
    /*删除*/
    public function delCos($url){
        preg_match("/https:\/\/(.+?)com\//", $url, $res);
        $file_urs = str_replace($res[0],"",$url);
        $arr = array();
        array_push($arr,$file_urs);
        try {
            $disk = Storage::disk('oss');
            $file_content = $disk->delete($arr);
        } catch (\Exception $e) {
            //请求失败
            echo($e);
        }
        return $file_content;
    }
    /**
     * 上传public
     * @param $file
     * @return mixed
     */
    public function addImages($file,$type=0){
        try {

            if($type ==1){
                $filename = md5(time() . rand(100000, 999999)) . '.' . $file->getClientOriginalExtension();
                //$path =  $file->storeAs('public/uploads', $filename);
                $file_url =  $file->move('uploads', $filename);
                $file_url = base_path().'/public/'.$file_url;
               // $path = $file->store('uploads','public');
                //$path = Storage::putFileAs('public/uploads', $file, $filename);   //指定路径，文件名

               /* $path = str_replace('public/','',$path);
                $file_url =  Storage::disk('public')->url($path);*/
            }else{
                $path = $file->store('images','public');
                $file_url = Storage::disk('public')->url($path);
            }

        } catch (\Exception $e) {
            //请求失败
            echo($e);
        }
        return $file_url;
    }
    /*删除*/
    public function delImages($url,$type=0){
        if($type == 1){
            Storage::delete(storage_path('app/uploads/' . $url));
        }else{
            Storage::delete(storage_path('app/public/upload/' . $url));
        }
    }
    /**
     * 身份证验证
     * @param $id
     * @return bool
     */
    public function isValidCard($id) {
        if(18 != strlen($id)) {
            return false;
        }
        $weight = [7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2];
        $code = ['1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2'];
        $mode = 0;
        $ver = substr($id, -1);
        if($ver == 'x') {
            $ver = 'X';
        }
        foreach($weight as $key => $val) {
            if($key == 17) {
                continue;
            }
            $digit = intval(substr($id, $key, 1));
            $mode += $digit * $val;
        }
        $mode %= 11;
        if($ver != $code[$mode]) {
            return false;
        }
        list($month, $day, $year) = self::getMDYFromCard($id);
        $check = checkdate($month, $day, $year);
        if(!$check) {
            return false;
        }
        $today = date('Ymd');
        $date = substr($id, 6, 8);
        if($date >= $today) {
            return false;
        }
        return true;
    }
    public function getMDYFromCard($id) {
        $date = substr($id, 6, 8);
        $year = substr($date, 0, 4);
        $month = substr($date, 4, 2);
        $day = substr($date, 6);
        return [$month, $day, $year];
    }
    /**
     * 商品减库存
     * @param $proid
     * @param $method 1为加库存 -1 减库存
     */
    public function editStore($proid,$method,$count,$skey = ''){
        try {
            DB::beginTransaction();
            $proinfo = storeProduct::getProById($proid,['stock','good_sku','sales']);
            $oldStore = $proinfo->stock;
            $oldSales = $proinfo->sales;
            //作为常规商品进行减
            if ($skey) {
                $priceDetails = $proinfo->good_sku;
                if (!is_array($priceDetails) && $priceDetails) {
                    $priceDetails = json_decode($priceDetails, true);
                }
            }
            if ($method == 1) {
                //总库存加上
                $store = $oldStore + $count;
                $sales = $oldSales - $count;
                //分库存
                if (isset($priceDetails) && is_array($priceDetails) && !empty($priceDetails)) {
                    foreach ($priceDetails['sku'] as $key => $value) {
                        if ($value['id'] == $skey && isset($value['stock'])) {
                            $priceDetails['sku'][$key]['stock'] = $value['stock'] + $count;
                        }
                    }
                }
            } else {
                //总库存减上
                $store = $oldStore - $count;
                $sales = $oldSales + $count;
                //分库存
                if (isset($priceDetails) && is_array($priceDetails) && !empty($priceDetails)) {
                    foreach ($priceDetails['sku'] as $key => $value) {
                        if ($value['id'] == $skey && isset($value['stock'])) {
                            $priceDetails['sku'][$key]['stock'] = $value['stock'] - $count;
                        }
                    }
                }
            }
            if (isset($priceDetails) && is_array($priceDetails)) {
                storeProduct::where('id', $proid)->update([
                    'sales' => $sales,
                    'stock' => $store,
                    'good_sku' => json_encode($priceDetails),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            } else {
                storeProduct::where('id', $proid)->update([
                    'sales' => $sales,
                    'stock' => $store,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            }
            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            Log::info($e->getMessage().'--'.$proid. '--editstore');
            return 0;
        }
    }
    /*随机字符串*/
    public function incrementalHash($uid='',$link='')
    {
        $code = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $rand = $code[rand(0,25)]
            .strtoupper(dechex(date('m')))
            .date('d').substr(time(),-5)
            .substr(microtime(),2,5)
            .sprintf('%02d',rand(0,99));
        for(
            $a = md5( $rand, true ),
            $s = '0123456789ABCDEFGHIJKLMNOPQRSTUV',
            $d = '',
            $f = 0;
            $f < 8;
            $g = ord( $a[ $f ] ),
            $d .= $s[ ( $g ^ ord( $a[ $f + 8 ] ) ) - $g & 0x1F ],
            $f++
        );
        return  $link.$uid.$d;
    }

    public function http($url, $data = NULL, $json = false){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        if (!empty($data)) {
            if($json && is_array($data)){
                $data = json_encode( $data );
            }
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            if($json){ //发送JSON数据
                curl_setopt($curl, CURLOPT_HEADER, 0);
                curl_setopt($curl, CURLOPT_HTTPHEADER,array('Content-Type: application/json; charset=utf-8', 'Content-Length:' . strlen($data)));
            }
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $res = curl_exec($curl);
        $errorno = curl_errno($curl);
        if ($errorno) {
            return array('errorno' => false, 'errmsg' => $errorno);
        }
        curl_close($curl);
        return json_decode($res, true);
    }
    // curl 发送http 请求
    public function curlRequest($url, $post = '')
    {
        // 初始化
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/6.0)');
        // post请求
        if ($post) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post));
        }
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        // 执行请求
        $data = curl_exec($curl);
        if (curl_exec($curl) === false) {
            return 'Curl error: ' . curl_error($curl);
        } else {
            curl_close($curl);
            return $data;
        }
    }
}
