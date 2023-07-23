<?php
namespace App\Admin\Controllers;

use App\Models\articleCategory;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Layout\Content;
use Encore\Admin\Tree;

class ArticleCategoryController extends AdminController
{
    protected $title="文章分类";

    public function index(Content $content)
    {
        $tree = new Tree(new articleCategory);
        /*$tree->branch(function ($branch) {
            $src = asset(str_replace('public','storage',$branch['icon']));
            $logo = "<img src='$src' style='max-width:30px;max-height:30px' class='img'/>";

            return "{$logo} - {$branch['title']}";
        });*/
        return $content
            ->header('文章分类管理')
            ->body($tree);
    }
    public function create( Content $content)
    {
        return $content->header('新增')
            ->description('新增分类')
            ->body($this->form()
            );
    }
    public function edit($id, Content $content)
    {
        return
            $content->header('修改')
                ->description('修改分类')
                ->body($this->form()->edit($id)
                );

    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new articleCategory());

        $form->display('id', 'ID');
        $form->select('pid','上级')->options(articleCategory::selectOptions());
        $form->text('title', __('分类名称'))->required();
        $form->image('image','缩略图');
        $form->textarea('intro', __('简介'));
        $form->number('sort', __('排序'))->default(0);
        $form->radio('status', __('状态'))->options(['0' => '隐藏', '1'=> '显示'])->default('1');

        return $form;
    }
}
