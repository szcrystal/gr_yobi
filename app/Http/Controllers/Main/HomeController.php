<?php

namespace App\Http\Controllers\Main;

use App\Item;
use App\Category;
use App\CategorySecond;
use App\Tag;
use App\TagRelation;
use App\Fix;
use App\Setting;
use App\ItemImage;
use App\Favorite;
use App\ItemStockChange;
use App\TopSetting;
use App\DeliveryGroup;
use App\DeliveryGroupRelation;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Auth;
use Ctm;
use Cookie;
use Illuminate\Pagination\LengthAwarePaginator;

class HomeController extends Controller
{
    public function __construct(Item $item, Category $category, CategorySecond $cateSec, Tag $tag, TagRelation $tagRel, Fix $fix, Setting $setting, ItemImage $itemImg, Favorite $favorite, ItemStockChange $itemSc, TopSetting $topSet, DeliveryGroup $dg, DeliveryGroupRelation $dgRel, Auth $auth)
    {
        //$this->middleware('search');
        
        $this->item = $item;
        $this->category = $category;
        $this->cateSec = $cateSec;
        $this->tag = $tag;
        $this->tagRel = $tagRel;
        $this->fix = $fix;

        $this->setting = $setting;
        $this->itemImg = $itemImg;
        $this->favorite = $favorite;
        $this->itemSc = $itemSc;
        $this->topSet = $topSet;
        
        $this->dg = $dg;
        $this->dgRel = $dgRel;
                
        //ここでAuth::check()は効かない
        $this->whereArr = ['open_status'=>1, ['pot_type', '<', 3]]; //こことSingleとSearchとCtm::isPotParentAndStockにある
                
        $this->perPage = Ctm::isAgent('sp') ? 21 : 20;
        
        //$this->itemPerPage = 15;
    }
    
