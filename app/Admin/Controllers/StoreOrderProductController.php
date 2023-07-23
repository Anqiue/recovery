<?php

namespace App\Admin\Controllers;

use App\Models\storeOrderProduct;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class StoreOrderProductController extends AdminController
{
    protected $title = "订单管理";

    protected function grid()
    {
        $grid = new Grid(new storeOrderProduct());
        $grid->model()->orderBy("id", "desc");
        $grid->disableExport();
        $grid->column("id", "编号")->sortable();
        $grid->column("oid", "订单ID")->sortable();
        $grid->column("cart_id", "购物车id")->sortable();
        $grid->column("product_id", "产品id")->sortable();
        $grid->column("product_info", "产品信息")->sortable();
        $grid->column("pro_type", "产品类型 1-普通产品 2-服务包产品")->sortable();
        $grid->column("number", "产品数量")->sortable();
        $grid->column("created_at", "创建时间")->sortable();//$grid->column("updated_at", "更新时间")->sortable();$grid->column("deleted_at", "")->sortable();
        return $grid;
    }

    protected function detail($id)
    {
        $show = new Show(storeOrderProduct::findOrFail($id));
        $show->field("id", "编号");
        $show->field("oid", "订单ID");
        $show->field("cart_id", "购物车id");
        $show->field("product_id", "产品id");
        $show->field("product_info", "产品信息");
        $show->field("pro_type", "产品类型 1-普通产品 2-服务包产品");
        $show->field("number", "产品数量");
        $show->field("created_at", "创建时间");
        $show->field("updated_at", "更新时间");
        $show->field("deleted_at", "删除时间");
        return $show;
    }

    protected function form()
    {
        $form = new Form(new storeOrderProduct());
        $form->display("id", "编号");
        $form->text("oid", "订单ID")->required();
        $form->text("cart_id", "购物车id")->required();
        $form->text("product_id", "产品id")->required();
        $form->text("product_info", "产品信息")->required();
        $form->text("pro_type", "产品类型 1-普通产品 2-服务包产品")->required();
        $form->text("number", "产品数量")->required();
        $form->display("created_at", "创建时间");
        return $form;
    }
}
