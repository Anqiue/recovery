<?php

namespace App\Admin\Controllers;

use App\Models\userAddress;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\DB;

class UserAddressController extends AdminController
{
    protected $title = "地址管理";

    protected function grid()
    {
        $grid = new Grid(new userAddress());
        $grid->model()->orderBy("id", "desc");
        $grid->disableExport();
        $grid->filter(function($filter){
            $filter->equal('uid', '用户ID');
            $filter->like('real_name', '收货人姓名');
            $filter->equal('phone', '收货人电话');
        });
        $grid->column("id", "编号")->sortable();
        $grid->column("user.nickname", "用户")->display(function ($username){
            return $username.'['.$this->uid.']';
        });
        $grid->column("real_name", "收货人姓名");
        $grid->column("phone", "收货人电话");
        $grid->column("province", "收货人所在省")->display(function ($code) {
            return DB::table('china_area')->where('code',$code)->value('name');
        });
        $grid->column("city", "收货人所在市")->display(function ($code) {
            return DB::table('china_area')->where('code',$code)->value('name');
        });
        $grid->column("district", "收货人所在区")->display(function ($code) {
            return DB::table('china_area')->where('code',$code)->value('name');
        });
       //$grid->column("post_code", "邮编")->sortable();
        $grid->column("is_default", "是否默认")->using(['0'=>'否','1' => '是'])->label([
            0 => 'default',
            1 => 'success',
        ]);;
        $grid->column("created_at", "创建时间")->sortable();
        $grid->disableCreateButton();
        $grid->disableActions();
        return $grid;
    }

    protected function detail($id)
    {
        $show = new Show(userAddress::findOrFail($id));
        $show->field("id", "编号");
        $show->field("uid", "用户id");
        $show->field("real_name", "收货人姓名");
        $show->field("phone", "收货人电话");
        $show->field("province", "收货人所在省");
        $show->field("city", "收货人所在市");
        $show->field("district", "收货人所在区");
        $show->field("detail", "收货人详细地址");
        $show->field("post_code", "邮编");
        $show->field("longitude", "经度");
        $show->field("latitude", "纬度");
        $show->field("is_default", "是否默认");
        $show->field("created_at", "创建时间");
        $show->field("updated_at", "更新时间");
        $show->field("deleted_at", "删除时间");
        return $show;
    }

    protected function form()
    {
        $form = new Form(new userAddress());
        $form->display("id", "编号");
        $form->text("uid", "用户id")->required();
        $form->text("real_name", "收货人姓名")->required();
        $form->text("phone", "收货人电话")->required();
        $form->text("province", "收货人所在省")->required();
        $form->text("city", "收货人所在市")->required();
        $form->text("district", "收货人所在区")->required();
        $form->text("detail", "收货人详细地址")->required();
        $form->text("post_code", "邮编")->required();
        $form->text("longitude", "经度")->required();
        $form->text("latitude", "纬度")->required();
        $form->text("is_default", "是否默认")->required();
        $form->display("created_at", "创建时间");
        return $form;
    }
}
