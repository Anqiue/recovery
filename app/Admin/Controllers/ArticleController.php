<?php
namespace App\Admin\Controllers;

use App\Models\article;
use App\Models\articleCategory;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Config;

class ArticleController extends AdminController
{
    protected $title="文章管理";

    protected function grid()
    {
        $grid = new Grid(new article());
        $grid->model()->orderBy("id", "desc");
        $grid->model()->where('cid', '>',0);
        $grid->disableExport();
        $grid->filter(function($filter){
            $filter->like('title', '标题');
            $filter->equal('cid','分类')->select(articleCategory::where('status',1)->pluck('title','id'));
        });

        $grid->column("id", "ID")->sortable();
        $grid->column('cid', __('分类'))->display(function ($cid){
            $catName = articleCategory::where('id',$cid)->value('title');
            return $catName;
        });
        $grid->column("title", "标题")->sortable();
        $grid->column("author", "作者");
        $grid->column("image", "缩略图")->image('','50');
        $grid->column("sort", "排序")->editable()->sortable();
        $grid->column("recommend", "推荐")->using(['0' => '否', '1' => '是'])->filter([
            0 => '否',
            1 => '是',
        ]);
        $grid->column("status", "状态")->using(['0' => '隐藏', '1' => '显示'])->filter([
            0 => '隐藏',
            1 => '显示',
        ]);
        $grid->column("created_at", "创建时间")->sortable();
        return $grid;
    }

    protected function detail($id)
    {
        $show = new Show(article::findOrFail($id));
        $show->field("id", "ID");
        $show->field('cid', __('分类'))->as(function ($cid){
            $catName = articleCategory::where('id',$cid)->value('title');
            return $catName;
        });
        $show->field("title", "标题");
        $show->field("author", "作者");
        $show->field('image', __('缩略图'))->image();
        $show->field("synopsis", "简介");
        $show->field("sort", "排序");
        $show->field("recommend", "推荐")->using(['0' => '否', '1' => '是']);;
        $show->field("status", "状态")->using(['0' => '隐藏', '1' => '显示']);;
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
        $form->select('cid', __('分类'))->options(articleCategory::where('status',1)->pluck('title','id'))->required();
        $form->text("title", "标题")->required();
        $form->text("author", "作者");
        $form->image("image", "缩略图")->required();
        $form->textarea("synopsis", "简介");
        $form->editor('content', __('内容'));
        $form->number("sort", "排序")->default(0);
        $form->radio('recommend', __('推荐'))->options(['0' => '否', '1'=> '是'])->default(0);
        $form->radio('status', __('状态'))->options(['0' => '隐藏', '1'=> '显示'])->default('1');
        $form->display("created_at", "创建时间");
        return $form;
    }
}
