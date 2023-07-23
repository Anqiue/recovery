<?php

namespace App\Jobs;

use App\Models\storeOrder;
use App\Models\systemConfig;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdatePlantform implements ShouldQueue
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
        $expiration_time = systemConfig::where('id',1)->value('expiration_time');
        $mintime = $expiration_time*60;
        Log::info('时间'.$mintime);
        $this->delay($mintime);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info('重新分配'.$this->id);
        $order = DB::table('store_order')->where('id',$this->id)->where('need_service',1)->where('plantfrom',2)->lockForUpdate()->first();
        if($order && $order->status == 5){
            /*师傅规定时间未接单，返回平台去分配*/
            DB::table('store_order')->where('id',$this->id)->update([
                'plantfrom'=>1,
                'give_id'=>0,
                'updated_at'=>date('Y-m-d H:i:s'),
            ]);
        }else{
            return;
        }
    }
}
