<?php

namespace App\Admin\Controllers;

use App\Models\storeCategory;
use App\Models\storeExpressPostage;
use App\Models\storeProduct;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\MessageBag;

class StoreProductController extends AdminController
{
    protected $title = "商品管理";

    protected function grid()
    {
        $grid = new Grid(new storeProduct());
        $grid->model()->orderBy("id", "desc");
        $grid->disableExport();
        $grid->filter(function($filter){
            $filter->like('product_name', '商品名称');
            $filter->equal('type','类型')->radio([
                ''   => '全部',
                1    => '普通产品',
                2    => '服务包',
            ]);
           /* $filter->equal('is_show','状态')->radio([
                ''   => '全部',
                0    => '下架',
                1    => '上架',
            ]);*/
            $filter->equal('is_discount','优惠')->radio([
                ''   => '全部',
                0    => '否',
                1    => '是',
            ]);
            $filter->equal('is_diy','定制产品')->radio([
                ''   => '全部',
                0    => '否',
                1    => '是',
            ]);
            $filter->equal('is_sample','小样')->radio([
                ''   => '全部',
                0    => '否',
                1    => '是',
            ]);
            $filter->equal('cate_id','分类')->select(storeCategory::orderBy('sort','asc')->pluck('cate_name','id'));
        });
        $grid->column("id", "编号")->sortable();
        $grid->column("type", "类型")->using(['1'=>'普通产品','2'=>'服务包']);
        $grid->column("cate_id", "分类")->display(function ($cate_id){
            $catName = storeCategory::where('id',$cate_id)->value('cate_name');
            return $catName;
        });
        $grid->column("product_name", "商品名称");
        $grid->column("image", "商品图片")->image('','50');
        $grid->column("price", "商品价格")->sortable();
        $grid->column("ot_price", "市场价")->sortable();
        $grid->column("sort", "排序")->editable()->sortable();
        $grid->column("sales", "销量")->sortable();
        $grid->column("stock", "库存")->sortable();
        $grid->column("is_show", "状态")->using(['0' => '下架', '1' => '上架'])->label([
            0 => 'default',
            1 => 'success',
        ])->filter([
            0 => '下架',
            1 => '上架',
        ]);
        $grid->column("is_hot", "热销")->radio(['0' => '否', '1' => '是'])->label([
            0 => 'danger',
            1 => 'info',
        ])->filter([
            0 => '否',
            1 => '是',
        ]);
        $grid->column('sales_time','销售日期')->display(function () {
            return $this->sales_start_time.'-'.$this->sales_end_time;
        });
        $grid->column("browse", "浏览量")->sortable();
        $grid->column("created_at", "创建时间")->sortable();
        $grid->actions(function ($actions) {
            // 去掉查看
            $actions->disableView();
        });
        return $grid;
    }

