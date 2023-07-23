<?php

namespace App\Admin\Controllers;

use App\Models\storeAds;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class StoreAdsController extends AdminController
{
    protected $title = "广告管理";

    protected function grid()
    {
        $grid = new Grid(new storeAds());
        $grid->model()->orderBy("id", "desc");
        $grid->disableExport();
        $grid->filter(function($filter){
            $filter->like('title', '广告名称');
            $filter->equal('position','广告位置')->radio([
                ''   => '全部',
                1    => '轮播图',
                2    => '首页入口广告',
            ]);
            $filter->gt('start_time','开始时间')->datetime();
            $filter->lt('end_time','结束时间')->datetime();
        });

        $grid->column("id", "编号")->sortable();
        $grid->column("type", "类型")->using(['1' => '图片', '2'=> '视频']);
        $grid->column("title", "广告名称");
        $grid->column("image", "广告图片")->image('','50');
        //$grid->column("file", "视频链接")->link();
        $grid->column("link", "链接")->sortable();
        $grid->column("start_time", "开始时间")->sortable();
        $grid->column("end_time", "结束时间")->sortable();
        $grid->column("position", "位置")->using(['1' => '用户端轮播图', '2' => '首页入口广告','3' => '师傅端入口广告','4' => '师傅端资讯轮播图']);
        $grid->column("sort", "排序")->editable()->sortable();
        $grid->column("status", "状态")->using(['0' => '隐藏', '1' => '显示'])->filter([
            0 => '隐藏',
            1 => '显示',
        ]);
        $grid->column("created_at", "创建时间")->sortable();
        return $grid;
    }

    protected function detail($id)
    {
        $show = new Show(storeAds::findOrFail($id));
        $show->field("id", "编号");
        $show->field("type", "类型")->using(['1' => '图片', '2'=> '视频']);
        $show->field("title", "广告名称");
        $show->field("image", "广告图片")->image();
        $show->field("file", "视频")->file();
        $show->field("link", "链接");
        $show->field("start_time", "开始时间");
        $show->field("end_time", "结束时间");
        $show->field("position", "位置")->using(['1' => '用户端轮播图', '2' => '首页广告','3' => '师傅端入口广告','4' => '师傅端资讯轮播图']);
        $show->field("sort", "排序");
        $show->field("status", "状态")->using(['0' => '隐藏', '1' => '显示']);;
        $show->field("created_at", "创建时间");
        $show->field("updated_at", "更新时间");
        return $show;
    }

    protected function form()
    {
        $form = new Form(new storeAds());
        $form->display("id", "编号");
        $form->text("title", "广告名称")->required();
        $form->radio('type', __('类型'))->options(['1' => '图片', '2'=> '视频'])->when(1, function (Form $form) {
            $form->image("image", "广告图片");
        })->when(2, function (Form $form) {
            $form->image("image", "视频封面");
            $form->file("file", "视频文件");
        })->default('1');
        $form->text("link", "链接")->help('产品链接(修改id)：pages/switchPages/product/detail?id=4;
        师傅端资讯文章链接(修改id):pages/InformationDetails/InformationDetails?id=11；优惠券发布链接（修改发布优惠券id）：pages/switchPages/voucher/voucher?couponissue_id=2');
        $form->datetime("start_time", "开始时间")->required();
        $form->datetime("end_time", "结束时间")->required();
        $form->radio('position', __('广告位置'))->options(['1' => '用户端轮播图','4' => '师傅端资讯轮播图'])->default('1');
        $form->number("sort", "排序")->default(0);
        $form->radio('status', __('状态'))->options(['0' => '隐藏', '1'=> '显示'])->default('1');
        $form->display("created_at", "创建时间");
        return $form;
    }
}
