<?php
namespace App\Admin\Controllers;

use App\Models\userNoticeSee;
    use Encore\Admin\Controllers\AdminController;
    use Encore\Admin\Form;
    use Encore\Admin\Grid;
    use Encore\Admin\Show;
        class UserNoticeSeeController extends AdminController
{
    protected $title="User_notice_see";protected function grid()
                {
                    $grid = new Grid(new userNoticeSee());
        $grid->model()->orderBy("id", "desc");
        $grid->disableExport();
        $grid->column("id", "编号")->sortable();$grid->column("nid", "查看的通知id")->sortable();$grid->column("uid", "查看通知的用户id")->sortable();$grid->column("created_at", "创建时间")->sortable();//$grid->column("updated_at", "更新时间")->sortable();$grid->column("deleted_at", "")->sortable();
        return $grid;
    }protected function detail($id)
                {
                    $show = new Show(userNoticeSee::findOrFail($id));
        $show->field("id", "编号");$show->field("nid", "查看的通知id");$show->field("uid", "查看通知的用户id");$show->field("created_at", "创建时间");$show->field("updated_at", "更新时间");$show->field("deleted_at", "删除时间");
        return $show;
    }protected function form()
                    {
                        $form = new Form(new userNoticeSee());
        $form->display("id", "编号");$form->text("nid", "查看的通知id")->required();$form->text("uid", "查看通知的用户id")->required();$form->display("created_at", "创建时间");
        return $form;
    }}