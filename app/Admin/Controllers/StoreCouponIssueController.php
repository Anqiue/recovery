<?php

namespace App\Admin\Controllers;

use App\Models\storeCouponIssue;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class StoreCouponIssueController extends AdminController
{
    protected $title = "已发布管理";

    protected function grid()
    {
        $grid = new Grid(new storeCouponIssue());
        $grid->model()->orderBy("id", "desc");
        $grid->disableExport();
        $grid->filter(function($filter){
            $filter->like('coupon.title', '优惠券名称');
        });
        $grid->column("id", "编号")->sortable();
        $grid->column("coupon.title", "优惠券名称");
        $grid->column("price", "优惠券价格");
        $grid->column('sales_time','领取日期')->display(function () {
            return $this->start_time.'-'.$this->end_time;
        });
        $grid->column("total_count", "优惠券领取数量")->sortable();
        $grid->column("remain_count", "优惠券剩余领取数量")->sortable();
        $grid->column("is_permanent", "限量张数")->display(function ($is_per){
            if($is_per == 0){
                return '不限量';
            }else{
                return $is_per.'张';
            }
        });
        $grid->column("status", "状态")->using(['0' => '关闭', '1' => '正常'])->label([
            0 => 'default',
            1 => 'success',
        ])->filter([
            0 => '关闭',
            1 => '正常',
        ]);
        $grid->column("created_at", "创建时间")->sortable();
        $grid->disableCreateButton();
        $grid->actions(function ($actions) {
            // 去掉编辑
            $actions->disableEdit();
        });
        return $grid;
    }

    protected function detail($id)
    {
        $show = new Show(storeCouponIssue::findOrFail($id));
        $show->field("id", "编号");
        $show->field("coupon.title", "优惠券名称");
        $show->field("price", "优惠券价格");
        $show->field("start_time", "优惠券领取开启时间");
        $show->field("end_time", "优惠券领取结束时间");
        $show->field("total_count", "优惠券领取数量");
        $show->field("remain_count", "优惠券剩余领取数量");
             $show->field("is_permanent", "是否不限量");
        $show->field("status", "状态")->using(['0' => '关闭', '1' => '开启']);
        $show->field("created_at", "创建时间");
        $show->field("updated_at", "更新时间");
        return $show;
    }

    protected function form()
    {
        $form = new Form(new storeCouponIssue());
        $form->display("id", "编号");
        $form->text("cid", "优惠券ID")->required();
        $form->text("start_time", "优惠券领取开启时间")->required();
        $form->text("end_time", "优惠券领取结束时间")->required();
        $form->text("total_count", "优惠券领取数量")->required();
        $form->text("remain_count", "优惠券剩余领取数量")->required();
        $form->decimal("price", "价格")->default(0);
        $form->text("is_permanent", "是否限量")->default(0)->help('0：不限量；>0：限量领取或购买张数');
        $form->text("status", "1 正常 0 未开启 -1 已无效")->required();
        $form->display("created_at", "创建时间");
        return $form;
    }
}
