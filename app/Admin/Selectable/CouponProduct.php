<?php

namespace App\Admin\Selectable;

use App\Models\storeProduct;
use Encore\Admin\Grid\Filter;
use Encore\Admin\Grid\Selectable;
use Illuminate\Support\Facades\Auth;

class CouponProduct extends Selectable
{
    public $model = storeProduct::class;

    public function make()
    {
        $this->model()->where('is_show', 1);
        $this->column('id');
        $this->column('image', '缩略图')->image();
        $this->column('product_name','产品名称');
        $this->column('price','商品价格');
        $this->column('is_show','状态')->using(['0' => '下架', '1' => '上架']);

        $this->filter(function (Filter $filter) {
            $filter->like('product_name','产品名称');
        });
    }
}