    public function index(Request $request)
    {
//        $request->session()->forget('item.data');
//        $request->session()->forget('all');

        $isLookfor = $request->is('lookfor') ? 1 : 0;

        $cates = $this->category->all();
        
        $whereArr = $this->whereArr;
        
/*
//        $tagIds = TagRelation::where('item_id', 1)->get()->map(function($obj){
//            return $obj->tag_id;
//        })->all();
//        
//        $strs = implode(',', $tagIds);
        
//        $placeholder = '';
//        foreach ($tagIds as $key => $value) {
//           $placeholder .= ($key == 0) ? $value : ','.$value;
//        }
//        //exit;
//        
//    //    $strs = "FIELD(id, $strs)";
//    //    echo $strs;
//        //exit;
//        
//        //->orderByRaw("FIELD(id, $sortIDs)"
//        $tags = Tag::whereIn('id', $tagIds)->orderByRaw("FIELD(id, $placeholder)")->take(2)->get();
//        print_r($tags);
//        exit;
        
//        $stateObj = null;
//        //$stateName = '';
//        
//        if(isset($state)) {
//            $stateObj = $this->state->where('slug', $state)->get()->first();
//            $whereArr['state_id'] = $stateObj->id;
//            $whereArrSec['state_id'] = $stateObj->id;
//            //$stateName = $stateObj->name;
//        }
*/
		//Carousel
        $caros = $this->itemImg->where(['item_id'=>9999, 'type'=>6])->inRandomOrder()->get();

		//FirstItem =================================================
        $saleItems = null;
        $newItems = null;
        $uekiItems = null;
        $rankItems = null;
        $cookieItems = null;
        
        $allRecoms = null;
        
        $getNum = Ctm::isAgent('sp') ? 3 : 4;
        
        if(! $isLookfor) {
            //SaleItem 全セール中の時でも各商品のセール指定が優先表示
            $saleItems['items'] = $this->item->where('sale_price', '>', 0)->where($whereArr)->orderBy('updated_at', 'desc')->get();
            $saleItems['type'] = 4;
            $saleItems['slug'] = 'sale-items';
            
            //New 新着情報
            $newItems = null;
            
            $scIds = $this->itemSc->orderBy('updated_at','desc')->get()->map(function($isc){
                return $isc->item_id;
            })->all();
            
            if(count($scIds) > 0) {
                $scIdStr = implode(',', $scIds);
                $newItems['items'] = $this->item->whereIn('id', $scIds)->where($whereArr)->where('stock', '>', 0)->orderByRaw("FIELD(id, $scIdStr)")->take($getNum)->get();
            }
            
            $newItems['type'] = 1;
            $newItems['slug'] = 'new-items';
        }
        
        
        // Ranking ueki/niwaki =====================
/*
//        $cateUekis = $this->cateSec->where('parent_id', 1)->get();
//        $cateSecSum = array();
//        
//        foreach($cateUekis as $cateUeki) {
//        	$cateSecSum[$cateUeki->id] = $this->item->where('subcate_id', $cateUeki->id)->sum('sale_count'); 
//        }
//        
//        arsort($cateSecSum); //降順Sort
//		$cateSecSum = array_keys($cateSecSum);
//        
//        $cateSecIds = implode(',', $cateSecSum);
*/
        
        //$uekiSecObj = Ctm::getUekiSecObj(); //get()で返る
        //$uekiItems['items'] = Ctm::getUekiSecObj()->take($getNum); //get()で返る 元々->all()を付けていたが不要
        $uekiItems['items'] = Ctm::getUekiSecObj2()->take($getNum); //get()で返る 元々->all()を付けていたが不要
        
        $uekiItems['type'] = 5;
        $uekiItems['slug'] = 'ranking-ueki';
        
        
        //Ranking Other =====================
        //$rankItems['items'] = Ctm::getRankObj()->take($getNum);
        $rankItems['items'] = Ctm::getRankObj2()->take($getNum);
        
        //$rankItems['items'] = array();
        //$rankItems['items'] = $this->item->where($whereArr)->orderBy('sale_count', 'desc')->take($getNum)->get()->all();
        $rankItems['type'] = 2;
        $rankItems['slug'] = 'ranking';
        
        
        //Recent 最近見た 最近チェックした =====================
        $cookieArr = array();
        //$getNum = Ctm::isAgent('sp') ? 6 : 7;
        $a = array('a', 'b', 'c', 123);
        
        Cookie::queue(Cookie::make('item_ids', '', config('app.cookie_time') ));
        
        $cookieIds = Cookie::get('item_ids');
        print_r($cookieIds);
        print_r($a);
        exit;
        
        if(isset($cookieIds) && $cookieIds != '') {
            $cookieArr = explode(',', $cookieIds); //orderByRowに渡すものはString
          	$cookieItems['items'] = $this->item->whereIn('id', $cookieArr)->where($whereArr)->orderByRaw("FIELD(id, $cookieIds)")->take($getNum)->get();
        }
        
        $cookieItems['type'] = 3; 
        $cookieItems['slug'] = 'recent-items';
        
        //FirstItem END =========================
        
        /*
        if(cache()->has('item_ids')) {
        	
        	$cacheIds = cache('item_ids');
            
            $caches = implode(',', $cacheIds); //orderByRowに渡すものはString
            
          	$cacheItems = $this->item->whereIn('id', $cacheIds)->where($whereArr)->orderByRaw("FIELD(id, $caches)")->take($getNum)->get()->all();  
        }
        */
        
        //array
        if($isLookfor) {
            $firstItems = [
                '最近チェックしたアイテム'=> $cookieItems, //type:3
                '人気ランキング(植木・庭木)'=> $uekiItems, //type:5
                '人気ランキング(その他)'=> $rankItems, //type:2
            ];
        }
        else {
            $firstItems = [
                'SALE !!'=> $saleItems, //type:4
                '人気ランキング(植木・庭木)'=> $uekiItems, //type:5
                '人気ランキング(その他)'=> $rankItems, //type:2
                '新着情報'=> $newItems, //type:1
                '最近チェックしたアイテム'=> $cookieItems, //type:3
            ];
        }
        //FirstItem END ================================
        
        
        //おすすめ情報 RecommendInfo (cate & cateSecond & tag)
        if(! $isLookfor) {
            $tagRecoms = $this->tag->where(['is_top'=>1])->orderBy('updated_at', 'desc')->get()->all();
            $cateRecoms = $this->category->where(['is_top'=>1])->orderBy('updated_at', 'desc')->get()->all();
            $subCateRecoms = $this->cateSec->where(['is_top'=>1])->orderBy('updated_at', 'desc')->get()->all();
            
            $res = array_merge($tagRecoms, $cateRecoms, $subCateRecoms);
            
            $collection = collect($res);
            $allRecoms = $collection->sortByDesc('updated_at')->take(20);
        }
//        print_r($allRecoms);
//        exit;

        //$allRecoms = $this->item->where($whereArr)->orderBy('created_at', 'desc')->take(10)->get(); 

		//category =>現在未使用のはず
        $itemCates = array();
        /*
        foreach($cates as $cate) { //カテゴリー名をkeyとしてatclのかたまりを配列に入れる
        
            $whereArr['cate_id'] = $cate->id;
            
            $as = $this->item->where($whereArr)->orderBy('created_at','DESC')->take(8)->get()->all();
            
            if(count($as) > 0) {
                $itemCates[$cate->id] = $as;
            }
        }
        */
        
//        $items = $this->item->where(['open_status'=>1])->orderBy('created_at','DESC')->get();
//        $items = $items->groupBy('cate_id')->toArray();

        //人気タグ
        $tagGetCount = 30;
        $popTagsFirst = $this->tag->orderBy('view_count', 'desc')->take($tagGetCount)->get();
        $popTagsSecond = $this->tag->orderBy('view_count', 'desc')->get()->slice($tagGetCount);

		//head news
        $setting = $this->topSet->get()->first();
        
		$newsCont = $setting->contents;
		
        $metaTitle = $setting->meta_title;
        $metaDesc = $setting->meta_description;
        $metaKeyword = $setting->meta_keyword;
        
        //For this is top
        if($isLookfor) {
            $isTop = 0;
            //$view = 'main.home.index';
        }
        else {
            $isTop = 1;
            //$view = 'main.home.index';
        }
        

        return view('main.home.index', ['firstItems'=>$firstItems, 'allRecoms'=>$allRecoms, 'itemCates'=>$itemCates, 'cates'=>$cates, 'newsCont'=>$newsCont, 'popTagsFirst'=>$popTagsFirst, 'popTagsSecond'=>$popTagsSecond, 'metaTitle'=>$metaTitle, 'caros'=>$caros, 'metaDesc'=>$metaDesc, 'metaKeyword'=>$metaKeyword, 'isTop'=>$isTop, 'isLookfor'=>$isLookfor]);
    }
        
    
    
