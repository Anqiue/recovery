<?php

namespace App\Admin\Controllers;

use App\Models\storeCategory;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class StoreCategoryController extends AdminController
{
    protected $title = "产品分类";

    protected function grid()
    {
        $grid = new Grid(new storeCategory());
        $grid->model()->orderBy("id", "desc");
        $grid->filter(function($filter){
            $filter->like('cate_name', '分类名称');
        });
        $grid->disableExport();

        $grid->column("id", "编号")->sortable();
        $grid->column("cate_name", "分类名称")->sortable();
        $grid->column("pic", "缩略图")->image('','50');
        $grid->column("sort", "排序")->editable()->sortable();
        $grid->column("status", "状态")->using(['0' => '隐藏', '1' => '显示'])->filter([
            0 => '隐藏',
            1 => '显示',
        ]);
        $grid->column("created_at", "创建时间")->sortable();
        return $grid;
    }

    protected function detail($id)
    {
        $show = new Show(storeCategory::findOrFail($id));
        $show->field("id", "编号");
        $show->field("cate_name", "分类名称");
        $show->field("pic", "广告图片")->image();
        $show->field("sort", "排序");
        $show->field("desc", "分类说明");
        $show->field("status", "状态")->using(['0' => '隐藏', '1' => '显示']);;
        $show->field("created_at", "创建时间");
        $show->field("updated_at", "更新时间");
        return $show;
    }


    protected function form()
    {
        $form = new Form(new storeCategory());
        $form->display("id", "编号");
       // $form->select('pid','上级')->options(storeCategory::selectOptions());
        $form->text("cate_name", "分类名称")->required();
        $form->image('pic','缩略图')->required();
        $form->textarea('desc', __('简介'));
        $form->number('sort', __('排序'))->default(0);
        $form->radio('status', __('状态'))->options(['0' => '隐藏', '1'=> '显示'])->default('1');
        $form->display("created_at", "创建时间");
        return $form;
    }
}
