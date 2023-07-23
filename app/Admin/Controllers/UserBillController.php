<?php

namespace App\Admin\Controllers;

use App\Models\userBill;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class UserBillController extends AdminController
{
    protected $title = "账单管理";

    protected function grid()
    {
        $grid = new Grid(new userBill());
        $grid->model()->orderBy("id", "desc");
        $grid->filter(function($filter){
            $filter->equal('uid', '用户ID');
            $filter->like('user.name', '姓名');
            $filter->equal('user.mobile', '手机号');
            $filter->equal('pm','状态')->radio([
                ''   => '全部',
                0    => '支出',
                1    => '收入',
            ]);
        });
        //$grid->disableExport();
        $grid->column("id", "编号");
        $grid->column("user.name", "用户")->display(function ($username){
            return $username.'['.$this->uid.']';
        });
        $grid->column("user.mobile", "手机号");
        //$grid->column("link_id", "关联订单ID");
        $grid->column("title", "账单标题");
        $grid->column("pm", "状态")->using(['0'=>'支出','1'=>'收入']);
        $grid->column("number", "金额")->display(function ($number){
            if($this->pm == 0){
                return '<span style="color: #000000;">-' .$number.'</span>';
            }else{
                return '<span style="color: forestgreen;">+'.$number.'</span>';
            }
        });
        $grid->column("created_at", "创建时间")->sortable();
        $grid->disableCreateButton();
        $grid->batchActions(function ($batch) {
            $batch->disableDelete();
        });
        $grid->disableActions();
        return $grid;
    }

    protected function detail($id)
    {
        $show = new Show(userBill::findOrFail($id));
        $show->field("id", "编号");
        $show->field("uid", "用户uid");
        $show->field("link_id", "关联id");
        $show->field("pm", "0 = 支出 1 = 获得");
        $show->field("title", "账单标题");
        $show->field("category", "明细种类");
        $show->field("type", "明细类型");
        $show->field("number", "明细数字");
        $show->field("balance", "剩余");
        $show->field("mark", "备注");
        $show->field("status", "0 = 带确定 1 = 有效 -1 = 无效");
        $show->field("created_at", "创建时间");
        $show->field("updated_at", "更新时间");
        $show->field("deleted_at", "删除时间");
        return $show;
    }

    protected function form()
    {
        $form = new Form(new userBill());
        $form->display("id", "编号");
        $form->text("uid", "用户uid")->required();
        $form->text("link_id", "关联id")->required();
        $form->text("pm", "0 = 支出 1 = 获得")->required();
        $form->text("title", "账单标题")->required();
        $form->text("category", "明细种类")->required();
        $form->text("type", "明细类型")->required();
        $form->text("number", "明细数字")->required();
        $form->text("balance", "剩余")->required();
        $form->text("mark", "备注")->required();
        $form->text("status", "0 = 带确定 1 = 有效 -1 = 无效")->required();
        $form->display("created_at", "创建时间");
        return $form;
    }
}