    //NewItem Ranking RecentCheck
    public function uniqueArchive(Request $request)
    {
    	$path = $request->path();
        
        $whereArr = $this->whereArr;
        
        $items = null;
        
        $orgItem = null;
        $title = '';
        
        $type = 'unique';
        
        if($path == 'sale-items') {
            $items = $this->item->where($whereArr)->where('sale_price', '>', 0)->orderBy('stock', 'desc')->orderBy('updated_at', 'desc')->paginate($this->perPage);
            
//        	$itemObjs = $this->item->where('sale_price', '>', 0)->where($whereArr)->orderBy('updated_at', 'desc')->get();
//
//            $stockIds = $this->getStockSepIds($itemObjs); //$itemObjsはコレクション
//            $strs = implode(',', $stockIds); //$strs = '"'. implode('","', $stockIds) .'"';
//            $items = $this->item->whereIn('id', $stockIds)->orderByRaw("FIELD(id, $strs)")->paginate($this->perPage);
            
            $title = 'Sale商品';
            
        }
        elseif($path == 'new-items') {
        
            //$scs = $this->itemSc->orderBy('updated_at','desc')/*->take(100)*/->get();            
            $scIds = $this->itemSc->orderBy('updated_at','desc')->get()->map(function($obj) {
            	return $obj->item_id;
            })->all();
            
            //$scIds = array();
            //$n = 0;
            
//            foreach($scs as $sc) {
//                //if($n > 99) break;
//                
//                $whereArr['id'] = $sc->item_id;                
//                $i = $this->item->where($whereArr)->where('stock', '>', 0)->get();
//                if($i->isNotEmpty()) {
//                    $scIds[] = $sc->item_id;
//                    $n++;
//                }
//            }
            
            if(count($scIds) > 0) {
            	//$scIds = array_slice($scIds, 0, 100);
                $scIdStr = implode(',', $scIds);
                $items = $this->item->whereIn('id', $scIds)->where($whereArr)->where('stock', '>', 0)->orderByRaw("FIELD(id, $scIdStr)")->take(100)->get()->all()/*->paginate($this->perPage)*/; //paginateにtake()が効かない
                $items = Ctm::customPaginate($items, $this->perPage, $request);
            }
            
            $title = '新着情報';
        }
        elseif($path == 'ranking') {
//        	$rankItemIds = $this->item->where($whereArr)->orderBy('sale_count', 'desc')->limit(100)->get()->map(function($obj){ //paginateとlimitが併用できないのでこのやり方になる
//            	return $obj->id;
//            })->all();
//            
//            $items= $this->item->whereIn('id', $rankItemIds)->orderBy('sale_count', 'desc')->paginate($this->perPage); //ここで更にorderByする必要がある
            
            /*ORG =======
            $items = $this->item->where($whereArr)->orderBy('sale_count', 'desc')->take(100)->get()->all();
            ============ */
            $items = Ctm::getRankObj2()->take(100)->all();
            $items = Ctm::customPaginate($items, $this->perPage, $request);
            
            $title = '人気ランキング(その他)';
        }
        elseif($path == 'ranking-ueki') {
            $cateSecs= Ctm::getUekiSecObj2()->all();
            
            $items = Ctm::customPaginate($cateSecs, $this->perPage, $request);
            
//            //配列を1ページに表示する件数分分割する
//        	$displayData = array_chunk($cateSecs, $this->perPage);
//
//            //ページがnullの場合は1を設定
//            $currentPageNo = $request->query('page');
//            
//            if (is_null($currentPageNo)) {
//                $currentPageNo = 1;
//            }
//
//         	$items = new LengthAwarePaginator(
//                $displayData[$currentPageNo-1], //該当ページに表示するデータ
//                count($cateSecs), //全件数
//                $this->perPage, //1ページに表示する数
//                $currentPageNo, //現在のページ番号
//                ['path' => $path] //URLをオプションとして設定
//            );
            
            $type = $type . '-ueki';
            $title = '人気ランキング(植木・庭木)';
        }
        elseif($path == 'recent-items') {
        	$cookieArr = array();
            
            $cookieIds = Cookie::get('item_ids');
        
        	if(isset($cookieIds) && $cookieIds != '') {
                $cookieArr = explode(',', $cookieIds); //orderByRowに渡すものはString
                $items = $this->item->whereIn('id', $cookieArr)->where($whereArr)->orderByRaw("FIELD(id, $cookieIds)")->paginate($this->perPage);  
            }
            
            /*
            if(cache()->has('item_ids')) {
                $cacheIds = cache('item_ids');
                $caches = implode(',', $cacheIds); //orderByRowに渡すものはString
                $items = $this->item->whereIn('id', $cacheIds)->where($whereArr)->orderByRaw("FIELD(id, $caches)")->paginate($this->perPage);  
            }
            */
            
            $title = '最近チェックしたアイテム';               
        }
        
        elseif($path == 'item/packing') { //同梱包可能商品レコメンド -> 同じ出荷元で同梱包可能なもの の一覧用
        	
            $orgId = $request->query('orgId');
            $orgItem = $this->item->find($orgId);
            
            if(isset($orgId) && isset($orgItem) && $orgItem->is_once) {
            	$whereArr = array_merge($whereArr, ['consignor_id'=>$orgItem->consignor_id, 'is_once'=>1, 'is_once_recom'=>0]);
            
                $items = $this->item->whereNotIn('id', [$orgId])->where($whereArr)->orderBy('stock', 'desc')->orderBy('updated_at', 'desc')->paginate($this->perPage);

                //ページネーションのリンクにqueryをつける
                $items->appends(['orgId' => $orgId]);
                                
                $title = '"' . $orgItem->title . '" ' . 'と同梱包可能な商品';
            }
            else {
               abort(404); 
            }
 
        }
        
        $metaTitle = $title . '｜植木買うならグリーンロケット';
        $metaDesc = '';
        $metaKeyword = '';
        
        if(! isset($items)) {
        	abort(404);
        }
        
        return view('main.archive.index', ['items'=>$items, 'type'=>$type, 'title'=>$title, 'metaTitle'=>$metaTitle, 'metaDesc'=>$metaDesc, 'metaKeyword'=>$metaKeyword, 'orgItem'=>$orgItem]);
 
    }
    
    
    
