<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Coupon\Issue;
use App\Admin\Selectable\CouponProduct;
use App\Models\storeCoupon;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class StoreCouponController extends AdminController
{
    protected $title = "优惠券制作";

    protected function grid()
    {
        $grid = new Grid(new storeCoupon());
        $grid->model()->orderBy("id", "desc");
        $grid->disableExport();
        $grid->filter(function($filter){
            $filter->like('title', '优惠券名称');
            $filter->equal('status','状态')->radio([
                ''   => '全部',
                0    => '无效',
                1    => '有效',
            ]);
        });
        $grid->column("id", "编号")->sortable();
        $grid->column("title", "优惠券名称");
        $grid->column("subtitle", "副标题");
        $grid->column("type", "优惠券类型")->using(['1' => '专属券', '2' => '代金券']);
        $grid->column("coupon_price", "优惠券面值")->sortable();
        $grid->column("use_min_price", "最低消费多少金额")->sortable();
        $grid->column("use_count", "单次累计使用")->sortable();
        //$grid->column("coupon_day", "优惠券有效期限（单位：天）")->sortable();
        $grid->column('available_time','可使用日期')->display(function () {
            if($this->use_status == 1){
                return $this->coupon_start.'—'.$this->coupon_end;
            }else{
                return '领取后'.$this->coupon_day.'天有效';
            }
        });
        $grid->column("sort", "排序")->sortable();
        $grid->column("status", "状态")->using(['0' => '无效', '1' => '有效'])->label([
            0 => 'default',
            1 => 'success',
        ])->filter([
            0 => '无效',
            1 => '有效',
        ]);
        $grid->column("created_at", "创建时间")->sortable();
        $grid->actions(function ($actions) {
            $actions->add(new Issue());
            $actions->disableView();
            //$actions->disableEdit();
        });
        return $grid;
    }

    protected function detail($id)
    {
        $show = new Show(storeCoupon::findOrFail($id));
        $show->field("id", "编号");
        $show->field("title", "优惠券名称");
        $show->field("type", "优惠券类型")->using(['1' => '专属券', '2' => '代金券']);
        $show->field("coupon_price", "兑换的优惠券面值");
        $show->field("use_min_price", "最低消费多少金额可用优惠券");
        $show->field("coupon_day", "优惠券有效期限（单位：天）");
        $show->field("use_count", "单次累计可使用数量");
        $show->field("sort", "排序");
        $show->field("status", "状态")->using(['0' => '隐藏', '1' => '显示']);;
        $show->field("created_at", "创建时间");
        $show->field("updated_at", "更新时间");
        return $show;
    }

    protected function form()
    {
        $form = new Form(new storeCoupon());

        $form->display("id", "编号");
        $form->text("title", "优惠券名称")->required();
        $form->text("subtitle", "副标题");
        $form->radio("type", "优惠券类型")->options(['1'=>'专属券','2'=>'代金券'])->default('1');
        $form->decimal("coupon_price", "兑换的优惠券面值")->required();
        $form->decimal("use_min_price", "最低消费多少金额可用优惠券")->required();
        $form->number("use_count", "单次累计可使用数量")->min(1)->default(1);
        //$form->number("coupon_day", "优惠券有效期限（单位：天）")->default(1)->required();
        $form->radio('use_status', '可使用日期')
            ->options([
                1 => '指定日期',
                2 => '领券当日起',
            ])->when(1, function (Form $form) {

                $form->datetime("coupon_start", "使用开始时间");
                $form->datetime("coupon_end", "使用结束时间");

            })->when(2, function (Form $form) {
                $form->number("coupon_day", "可使用天数")->min(0)->default(0);
            })->default(1);
        $form->radio('pro_limit_type', '优惠券可使用商品')
        ->options([1=>'全部商品可使用',2=>'指定商品可用'])
        ->when(2, function (Form $form) {
            $form->belongsToMany('coupon_allow', CouponProduct::class, __('选择商品'));
        });
        $form->editor("content", "图文内容")->required();
        $form->number("sort", "排序")->default(0);
        $form->radio('status', __('状态'))->options(['0' => '隐藏', '1'=> '显示'])->default('1');
        $form->display("created_at", "创建时间");
        $form->tools(function (Form\Tools $tools) {
            // 去掉`列表`按钮
            //$tools->disableList();
            // 去掉`删除`按钮
            //$tools->disableDelete();
            // 去掉`查看`按钮
            $tools->disableView();
        });
        $form->footer(function ($footer) {
            // 去掉`重置`按钮
            //$footer->disableReset();
            // 去掉`提交`按钮
           // $footer->disableSubmit();
            // 去掉`查看`checkbox
            $footer->disableViewCheck();
            // 去掉`继续编辑`checkbox
            //$footer->disableEditingCheck();
            // 去掉`继续创建`checkbox
            //$footer->disableCreatingCheck();

        });
        return $form;
    }
}
