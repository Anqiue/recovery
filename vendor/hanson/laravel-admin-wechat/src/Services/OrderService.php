<?php

namespace Hanson\LaravelAdminWechat\Services;

use App\Models\storeOrder;
use Hanson\LaravelAdminWechat\Exceptions\PaymentException;
use Hanson\LaravelAdminWechat\Models\WechatOrder;

class OrderService
{
    /**
     * @param array $data
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model
     */
    protected function create(array $data)
    {
        return WechatOrder::query()->create($data);
    }

    /**
     * 统一下单
     *
     * @param string $mchId
     * @param string $tradeType
     * @param array $data
     * @return array
     * @throws PaymentException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function unify(string $mchId, string $tradeType, array $data)
    {
        $app = \Hanson\LaravelAdminWechat\Facades\MerchantService::getInstanceByMchId($mchId);
        $order = [];
        if($data['link_order_id']){
            $order = WechatOrder::where('wechat_user_id',$data['wechat_user_id'])
                ->where('link_order_id',$data['link_order_id'])->first();
            if($order){
                $app->order->close($data['out_trade_no']);
                $data['out_trade_no'] = $data['out_trade_no'].'a';
                WechatOrder::where('id',$order['id'])->update(['out_trade_no'=>$data['out_trade_no'],'updated_at'=>date('Y-m-d H:i:s')]);
                storeOrder::where('id',$data['link_order_id'])->update(['order_id'=>$data['out_trade_no'],'updated_at'=>date('Y-m-d H:i:s')]);
            }
        }
        $data = array_merge([
            'app_id' => $app->getConfig()['app_id'],
            'mch_id' => $app->getConfig()['mch_id'],
        ], $data);

        $data['out_trade_no'] = $data['out_trade_no'] ?? $this->outTradeNo();

        $result = $app->order->unify(array_merge($data, ['trade_type' => $tradeType]));

        if ($result['return_code'] === 'FAIL') {
            throw new PaymentException($result['return_msg']);
        }

        if ($result['result_code'] === 'FAIL') {
            throw new PaymentException($result['err_code_des']);
        }

        if(!$order){
            $order = $this->create($data);
        }

        return ['unify' => $result, 'order' => $order];
    }

    /**
     * 获取微信支付 js 参数
     *
     * @param string $mchId
     * @param string $tradeType
     * @param array $data
     * @return array
     * @throws PaymentException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function jsConfig(string $mchId, string $tradeType, array $data)
    {
        $app = \Hanson\LaravelAdminWechat\Facades\MerchantService::getInstanceByMchId($mchId);

        $result = $this->unify($mchId, $tradeType, $data);

        $result['config'] = $app->jssdk->bridgeConfig($result['unify']['prepay_id'], false);

        return $result;
    }

    protected function outTradeNo()
    {
        return date('YmdHis').(microtime(true) % 1) * 1000 .mt_rand(0, 9999);
    }
}