    protected function form()
    {
        $form = new Form(new storeProduct());
        $form->tab('基础信息', function ($form){
            $form->radio('type', __('类型'))->options(['1' => '普通产品', '2'=> '服务包'])->default('1') ->when('=', 2, function ($form) {
                $form->number('service_day', __('规定服务时间（天）'))->min(1)->default(1);
                $form->decimal('service_base_wage', __('服务基础工价'))->default(0.00);
            });
            $form->select('cate_id', __('分类'))->options(storeCategory::pluck('cate_name', 'id'))->required();
            $form->text("product_name", "商品名称")->required();
            //$form->text("short_name", "商品简称")->required();
            $form->decimal("price", "售价")->required();
            $form->decimal("ot_price", "市场价")->required();
            $form->number("stock", "库存")->default(999)->required();
            $form->number("sales", "销量")->default(0)->required();
           /* $form->number("min_buy", "单用户起售")->default(1)->min(1)->required();
            $form->number("max_buy", "单用户购买上限 ")->default(999)->min(1)->required();*/
            //$form->textarea("product_info", "商品简介");
            $form->url("vrlink", "VR Link");
            $form->radio('is_show', __('状态'))->options(['0' => '下线', '1'=> '上线'])->default('1');
            $form->radio('is_hot', __('是否热销'))->options(['0' => '否', '1'=> '是'])->default('0')->help('热销商品列表展示');
            $form->radio('is_diy', __('是否是定制产品'))->options(['0' => '否', '1'=> '是'])->default('0');
            $form->radio('is_discount', __('是否优惠'))->options(['0' => '否', '1'=> '是'])->default('0')->help('优惠专区列表展示');
            $form->radio('is_sample', __('是否是小样'))->options(['0' => '否', '1'=> '是'])->default('0')->help('小样专区列表展示');
            $form->number("sort", "排序")->default(0);
        })->tab('常规销售设置', function ($form) {
            $form->sku('good_sku','商品SKU');
            $produclist = storeProduct::where('is_show',1)->where('type',2)->pluck('product_name', 'id');
            $form->select('asso_service_id', __('关联服务包'))->options($produclist);
            $form->datetime("sales_start_time", "销售开始时间")->required();
            $form->datetime("sales_end_time", "销售结束时间")->required();
            $postagelist = storeExpressPostage::where('status',1)->pluck('name','id')->toArray();
            $postage[0]= '免邮';
            //$postage['-1']= '邮费到付';
            if($postagelist){
                $postagelist = $postage+$postagelist;
            }else{
                $postagelist = $postage;
            }
            $form->select("postage", "邮费规则")->options($postagelist);
            $form->radio('postage_num','邮费系数')->options(['0' => '无系数', '1'=> '自动翻倍','2'=>'按梯度设置'])->default(0)->help('邮费系数代表用户购买商品到达某个数值时，需要支付更多邮费，请慎重选择')->when(2, function (Form $form) {
                $form->table('postage_gradient','梯度设置', function ($table){
                    $table->number('buy_number','达到购买数量')->min(0)->default(0);
                    $table->decimal('gradient','邮费系数')->default(0);
                });
            });
            $form->image("cover_image", "首页封面图");
            $form->image("image", "缩略图")->help('建议尺寸为750*750')->required();
            $form->multipleImage("slider_image", "轮播图")->help('建议尺寸为750*750，最多上传6张')->removable()->sortable()->options([
                'dropZoneEnabled' => true, // 该参数允许拖拽上传
                'browseOnZoneClick' => true, // 该参数允许点击上传
                'showDrag' => true,
                'sortable' => true,
                'slugCallback' => false, // 该参数是重新选择后依旧保留之前的，并且不会重复显示
                'uploadUrl' => '#', // 异步上传
                'showUpload' => false, // 是否显示上传按钮
                'layoutTemplates' => ['actionUpload' => ''], // 该参数要与uploadUrl结合使用，目的是为了不是异步上传的，但是能删掉多张图片中的某一张
                'maxFileCount' => 6 // 该参数是最多只能选择多少张
            ]);
            $form->editor("content", "图文内容")->required();
        });
        $form->submitted(function (Form $form){
            if (request()->route()->getActionMethod() == 'store') {
                $validator = Validator::make(request()->all(),
                    [
                        'slider_image'=>'required',
                    ],
                    [
                        'slider_image.required'=> '轮播图片必填',
                    ]);
                if ($validator->fails()) {
                    $error = new MessageBag([
                        'title'   => '提示',
                        'message' => '轮播图片不能为空',
                    ]);
                    return back()->with(compact('error'))->withInput();
                }
            } else {
                $error = new MessageBag([
                    'title'   => '提示',
                    'message' => '轮播图片不能为空',
                ]);
                // 判断数据库中是否还有图片，并判断是否有新图片上传
                if (!$form->model()->slider_image && !request()->input('slider_image')) {
                    return back()->with(compact('error'))->withInput();
                }
            }
        });
        $form->tools(function (Form\Tools $tools) {
            // 去掉`查看`按钮
            $tools->disableView();
        });
        $form->footer(function ($footer) {
            // 去掉`查看`checkbox
            $footer->disableViewCheck();
        });
        return $form;
    }
}
