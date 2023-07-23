<?php

namespace App\Admin\Controllers;

use App\Models\userMasterLevel;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class UserMasterLevelController extends AdminController
{
    protected $title = "师傅等级";

    protected function grid()
    {
        $grid = new Grid(new userMasterLevel());
        $grid->model()->orderBy("id", "desc");
        $grid->disableExport();
        $grid->filter(function($filter){
            $filter->like('name', '等级名');
        });
        $grid->column("id", "编号")->sortable();
        $grid->column("level_title", "等级名");
        $grid->column("grade", "级别")->sortable();
        $grid->column("amount", "等级补贴");
        $grid->column("status", "状态")->using(['0' => '隐藏', '1' => '显示'])->filter([
            0 => '隐藏',
            1 => '显示',
        ]);
        $grid->column("created_at", "创建时间")->sortable();
        return $grid;
    }

    protected function detail($id)
    {
        $show = new Show(userMasterLevel::findOrFail($id));
        $show->field("id", "编号");
        $show->field("level_title", "等级名");
        $show->field("grade", "级别");
        $show->field("amount", "等级补贴");
        $show->field("mark", "备注");
        $show->field("status", "状态")->using(['0' => '隐藏', '1' => '显示']);;
        $show->field("created_at", "创建时间");
        $show->field("updated_at", "更新时间");
        return $show;
    }

    protected function form()
    {
        $form = new Form(new userMasterLevel());

        $form->display("id", "编号");
        $form->text("level_title", "等级名")->required();
        $form->number("grade", "级别")->min(0)->creationRules('required|unique:user_master_level', ['required' => '此项不能为空','unique' => '级别不能重复'])
            ->updateRules('required|unique:user_master_level,grade,{{id}}', ['required' => '此项不能为空','unique' => '级别不能重复']);;
        $form->decimal("amount", "等级补贴");
        $form->textarea("mark", "备注");
        $form->radio('status', __('状态'))->options(['0' => '隐藏', '1'=> '显示'])->default('1');
        $form->display("created_at", "创建时间");
        return $form;
    }
}
