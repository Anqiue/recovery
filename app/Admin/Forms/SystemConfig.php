<?php

    namespace App\Admin\Forms;

    use App\Models\storeCategory;
    use Encore\Admin\Widgets\Form;
    use Illuminate\Http\Request;

    class SystemConfig extends Form {
        /**
         * The form title.
         *
         * @var string
         */
        public $title = '基础设置';

        /**
         * Handle the form request.
         *
         * @param array $input
         *
         * @return Response
         */
        public function handle(Request $request)
        {
            $site_name = $request->input('site_name');
            $wechat_first_coupon = $request->input('wechat_first_coupon');
            $system_express_app_code = $request->input('system_express_app_code');
            $service_day = $request->input('service_day');
            $expiration_time = $request->input('expiration_time');
            $key_count = $request->input('key_count');
            $store_reason = $request->input('store_reason');
            $shop_intro = $request->input('shop_intro');
            $message_type = $request->input('message_type');
            $service_review = $request->input('service_review');
            $reject_reason = $request->input('reject_reason');
            $kefu_link = $request->input('kefu_link');
            $home_activity = '';
            if($request->home_activity){
                $home_activity = $request->home_activity;
                foreach ($home_activity as $key=>$val){
                    if($val['_remove_'] == 1){
                        unset($home_activity[$key]);
                    }
                }
                $home_activity = json_encode(array_values($home_activity));
            }

            $res = \App\Models\systemConfig::first();
            if($res){
                \App\Models\systemConfig::where('id',$res->id)->update([
                    'site_name'=>$site_name,
                    'wechat_first_coupon'=>$wechat_first_coupon,
                    'system_express_app_code'=>$system_express_app_code,
                    'expiration_time'=>$expiration_time,
                    'service_day'=>$service_day,
                    'key_count'=>$key_count,
                    'store_reason'=>$store_reason,
                    'shop_intro'=>$shop_intro,
                    'message_type'=>$message_type,
                    'service_review'=>$service_review,
                    'reject_reason'=>$reject_reason,
                    'kefu_link'=>$kefu_link,
                    'home_activity'=>$home_activity,
                    'updated_at'=>date('Y-m-d H:i:s')
                ]);
            }else{
                \App\Models\systemConfig::insert([
                    'site_name'=>$site_name,
                    'wechat_first_coupon'=>$wechat_first_coupon,
                    'system_express_app_code'=>$system_express_app_code,
                    'expiration_time'=>$expiration_time,
                    'service_day'=>$service_day,
                    'key_count'=>$key_count,
                    'store_reason'=>$store_reason,
                    'shop_intro'=>$shop_intro,
                    'message_type'=>$message_type,
                    'service_review'=>$service_review,
                    'reject_reason'=>$reject_reason,
                    'kefu_link'=>$kefu_link,
                    'home_activity'=>$home_activity,
                    'created_at'=>date('Y-m-d H:i:s')
                ]);
            }
            admin_success('保存成功');

            return back();
        }

        /**
         * Build a form here.
         */
        public function form()
        {
            $this->text('site_name', '站点名称');
            $this->text('wechat_first_coupon', '新人赠送优惠券ID')->help('首次关注赠送优惠券ID,多个用英文逗号隔开；0为不赠送');
            $this->table('home_activity','首页优惠券活动', function ($table) {
                $table->text('title','活动标题')->required();
                $table->text('coupon', '已发布优惠券ID')->required();
            });
            $this->text('system_express_app_code', '快递查询密钥AppCode')->help('阿里云快递查询接口密钥购买地址：https://market.aliyun.com/products/56928004/cmapi021863.html');
            $this->number('service_day', '几天后上门服务')->default(1)->help('下单预约服务时间设置几天后上门服务');
            $this->number('key_count', '热门搜索显示数')->default(15)->help('热门搜索显示个数');
            //$this->number('expiration_time', '失效时间/分钟')->help('师傅多久不接单，就返回平台');
            $this->textarea('store_reason', '退款理由')->help(' 配置退货理由，一行一个理由');
            $this->textarea('shop_intro', '购物须知');
            $this->textarea('message_type', '留言类型')->help(' 配置留言类型，一行一个类型');
            $this->textarea('service_review', '服务评价')->help(' 服务评价问题，一行一个');
            $this->textarea('reject_reason', '拒单理由')->help(' 师傅拒单理由，一行一个');
            $this->url('kefu_link', '微信客服链接')->required();
        }

        /**
         * The data of the form.
         *
         * @return array
         */
        public function data()
        {
            $res = \App\Models\systemConfig::first();
            if($res) {
                return [
                    'site_name' => $res->site_name,
                    'service_day' => $res->service_day,
                    'expiration_time' => $res->expiration_time,
                    'store_reason' => $res->store_reason,
                    'shop_intro' => $res->shop_intro,
                    'message_type' => $res->message_type,
                    'service_review' => $res->service_review,
                    'reject_reason' => $res->reject_reason,
                    'kefu_link' => $res->kefu_link,
                    'wechat_first_coupon' => $res->wechat_first_coupon,
                    'key_count' => $res->key_count,
                    'system_express_app_code' => $res->system_express_app_code,
                    'home_activity' => $res->home_activity,
                ];
            }
        }
    }
