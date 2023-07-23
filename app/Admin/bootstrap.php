<?php

/**
 * Laravel-admin - admin builder based on Laravel.
 * @author z-song <https://github.com/z-song>
 *
 * Bootstraper for Admin.
 *
 * Here you can remove builtin form field:
 * Encore\Admin\Form::forget(['map', 'editor']);
 *
 * Or extend custom form field:
 * Encore\Admin\Form::extend('php', PHPEditor::class);
 *
 * Or require js and css assets:
 * Admin::css('/packages/prettydocs/css/styles.css');
 * Admin::js('/packages/prettydocs/js/main.js');
 *
 */

//Encore\Admin\Form::forget(['map', 'editor']);

Admin::css('/vendor/adminstyle.css');
Admin::css('/vendor/ionicons.min.css');
Admin::css('/vendor/dataTables.bootstrap.min.css');
Admin::js('/vendor/dataTables.bootstrap.min.js');
Admin::js('/vendor/jquery.dataTables.min.js');

Admin::css('/vendor/layui/css/layui.css');
Admin::js('/vendor/layui/layui.js');
Admin::js('/vendor/layui/layer.js');
Admin::js('/vendor/jquery.fileDownload.js');
