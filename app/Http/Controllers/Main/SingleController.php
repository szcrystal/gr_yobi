<?php

namespace App\Http\Controllers\Main;

use App\Item;
use App\Category;
use App\CategorySecond;
use App\Tag;
use App\TagRelation;
use App\ItemImage;
use App\Favorite;
use App\User;
use App\FavoriteCookie;
use App\Post;
use App\PostRelation;
use App\PostTagRelation;
use App\ItemContent;

use App\ItemUpper;
use App\ItemUpperRelation;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;

use Auth;
use Ctm;
use Cookie;
use DateTime;

class SingleController extends Controller
{
    public function __construct(Item $item, Category $category, CategorySecond $subCate, Tag $tag, TagRelation $tagRel, ItemImage $itemImg, Favorite $favorite, User $user, ItemUpper $itemUpper, ItemUpperRelation $itemUpperRel, FavoriteCookie $favCookie, Post $post, PostRelation $postRel, PostTagRelation $postTagRel, ItemContent $itemCont)
    {
        //$this->middleware('search');
        
        $this->item = $item;
        $this->category = $category;
        $this->subCate = $subCate;
        $this->tag = $tag;
        $this->tagRel = $tagRel;
        $this->itemImg = $itemImg;
        $this->favorite = $favorite;
        $this->user = $user;
        
        $this->upper = $itemUpper;
        $this->upperRel = $itemUpperRel;
        $this->favCookie = $favCookie;
        
        $this->post = $post;
        $this->postRel = $postRel;
        $this->postTagRel = $postTagRel;
        $this->itemCont = $itemCont;
        
//        $this->tag = $tag;
//        $this->tagRelation = $tagRelation;
//        $this->tagGroup = $tagGroup;
//        $this->category = $category;
//        $this->item = $item;
//        $this->fix = $fix;
//        $this->totalize = $totalize;
//        $this->totalizeAll = $totalizeAll;
        
        $this->whereArr = ['open_status'=>1, ['pot_type', '<', 3]]; //こことSingleとSearchとCtm::isPotParentAndStockにある
        
        $this->itemPerPage = 15;
        
    }
    
