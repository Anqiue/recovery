<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

trait ExpressQuery
{

    protected  $express_api_url = 'http://wuliu.market.alicloudapi.com/kdi';
    protected $appCode;
    protected $params = [];

    //设置运单号
    protected function setNo($no)
    {
        $this->params['no'] = $no;
        return $this;
    }

    //设置物流公司
    protected function setType($type)
    {
        $this->params['type'] = $type;
        return $this;
    }

    protected function setAppCode($appCode)
    {
        $this->appCode = 'APPCODE '.$appCode;
        return $this;
    }

    protected function query()
    {
        $res = Http::WithHeaders([
            'Authorization' => $this->appCode
        ])->get($this->express_api_url,$this->params)
            ->getBody()
            ->getContents();

        return json_decode($res,true)?:false;
    }

}
