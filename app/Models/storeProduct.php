<?php

namespace App\Models;

use Encore\Admin\Traits\DefaultDatetimeFormat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Config;

class storeProduct extends Model
{
    use SoftDeletes;
    use DefaultDatetimeFormat;
    protected $table = "store_product";
    protected $fillable = [];
    protected $guarded = [];
    protected $appends = [];
    protected $hidden = [];

    public function setSliderImageAttribute($sliderImage)
    {
        if (is_array($sliderImage)) {
            $this->attributes['slider_image'] = json_encode($sliderImage);
        }
    }
    public function getSliderImageAttribute($sliderImage)
    {
        return json_decode($sliderImage, true);
    }
    public function getPostageGradientAttribute($value)
    {
        return array_values(json_decode($value, true) ?: []);
    }

    public function setPostageGradientAttribute($value)
    {
        $this->attributes['postage_gradient'] = json_encode(array_values($value));
    }
    /**
     * 获取产品 By ID
     * @param $mid
     * @param $id
     * @return mixed
     */
    public function getProById($id,$field=['id','product_name','image','stock','price','ot_price']){
        $model = self::validWhere();
        $row = $model->where('id',$id)
            ->select($field)
            ->lockForUpdate()
            ->first();
        if($row){
            $row['image'] =  Config::get('app.oss_cdn').$row['image'];
        }
        return $row?$row:[];
    }
    /**
     * 获取首页区块产品
     * @param $mid
     * @return mixed
     */
    public function getHomeProductList($field,$limit){
        $model = self::validWhere();
        $list = $model->where($field,1)
            //->where('type',1)
            ->orderBy('sort','asc')->orderBy('created_at','desc')
            ->select('id','product_name','image','price','ot_price','sales','type','cover_image','good_sku','asso_service_id')
            ->limit($limit)->get();
        if($list){
            $list->each(function ($item, $key)use($field) {
                if($item['cover_image'] && ($field == 'is_diy' || $field == 'is_sample')){
                    $item['image'] =  Config::get('app.oss_cdn').$item['cover_image'];
                }else{
                    $item['image'] = Config::get('app.oss_cdn').$item['image'];
                }
                $goodsku = json_decode($item['good_sku'],true);
                $asso_service_id = $item['asso_service_id'];
                if($goodsku['type'] == 'many'){
                    foreach ($goodsku['sku'] as $sk=> &$sval){
                        $sval['asso_service_info'] = [];
                        if(isset($sval['asso_service_id']) && $sval['asso_service_id'] >0){
                            $asso_service_id = $sval['asso_service_id'];
                            $sval['asso_service_info'] = storeProduct::getProById($sval['asso_service_id']);
                        }
                    }
                }
                $item['good_sku'] = $goodsku;
                /*服务包*/
                $item['asso_service_id'] = $asso_service_id;
                $item['asso_service_info'] = [];
                if($asso_service_id && $asso_service_id > 0){
                    $item['asso_service_info'] = storeProduct::getProById($asso_service_id);
                }
            });
        }
        $list=count($list) ? $list->toArray() : [];
        return $list;
    }
    /**
     * 根据不同模块获取列表
     * @param $mid
     * @return mixed
     */
    public function getListByModule($module,$order,$dir){
        $model = self::validWhere();
        $field = 'is_hot';
        if($module == 2){
            $field = 'is_discount';
        }elseif ($module == 3){
            $field = 'is_diy';
        }elseif ($module == 4){
            $field = 'is_sample';
        }
        if($order == 'complex'){
            $list = $model->where($field,1)
                ->orderBy('sales',$dir)
                ->orderBy('price',$dir)
                ->orderBy('created_at',$dir)
                ->select('id','product_name','image','price','ot_price','sales','type','good_sku','asso_service_id')
                ->paginate(15);
        }elseif ($order == 'sales'){
            $list = $model->where($field,1)
                ->orderBy('sales',$dir)
                ->orderBy('created_at','desc')
                ->select('id','product_name','image','price','ot_price','sales','type','good_sku','asso_service_id')
                ->paginate(15);
        }elseif ($order == 'price'){
            $list = $model->where($field,1)
                ->orderBy('price',$dir)
                ->orderBy('created_at','desc')
                ->select('id','product_name','image','price','ot_price','sales','type','good_sku','asso_service_id')
                ->paginate(15);
        }
        if($list){
            $list->each(function ($item, $key) {
                $goodsku = json_decode($item['good_sku'],true);
                $asso_service_id = $item['asso_service_id'];
                if($goodsku['type'] == 'many'){
                    foreach ($goodsku['sku'] as $sk=> &$sval){
                        $sval['asso_service_info'] = [];
                        if(isset($sval['asso_service_id']) && $sval['asso_service_id'] >0){
                            $asso_service_id = $sval['asso_service_id'];
                            $sval['asso_service_info'] = storeProduct::getProById($sval['asso_service_id']);
                        }
                    }
                }
                $item['good_sku'] = $goodsku;
                $item['image'] = Config::get('app.oss_cdn').$item['image'];
                /*服务包*/
                $item['asso_service_id'] = $asso_service_id;
                $item['asso_service_info'] = [];
                if($asso_service_id && $asso_service_id > 0){
                    $item['asso_service_info'] = storeProduct::getProById($asso_service_id);
                }
            });
        }
        return $list;
    }
    /**
     * 双十一优惠传去列表
     * @param $mid
     * @return mixed
     */
    public function getListByCoupon($ids,$order,$dir){
        $model = self::validWhere();
        $field = 'is_hot';
        if($ids){
            $model = $model->whereIn('id',$ids);
        }

        if($order == 'complex'){
            $list = $model->orderBy('sales',$dir)
                ->orderBy('price',$dir)
                ->orderBy('created_at',$dir)
                ->select('id','product_name','image','price','ot_price','sales','type','good_sku','asso_service_id')
                ->paginate(15);
        }elseif ($order == 'sales'){
            $list = $model->orderBy('sales',$dir)
                ->orderBy('created_at','desc')
                ->select('id','product_name','image','price','ot_price','sales','type','good_sku','asso_service_id')
                ->paginate(15);
        }elseif ($order == 'price'){
            $list = $model->orderBy('price',$dir)
                ->orderBy('created_at','desc')
                ->select('id','product_name','image','price','ot_price','sales','type','good_sku','asso_service_id')
                ->paginate(15);
        }
        if($list){
            $list->each(function ($item, $key) {
                $goodsku = json_decode($item['good_sku'],true);
                $asso_service_id = $item['asso_service_id'];
                if($goodsku['type'] == 'many'){
                    foreach ($goodsku['sku'] as $sk=> &$sval){
                        $sval['asso_service_info'] = [];
                        if(isset($sval['asso_service_id']) && $sval['asso_service_id'] >0){
                            $asso_service_id = $sval['asso_service_id'];
                            $sval['asso_service_info'] = storeProduct::getProById($sval['asso_service_id']);
                        }
                    }
                }
                $item['good_sku'] = $goodsku;
                $item['image'] = Config::get('app.oss_cdn').$item['image'];
                /*服务包*/
                $item['asso_service_id'] = $asso_service_id;
                $item['asso_service_info'] = [];
                if($asso_service_id && $asso_service_id > 0){
                    $item['asso_service_info'] = storeProduct::getProById($asso_service_id);
                }
            });
        }
        return $list;
    }
    /**
     * 根据分类获取列表
     * @param $mid
     * @return mixed
     */
    public function getListByCatId($catid,$limit=15){
        $model = self::validWhere();
        $list = $model->where('cate_id',$catid)
            ->orderBy('sort','asc')->orderBy('created_at','desc')
            ->select('id','product_name','image','price','ot_price','sales','type')
            ->paginate($limit);
        if($list){
            $list->each(function ($item, $key) {
                $item['image'] = Config::get('app.oss_cdn').$item['image'];
            });
        }
        return $list;
    }