    //RecommendInfo : Cate/SubCate/Tag
    public function recomInfo(Request $request)
    {
    	$items = null;
        
        $path = $request->path();
        
    	if($path == 'recommend-info') {

        	$tagRecoms = $this->tag->where(['is_top'=>1])->orderBy('updated_at', 'desc')->get()->all();
            $cateRecoms = $this->category->where(['is_top'=>1])->orderBy('updated_at', 'desc')->get()->all();
            $subCateRecoms = $this->cateSec->where(['is_top'=>1])->orderBy('updated_at', 'desc')->get()->all();
            
            //$concat = $tagRecoms->concat($cateRecoms)->concat($cateRecoms);
            
//            $aaa = $tagRecoms->merge($cateRecoms);
//            $b = $aaa->paginate($this->perPage);
            
            
            $res = array_merge($tagRecoms, $cateRecoms, $subCateRecoms);
            
            $collection = collect($res);
            $sorts = $collection->sortByDesc('updated_at')->toArray();
            
            //Custom Pagination
            $perPage = $this->perPage;
            $total = count($sorts);
            $chunked = array();
            
            if($total) {
                $chunked = array_chunk($sorts, $perPage);
                $current_page = $request->page ? $request->page : 1;
                $chunked = $chunked[$current_page - 1]; //現在のページに該当する配列を$chunkedに入れる
            }
            
            $items = new LengthAwarePaginator($chunked, $total, $perPage); //pagination インスタンス作成
            $items -> setPath($path); //url pathセット
            //$allResults -> appends(['s' => $search]); //get url set
            //Custom pagination END
            
//            print_r($items);
//            exit;
            
            $title = 'おすすめ情報';
        }
        
        $metaTitle = $title . '｜植木買うならグリーンロケット';
        $metaDesc = '';
        $metaKeyword = '';
        
        return view('main.archive.recomInfo', ['items'=>$items, 'type'=>'unique', 'title'=>$title, 'metaTitle'=>$metaTitle, 'metaDesc'=>$metaDesc, 'metaKeyword'=>$metaKeyword,]);
    }
    
    
    
    
	//FIx Page =====================
    public function getFix(Request $request)
    {
        $path = $request->path();
        $fix = $this->fix->where('slug', $path)->first();
        
        if(!isset($fix)) {
            abort(404);
        }
        
        
        $title = $fix->title;
        $type = 'fix';
        
        $metaTitle = isset($fix->meta_title) ? $fix->meta_title : $title . '｜植木買うならグリーンロケット';
//        $metaDesc = $item->meta_description;
//        $metaKeyword = $item->meta_keyword;
        
        return view('main.home.fix', ['fix'=>$fix, 'metaTitle'=>$metaTitle, 'title'=>$title, 'type'=>$type]);
    }
    
