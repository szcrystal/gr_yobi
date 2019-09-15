<?php

namespace App\Http\Controllers\Main;

use App\Item;
use App\Post;
use App\PostRelation;
use App\PostCategory;
use App\Tag;
use App\PostTagRelation;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Ctm;

class PostController extends Controller
{
    
    public function __construct(Item $item, Post $post, PostRelation $postRel, PostCategory $postCate, Tag $tag, PostTagRelation $postTagRel)
    {
        //$this->middleware('search');
        
        $this->item = $item;
        $this->post = $post;
        $this->postRel = $postRel;
        
        $this->postCate = $postCate;
        $this->tag = $tag;
        $this->postTagRel = $postTagRel;
        
        
        $this->whereArr = ['open_status'=>1, 'is_potset'=>0]; //こことSingleとSearchとCtm::isPotParentAndStockにある
        
        $this->itemPerPage = 15;
        
    }
    
    public function index($id)
    {
    	if(Ctm::isEnv('product') || Ctm::isEnv('alpha')) abort(404);
        
        
        $item = $this->item->find($id);
        
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
        $potWhere = ['open_status'=>1, 'is_potset'=>1, 'pot_parent_id'=>$item->id];
        
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
        
        $getNum = Ctm::isAgent('sp') ? 6 : 6;
        $chunkNum = $getNum/2;
        
        //在庫がないIDを取得する 以下のwhereNotInで使用する ===========
        $noStockIds = $this->item->whereNotIn('id', [$item->id])->where($whereArr)->get()->map(function($obj) {
            $switchArr = Ctm::isPotParentAndStock($obj->id); //親ポットか、Stockあるか、その子ポットのObjsを取る。$switchArr['isPotParent'] ! $switchArr['isStock']
            if($switchArr['isPotParent']) {
                if(! $switchArr['isStock'])
                    return $obj->id;
            }
            else {
                if(! $obj->stock)
                    return $obj->id;
            }
        })->all();
    
        $noStockIds = array_filter($noStockIds);
        $noStockIds[] = $item->id;
        //在庫がないID END ===========
        
        if($item->is_once) {
        	$isOnceItems = $this->item->whereNotIn('id', $noStockIds)->where($whereArr)->where(['consignor_id'=>$item->consignor_id, 'is_once'=>1, 'is_once_recom'=>0])->inRandomOrder()->take($getNum)->get()->chunk($chunkNum);
            //->inRandomOrder()->take()->get() もあり クエリビルダに記載あり
        }
        
        // レコメンド：同カテゴリー 植木庭木の時のみsubcateに合わせる
        $cateArr = ($item->cate_id == 1) ? ['subcate_id' => $item->subcate_id] : ['cate_id' => $item->cate_id];
                
        $recomCateItems = $this->item->whereNotIn('id', $noStockIds)->where($whereArr)->where($cateArr)->inRandomOrder()->take($getNum)->get()->chunk($chunkNum);
        
        // レコメンド：同カテゴリーのランキング
        $recomCateRankItems = $this->item->whereNotIn('id', $noStockIds)->where($whereArr)->where($cateArr)->orderBy('view_count', 'desc')->take($getNum)->get()->chunk($chunkNum);
        
        
        //Recommend レコメンド 先頭タグと同じものをレコメンド ==============
        //$getNum = Ctm::isAgent('sp') ? 3 : 3;
        
        if(isset($tagRels[1])) {
        	$ar = [$tagRels[1]];
            
            if(isset($tagRels[2])) {
            	$ar[] = $tagRels[2];
            }
            
            if(isset($tagRels[3])) {
            	$ar[] = $tagRels[3];
            }
            
        	$idWithTag = $this->tagRel->whereIn('tag_id', $ar)->get()->map(function($obj){
            	return $obj->item_id;
            })->all(); 
            
//            $tempIds = $idWithTag;
//            $tempIds[] = $item->id;
            
            $idWithCate = $this->item/*->whereNotIn('id', $tempIds)*/->where('subcate_id', $item->subcate_id)->get()->map(function($obj){
            	return $obj->id;
            })->all();
            
            $res = array_merge($idWithTag, $idWithCate);
            $res = array_unique($res); //重複要素を削除
            
			//shuffle($res);
            //$res = array_rand($res, 5);
//            print_r($res);
//            exit;
            
            $recommends = $this->item->whereNotIn('id', $noStockIds)->whereIn('id', $res)->where($whereArr)->inRandomOrder()->take($getNum)->get()->chunk($chunkNum);
            //->inRandomOrder()->take()->get() もあり クエリビルダに記載あり
        }
        else {
        	$recommends = $this->item->whereNotIn('id', $noStockIds)->where($whereArr)->where(['subcate_id'=>$item->subcate_id])->inRandomOrder()->take($getNum)->get()->chunk($chunkNum);
            //->inRandomOrder()->take()->get() もあり クエリビルダに記載あり
        }
        
//        print_r($recommends);
//        exit;

		$recomArr = [
        	'同梱包可能なおすすめ商品' => $isOnceItems,
            'この商品を見た人におすすめの商品' => $recomCateItems,
            'カテゴリーランキング' => $recomCateRankItems,
            '他にもこんな商品が買われています' => $recommends,
        ];
        
        
        //Cache 最近見た ===================
        $cookieArr = array();
        $cacheItems = null;
        $getNum = Ctm::isAgent('sp') ? 8 : 8;
        
        
        $cookieIds = Cookie::get('item_ids');
//        echo $cookieIds;
//        exit;
        
        if(isset($cookieIds) && $cookieIds != '') {
	        $cookieArr = explode(',', $cookieIds); 
            
	        $chunkNum = Ctm::isAgent('sp') ? $getNum/2 : $getNum;
          	
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
        
        $upperRelArr = Ctm::getUpperArr($id, 'item');
        
//        print_r($upperRelArr);
//        exit;

		
        $metaTitle = isset($item->meta_title) ? $item->meta_title : $item->title;
        $metaDesc = $item->meta_description;
        $metaKeyword = $item->meta_keyword;
        
        
        return view('main.home.single', ['item'=>$item, 'potSets'=>$potSets, 'otherItem'=>$otherItem, 'cate'=>$cate, 'subCate'=>$subCate, 'tags'=>$tags, 'imgsPri'=>$imgsPri, 'imgsSec'=>$imgsSec, 'isFav'=>$isFav, 'recomArr'=>$recomArr, 'cacheItems'=>$cacheItems, 'recommends'=>$recommends, 'upperRelArr'=>$upperRelArr, 'metaTitle'=>$metaTitle, 'metaDesc'=>$metaDesc, 'metaKeyword'=>$metaKeyword, 'type'=>'single', 'isSingle'=>1]);
    }
    
    
    public function show($postId)
    {
    	if(Ctm::isEnv('product') || Ctm::isEnv('alpha')) abort(404);
        
        
        //ItemUpper
        $upperRels = null;
        $upperRelArr = array();
        
        $postRel = $this->postRel->find($postId);
        
        if(! isset($postRel) || ! $postRel->open_status) {
        	abort(404);
        }
        
        $posts = $this->post->where('rel_id', $postId)->get();
        
        $bigTitle = '';
        $postArr = array();
        
        foreach($posts as $post) {
        	if($post->is_section) {
            	if(! $post->sort_num) { //sort_numが0なのは、h1タイトルのみ
	                $bigTitle = $post->title;
                }
                else {
                	if($post->title != '') {
                    	$postArr[$post->id]['h2'] = $post;
                        
                        $conts = $this->post->where(['rel_id'=>$postId, 'mid_title_id'=>$post->id])->get();
                    	$postArr[$post->id]['contents'] = $conts;
                    }
                    else {
                    	$postArr[0]['contents'] = $this->post->where(['rel_id'=>$postId, 'mid_title_id'=>0])->get();
                    }
                }
            }
        }
        
        
        //Cate
        $postCate = $this->postCate->find($postRel->cate_id);
        
        //Tag
        $tags = null;
        $tagRels = array();
        $sortIDs = array();
        
        $tagRels = $this->postTagRel->where('postrel_id', $postRel->id)->orderBy('sort_num','asc')->get()->map(function($obj){
            return $obj->tag_id;
        })->all();
        
        if(count($tagRels) > 0) { //tagのget ->main.shared.tagの中でも指定しているのでここでは不要だが入れておく
			$sortIDs = implode(',', $tagRels);
        	$tags = $this->tag->whereIn('id', $tagRels)->orderByRaw("FIELD(id, $sortIDs)")->get();
        }
        
        
        
        $metaTitle = isset($postRel->meta_title) ? $postRel->meta_title : $bigTitle . '｜植木買うならグリーンロケット';
        $metaDesc = $postRel->meta_description;
        $metaKeyword = $postRel->meta_keyword;
        
        return view('main.post.single', ['postRel'=>$postRel, 'bigTitle'=>$bigTitle, 'postArr'=>$postArr, 'postCate'=>$postCate, 'tags'=>$tags, 'metaTitle'=>$metaTitle, 'metaDesc'=>$metaDesc, 'metaKeyword'=>$metaKeyword]);
        
        
        		
        if(isset($posts)) {
        	
            //登録されるブロックはa,b,cの3つのみ
            
        	$upperRels = ItemUpperRelation::where(['upper_id'=>$upper->id, ])->orderBy('id', 'asc')->get(); //->orderBy('sort_num', 'asc')
            
            if($upperRels->isNotEmpty()) {
            	foreach($upperRels as $upperRel) {

                    if($upperRel->is_section) { //大タイトル・中タイトルがis_section:trueになる
                        if($upperRel->sort_num > 0) { //sort_numが1以上なら中タイトル 0は大タイトル(1つのみ)
                            $upperRelArr[$upperRel->block]['mid_section'][] = $upperRel;
                        }
                        else {
                            $upperRelArr[$upperRel->block]['section'] = $upperRel; //大タイトルは一つのみなので、pushしない
                        }
                    }
                    else {
                        $upperRelArr[$upperRel->block]['block'][] = $upperRel;
                    }

                }
            }

        }
        
        return $upperRelArr;
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
