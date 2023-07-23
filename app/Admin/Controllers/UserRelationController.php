<?php
namespace App\Admin\Controllers;

use App\Models\userRelation;
    use Encore\Admin\Controllers\AdminController;
    use Encore\Admin\Form;
    use Encore\Admin\Grid;
    use Encore\Admin\Show;
        class UserRelationController extends AdminController
{
    protected $title="User_relation";protected function grid()
                {
                    $grid = new Grid(new userRelation());
        $grid->model()->orderBy("id", "desc");
        $grid->disableExport();
        $grid->column("id", "编号")->sortable();$grid->column("uid", "用户ID")->sortable();$grid->column("link_id", "关联ID")->sortable();$grid->column("type", "类型(1-普通商品、2-视频 3-案例)")->sortable();$grid->column("created_at", "创建时间")->sortable();//$grid->column("updated_at", "更新时间")->sortable();$grid->column("deleted_at", "")->sortable();
        return $grid;
    }protected function detail($id)
                {
                    $show = new Show(userRelation::findOrFail($id));
        $show->field("id", "编号");$show->field("uid", "用户ID");$show->field("link_id", "关联ID");$show->field("type", "类型(1-普通商品、2-视频 3-案例)");$show->field("created_at", "创建时间");$show->field("updated_at", "更新时间");$show->field("deleted_at", "删除时间");
        return $show;
    }protected function form()
                    {
                        $form = new Form(new userRelation());
        $form->display("id", "编号");$form->text("uid", "用户ID")->required();$form->text("link_id", "关联ID")->required();$form->text("type", "类型(1-普通商品、2-视频 3-案例)")->required();$form->display("created_at", "创建时间");
        return $form;
    }}