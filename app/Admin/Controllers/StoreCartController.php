<?php
namespace App\Admin\Controllers;

use App\Models\storeCart;
    use Encore\Admin\Controllers\AdminController;
    use Encore\Admin\Form;
    use Encore\Admin\Grid;
    use Encore\Admin\Show;
        class StoreCartController extends AdminController
{
    protected $title="Store_cart";protected function grid()
                {
                    $grid = new Grid(new storeCart());
        $grid->model()->orderBy("id", "desc");
        $grid->disableExport();
        $grid->column("id", "编号")->sortable();$grid->column("uid", "用户ID")->sortable();$grid->column("type", "类型")->sortable();$grid->column("product_id", "商品ID")->sortable();$grid->column("product_setvice_id", "服务包商品ID")->sortable();$grid->column("product_attr", "商品属性")->sortable();$grid->column("cart_num", "商品数量")->sortable();$grid->column("is_pay", "0 = 未购买 1 = 已购买")->sortable();$grid->column("is_new", "是否为立即购买")->sortable();$grid->column("created_at", "创建时间")->sortable();//$grid->column("updated_at", "更新时间")->sortable();$grid->column("deleted_at", "")->sortable();
        return $grid;
    }protected function detail($id)
                {
                    $show = new Show(storeCart::findOrFail($id));
        $show->field("id", "编号");$show->field("uid", "用户ID");$show->field("type", "类型");$show->field("product_id", "商品ID");$show->field("product_setvice_id", "服务包商品ID");$show->field("product_attr", "商品属性");$show->field("cart_num", "商品数量");$show->field("is_pay", "0 = 未购买 1 = 已购买");$show->field("is_new", "是否为立即购买");$show->field("created_at", "创建时间");$show->field("updated_at", "更新时间");$show->field("deleted_at", "删除时间");
        return $show;
    }protected function form()
                    {
                        $form = new Form(new storeCart());
        $form->display("id", "编号");$form->text("uid", "用户ID")->required();$form->text("type", "类型")->required();$form->text("product_id", "商品ID")->required();$form->text("product_setvice_id", "服务包商品ID")->required();$form->text("product_attr", "商品属性")->required();$form->text("cart_num", "商品数量")->required();$form->text("is_pay", "0 = 未购买 1 = 已购买")->required();$form->text("is_new", "是否为立即购买")->required();$form->display("created_at", "创建时间");
        return $form;
    }}