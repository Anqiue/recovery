<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AutoGenerate extends Command
{
    protected $relateUse = [];
    ///安装
    ///使用步骤1：直接在字段注释中写入如下格式即可 const|0=>待开启,1=>开启*relate|belongTo|user|user_id|id*attr
    ///使用步骤2：执行命令 php artisan auto:generate
    protected $signature = 'auto:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '自动创建模型、路由、菜单、管理控制器';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('开始执行生成>>>>>>>>>>>>');
        $tables = $this->getTableSchema();
        foreach ($tables as $table) {
            //过滤掉不需要处理的
            if (in_array($table, ['admin_menu', 'admin_operation_log', 'admin_permissions', 'admin_role_menu', 'admin_role_permissions',
                'admin_role_users', 'admin_roles', 'admin_user_permissions', 'admin_users', 'jobs', 'failed_jobs', 'migrations', 'password_resets', 'personal_access_tokens',
                'wechat_cards', 'wechat_configs', 'wechat_merchants', 'wechat_orders', 'wechat_users', 'users'])) {
                continue;
            }
            //生成模型文件
            $this->generateModels($table);
            //写入或者更新菜单
            $this->createOrUpdateModalMenu($table);
        }

        //写入路由文件，写之前要去掉所有的
        $this->geneRoutes($tables);
        //写入控制器管理文件
        $this->geneAdminController($tables);
        $this->info('执行完成>>>>>>>>>>>>');
        return 0;
    }

//    --------------------获取基础数据--------------------------

    //所有数据表和所有字段属性
    protected function getAllTableAndAttributes()
    {
        $tables = $this->getTableSchema();
        $tableFiles = [];
        foreach ($tables as $table) {
            $tableFiles[$table] = $this->getTableStructure($table);
        }
        return $tableFiles;
    }

    //获取数据库的schema
    protected function getTableSchema()
    {
        $tables = DB::connection()->getDoctrineSchemaManager()->listTableNames();
        return $tables;
    }

    //获取表结构
    protected function getTableStructure($tableName)
    {
        return DB::connection()->getDoctrineSchemaManager()->listTableColumns($tableName);
    }

    //获取文件夹路径
    protected function getDocumentPath($type = 'Models')
    {
        return app_path() . '/' . $type;
    }

    //写文件
    protected function writeFile($path, $fileName, $content)
    {
        file_put_contents($path . '/' . $fileName . '.php', $content);
    }

