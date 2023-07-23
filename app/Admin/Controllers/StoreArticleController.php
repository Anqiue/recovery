<?php
namespace App\Admin\Controllers;

use App\Models\article;
use App\Models\articleCategory;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Config;

class StoreArticleController extends AdminController
{
    protected $title="系统文章管理";

    protected function grid()
    {
        $grid = new Grid(new article());
        $grid->model()->where("cid", "0");
        $grid->model()->orderBy("id", "desc");
        $grid->disableExport();
        $grid->filter(function($filter){
            $filter->like('title', '标题');
        });

        $grid->column("id", "ID")->sortable();
        $grid->column("title", "标题")->sortable();
        $grid->column("image", "缩略图")->image('','50');
        $grid->column("created_at", "创建时间")->sortable();
        $grid->actions(function ($actions) {
            // 去掉删除
            $actions->disableDelete();
        });
        // 去掉批量操作
        $grid->disableBatchActions();
        return $grid;
    }

    protected function detail($id)
    {
        $show = new Show(article::findOrFail($id));
        $show->field("id", "ID");
        $show->field("title", "标题");
        $show->field('image', __('缩略图'))->image();
        $show->field("synopsis", "简介");
        $show->field('content', __('内容'))->unescape()->as(function($content){ //加入unescape方法即可
            return $content;
        });
        $show->field("created_at", "创建时间");
        $show->field("updated_at", "修改时间");
        return $show;
    }

    protected function form()
    {
        $form = new Form(new article());

        $form->display("id", "ID");
        $form->hidden("cid")->default(0);
        $form->hidden("status", "状态")->default(0);
        $form->text("title", "标题")->required();
        $form->image("image", "缩略图");
        $form->textarea("synopsis", "简介");
        $form->editor('content', __('内容'));
        $form->display("created_at", "创建时间");
        $form->tools(function (Form\Tools $tools) {
            $tools->disableDelete();
        });
        return $form;
    }
}