    //Category ==============================
    //Parent
    public function category($slug)
    {
    	$cate = $this->category->where('slug', $slug)->first();
        
        $whereArr = $this->whereArr;
        
        if(!isset($cate)) {
            abort(404);
        }
        
        $items = $this->item->where($whereArr)->where(['cate_id'=>$cate->id])->orderBy('stock', 'desc')->orderBy('updated_at', 'desc')->paginate($this->perPage);
        
        /*
        $itemObjs = $this->item->where($whereArr)->where(['cate_id'=>$cate->id])->get();
        
        //在庫有りなしでソートしたidを取得
        $stockIds = $this->getStockSepIds($itemObjs); //$itemObjsはコレクション
        
        //Controller内でないと下記のダブルクオーテーションで囲まないと効かない(tag.blade.phpに記載あり)
        $strs = implode(',', $stockIds); //$strs = '"'. implode('","', $stockIds) .'"';
        
        $items = $this->item->whereIn('id', $stockIds)->orderByRaw("FIELD(id, $strs)")->paginate($this->perPage);
        */
//        if(Ctm::isAgent('sp'))
//            $items = $items->simplePaginate($this->perPage);
//        else
//            $items = $items->paginate($this->perPage);
        
        //$items = $this->item->where(['cate_id'=>$cate->id, 'open_status'=>1, 'is_potset'=>0])->orderBy('id', 'desc')->paginate($this->perPage);
        //$items = $this->cateSec->where(['parent_id'=>$cate->id, ])->orderBy('updated_at', 'desc')->paginate($this->perPage);
        
        //Upper取得
        $uppers = Ctm::getUpperArr($cate->id, 'cate');
        $upperMore = $uppers['isMore'];
        $upperRelArr = $uppers['contents'];
        
        //Meta
        $metaTitle = isset($cate->meta_title) ? $cate->meta_title : $cate->name . '｜植木買うならグリーンロケット';
        $metaDesc = $cate->meta_description;
        $metaKeyword = $cate->meta_keyword;
        
        $cate->timestamps = false;
        $cate->increment('view_count');
        
        return view('main.archive.index', ['items'=>$items, 'cate'=>$cate, 'type'=>'category', 'upperMore'=>$upperMore, 'upperRelArr'=>$upperRelArr, 'metaTitle'=>$metaTitle, 'metaDesc'=>$metaDesc, 'metaKeyword'=>$metaKeyword,]);
    }
    
    
    //Sub Category Child
    public function subCategory($slug, $subSlug)
    {
    	$cate = $this->category->where('slug', $slug)->first();
        $subcate = $this->cateSec->where('slug', $subSlug)->first();
        
        $whereArr = $this->whereArr;
        
        if(!isset($cate) || !isset($subcate)) {
            abort(404);
        }
        
        $items = $this->item->where($whereArr)->where(['subcate_id'=>$subcate->id])->orderBy('stock', 'desc')->orderBy('updated_at', 'desc')->paginate($this->perPage);
        
        /*
        $itemObjs = $this->item->where($whereArr)->where(['subcate_id'=>$subcate->id])->get();
        
        //在庫有りなしでソートしたidを取得
        $stockIds = $this->getStockSepIds($itemObjs); //$itemObjsはコレクション

        //Controller内でないと下記のダブルクオーテーションで囲まないと効かない(tag.blade.phpに記載あり)
        $strs = implode(',', $stockIds); //$strs = '"'. implode('","', $stockIds) .'"';

        $items = $this->item->whereIn('id', $stockIds)->orderByRaw("FIELD(id, $strs)")->paginate($this->perPage);
        //$items = $this->item->where(['subcate_id'=>$subcate->id, 'open_status'=>1, 'is_potset'=>0])->orderBy('id', 'desc')->paginate($this->perPage);
        */
        
        //Upper取得
        $uppers = Ctm::getUpperArr($subcate->id, 'subcate');
        $upperMore = $uppers['isMore'];
        $upperRelArr = $uppers['contents'];
        
        //Meta
        $metaTitle = isset($subcate->meta_title) ? $subcate->meta_title : $subcate->name . '｜植木買うならグリーンロケット';
        $metaDesc = $subcate->meta_description;
        $metaKeyword = $subcate->meta_keyword;
        
        $subcate->timestamps = false;
        $subcate->increment('view_count');
        
        return view('main.archive.index', ['items'=>$items, 'cate'=>$cate, 'subcate'=>$subcate, 'type'=>'subcategory', 'upperMore'=>$upperMore, 'upperRelArr'=>$upperRelArr, 'metaTitle'=>$metaTitle, 'metaDesc'=>$metaDesc, 'metaKeyword'=>$metaKeyword,]);
    }
    
    
    //Tag
    public function tag($slug)
    {
    	$tag = $this->tag->where('slug', $slug)->first();
        
        $whereArr = $this->whereArr;
        
        if(!isset($tag)) {
            abort(404);
        }
        
        $tagItemIds = $this->tagRel->where('tag_id',$tag->id)->get()->map(function($obj){
        	return $obj -> item_id;
        })->all();
        
        $items = $this->item->where($whereArr)->whereIn('id', $tagItemIds)->orderBy('stock', 'desc')->orderBy('updated_at', 'desc')->paginate($this->perPage);
        
        /*
        $itemObjs = $this->item->whereIn('id', $tagItemIds)->where($whereArr)->get();
        
        //在庫有りなしでソートしたidを取得
        $stockIds = $this->getStockSepIds($itemObjs); //$itemObjsはコレクション

        //orderByRaw用の文字列
        $strs = implode(',', $stockIds); //$strs = '"'. implode('","', $stockIds) .'"';

        $items = $this->item->whereIn('id', $stockIds)->orderByRaw("FIELD(id, $strs)")->paginate($this->perPage);
        //$items = $this->item->whereIn('id',$itemIds)->where(['open_status'=>1, 'is_potset'=>0])->orderBy('id', 'desc')->paginate($this->perPage);
        */
        
        //Upper取得
        $uppers = Ctm::getUpperArr($tag->id, 'tag');
        $upperMore = $uppers['isMore'];
        $upperRelArr = $uppers['contents'];
        
        $metaTitle = isset($tag->meta_title) ? $tag->meta_title : $tag->name . '｜植木買うならグリーンロケット';
        $metaDesc = $tag->meta_description;
        $metaKeyword = $tag->meta_keyword;
        
        $tag->timestamps = false;
        $tag->increment('view_count');
        
        return view('main.archive.index', ['items'=>$items, 'tag'=>$tag, 'type'=>'tag', 'upperMore'=>$upperMore, 'upperRelArr'=>$upperRelArr, 'metaTitle'=>$metaTitle, 'metaDesc'=>$metaDesc, 'metaKeyword'=>$metaKeyword,]);
    }
    
    
    //在庫の有無で区分けする（在庫なしを後ろに回す）関数 => 現在未使用
    private function getStockSepIds($itemObjs)
    {
        //ここで渡される$itemObjsはコレクションなので、以下記述がいつもと異なることに注意
        //$itemObjs = $itemObjs->where('open_status', 1)->where('is_potset', 0);とすることも可能
        
        //ORG : $whereArr = ['open_status'=>1, 'is_potset'=>0];
        //$stockTrues = $this->item->where($whereArr)->whereNotIn('stock', [0])->orderBy('id', 'desc')->get()->map(function($obj){
                
        $stockTrues = $itemObjs->whereNotIn('stock', [0])->sortByDesc('updated_at')->map(function($obj){ //Desc 降順 3,2,1
        	return $obj->id;
        })->all();
                
        $stockFalses = $itemObjs->where('stock', 0)->sortByDesc('updated_at')->map(function($objSec){ //Desc 降順 3,2,1
        	return $objSec->id;
        })->all();
        
        $stockIds = array_merge($stockTrues, $stockFalses);
        $potsStockFalses = array();
        
        return $stockIds;
        
        //以下、未使用 ========================================
        //pot親の時にpotの子のstockを見る
        foreach($stockIds as $stockId) {
        	$switchArr = Ctm::isPotParentAndStock($stockId); //親ポットか、Stockあるか、その子ポットのObjsを取る
            
            //親ポットで子ポットの在庫が全て0の時
            if($switchArr['isPotParent']) { 
                if(! $switchArr['isStock']) { //子ポット在庫が全て0の時
                    $potsStockFalses[] = $stockId;
                }
                else { // 子ポットに在庫があっても親に在庫0が指定されている時。本来0が入力されてはならない。現在の0を1に修正すればこの部分は不要
                    if(! $this->item->find($stockId)->stock) {
                		$stockTrues[] = $stockId;
                        rsort($stockTrues);
                        
                        //ここで重複があっても最後にwhereInする時に省かれるので影響しないが一応重複IDを削除しておく
                        $stockFalses = array_diff($stockFalses, $stockTrues); //stockFalseから重複IDを削除
                    }
                }
        	}
/*        	
//            $pots = $this->item->where(['is_potset'=>1, 'pot_parent_id'=>$stockId])->get();
//            
//            if($pots->isNotEmpty()) {
//            	$switch = 0;
//            	foreach($pots as $pot) {
//                	if($pot->stock) {
//                    	$switch = 1;
//                    	break;
//                    }
//                }
//                
//                if(! $switch) {
//                	$potsStockFalses[] = $stockId;
//                }
//            }
*/
        }
        
        
        //potSetの親はstockTrue,stockFalseどちらにも入る可能性があるので両方から重複idを取り除く
        $stockTrues = array_diff($stockTrues, $potsStockFalses); //stockTrueから重複IDを削除
        $stockFalses = array_diff($stockFalses, $potsStockFalses); //stockFalseから重複IDを削除 親ポットの在庫は1に固定したので$stockFalsesに親ポットが入ることはないのでここは不要かも
        
        $stockFalses = array_merge($stockFalses, $potsStockFalses); //stockFalseにmergeして、その中でsortする
        //rsort($stockFalses); //降順 3,2,1,
        
        $stockFalsesLast = $this->item->whereIn('id', $stockFalses)->orderBy('updated_at', 'desc')->get()->map(function($obj){
        	return $obj->id;
        })->all();
        
        
        //return ['stockTrue'=>$stockTrues, 'stockFalse'=>$stockFalses];
        return array_merge($stockTrues, $stockFalsesLast); //重複を削除したstockTrueとstockFalseをmergeする
        
    }
    
    
    //送料区分別 送料表のページ
    public function showDeliFeeTable($dgId)
    {
    	$dg = $this->dg->find($dgId);
        
        if(! isset($dg) || $dg->table_name == '' ) {
            abort(404);
        }
        
    	$dgRel = $this->dgRel->where('dg_id', $dgId)->get();
        
        return view('main.home.deliFee', ['dg'=>$dg, 'dgRel'=>$dgRel, 'dgId'=>$dgId ]);
        
    }
    
    
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
