<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Encore\Admin\Traits\ModelTree;
use Illuminate\Database\Eloquent\SoftDeletes;

class articleCategory extends Model{
    use SoftDeletes;
    use ModelTree;

    protected $table="article_category";
    protected $fillable=[];
    protected $guarded=[];
    protected $appends=[];
    protected $hidden=[];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setParentColumn('pid');
        $this->setOrderColumn('sort');
        $this->setTitleColumn('title');
    }

    /*首页列表*/
    public function indexList($limit){
        $list = self::where('status',1)->orderBy('sort','asc')
            ->orderBy('created_at','desc')
            ->select('id','title')
            ->get()->toArray();
        if($list){
           foreach ($list as $key=>$val){
               $artList = article::getArticleByCid($val['id'],2);
               $list[$key]['list'] = $artList;
           }
        }
        return $list;
    }

    /*获取分类名称*/
    public function getCatName($cid){
        return self::where('id',$cid)->value('title');
    }
}
