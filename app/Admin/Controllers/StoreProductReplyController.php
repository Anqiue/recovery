<?php

namespace App\Admin\Controllers;

use App\Models\storeProductReply;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Hanson\LaravelAdminWechat\Models\WechatUser;

class StoreProductReplyController extends AdminController
{
    protected $title = "评价服务";

    protected function grid()
    {
        $grid = new Grid(new storeProductReply());
        $grid->model()->orderBy("id", "desc");

        $grid->filter(function($filter){
            $filter->like('user.nickname', '用户昵称');
            $filter->equal('master_id', '师傅名称')->select(WechatUser::where('type',2)->pluck('name','id'));
        });
       // $grid->disableExport();

        $grid->column("id", "编号")->sortable();
        $grid->column("user.nickname", "用户")->display(function ($username){
            return $username.'['.$this->uid.']';
        });
        $grid->column("oid", "订单ID");
        $grid->column("master_id", "评价师傅")->display(function ($masterid){
            return WechatUser::getFieldsById($masterid);
        });
        $grid->column("comment", "评论")->display(function ($comment){
            $html = '';
            $comment = json_decode($comment,true);
            foreach ($comment as $val){
                $html .= '问题：'.$val['ask'].',评分：'.$val['score'].'<br/>';
            }
            return $html;
        });
        $grid->column("created_at", "创建时间")->sortable();
        $grid->disableCreateButton();
        $grid->actions(function ($actions) {
            // 去掉删除
            //$actions->disableDelete();
            // 去掉编辑
            $actions->disableEdit();
            // 去掉查看
            $actions->disableView();
        });
        return $grid;
    }

    protected function detail($id)
    {
        $show = new Show(storeProductReply::findOrFail($id));
        $show->field("id", "编号");
        $show->field("uid", "用户ID");
        $show->field("oid", "订单ID");
        $show->field("product_id", "产品id");
        $show->field("reply_type", "某种商品类型(普通商品、服务包）");
        $show->field("product_score", "商品分数");
        $show->field("service_score", "服务分数");
        $show->field("comment", "评论内容");
        $show->field("pics", "评论图片");
        $show->field("created_at", "创建时间");
        $show->field("updated_at", "更新时间");
        $show->field("deleted_at", "删除时间");
        return $show;
    }

    protected function form()
    {
        $form = new Form(new storeProductReply());
        $form->display("id", "编号");
        $form->text("uid", "用户ID")->required();
        $form->text("oid", "订单ID")->required();
        $form->text("product_id", "产品id")->required();
        $form->text("reply_type", "某种商品类型(普通商品、服务包）")->required();
        $form->text("product_score", "商品分数")->required();
        $form->text("service_score", "服务分数")->required();
        $form->text("comment", "评论内容")->required();
        $form->text("pics", "评论图片")->required();
        $form->display("created_at", "创建时间");
        return $form;
    }
}
