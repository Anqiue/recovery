<?php

namespace App\Admin\Controllers;

use App\Admin\Forms\SystemConfig;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Layout\Content;
use Encore\Admin\Widgets\Tab;

class SystemConfigController extends AdminController
{
    protected $title = '基础设置';

    public function index(Content $content)
    {
        $forms = [
            '编辑' => SystemConfig::class,
        ];
        return $content->title('基础设置')->body(Tab::forms($forms));
    }
}