//    --------------------获取基础数据--------------------------
//    --------------------生成模型------------------------------

    // 生成模型，减少10%的工作量
    protected function generateModels($tableName)
    {
        $this->writeFile($this->getDocumentPath(), Str::camel($tableName), $this->getModalContent($tableName));
        $this->info('模型文件' . $tableName . '生成');
    }

    //获取模型文件的所有内容
    protected function getModalContent($tableName)
    {
        $tableStrutue = $this->getTableStructure($tableName);
        $content = $this->getModelHeader($tableName) . $this->getModelBody($tableName, $tableStrutue) . $this->getModelFooter();
        return $content;
    }

    //获取模型文件的头部
    protected function getModelHeader($tableName)
    {
        $tableNameCamel = Str::camel($tableName);

        $tmp = "<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;use Illuminate\Database\Eloquent\SoftDeletes;class $tableNameCamel extends Model
{";
        $tmp = $tmp . 'use SoftDeletes;protected $table="' . Str::lower($tableName) . '";
    protected $fillable=[];
    protected $guarded=[];
    protected $appends=[];
    protected $hidden=[];';
        return $tmp;
    }

    //  获取模型文件的正文部分: 主要是解析字段是数组对象，const数组
    //  注释结构
    //example const|0=>待开启,1=>开启*relate|belongTo|user|user_id|id*attr
    protected function getModelBody($tableName, $tableStrutue)
    {
        $commentsArr = $this->getTableComments($tableStrutue);

        if (empty($commentsArr)) {
            return '';
        }
        $tmp = '';
        foreach ($commentsArr as $field => $comments) {
            $tmp = $tmp . $this->parseComments($field, $comments);
        }
        return $tmp;
    }

    // 核心解析注释
    protected function parseComments($field, $comments)
    {
        $tmp = '';
        $comments = explode('*', $comments);
        foreach ($comments as $comment) {
            $commentCommand = explode('|', $comment);
            $command = $commentCommand[0];
            switch ($command) {
                case 'relate':
                    $relateType = $commentCommand[1];
                    $relateTable = $commentCommand[2];
                    $relateFields1 = $commentCommand[3];
                    $relateFields2 = $commentCommand[4];
                    $main = 'return $this->' . $relateType . '(' . $relateTable . '::class, "' . $relateFields1 . '","' . $relateFields2 . '");';
                    $geneStr = 'public function ' . $relateTable . '(){' . $main . '}';
                    break;
                case 'attr':
                    $main = 'return (empty($this->attributes["' . $field . '"]) ? "" : "http://" . config("filesystems.disks.admin.domains.default")' . '."/".' . '$this->attributes["cover"]);';
                    $geneStr = 'public function get' . Str::ucfirst(Str::camel($field)) . 'Attribute' . '(){' . $main . '}';
                    break;
                case 'const':
                    $geneStr = 'const ' . Str::upper($field) . '=[' . $commentCommand[1] . '];';
                    break;
                default:
                    $geneStr = '';
                    break;
            }
            $tmp = $tmp . $geneStr;
        }
        return $tmp;
    }

    //获取表字段和注释数组
    protected function getTableComments($tableStrutue)
    {
        $commentsArr = [];
        foreach ($tableStrutue as $field => $obj) {
            $comment = $obj->getComment();
            if (!empty($comment)) {
                $commentsArr[$field] = $comment;
            }
        }
        return $commentsArr;
    }

    //获取模型文件的尾部
    protected function getModelFooter()
    {
        return "}";
    }

//    --------------------生成模型------------------------------
//    --------------------生成菜单：要考虑已经有的情况------------------------------
    protected function createOrUpdateModalMenu($tableName)
    {
        // 过滤掉laravel-admin自带的
        if (in_array($tableName, ['admin_menu', 'admin_users', 'jobs', 'failed_jobs', 'migrations', 'password_resets'])) {
            return true;
        }
        $where = [
            'uri' => '/' . Str::lower($tableName)
        ];
        $has = DB::table('admin_menu')->where($where)->first();
        $data = [
            'parent_id' => 0,
            'order' => 1,
            'title' => Str::lower($tableName),
            'icon' => 'fas fa-bars',
            'uri' => '/' . Str::lower($tableName)
        ];
        if (empty($has)) {
            DB::table('admin_menu')->insert($data);
        }
        DB::table('admin_menu')->where($where)->update($data);
        $this->info('菜单数据库生成完成');
    }
//    --------------------生成菜单:要考虑已经有的情况------------------------------

//    --------------------生成路由:1种是覆盖，1种是后面添加------------------------------
    //执行生成路由文件
    protected function geneRoutes($tableArr)
    {
        //获取旧的文件内容
        $path = app_path() . '/Admin/routes.php';
        //生成要写入的路由内容
        $newRouteFileContent = $this->geneRouteHeader() . $this->geneRouteBody($tableArr) . $this->geneRouteFooter();
        //写入文件
        file_put_contents($path, $newRouteFileContent);
        $this->info('路由文件生成完成');
    }

    //生成路由文件头
    protected function geneRouteHeader()
    {
        return '<?php

use Illuminate\Routing\Router;

Admin::routes();

Route::group([
    "prefix"        => config("admin.route.prefix"),
    "namespace"     => config("admin.route.namespace"),
    "middleware"    => config("admin.route.middleware"),
    "as"            => config("admin.route.as"),
], function (Router $router) {
    $router->get("/", "HomeController@index")->name("home");';
    }

    //生成路由文件主体
    protected function geneRouteBody($tableArr)
    {
        $tmp = "";
        foreach ($tableArr as $k => $tableName) {
            if (in_array($tableName, ['admin_menu', 'admin_operation_log', 'admin_permissions', 'admin_role_menu', 'admin_role_permissions',
                'admin_role_users', 'admin_roles', 'admin_user_permissions', 'admin_users', 'jobs', 'failed_jobs', 'migrations', 'password_resets', 'personal_access_tokens',
                'wechat_cards', 'wechat_configs', 'wechat_merchants', 'wechat_orders', 'wechat_users', 'users'])) {
                continue;
            }
            $str = '$router->resource("' . Str::lower($tableName) . '",' . Str::ucfirst(Str::camel($tableName)) . 'Controller::class);';
            $tmp = $tmp . $str;
        }
        return $tmp;
    }

    //生成路由文件结尾
    protected function geneRouteFooter()
    {
        return "});";
    }

//    --------------------生成菜单:要考虑已经有的情况------------------------------
//    --------------------生成菜单:要考虑已经有的情况------------------------------
    protected function geneAdminController($tables)
    {
        foreach ($tables as $k => $table) {
            $this->relateUse = [];
            if (in_array($table, ['admin_menu', 'admin_operation_log', 'admin_permissions', 'admin_role_menu', 'admin_role_permissions',
                'admin_role_users', 'admin_roles', 'admin_user_permissions', 'admin_users', 'jobs', 'failed_jobs', 'migrations', 'password_resets', 'personal_access_tokens',
                'wechat_cards', 'wechat_configs', 'wechat_merchants', 'wechat_orders', 'wechat_users', 'users'])) {
                continue;
            }
            $content = $this->geneAdminControllerHeader($table) . $this->geneAdminControllerBody($table) . $this->geneAdminControllerFooter();
            $this->writeAdminControllerFile($table, $content);

            // (必须做，否则还是需要进行处理)根据关联字段数据，生成对应的模型use引用 ;要根据一个表的文件来；有N个表
            $insertUse = '';
            foreach ($this->relateUse as $v) {
                $insertUse = $insertUse . str_replace('/', '', $v);
            }
            $file = app_path() . "/Admin/Controllers/" . Str::ucfirst(Str::camel($table)) . "Controller.php";
            $this->insert($file, 5, $insertUse);
        }
    }

    //生成admin controller的头部文件
    protected function geneAdminControllerHeader($tableName)
    {
        $str = $tableName;
        return '<?php
namespace App\Admin\Controllers;

use App\Models\\' . Str::camel($str) . ';
    use Encore\Admin\Controllers\AdminController;
    use Encore\Admin\Form;
    use Encore\Admin\Grid;
    use Encore\Admin\Show;
        class ' . Str::ucfirst(Str::camel($tableName)) . 'Controller extends AdminController
{
    ';
    }

    //生成admin controller的主体内容
    protected function geneAdminControllerBody($tableName)
    {
        $tmp = '';
        $tableStructure = $this->getTableStructure($tableName);
        $tmp = $tmp . $this->getAdminControllerTitle($tableName);
        $tmp = $tmp . $this->getAdminControllerBodyTable($tableName, $tableStructure);
        $tmp = $tmp . $this->getAdminControllerBodyDetail($tableName, $tableStructure);
        $tmp = $tmp . $this->getAdminControllerBodyForm($tableName, $tableStructure);

        return $tmp;
    }

    // 生成admin controller的title部分
    protected function getAdminControllerTitle($tableName)
    {
        $tableComment = DB::connection()->getDoctrineSchemaManager()->listTableDetails($tableName)->getComment();
        $title = Str::contains($tableComment, 'title=');
        if (empty($title)) {
            $title = Str::ucfirst($tableName);
        } else {
            $title = Str::between($tableComment, 'title=', '*');
        }
        return 'protected $title="' . $title . '";';
    }

    //生成admin controller的table表格
    protected function getAdminControllerBodyTable($tableName, $tableStructure)
    {
        $rows = '';
        foreach ($tableStructure as $field => $obj) {
            // 判断注释中，是否存在注释中文
            $has = Str::startsWith('注释=', $obj->getComment());
            if (!empty($has)) {
                dd($obj->getComment());
            }
            //如果是created_at等，直接__created_at使用;如果是没有注释的，就字段名大写即可；如果是有comment，但是没有注释=的，字段名大写即可；如果是有comment，comment中包含注释的，则使用注释=后面的中文
            if (in_array($field, ['created_at'])) {
                $chineseName = '创建时间';
                $rows = $rows . '$grid->column("' . $obj->getName() . '", "' . $chineseName . '")->sortable();';
                continue;
            }
            if (in_array($field, ['id'])) {
                $chineseName = '编号';
                $rows = $rows . '$grid->column("' . $obj->getName() . '", "' . $chineseName . '")->sortable();';
                continue;
            }
            if (in_array($field, ['updated_at'])) {
//                $chineseName = '__' . $field;
                $chineseName = '更新时间';
                $rows = $rows . '//$grid->column("' . $obj->getName() . '", "' . $chineseName . '")->sortable();';
                continue;
            }
            if (empty($obj->getComment())) {
//                $chineseName = Str::ucfirst($field);
                $chineseName = $obj->getComment();
                $rows = $rows . '$grid->column("' . $obj->getName() . '", "' . $chineseName . '")->sortable();';
                continue;
            } else {
                //包含注释的，需要进行解析处理
                $comment = $obj->getComment();
                $has = Str::contains($comment, '注释=');
                if (empty($has)) {
//                    $chineseName = '__' . $field;
                    $chineseName = $obj->getComment();
                    $parseArr = explode('*', $comment);
                } else {
                    // 包含注释=的，
                    $chineseName = Str::between($comment, '注释=', '*');
                    $parseArr = explode('*', $comment);
                    Arr::forget($parseArr, 0);
                }
                $const = Str::contains($comment, 'const|');
                if (!empty($const)) {
                    $rows = $rows . '$grid->column("' . $obj->getName() . '", "' . Str::after(Str::before($comment, '*'), '注释=') . '")->using(' . Str::lower($tableName) . '::' . Str::upper($field) . ')->sortable();';
                    continue;
                }
                $relate = Str::contains($comment, 'relate|');
                if (!empty($relate)) {
                    $relate = Str::between($comment, 'relate|', '*');
                    $relate = explode('|', $relate);
                    //下面的.name是默认关联表的显示字段
                    $rows = $rows . '$grid->column("' . $relate[1] . '.name", "' . Str::after(Str::before($comment, '*'), '注释=') . '")->sortable();';
                    $this->info('表' . $tableName . '需要处理下控制器table中的关联');

                    array_push($this->relateUse, 'use App\Models/\/' . $relate[1] . ';');
                    continue;
                }
                $const = Str::contains($comment, 'attr|');
                if (!empty($const)) {
                    $rows = $rows . '$grid->column("' . $obj->getName() . '", "' . Str::after(Str::before($comment, '*'), '注释=') . '")->image("",100,50);';
                    continue;
                }

                $rows = $rows . '$grid->column("' . $obj->getName() . '", "' . $chineseName . '")->sortable();';
            }
        }
        $tmp = 'protected function grid()
                {
                    $grid = new Grid(new ' . Str::camel($tableName) . '());
        $grid->model()->orderBy("id", "desc");
        $grid->disableExport();
        ' . $rows . '
        return $grid;
    }';
        return $tmp;
    }

    //获取详情
    protected function getAdminControllerBodyDetail($tableName, $tableStructure)
    {
        $rows = '';
        foreach ($tableStructure as $field => $obj) {
            // 判断注释中，是否存在注释中文
            $has = Str::startsWith('注释=', $obj->getComment());
            if (!empty($has)) {
                dd($obj->getComment());
            }
            //如果是created_at等，直接__created_at使用;如果是没有注释的，就字段名大写即可；如果是有comment，但是没有注释=的，字段名大写即可；如果是有comment，comment中包含注释的，则使用注释=后面的中文
            if (in_array($field, ['id'])) {
                $chineseName = '编号';
                $rows = $rows . '$show->field("' . $obj->getName() . '", "' . $chineseName . '");';
                continue;
            }
            if (in_array($field, ['created_at'])) {
                $chineseName = '创建时间';
                $rows = $rows . '$show->field("' . $obj->getName() . '", "' . $chineseName . '");';
                continue;
            }
            if (in_array($field, ['updated_at'])) {
                $chineseName = '更新时间';
                $rows = $rows . '$show->field("' . $obj->getName() . '", "' . $chineseName . '");';
                continue;
            }
            if (in_array($field, ['deleted_at'])) {
                $chineseName = '删除时间';
                $rows = $rows . '$show->field("' . $obj->getName() . '", "' . $chineseName . '");';
                continue;
            }
            if (empty($obj->getComment())) {
                $chineseName = Str::ucfirst($field);
                $rows = $rows . '$show->field("' . $obj->getName() . '", "' . $chineseName . '");';
                continue;
            } else {
                //包含注释的，需要进行解析处理
                $comment = $obj->getComment();
                $has = Str::contains($comment, '注释=');
                if (empty($has)) {
                    $chineseName = $comment;
                    $parseArr = explode('*', $comment);
                } else {
                    // 包含注释=的，
                    $chineseName = Str::between($comment, '注释=', '*');
                    $parseArr = explode('*', $comment);
                    Arr::forget($parseArr, 0);
                }
                $const = Str::contains($comment, 'const|');
                if (!empty($const)) {
                    $rows = $rows . '$show->field("' . $obj->getName() . '", "' . Str::after(Str::before($comment, '*'), '注释=') . '")->using(' . Str::lower($tableName) . '::' . Str::upper($field) . ');';
                    continue;
                }
                $relate = Str::contains($comment, 'relate|');
                if (!empty($relate)) {
                    $relate = Str::between($comment, 'relate|', '*');
                    $relate = explode('|', $relate);
                    //下面的.name是默认关联表的显示字段
//                    $rows = $rows . '$show->field("' . $relate[1] . '.name", "' . Str::after(Str::before($comment, '*'), '注释=') . '");';
                    $rows = $rows . '$show->' . $relate[1] . '("' . Str::after(Str::before($comment, '*'), '注释=') . '",function($' . $relate[1] . '){
                        $' . $relate[1] . '->id();$' .
                        $relate[1] . '->name();$' . $relate[1] . '->disableExport()->disableFilter();' . '
                    });';
                    $this->info('表' . $tableName . '需要处理下控制器table中的关联');
                    continue;
                }
                $const = Str::contains($comment, 'attr|');
                if (!empty($const)) {
                    $rows = $rows . '$show->field("' . $obj->getName() . '", "' . Str::after(Str::before($comment, '*'), '注释=') . '")->image("",100,50);';
                    continue;
                }

                $rows = $rows . '$show->field("' . $obj->getName() . '", "' . $chineseName . '");';
            }
        }
        $tmp = 'protected function detail($id)
                {
                    $show = new Show(' . Str::camel($tableName) . '::findOrFail($id));
        ' . $rows . '
        return $show;
    }';
        return $tmp;
    }

    //获取form表单
    protected function getAdminControllerBodyForm($tableName, $tableStructure)
    {
        $rows = '';
        foreach ($tableStructure as $field => $obj) {
            //如果是id,或者created_at
            if (in_array($field, ['updated_at', 'deleted_at'])) {
                continue;
            }
            if (in_array($field, ['id'])) {
                $chineseName = '编号';
                $rows = $rows . '$form->display("' . $obj->getName() . '", "' . $chineseName . '");';
                continue;
            }
            if (in_array($field, ['created_at'])) {
                $chineseName = '创建时间';
                $rows = $rows . '$form->display("' . $obj->getName() . '", "' . $chineseName . '");';
                continue;
            }
            if (in_array($field, ['updated_at'])) {
                $chineseName = '更新时间';
                $rows = $rows . '$form->display("' . $obj->getName() . '", "' . $chineseName . '");';
                continue;
            }
            // 判断注释中，是否存在注释中文
            $has = Str::startsWith('注释=', $obj->getComment());
            if (!empty($has)) {
                dd($obj->getComment());
            }
            //如果是created_at等，直接__created_at使用;如果是没有注释的，就字段名大写即可；如果是有comment，但是没有注释=的，字段名大写即可；如果是有comment，comment中包含注释的，则使用注释=后面的中文
            if (in_array($field, ['created_at', 'updated_at', 'deleted_at'])) {
                $chineseName = '__' . $field;
                $rows = $rows . '$form->text("' . $obj->getName() . '", "' . $chineseName . '")->required();';
                continue;
            }
            if (empty($obj->getComment())) {
                $chineseName = Str::ucfirst($field);
                $rows = $rows . '$form->text("' . $obj->getName() . '", "' . $chineseName . '")->required();';
                continue;
            } else {
                //包含注释的，需要进行解析处理
                $comment = $obj->getComment();
                $has = Str::contains($comment, '注释=');
                if (empty($has)) {
                    $chineseName = $comment;
                    $parseArr = explode('*', $comment);
                } else {
                    // 包含注释=的，
                    $chineseName = Str::between($comment, '注释=', '*');
                    $parseArr = explode('*', $comment);
                    Arr::forget($parseArr, 0);
                }
                $const = Str::contains($comment, 'const|');
                if (!empty($const)) {
                    $rows = $rows . '$form->radio("' . $obj->getName() . '", "' . Str::after(Str::before($comment, '*'), '注释=') . '")->options(' . Str::lower($tableName) . '::' . Str::upper($field) . ')->required();';
                    continue;
                }
                $relate = Str::contains($comment, 'relate|');
                if (!empty($relate)) {
                    $relate = Str::between($comment, 'relate|', '*');
                    $relate = explode('|', $relate);
                    //下面的.name是默认关联表的显示字段
                    $rows = $rows . '$form->select("' . $field . '", "' . Str::after(Str::before($comment, '*'), '注释=') . '")
                    ->options(' . $relate[1] . '::all()->pluck("name", "id"))
                    ->required();';
                    $this->info('表' . $tableName . '需要处理下控制器table中的关联');
                    continue;
                }
                $const = Str::contains($comment, 'attr|');
                if (!empty($const)) {
                    $rows = $rows . '$form->image("' . $obj->getName() . '", "' . Str::after(Str::before($comment, '*'), '注释=') . '")->required();';
                    continue;
                }

                $rows = $rows . '$form->text("' . $obj->getName() . '", "' . $chineseName . '")->required();';
            }
        }
        $tmp = 'protected function form()
                    {
                        $form = new Form(new ' . Str::camel($tableName) . '());
        ' . $rows . '
        return $form;
    }';
        return $tmp;
    }

    //生成admin controller的结尾部分
    protected function geneAdminControllerFooter()
    {
        return "}";
    }

    //写管理控制器文件
    protected function writeAdminControllerFile($tableName, $fileContent)
    {
        $path = app_path() . '/Admin/Controllers/' . Str::ucfirst(Str::camel($tableName)) . 'Controller.php';
        $this->info('写管理控制器文件' . $tableName);
        file_put_contents($path, $fileContent);
    }

//    --------------------生成菜单:要考虑已经有的情况------------------------------

    /**
     * 在指定文件指定行数后面插入内容
     * @param $file
     * @param $line
     * @param $txt
     * @return bool
     */
    protected function insert($file, $line, $txt)
    {
        if (!$fileContent = @file($file)) {
            // exit('文件不存在');
            $data['msg'] = 'htaccess:文件不存在';
            $data['status'] = 'error';
            echo json_encode($data);
            die;

        }
        $lines = count($fileContent);
        if ($line >= $lines) $line = $lines;
        $fileContent[$line] .= $txt;
        $newContent = '';
        foreach ($fileContent as $v) {
            $newContent .= $v;
        }
        if (!file_put_contents($file, $newContent)) {
            // exit('无法写入数据');
            $data['msg'] = 'htaccess:无法写入数据';
            $data['status'] = 'error';
            echo json_encode($data);
            die;

        }
        // echo '已经将' . $txt . '写入文档' . $file;
        return true;
    }
}
