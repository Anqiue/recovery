<?php

namespace App\Admin\Controllers;

use App\Models\storeCouponUser;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class StoreCouponUserController extends AdminController
{
    protected $title = "优惠券领取记录";

    protected function grid()
    {
        $grid = new Grid(new storeCouponUser());
        $grid->model()->orderBy("id", "desc");
        $grid->disableExport();
        $grid->filter(function($filter){
            $filter->equal('uid', '用户ID');
            $filter->like('coupon_title', '优惠券名称');
            $filter->like('user.nickname', '发放人');
            $filter->equal('status','使用状态')->radio([
                ''   => '全部',
                0    => '未使用',
                1    => '已使用',
                2    => '已过期',
                3    => '已退款',
            ]);
        });
        $grid->column("id", "编号")->sortable();
        $grid->column("coupon_title", "优惠券名称");
        $grid->column("user.nickname", "发放人")->display(function ($username){
            return $username.'['.$this->uid.']';
        });
        $grid->column("coupon_price", "优惠券的面值");
        $grid->column("use_min_price", "优惠券最低消费");
        $grid->column("act_type", "获取方式")->using(['1' => '手动领取', '2' => '后台发放','3'=>'新人优惠券','4'=>'购买']);
        $grid->column("use_time", "优惠券开始使用时间");
        $grid->column("end_time", "优惠券结束使用时间");
        $grid->column("status", "使用状态")->using(['0'=>'未使用','1' => '已使用', '2' => '已过期','3'=>'已退款'])->label([
            0 => 'success',
            1 => 'info',
            2 => 'danger',
            3 => 'default',
        ]);
        $grid->disableCreateButton();
        $grid->disableActions();
        return $grid;
    }

    protected function detail($id)
    {
        $show = new Show(storeCouponUser::findOrFail($id));
        $show->field("id", "编号");
        $show->field("cid", "兑换的项目id");
        $show->field("uid", "优惠券所属用户");
        $show->field("coupon_title", "优惠券名称");
        $show->field("coupon_price", "优惠券的面值");
        $show->field("use_min_price", "最低消费多少金额可用优惠券");
        $show->field("act_type", "获取方式 1-前台领取 2-后台管理员定向发放");
        $show->field("order_id", "关联的订单id");
        $show->field("status", "状态（0：未使用，1：已使用, 2:已过期）");
        $show->field("use_time", "使用时间");
        $show->field("is_fail", "是否有效 0-有效 1-失效");
        $show->field("created_at", "创建时间");
        $show->field("updated_at", "更新时间");
        $show->field("deleted_at", "删除时间");
        return $show;
    }

    protected function form()
    {
        $form = new Form(new storeCouponUser());
        $form->display("id", "编号");
        $form->text("cid", "兑换的项目id")->required();
        $form->text("uid", "优惠券所属用户")->required();
        $form->text("coupon_title", "优惠券名称")->required();
        $form->text("coupon_price", "优惠券的面值")->required();
        $form->text("use_min_price", "最低消费多少金额可用优惠券")->required();
        $form->text("act_type", "获取方式 1-前台领取 2-后台管理员定向发放")->required();
        $form->text("order_id", "关联的订单id")->required();
        $form->text("status", "状态（0：未使用，1：已使用, 2:已过期）")->required();
        $form->text("use_time", "使用时间")->required();
        $form->text("is_fail", "是否有效 0-有效 1-失效")->required();
        $form->display("created_at", "创建时间");
        return $form;
    }
}
