<?php

namespace App\Admin\Controllers;

use App\Models\storeVideo;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Config;

class StoreVideoController extends AdminController
{
    protected $title = "视频管理";

    protected function grid()
    {
        $grid = new Grid(new storeVideo());
        $grid->model()->orderBy("id", "desc");
        $grid->disableExport();
        $grid->column("id", "编号")->sortable();
        $grid->column("title", "标题")->sortable();
        $grid->column("image", "缩略图")->image('','50');
        $grid->column("sort", "排序")->editable()->sortable();
        $grid->column("is_recomm", "推荐")->using(['0' => '否', '1' => '是'])->filter([
            0 => '否',
            1 => '是',
        ]);
        $grid->column("status", "状态")->using(['0' => '隐藏', '1' => '显示'])->filter([
            0 => '隐藏',
            1 => '显示',
        ]);
        $grid->column("created_at", "创建时间")->sortable();
        return $grid;
    }

    protected function detail($id)
    {
        $show = new Show(storeVideo::findOrFail($id));
        $show->field("id", "编号");
        $show->field("title", "标题");
        $show->field("intro", "简介");
        $show->field("image", "缩略图")->image();
        $show->field("video", "视频")->file();
        $show->field("status", "状态")->using(['0' => '隐藏', '1' => '显示']);
        $show->field("is_recomm", "推荐")->using(['0' => '否', '1' => '是']);
        $show->field("sort", "排序");
        $show->field("created_at", "创建时间");
        $show->field("updated_at", "更新时间");
        return $show;
    }

    protected function form()
    {
        $form = new Form(new storeVideo());

        $form->display("id", "编号");
        $form->text("title", "标题")->required();
        $form->textarea("intro", "简介");
        $form->image("image", "缩略图");
        $form->file("video", "视频")->rules('mimes:mp4')->required();
        $form->radio('status', __('状态'))->options(['0' => '隐藏', '1'=> '显示'])->default('1');
        $form->radio('is_recomm', __('推荐'))->options(['0' => '否', '1'=> '是'])->default('0')->help('首页展示推荐案例');
        $form->number("sort", "排序")->default(0);
        $form->display("created_at", "创建时间");
        return $form;
    }
}
