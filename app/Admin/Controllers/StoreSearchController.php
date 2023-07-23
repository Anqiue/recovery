<?php
namespace App\Admin\Controllers;

use App\Models\storeSearch;
    use Encore\Admin\Controllers\AdminController;
    use Encore\Admin\Form;
    use Encore\Admin\Grid;
    use Encore\Admin\Show;
        class StoreSearchController extends AdminController
{
    protected $title="Store_search";protected function grid()
                {
                    $grid = new Grid(new storeSearch());
        $grid->model()->orderBy("id", "desc");
        $grid->disableExport();
        $grid->column("id", "编号")->sortable();$grid->column("uid", "用户ID")->sortable();$grid->column("keyword", "搜索关键字")->sortable();$grid->column("count", "搜索次数")->sortable();$grid->column("created_at", "创建时间")->sortable();//$grid->column("updated_at", "更新时间")->sortable();$grid->column("deleted_at", "")->sortable();
        return $grid;
    }protected function detail($id)
                {
                    $show = new Show(storeSearch::findOrFail($id));
        $show->field("id", "编号");$show->field("uid", "用户ID");$show->field("keyword", "搜索关键字");$show->field("count", "搜索次数");$show->field("created_at", "创建时间");$show->field("updated_at", "更新时间");$show->field("deleted_at", "删除时间");
        return $show;
    }protected function form()
                    {
                        $form = new Form(new storeSearch());
        $form->display("id", "编号");$form->text("uid", "用户ID")->required();$form->text("keyword", "搜索关键字")->required();$form->text("count", "搜索次数")->required();$form->display("created_at", "创建时间");
        return $form;
    }}