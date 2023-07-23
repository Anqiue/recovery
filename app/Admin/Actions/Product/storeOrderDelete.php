<?php

namespace App\Admin\Actions\Product;

use App\Models\storeOrder;
use Encore\Admin\Actions\BatchAction;
use Illuminate\Database\Eloquent\Collection;

class storeOrderDelete extends BatchAction
{
    public $name = '删除已取消订单';

    public function handle(Collection $collection)
    {
        $i = 0;
        foreach ($collection as $model => $value) {
            if($value['status'] == 0){
                storeOrder::where('id',$value['id'])->delete();
                $i++;
            }
        }
        return $this->response()->success('成功删除'.$i.'个订单')->refresh();
    }

    public function dialog()
    {
        $this->confirm('确定删除已取消的订单？');
    }
}