    /**
     * 根据关键字搜索商品
     * @param $mid
     * @return mixed
     */
    public function getListByKeyword($keyword,$limit=15){
        $model = self::validWhere();
        $list = $model->where('product_name', 'like',"%$keyword%")
            ->orderBy('sort','asc')->orderBy('created_at','desc')
            ->select('id','product_name','image','price','ot_price','sales','type','good_sku','asso_service_id')
            ->paginate($limit);
        if($list){
            $list->each(function ($item, $key) {
                $goodsku = json_decode($item['good_sku'],true);
                $asso_service_id = $item['asso_service_id'];
                if($goodsku['type'] == 'many'){
                    foreach ($goodsku['sku'] as $sk=> &$sval){
                        $sval['asso_service_info'] = [];
                        if(isset($sval['asso_service_id']) && $sval['asso_service_id'] >0){
                            $asso_service_id = $sval['asso_service_id'];
                            $sval['asso_service_info'] = storeProduct::getProById($sval['asso_service_id']);
                        }
                    }
                }
                $item['good_sku'] = $goodsku;
                $item['image'] = Config::get('app.oss_cdn').$item['image'];
                /*服务包*/
                $item['asso_service_id'] = $asso_service_id;
                $item['asso_service_info'] = [];
                if($asso_service_id && $asso_service_id > 0){
                    $item['asso_service_info'] = storeProduct::getProById($asso_service_id);
                }
            });
        }
        return $list;
    }
    /**
     * 所有产品数
     * @param $mid
     * @return mixed
     */
    public function getCount(){
        $model = self::validWhere();
        $count = $model->count();
        return $count;
    }

    public function getProductStock($proid,$attrid){
        $proinfo = self::getProById($proid,['stock','good_sku']);
        if($attrid>0){
            $attrs = $proinfo->good_sku;
            if (!is_array($attrs) && $attrs) {
                $attrs = json_decode($attrs, true);
            }
            if (isset($attrs) && is_array($attrs) && !empty($attrs)) {
                foreach ($attrs['sku'] as $key => $value) {
                    if ($value['id'] == $attrid && isset($value['stock'])) {
                        return $value['stock'];
                    }
                }
            }
        }else{
            return $proinfo->stock;
        }
    }
    public static function validWhere()
    {
        $time = date('Y-m-d H:i:s',time());
        return self::where('is_show',1)
            ->where('sales_start_time','<=',$time)
            ->where('sales_end_time','>',$time);
    }
    public static function validDateWhere()
    {
        $time = date('Y-m-d H:i:s',time());
        return self::where('sales_start_time','<=',$time)
            ->where('sales_end_time','>',$time);
    }
}