    public function index($id)
    {
        $item = $this->item->find($id);
        $itemCont = $this->itemCont->where('item_id', $id)->first();
        
        $whereArr = $this->whereArr;
        
        if(!isset($item)) {
            abort(404);
        }
        else {
            if($item->is_potset || ! $item->open_status) // || $item->is_secret
            	abort(404);
        }
        
        $cate = $this->category->find($item->cate_id);
        $subCate = $this->subCate->find($item->subcate_id);
        

        //ポットセットがある場合
        $potWhere = ['open_status'=>1, 'pot_type'=>3, 'pot_parent_id'=>$item->id];
        
        if(isset($item->pot_sort) && $item->pot_sort != '') {
        	$potSorts = $item->pot_sort;
        	$potSets = $this->item->where($potWhere)->orderByRaw("FIELD(id, $potSorts)")->get();
        }
        else {
        	$potSets = $this->item->where($potWhere)->orderBy('pot_count', 'asc')->get();
        }
        
        //Other Atcl
        $otherItem = $this->item->where($whereArr)->whereNotIn('id', [$id])->orderBy('created_at','DESC')->take(5)->get();
        
        //Tag
        $tags = null;
        $tagRels = array();
        $sortIDs = array();
        
        $tagRels = $this->tagRel->where('item_id', $item->id)->orderBy('sort_num','asc')->get()->map(function($obj){
            return $obj->tag_id;
        })->all();
        
        if(count($tagRels) > 0) { //tagのget ->main.shared.tagの中でも指定しているのでここでは不要だが入れておく
			$sortIDs = implode(',', $tagRels);
        	$tags = $this->tag->whereIn('id', $tagRels)->orderByRaw("FIELD(id, $sortIDs)")->get();
        }
        
        //商品画像
        $imgsPri = $this->itemImg->where(['item_id'=>$id, 'type'=>1])->orderBy('number', 'asc')->get();
        //セカンド画像
        $imgsSec = $this->itemImg->where(['item_id'=>$id, 'type'=>2])->orderBy('number', 'asc')->get();
        
        
        //お気に入り確認
        $isFav = 0;
        if(Auth::check()) {
	        $fav = $this->favorite->where(['user_id'=>Auth::id(), 'item_id'=>$id])->first();
        	
            if(isset($fav)) $isFav = 1;   
        }
        else { //Cookie確認
        	$favKey = Cookie::get('fav_key');
//        echo $favKey;
//        exit;
			$favCookie = $this->favCookie->where(['key'=>$favKey, 'item_id'=>$item->id])->first();
            
            if(isset($favCookie)) $isFav = 1;
        }
        
        //View Count
        $item->timestamps = false;
        $item->increment('view_count');
        
        //レコメンド ===========================
        //同梱包可能商品レコメンド -> 同じ出荷元で同梱包可能なもの
        $isOnceItems = null;
        $recomCateItems = null;
        $recomCateRankItems = null;
        $recommends = null;
        $posts = null;
        $ar = array();
        
        $getNum = Ctm::isAgent('sp') ? 6 : 6;
        $chunkNum = $getNum/2;
        
        
        //在庫がないIDを取得する 以下のwhereNotInで使用する ===========
//        $noStockIds = $this->item->whereNotIn('id', [$item->id])->where($whereArr)->get()->map(function($obj) {
//            
//            //$stock = 0;
//            
//            /*
//            if($obj->pot_parent_id === 0) {
//                $switchArr = Ctm::isPotParentAndStock($obj->id); //親ポットか、Stockあるか、その子ポットのObjsを取る。$switchArr['isPotParent'] $switchArr['isStock']
//                $stock = $switchArr['isStock'];
//            }
//            else {
//                $stock = $obj->stock;
//            }
//            */
//            
//            if(! $obj->stock)
//                return $obj->id;
//                
//        })->all();
//    
//        $noStockIds = array_filter($noStockIds);
//        $noStockIdsNoThis = $noStockIds;
//        $noStockIds[] = $item->id;
        //在庫がないID END =========================
        
        if($item->is_once) {
            $isOnceItems = $this->item->where($whereArr)->where(['consignor_id'=>$item->consignor_id, 'is_once'=>1, 'is_once_recom'=>0, ['stock', '>', 0]])->whereNotIn('id', [$item->id])->inRandomOrder()->take($getNum)->get()->chunk($chunkNum);
            
        	//$isOnceItems = $this->item->whereNotIn('id', $noStockIds)->where($whereArr)->where(['consignor_id'=>$item->consignor_id, 'is_once'=>1, 'is_once_recom'=>0])->inRandomOrder()->take($getNum)->get()->chunk($chunkNum);
            //->inRandomOrder()->take()->get() もあり クエリビルダに記載あり
        }
        //同梱包可能商品レコメンド END ================
        
        // この商品を見た人におすすめの商品：同カテゴリーのランダム =====================
        $recomCateItems = $this->item->where($whereArr)->where(['cate_id'=>$item->cate_id, ['stock', '>', 0]])->whereNotIn('id', [$item->id])->inRandomOrder()->take($getNum)->get()->chunk($chunkNum);
        
        //$recomCateItems = $this->item->whereNotIn('id', $noStockIds)->where($whereArr)->where('cate_id', $item->cate_id)->inRandomOrder()->take($getNum)->get()->chunk($chunkNum);
        // この商品を見た人におすすめの商品：同カテゴリーのランダム END ====================
        
        // カテゴリーランキング：同カテゴリーのランキング ====================
        if($item->cate_id == 1) {
            $recomCateRankItems = Ctm::getUekiSecObj2()->take($getNum)->chunk($chunkNum); //get()で返る
            //$items = Ctm::customPaginate($items, $this->perPage, $request);
        }
        else {
            $recomCateRankItems = Ctm::getRankObj2($item->cate_id)->take($getNum)->chunk($chunkNum);
            
            /*
            $arIds = Ctm::getRankObj2($item->cate_id)->map(function($obj){
                return $obj->id;
            })->all();
            
            $arIds = array_diff($arIds, $noStockIdsNoThis);
            $arIds = array_values($arIds);

            if(count($arIds) > 0) {
                $scIdStr = implode(',', $arIds);
                $recomCateRankItems = $this->item->whereIn('id', $arIds)->orderByRaw("FIELD(id, $scIdStr)")->take($getNum)->get()->chunk($chunkNum);
            }
            */
            
            //ORG
            //$recomCateRankItems = $this->item->whereNotIn('id', $noStockIds)->where($whereArr)->where('cate_id', $item->cate_id)->orderBy('sale_count', 'desc')->take($getNum)->get()->chunk($chunkNum);
        }
        // カテゴリーランキング：同カテゴリーのランキング END ====================
        
        //他にもこんな商品が買われています：Recommend レコメンド 先頭タグと同じものをレコメンド & 合わせて関連する記事（Post）もここで取得 ==============
        //$getNum = Ctm::isAgent('sp') ? 3 : 3;
        if(isset($tagRels[1])) {
        	$ar = [$tagRels[1]];
            
            if(isset($tagRels[2])) {
            	$ar[] = $tagRels[2];
            }
            
            if(isset($tagRels[3])) {
            	$ar[] = $tagRels[3];
            }
            
            //他にもこんな商品：部分 =====================
        	$idWithTag = $this->tagRel->whereIn('tag_id', $ar)->get()->map(function($obj){
            	return $obj->item_id;
            })->all();
            
            $idWithCate = $this->item/*->whereNotIn('id', $tempIds)*/->where('subcate_id', $item->subcate_id)->get()->map(function($obj){
            	return $obj->id;
            })->all();
            
            $res = array_merge($idWithTag, $idWithCate);
            $res = array_unique($res); //重複要素を削除
            
			//shuffle($res);
            //$res = array_rand($res, 5);
//            print_r($res);
//            exit;
            
            $recommends = $this->item->whereIn('id', $res)->where($whereArr)->where('stock', '>', 0)->whereNotIn('id', [$item->id])->inRandomOrder()->take($getNum)->get()->chunk($chunkNum);
            
            //$recommends = $this->item->whereNotIn('id', $noStockIds)->whereIn('id', $res)->where($whereArr)->inRandomOrder()->take($getNum)->get()->chunk($chunkNum);
            //他にもこんな商品：部分 END =====================
            
            // Get SimilerPost : Tagルール tagが2個以上なら2~4を対象に、2個以下なら表示なし =====================================
            $postRelIds = $this->postTagRel->whereIn('tag_id', $ar)->get()->map(function($obj){
                return $obj->postrel_id;
            })->all();
            
            $postNum = Ctm::isAgent('sp') ? 3 : 3; //ORG ? 4 : 3;
            $postChunkNum = Ctm::isAgent('sp') ? 3 : 3; //ORG ? 2 : 3;
            $posts = $this->postRel->whereIn('id', $postRelIds)->where(['open_status'=>1, ])->inRandomOrder()->take($postNum)->get()->chunk($postChunkNum);
            // Get SimilerPost END ===================================================
        }
        else { //タグがない時
            $recommends = $this->item->where($whereArr)->where(['subcate_id'=>$item->subcate_id, ['stock', '>', 0]])->whereNotIn('id', [$item->id])->inRandomOrder()->take($getNum)->get()->chunk($chunkNum);
            
        	//$recommends = $this->item->whereNotIn('id', $noStockIds)->where($whereArr)->where(['subcate_id'=>$item->subcate_id])->inRandomOrder()->take($getNum)->get()->chunk($chunkNum);
            //->inRandomOrder()->take()->get() もあり クエリビルダに記載あり
        }
        
        
        // Get SimilerPost : Tagルール tagが2個以上なら2~4を対象に、2個以下なら1個目を対象、0ならなし =====================================
        /*
        if(isset($tagRels[0])) { //関連Post用のarをセットする
            $ar[] = $tagRels[0];
        }
        
        if(count($ar) > 0) {
            $postRelIds = $this->postTagRel->whereIn('tag_id', $ar)->get()->map(function($obj){
                return $obj->postrel_id;
            })->all();
            
            $postNum = Ctm::isAgent('sp') ? 3 : 3; //ORG ? 4 : 3;
            $postChunkNum = Ctm::isAgent('sp') ? 3 : 3; //ORG ? 2 : 3;
            $posts = $this->postRel->whereIn('id', $postRelIds)->where(['open_status'=>1, ])->inRandomOrder()->take($postNum)->get()->chunk($postChunkNum);
        }
        */
        // Get SimilerPost END =====================================
        

		$recomArr = [
        	'同梱包可能なおすすめ商品' => $isOnceItems,
            'この商品を見た人におすすめの商品' => $recomCateItems,
            'カテゴリーランキング' => $recomCateRankItems,
            '他にもこんな商品が買われています' => $recommends,
        ];
        //Recommend ALL END ============================
        
        //Cookie 最近チェックしたアイテム　最近見た CacheではなくCookieなので注意===================
//        $getNum = Ctm::isAgent('sp') ? 8 : 8;
//        $cacheItems = Ctm::getCookieItems($getNum, $item->id); //$whereArrはこの関数の中で指定している
        
        $cookieArr = array();
        $cacheItems = null;
        $getNum = Ctm::isAgent('sp') ? 8 : 8;
        
        
        $cookieIds = Cookie::get('item_ids');
//        echo $cookieIds;
//        exit;
        
        if(isset($cookieIds) && $cookieIds != '') {
	        $cookieArr = explode(',', $cookieIds); 
            
	        $chunkNum = Ctm::isAgent('sp') ? $getNum/2 : $getNum;
          	
            //Viewに渡すItems
	        $cacheItems = $this->item->whereIn('id', $cookieArr)->whereNotIn('id', [$item->id])->where($whereArr)->orderByRaw("FIELD(id, $cookieIds)")->take($getNum)->get()->chunk($chunkNum);
		}
        
        if(! in_array($item->id, $cookieArr)) { //配列にidがない時 or cachIdsが空の時
        	$count = array_unshift($cookieArr, $item->id); //配列の最初に追加
         	
          	if($count > 16) {
            	$cookieArr = array_slice($cookieArr, 0, 16); //16個分を切り取る
        	} 
        }
        else { //配列にidがある時 
        	$index = array_search($item->id, $cookieArr); //key取得
            
            //$split = array_splice($cacheIds, $index, 1); //keyからその要素を削除
            unset($cookieArr[$index]);
            $cookieArr = array_values($cookieArr);
            
        	$count = array_unshift($cookieArr, $item->id); //配列の最初に追加
        }
        
        $cookieIds = implode(',', $cookieArr);
        
        Cookie::queue(Cookie::make('item_ids', $cookieIds, config('app.cookie_time') )); //43200->1ヶ月 appにcookie_timeをセットしているが、設定変更後artisan config:cacheをする必要があるので直接時間指定した方がいいのかもしれない
        
        
//        echo env('APP_ENV');
//        exit;
        
        /*
        if(cache()->has('item_ids')) {
        	
        	$cacheIds = cache()->pull('item_ids'); //pullで元キャッシュを一旦削除する必要がある
            $caches = implode(',', $cacheIds); //順を逆にする
            
            $chunkNum = Ctm::isAgent('sp') ? $getNum/2 : $getNum;
          	
            $cacheItems = $this->item->whereIn('id', $cacheIds)->whereNotIn('id', [$item->id])->where($whereArr)->orderByRaw("FIELD(id, $caches)")->take($getNum)->get()->chunk($chunkNum);
            
//            print_r($cacheItems);
//            exit;  
        }
        
        if(! in_array($item->id, $cacheIds)) { //配列にidがない時 or cachIdsが空の時
        	$count = array_unshift($cacheIds, $item->id); //配列の最初に追加
         	
          	if($count > 16) {
            	$cacheIds = array_slice($cacheIds, 0, 16); //16個分を切り取る
        	}      
        }
        else { //配列にidがある時  
        	//print_r($cacheIds);   
                   
        	$index = array_search($item->id, $cacheIds); //key取得
            
            //$split = array_splice($cacheIds, $index, 1); //keyからその要素を削除
            unset($cacheIds[$index]);
            $cacheIds = array_values($cacheIds);
//            print_r($cacheIds);
//            
//            cache()->forget('cacheIds');
//            cache(['cacheIds'=>$cacheIds], env('CACHE_TIME', 360));
//            print_r(cache('cacheIds'));

            //exit;
            
        	$count = array_unshift($cacheIds, $item->id); //配列の最初に追加
        }

		cache()->forget('item_ids');
        cache(['item_ids'=>$cacheIds], env('CACHE_TIME', 43200)); //put 上書きではなく後ろに追加されている
        */


		//ItemUpper
//        $upperRels = null;
//        $upperRelArr = array();
//        $upper = $this->upper->where(['parent_id'=>$id, 'type_code'=>'item', 'open_status'=>1])->first();
//		
//        if(isset($upper)) {
//        	$upperRels = $this->upperRel->where(['upper_id'=>$upper->id, ])->orderBy('sort_num', 'asc')->get();
//            
//            if($upperRels->isNotEmpty()) {
//            	foreach($upperRels as $upperRel) {
//                	if($upperRel->is_section) {
//                    	$upperRelArr[$upperRel->block]['section'] = $upperRel;
//                    }
//                    else {
//                    	$upperRelArr[$upperRel->block]['block'][] = $upperRel;
//                    }
//                }
//            }
//        }
        
        $uppers = Ctm::getUpperArr($id, 'item');
        $upperMore = $uppers['isMore'];
        $upperRelArr = $uppers['contents'];
        
//        print_r($upperRelArr);
//        exit;

		
        $metaTitle = isset($itemCont->meta_title) ? $itemCont->meta_title : $item->title;
        $metaDesc = $itemCont->meta_description;
        $metaKeyword = $itemCont->meta_keyword;
        
        
        return view('main.home.single', ['item'=>$item, 'itemCont'=>$itemCont, 'potSets'=>$potSets, 'otherItem'=>$otherItem, 'cate'=>$cate, 'subCate'=>$subCate, 'tags'=>$tags, 'imgsPri'=>$imgsPri, 'imgsSec'=>$imgsSec, 'isFav'=>$isFav, 'recomArr'=>$recomArr, 'cacheItems'=>$cacheItems, 'recommends'=>$recommends, 'posts'=>$posts, 'upperMore'=>$upperMore, 'upperRelArr'=>$upperRelArr, 'metaTitle'=>$metaTitle, 'metaDesc'=>$metaDesc, 'metaKeyword'=>$metaKeyword, 'type'=>'single', 'isSingle'=>1, 'id'=>$id]);
    }
    
    
    public function postForm(Request $request)
    {
    	$data = $request->all();
     
     	$buyItem = $this->item->find($data['item_id']);
      
         
        return view('main.cart.index', ['data'=>$data ]);
    }
    
    
    public function postCart(Request $request)
    {
    	$data = $request->all();
     
        $buyItem = $this->item->find($data['item_id']);
        
//        $per = env('TAX_PER');
//        $per = $per/100;
//        
//        $tax = floor($item->price * $per);
//        $price = $item->price + $tax;
     	
      	//$title = $this->item->find($data['item_id'])->title;   
      //ここでsessionに入れる必要がある
         
    	
        return view('main.cart.single', ['buyItem'=>$buyItem, 'tax'=>$data['tax'], 'count'=>$data['count'], 'name'=>$data['name'] ]); 
        
    }
    
    
    //NoUser Favorite Index
    public function favIndex()
    {
    	if(Auth::check()) {
        	return redirect('mypage/favorite');
        }
        
    	$items = null;
        $getNum = Ctm::isAgent('sp') ? 8 : 8;
        
        $favKey = Cookie::get('fav_key');
        
        if(isset($favKey)) {
        	Cookie::queue(Cookie::make('fav_key', $favKey, config('app.fav_cookie_time') )); //分指定 129600に設定->3ヶ月 2month->86400 このfavIndxを開いた時に更新する
        }
//        }
//        else {
//            $favKey = Ctm::getOrderNum(30);
//            Cookie::queue(Cookie::make('fav_key', $favKey, env('FAV_COOKIE_TIME', 86400) )); //分指定 2ヶ月
//        }
        
//        echo $favKey;
//        exit;
        
        $itemIds = $this->favCookie->where(['key'=>$favKey])->orderBy('created_at', 'desc')->get()->map(function($obj){
        	return $obj->item_id;
        })->all();
        
        $itemIdStr = implode(',', $itemIds);
        
        if(count($itemIds)) {
            
	        $chunkNum = Ctm::isAgent('sp') ? $getNum/2 : $getNum;
          	
	        $items = $this->item->whereIn('id', $itemIds)->where($this->whereArr)->orderByRaw("FIELD(id, $itemIdStr)")->paginate(20);
            //->orderByRaw("FIELD(id, $cookieIds)")->take($getNum)->get()->chunk($chunkNum);
            
            foreach($items as $item) {
            	$fav = $this->favCookie->where(['key'=>$favKey, 'item_id'=>$item->id])->first();
                
                $item->fav_id = $fav->id;
            	$item->fav_created_at = $fav->created_at;
            }
            
		} 
       
//       	foreach($items as $item) {
//        	$fav = $this->favorite->where(['user_id'=>$user->id, 'item_id'=>$item->id])->first();
//            
//         	if($fav->sale_id) {
//          		$item->saleDate = $this->sale->find($fav->sale_id)->created_at;
//          	}
//            else {
//            	$item->saleDate = 0;
//            }       
//        	//$item->saled = 1;
//        }      
       
       	$metaTitle = 'お気に入り一覧' . '｜植木買うならグリーンロケット';
        $metaDesc = '';
        $metaKeyword = '';
      
        return view('mypage.favorite', ['items'=>$items, 'metaTitle'=>$metaTitle]); 
    
    }
    
