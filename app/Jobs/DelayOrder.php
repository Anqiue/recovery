<?php

namespace App\Jobs;

use App\Models\storeOrder;
use App\Models\storeOrderStatus;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DelayOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $id='';
    protected $redis='';
    protected  $key = 'store_order';
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($id)
    {
        $this->id = $id;
        Log::info('订单自动取消'.$id);
        $this->delay(1800);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info('77订单自动取消'.$this->id);
        $order = DB::table('store_order')->where('id',$this->id)->lockForUpdate()->first();
        Log::info('111订单自动取消'.$this->id);
        if($order && $order->paid == 0 && $order->status == 1){
            Log::info('222订单自动取消'.$this->id);
            //$affected = 1;
            $affected = DB::table('store_order')->where('id',$this->id)->update([
                'status'=>0,
                'updated_at'=>date('Y-m-d H:i:s'),
            ]);
            if($affected){
                /*优惠券回退*/
                if($order->coupon_id){
                    $arr = explode(',',$order->coupon_id);
                    foreach ($arr as $couponId){
                        DB::table('store_coupon_user')->where('id',$couponId)->where('status',1)->update(['status'=>0,'updated_at'=>date('Y-m-d H:i:s')]);
                    }
                }
                $this->status($this->id,'cancel_order','30分钟未支付自动取消订单',$order->uid);
            }
        }else{
            Log::info('333订单自动取消'.json_encode($order));
            return;
        }
    }

    public function status($oid,$change_type,$change_message,$masterid=0)
    {
        $change_time = date('Y-m-d H:i:s');
        return DB::table('store_order_status')->insertGetId([
            'oid'=>$oid,
            'change_type'=>$change_type,
            'change_message'=>$change_message,
            'master_id'=>$masterid,
            'created_at'=>$change_time
        ]);
    }
}
