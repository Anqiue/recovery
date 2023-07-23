<?php
namespace App\Admin\Controllers;

use App\Models\storeOrderStatus;
    use Encore\Admin\Controllers\AdminController;
    use Encore\Admin\Form;
    use Encore\Admin\Grid;
    use Encore\Admin\Show;
        class StoreOrderStatusController extends AdminController
{
    protected $title="Store_order_status";protected function grid()
                {
                    $grid = new Grid(new storeOrderStatus());
        $grid->model()->orderBy("id", "desc");
        $grid->disableExport();
        $grid->column("id", "编号")->sortable();$grid->column("oid", "订单id")->sortable();$grid->column("change_type", "操作类型")->sortable();$grid->column("change_message", "操作备注")->sortable();$grid->column("created_at", "创建时间")->sortable();//$grid->column("updated_at", "更新时间")->sortable();$grid->column("deleted_at", "")->sortable();
        return $grid;
    }protected function detail($id)
                {
                    $show = new Show(storeOrderStatus::findOrFail($id));
        $show->field("id", "编号");$show->field("oid", "订单id");$show->field("change_type", "操作类型");$show->field("change_message", "操作备注");$show->field("created_at", "创建时间");$show->field("updated_at", "更新时间");$show->field("deleted_at", "删除时间");
        return $show;
    }protected function form()
                    {
                        $form = new Form(new storeOrderStatus());
        $form->display("id", "编号");$form->text("oid", "订单id")->required();$form->text("change_type", "操作类型")->required();$form->text("change_message", "操作备注")->required();$form->display("created_at", "创建时间");
        return $form;
    }}