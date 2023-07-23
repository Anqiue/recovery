<?php

namespace App\Admin\Controllers;


use App\Admin\Actions\User\storeCouponAction;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Grid;
use Hanson\LaravelAdminWechat\Actions\ImportUsers;
use Hanson\LaravelAdminWechat\Models\WechatUser;

class UserListController extends AdminController
{
    protected $title = '用户列表';

    protected function grid()
    {
        $grid = new Grid(new WechatUser);
        $grid->model()->where('type', 1);
        $grid->model()->where('mobile', '<>',NULL);
        $grid->filter(function($filter){
            $filter->like('nickname', '昵称');
            $filter->like('mobile', '手机');
        });
        $grid->column('id', __('ID'))->sortable();
        $grid->column('avatar', '头像')->image('', 64, 64);
        //$grid->column('app_id', 'App Id');
        //$grid->column('openid', 'Openid');
        $grid->column('nickname', '昵称');
        $grid->column('mobile', '手机');
        //$grid->column('gender_readable', '性别');
//        $grid->column('country', '国家');
//        $grid->column('province', '省份');
//        $grid->column('city', '城市');
//        $grid->column('subscribed_at', '关注时间');

        $grid->disableCreateButton();
        $grid->disableActions();
        $grid->tools(function (Grid\Tools $tools) {
            //$tools->append(new ImportUsers());
            $tools->append(new storeCouponAction());
        });

        return $grid;
    }
}
