<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Master\masterInfo;
use App\Admin\Actions\Master\setForeman;
use App\Admin\Actions\Master\setMaster;
use App\Admin\Actions\Master\setNoForeman;
use App\Models\storeOrder;
use App\Models\userMasterApplication;
use App\Models\userMasterLevel;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Hanson\LaravelAdminWechat\Models\WechatUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OperateMasterController extends AdminController
{
    protected $title = "师傅管理";

    protected function grid()
    {
        $grid = new Grid(new WechatUser());
        $adminRow = Auth::guard('admin')->user();
        $grid->model()->where('type', 2);
        $grid->model()->where('application_id','>',0);
        $grid->model()->where('operate_id', $adminRow['id']);
        $grid->model()->orderBy("id", "desc");
        // $grid->disableExport();
        $userModel = config('admin.database.users_model');
        $grid->filter(function($filter)use($userModel){
            $filter->like('nickname', '昵称');
            $filter->like('name', '师傅名称');
            //$filter->equal('operate_id', '运营中心管理员')->select($userModel::where('type',2)->pluck('name','id'));
            $filter->equal('mobile', '手机号');
            $filter->equal('master_type', '类型')->radio([
                ''   => '全部',
                0    => '师傅',
                1    => '工长',
            ]);
        });
        $grid->column("id", "编号");
        $grid->column("pid", "所属工长")->display(function ($pid){
            $name = '无';
            if($pid > 0){
                $name = WechatUser::where('id',$pid)->value('name');
            }
            return $name;
        });
        //$grid->column("openid", "openid");
        $grid->column("name", "师傅名称");
        $grid->column("nickname", "昵称");
        $grid->column("mobile", "手机号");
//        $grid->column('gender_readable', '性别');
        //$grid->column('country', '国家');
        $grid->column('province', '省份')->display(function (){
            $code = userMasterApplication::where('id',$this->application_id)->value('province');
            return DB::table('china_area')->where('code',$code)->value('name');
        });
        $grid->column('city', '城市')->display(function (){
            $code = userMasterApplication::where('id',$this->application_id)->value('city');
            return DB::table('china_area')->where('code',$code)->value('name');
        });
        /*$grid->column("operate_id", "运营中心")->display(function ($opid)use($userModel){
            $name = '';
            if($opid > 0){
                $name = $userModel::where('id',$opid)->value('name');
            }
            return $name;
        });*/
        $grid->column("master_type", "类型")->using(['0'=>'师傅','1'=>'工长']);
        $grid->column("star", "星级")->display(function ($star){
            if($star == 5){
                $html = '<i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i>';
            }elseif ($star == 4){
                $html = '<i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star-o"></i>';
            }elseif ($star == 3){
                $html = '<i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star-o"></i><i class="fa fa-star-o"></i>';
            }elseif ($star == 2){
                $html = '<i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star-o"></i><i class="fa fa-star-o"></i><i class="fa fa-star-o"></i>';
            }elseif ($star == 3){
                $html = '<i class="fa fa-star"></i><i class="fa fa-star-o"></i><i class="fa fa-star-o"></i><i class="fa fa-star-o"></i><i class="fa fa-star-o"></i>';
            }
            return $html;
        });
        $grid->column("master_status", "状态")->using(['1'=>'空闲','2'=>'忙碌']);
        $grid->column("accept_order_num", "接单量")->display(function (){
            $number = storeOrder::where('master_id',$this->id)->whereIn('status',[8,9,6,10])->where('need_service',1)->count();
            return $number;
        });
        $grid->column("reject_order_num", "拒单量")->display(function (){
            $number = storeOrder::where('master_id','<>',$this->id)->where('reject_master_id',$this->id)->whereIn('status',[5,8,9,6,10])->count();
            return $number;
        });
        $grid->column("master_level", "师傅等级")->display(function ($level){
            return userMasterLevel::where('id',$level)->value('level_title');
        });
        $grid->column("amount", "等级补贴")->display(function (){
            $level = $this->master_level;
            return userMasterLevel::where('id',$level)->value('amount');
        })->sortable();
        //$grid->column("base_wage", "基础工价")->sortable();
        $grid->column("policy_subsidy", "政策补贴")->sortable();
        $grid->column("created_at", "创建时间")->sortable();
        $grid->disableCreateButton();
        $grid->batchActions(function ($batch) {
            $batch->disableDelete();
        });
        $grid->actions(function ($actions) {
            // 去掉删除
            $actions->disableDelete();
            // 去掉编辑
            $actions->disableEdit();
            // 去掉查看
            //$actions->disableView();
            $actions->add(new setMaster());
            if($actions->row['application_id'] > 0){
                $actions->add(new masterInfo());
            }
            if($actions->row['master_type'] == 0){
                $actions->add(new setForeman());
            }
            if($actions->row['master_type'] == 1){
                $actions->add(new setNoForeman());
            }
        });
        return $grid;
    }

    protected function detail($id)
    {
        $userModel = config('admin.database.users_model');
        $show = new Show(WechatUser::findOrFail($id));
        $show->field("id", "编号");
        $show->field("avatar", "头像")->image();
        $show->field("master_type", "师傅类型")->using(['0'=>'师傅','1'=>'工长']);
        $show->field("pid", "所属工长")->as(function($pid){
            $name = '无';
            if($pid > 0){
                $name = WechatUser::where('id',$pid)->value('nickname');
            }
            return $name;

        });
        $show->field("openid", "openid");
        $show->field("nickname", "昵称");
        $show->field("name", "名称");
        $show->field("mobile", "手机号");
        $show->field("star", "星级");
        $show->field("master_level", "师傅等级")->as(function ($level){
            return userMasterLevel::where('id',$level)->value('level_title');
        });
        $show->field("amount", "等级补贴")->as(function (){
            $level = $this->master_level;
            return userMasterLevel::where('id',$level)->value('amount');
        });
        $show->field("base_wage", "基础工价");
        $show->field("policy_subsidy", "政策补贴");
        $show->field("operate_id", "运营中心管理员")->as(function($opid)use($userModel){
            $name = '';
            if($opid > 0){
                $name = $userModel::where('id',$opid)->value('name');
            }
            return $name;
        });
        $show->field("master_type", "类型")->using(['0'=>'师傅','1'=>'工长']);
        $show->field("master_status", "师傅状态")->using(['1'=>'空闲','2'=>'忙碌']);
        $show->field("created_at", "创建时间");
        $show->field("updated_at", "更新时间");
        return $show;
    }
}