    //fav一覧からのfav delete
    public function postFavDel(Request $request)
    {
    	$favDelId = $request->input('fav_del_id');
        
        if(Auth::check()) {
        	$this->favorite->destroy($favDelId);
            $redirectUrl = 'mypage/favorite';
        }
        else {
        	$this->favCookie->destroy($favDelId);
            $redirectUrl = 'favorite';
        }
        
        
        return redirect($redirectUrl);
 
    }
    
    
    //お気に入り ajax
    public function postScript(Request $request)
    {
        $itemId = $request->input('itemId');
        $isOn = $request->input('isOn');
        
        
        if(Auth::check()) {
            $user = $this->user->find(Auth::id());
            $str = '';
            
            //Favorite Save ==================================================
            //foreach($data['spare_count'] as $count) {
                            
            if(!$isOn) { //お気に入り解除の時
                $favModel = $this->favorite->where(['user_id'=>$user->id, 'item_id'=>$itemId])->first();
                
                if($favModel !== null) {
                    $favModel ->delete();
                }
                
                $str = "お気に入りから削除されました";
            }
            else {
                $this->favorite->create([
                    'user_id'=>$user->id,
                    'item_id'=>$itemId,
                ]);
                
                $str = "お気に入りに登録されました";       
            }
                
            //} //foreach
            // Favorite END ========================================================
        }
        else {
            //Cookie お気に入り DB ===================
            
            if(Cookie::has('fav_key')) {
            	$favKey = Cookie::get('fav_key');
            }
            else {
                $favKey = Ctm::getOrderNum(30);
            }
            
			Cookie::queue(Cookie::make('fav_key', $favKey, config('app.fav_cookie_time') )); //分指定 129600に設定->3ヶ月 2month->86400　ここの操作後に3ヶ月更新するようにしている 
            
            if(! $isOn) { //お気に入り解除の時
            	$favModel = $this->favCookie->where(['key'=>$favKey, 'item_id'=>$itemId])->first();
                
                if($favModel !== null) 
                    $favModel->delete();
                
                $str = "お気に入りから削除されました";
            }
            else {
                $this->favCookie->create([
                    'key'=> $favKey,
                    'item_id'=> $itemId,
                    'type'=> 'favorite',
                    
                ]);
                
                $str = "お気に入りに登録されました"; 
            }
        
        }
        

        return response()->json(['str'=>$str]/*, 200*/); //200を指定も出来るが自動で200が返される  
          //return view('dashboard.script.index', ['val'=>$val]);
        //return response()->json(array('subCates'=> $subCates)/*, 200*/);
    }
    
    public function endCart()
    {
    	return view('main.cart.end');
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
}
