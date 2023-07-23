<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Master\withdrawalAudit;
use App\Models\userExtract;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class UserExtractController extends AdminController
{
    protected $title = "提现申请";

    protected function grid()
    {
        $grid = new Grid(new userExtract());
        $grid->model()->orderBy("id", "desc");
        $userModel = config('admin.database.users_model');
        //$grid->disableExport();
        $grid->filter(function($filter){
            $filter->equal('uid', '用户ID');
            $filter->like('user.name', '姓名');
            $filter->equal('user.mobile', '手机号');
        });
        $grid->column("id", "编号")->sortable();
        $grid->column("user.name", "用户")->display(function ($username){
            return $username.'['.$this->uid.']';
        });
        $grid->column("user.mobile", "手机号");

        $grid->column("adminid", "操作管理员")->display(function ($adminid)use($userModel){
            $name = '';
            if($adminid > 0){
                $name = $userModel::where('id',$adminid)->value('name');
            }
            return $name;
        });
        $grid->column("extract_price", "提现金额")->sortable();
        $grid->column("status", "状态")->using(['-1'=>'未通过','0'=>'审核中','1'=>'已提现'])->label([
            -1 => 'danger',
            0 => 'warning',
            1 => 'success',
        ]);
        $grid->column("created_at", "创建时间")->sortable();
        $grid->disableCreateButton();
        $grid->batchActions(function ($batch) {
            $batch->disableDelete();
        });
        $grid->actions(function ($actions) {
            $actions->disableView();
            $actions->disableDelete();
            $actions->disableEdit();
            if ($actions->row->status == 0){
                $actions->add(new withdrawalAudit());
            }
        });
        return $grid;
    }

    protected function detail($id)
    {
        $show = new Show(userExtract::findOrFail($id));
        $show->field("id", "编号");
        $show->field("uid", "用户ID");
        $show->field("adminid", "操作人id");
        $show->field("real_name", "名称");
        $show->field("extract_type", "bank = 银行卡wx=微信artificial=人工");
        $show->field("bank_code", "银行卡");
        $show->field("bank_address", "开户地址");
        $show->field("extract_price", "提现金额");
        $show->field("mark", "Mark");
        $show->field("balance", "Balance");
        $show->field("fail_msg", "无效原因");
        $show->field("fail_time", "Fail_time");
        $show->field("status", "-1 未通过 0 审核中 1 已提现");
        $show->field("created_at", "创建时间");
        $show->field("updated_at", "更新时间");
        $show->field("deleted_at", "删除时间");
        $show->field("wechat", "微信号");
        return $show;
    }

    protected function form()
    {
        $form = new Form(new userExtract());
        $form->display("id", "编号");
        $form->text("uid", "用户ID")->required();
        $form->text("adminid", "操作人id")->required();
        $form->text("real_name", "名称")->required();
        $form->text("extract_type", "bank = 银行卡wx=微信artificial=人工")->required();
        $form->text("bank_code", "银行卡")->required();
        $form->text("bank_address", "开户地址")->required();
        $form->text("extract_price", "提现金额")->required();
        $form->text("mark", "Mark")->required();
        $form->text("balance", "Balance")->required();
        $form->text("fail_msg", "无效原因")->required();
        $form->text("fail_time", "Fail_time")->required();
        $form->text("status", "-1 未通过 0 审核中 1 已提现")->required();
        $form->display("created_at", "创建时间");
        $form->text("wechat", "微信号")->required();
        return $form;
    }
}
