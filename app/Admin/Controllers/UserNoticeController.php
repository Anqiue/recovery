<?php
namespace App\Admin\Controllers;

use App\Models\userNotice;
    use Encore\Admin\Controllers\AdminController;
    use Encore\Admin\Form;
    use Encore\Admin\Grid;
    use Encore\Admin\Show;
        class UserNoticeController extends AdminController
{
    protected $title="User_notice";protected function grid()
                {
                    $grid = new Grid(new userNotice());
        $grid->model()->orderBy("id", "desc");
        $grid->disableExport();
        $grid->column("id", "编号")->sortable();$grid->column("uid", "接收消息的用户i或者师傅id")->sortable();$grid->column("type", "消息通知类型（1：用户id；2：师傅id）")->sortable();$grid->column("user", "发送人")->sortable();$grid->column("title", "通知消息的标题信息")->sortable();$grid->column("content", "通知消息的内容")->sortable();$grid->column("is_send", "是否发送（0：未发送；1：已发送）")->sortable();$grid->column("send_time", "发送时间")->sortable();$grid->column("is_see", "是否已读")->sortable();$grid->column("created_at", "创建时间")->sortable();//$grid->column("updated_at", "更新时间")->sortable();$grid->column("deleted_at", "")->sortable();
        return $grid;
    }protected function detail($id)
                {
                    $show = new Show(userNotice::findOrFail($id));
        $show->field("id", "编号");$show->field("uid", "接收消息的用户i或者师傅id");$show->field("type", "消息通知类型（1：用户id；2：师傅id）");$show->field("user", "发送人");$show->field("title", "通知消息的标题信息");$show->field("content", "通知消息的内容");$show->field("is_send", "是否发送（0：未发送；1：已发送）");$show->field("send_time", "发送时间");$show->field("is_see", "是否已读");$show->field("created_at", "创建时间");$show->field("updated_at", "更新时间");$show->field("deleted_at", "删除时间");
        return $show;
    }protected function form()
                    {
                        $form = new Form(new userNotice());
        $form->display("id", "编号");$form->text("uid", "接收消息的用户i或者师傅id")->required();$form->text("type", "消息通知类型（1：用户id；2：师傅id）")->required();$form->text("user", "发送人")->required();$form->text("title", "通知消息的标题信息")->required();$form->text("content", "通知消息的内容")->required();$form->text("is_send", "是否发送（0：未发送；1：已发送）")->required();$form->text("send_time", "发送时间")->required();$form->text("is_see", "是否已读")->required();$form->display("created_at", "创建时间");
        return $form;
    }}