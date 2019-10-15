<?php

namespace App\Http\Controllers\Main;

use App\Setting;
use App\TopSetting;
use App\Item;
use App\Post;
use App\PostRelation;
use App\PostCategory;
use App\PostCategorySecond;
use App\Tag;
use App\TagRelation;
use App\PostTagRelation;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Ctm;
use Search;
use DB;

class PostController extends Controller
{
    
    public function __construct(Setting $setting, TopSetting $topSetting, Item $item, Post $post, PostRelation $postRel, PostCategory $postCate, PostCategorySecond $postCateSec, Tag $tag, TagRelation $tagRel, PostTagRelation $postTagRel)
    {
        //$this->middleware('search');
        
        $this->item = $item;
        $this->post = $post;
        $this->postRel = $postRel;
        
        $this->postCate = $postCate;
        $this->postCateSec = $postCateSec;
        $this->tag = $tag;
        $this->tagRel = $tagRel;
        $this->postTagRel = $postTagRel;
        
        $this->setting = $setting;
        $this->set = $this->setting->first();
        
        $this->topSetting = $topSetting;
        $this->topSet = $this->topSetting->first();
        
        $this->postWhere = ['open_status'=>1]; 
        $this->itemWhere =['open_status'=>1, 'is_potset'=>0]; //こことSingleとSearch/ArchiveとCtm::isPotParentAndStockにある
        
        
        $this->itemPerPage = 16;
        
    }
    
    public function index(Request $request)
    {
    	//if(Ctm::isEnv('product') || Ctm::isEnv('alpha')) abort(404);
        
        //$isDate = isset($datePath) ? 1 : 0;
        $isDate = $request->has('isdate') ? 1 : 0;
        
        $whereArr = $this->postWhere;
        
        $target = $isDate ? 'created_at' : 'view_count';
        
        $postRels = $this->postRel->where($whereArr)->orderBy($target, 'DESC')->paginate($this->itemPerPage);
        
        if($isDate) { //ページネーションのリンクにqueryをつける
            $postRels->appends(['isdate' => 1]);
        }
        //bigTitle(H1)をセットする
        //$postRels = $this->setBigTitleToRel($postRels);
        
        //Category All
        $postCates = $this->postCate->all();
        
        //Category Ranking -----
        $takeNum = Ctm::isAgent('sp') ? 6 : 4;
        $rankCates = array();
        
        $cateSecs = $this->postCateSec->whereNotIn('parent_id', [1])->orderBy('view_count', 'desc')->get();
        
        foreach($cateSecs as $cateSec) {
        	$cateSecPost = $this->postRel->where($whereArr)->where('catesec_id', $cateSec->id)->orderBy('created_at', 'desc')->first();
            
            if(collect($cateSecPost)->isNotEmpty()) {
            	$cateSec->cate_slug = $this->postCate->find($cateSec->parent_id)->slug;
            	$cateSec->thumb_path = $cateSecPost->thumb_path;
          		$rankCates[] = $cateSec; 
           	}   
        }
        
        $rankCates = collect($rankCates)->take($takeNum);
        //Category Ranking END -----
        
        //Tag Ranking
        $rankTags = $this->tag->orderBy('view_count', 'desc')->take(20)->get();
        
        //Meta
        $metaTitle = $this->topSet->post_meta_title . ' | 植木買うならグリーンロケット';
        $metaDesc = $this->topSet->post_meta_description;
        $metaKeyword = $this->topSet->post_meta_keyword;
        
        //Type
        $type = 'top';
        
        return view('main.post.archive', compact('isDate', 'postRels', 'postCates', 'rankCates', 'rankTags', 'metaTitle', 'metaDesc', 'metaKeyword', 'type'));
        
        // ============================================================================================
        
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
    	//if(Ctm::isEnv('product') || Ctm::isEnv('alpha')) abort(404);
        
        
        //get Post
        $postRel = $this->postRel->find($postId);
        
        if(! isset($postRel) || ! $postRel->open_status) {
        	abort(404);
        }
        
        $posts = $this->post->where('rel_id', $postId)->get();
        
        $bigTitle = '';
        $postArr = array(); //post用
        $indexArr = array(); //目次用
        $introArr = array(); //Intro用
        
        foreach($posts as $post) {
        	if($post->is_section) {
                if(isset($post->title)) {
                    $postArr[$post->id]['h2'] = $post;
                    
                    $contIds = $this->post->where(['rel_id'=>$postId, 'mid_title_id'=>$post->id])->get()->map(function($obj){
                        if(isset($obj->img_path) || isset($obj->title) || isset($obj->detail)) {
                        	return $obj->id;
                        }
                    })->all(); //mid_title_id => h2のmidTitle これに属するコンテンツを取得するため
                    
                    $conts = $this->post->find($contIds);
                    
                    $postArr[$post->id]['contents'] = $conts;
                }
            }
        }
        
//        print_r($postArr);
//        exit;
        
        
        //Cate
        $postCate = $this->postCate->find($postRel->cate_id);
        $postCate->increment('view_count');
        
        //CateSec
        if($postRel->catesec_id) {
        	$postCateSec = $this->postCateSec->find($postRel->catesec_id);
        	$postCateSec->increment('view_count');
        }
        
        //Tag
        $tags = null;
        $tagRels = array();
        $sortIDs = array();
        
        $tagRelIds = $this->postTagRel->where('postrel_id', $postRel->id)->orderBy('sort_num','asc')->get()->map(function($obj){
            return $obj->tag_id;
        })->all();
        
        if(count($tagRelIds) > 0) { //tagのget ->main.shared.tagの中でも指定している（$numを指定するarchiveのみ使用）が、singleとpost-singleのみここで指定する
			$sortIDs = implode(',', $tagRelIds);
        	$tags = $this->tag->whereIn('id', $tagRelIds)->orderByRaw("FIELD(id, $sortIDs)")->get();
        }
        
        
        //関連記事 （こんな他の記事）===============================
        //同じPostカテゴリーと先頭タグ1-3までと同じものをunionしてrandomOrderする
        
        //カテゴリー
        $sameCates = $this->postRel->whereNotIn('id', [$postId])->where(['cate_id'=>$postRel->cate_id, 'open_status'=>1]);
    	
        //タグ random感をより出すのであれば、先頭タグ2-4までを抽出するのがいい
        $ar = array();
        
        if(isset($tagRelIds[0])) {
        	$ar[] = $tagRelIds[0];
            
            if(isset($tagRelIds[1])) {
            	$ar[] = $tagRelIds[1];
            }
            
            if(isset($tagRelIds[2])) {
            	$ar[] = $tagRelIds[2];
            }
        }
        
        $sameTagIds = $this->postTagRel->whereNotIn('postrel_id', [$postId])->whereIn('tag_id', $ar)->get()->map(function($obj){
        	return $obj->postrel_id;
        });
        
        $relatePosts = $this->postRel->whereIn('id', $sameTagIds)->union($sameCates)->inRandomOrder()->take(3)->get();
        
        //bigTitle(H1)をセットする
        //$relatePosts = $this->setBigTitleToRel($relatePosts);
        
//        print_r($relatePosts);
//        exit;
        
        
        //関連商品 ==================================
        /*
        	・強さ：ID > (ワード + タグ/カテ/子カテ) ワードを強くしてもいいが、表示数が多いことが多いのでワードだけになることがほとんど
        	・idsがセット：必ずそれらが入力順に先頭表示。不足分は(ワード + タグ/カテ/子カテ)のランダムから
            ・idsが空：(ワード + タグ/カテ/子カテ)のランダム
            ・検索ワードが空：タグ/カテ/子カテのランダム
            ・タグは1〜3個目までが連結対象(0~2)
            ・親カテセットの時は子カテ必須
        */
        
        $relateNum = 6;
        //$isIds = 0;
        
        //$dbItem = $this->item->where($this->itemWhere)->where('stock', '>', 0);
        
//        $searchItems = null;
//        $mustItems = null;
        
        
        $relateTagIds = $this->tagRel->whereIn('tag_id', $ar)->get()->map(function($obj){
            return $obj->item_id;
        });
        
        //$t = DB::table('items');
        //-----
        $tagItems = $this->item->whereIn('id', $relateTagIds)->where($this->itemWhere)->where('stock', '>', 0);
        //$t->whereIn('id', $relateTagIds);
        
        
        //item_cate_id/subcate_id=>未入力なら0なので必ずここを通ることになる
        $itemCateId = ($itemCateId = $postRel->item_cate_id != 1) ? $postRel->item_cate_id : 0; //カテゴリー植木庭木ならスルーする
        
        $cateItems = $this->item->where(['cate_id'=>$postRel->item_cate_id])->where($this->itemWhere)->where('stock', '>', 0);
        $cateSecItems = $this->item->where(['subcate_id'=>$postRel->item_subcate_id])->where($this->itemWhere)->where('stock', '>', 0);

        //Tag + Cate + cateSec
        $relateItems = $tagItems->union($cateItems)->union($cateSecItems)->inRandomOrder()->get()->all();

//        $t->orWhere(['cate_id'=>$postRel->item_cate_id]);
//        $t->orWhere(['subcate_id'=>$postRel->item_subcate_id]);
//		  $allItems = $t->where($this->itemWhere)->where('stock', '>', 0)->inRandomOrder()->take(6);

        //カラムだけ抽出
        
        
        if(isset($postRel->s_word)) {
            $word = $postRel->s_word;
            $s = new Search($word);
            
            $sRes = $s->getSearchObj();
            
            $sorts = implode(',', $sRes['allResIds']);
            
            //-----
            $searchItems = $this->item->whereIn('id', $sRes['allResIds'])->where($this->itemWhere)->where('stock', '>', 0)->orderBy('updated_at', 'desc');
            	//->union($allItems)->inRandomOrder();
            
            //allItemと混ぜてRandomする
            $si = array_merge($searchItems->get()->all(), $relateItems);
            $relateItems = array_unique($si); //重複要素削除
            shuffle($relateItems); //配列のランダム

            //$searchItems = collect($si)->random($relateNum);
            
            //DB::table('items')でタグカテitemを取得すればunionできる unionしたものをunionはできない
            //$sr = $searchItems->union($allItems)->inRandomOrder()->take(6)->get()->all();            
        }
        
        
        if(isset($postRel->item_ids)) {
        	$idsArr = explode(',', $postRel->item_ids);
        	$sortIDs = $postRel->item_ids;
        	
            $mustItems = $this->item->whereIn('id', $idsArr)->where($this->itemWhere)->where('stock', '>', 0)->orderByRaw("FIELD(id, $sortIDs)");
            
            //searchItemかallItemと混ぜる Randomしない
            //$targetArr = isset($searchItems) ? $searchItems : $relateItems;
            
            $mi = array_merge($mustItems->get()->all(), $relateItems);
            $relateItems = array_unique($mi); //重複要素削除
            
            //$isIds = 1;
            //$mustItems = collect($mi);
            
            /*
            if(isset($searchItems)) {
            	$sr = $searchItems->union($allItems)->inRandomOrder()->take(6)->get()->all();
                $mt = $mustItems->get()->all();
                
                $res = array_merge($sr, $mt);                
        		$mustItems = collect($res);
                
                 //おすすめ情報 RecommendInfo (cate & cateSecond & tag)
                //        $tagRecoms = $this->tag->where(['is_top'=>1])->orderBy('updated_at', 'desc')->get()->all();       
                //        $res = array_merge($tagRecoms, $cateRecoms, $subCateRecoms);
                        
                //        $books = array(
                //        	$tagRecoms,
                //            $cateRecoms,
                //            $subCateRecoms
                //        );

            	//$mustItems = $mustItems->union($sr);
            }
            else {
            	$mustItems = $mustItems->union($allItems);
            }
            */
            
        }
         
        //親ポットの在庫を確認する
        foreach($relateItems as $k => $v) {
            extract(Ctm::isPotParentAndStock($v['id']));
            
            if($isPotParent && ! $isStock) 
            	unset($relateItems[$k]);
        }
        
        $relateItems = collect($relateItems)->take($relateNum);
        
//        if($isIds) {
//            $relateItems = collect($relateItems)->take($relateNum);
//        }
//        else {
//        	$relateItems = collect($relateItems)->random($relateNum);
//        }
        
//        if(isset($mustItems)) {
//        	
//            $relateItems = $mustItems->take($relateNum);
//        }
//        else if(isset($searchItems)) {
//        	$relateItems = $searchItems->take($relateNum);
//        }
//        else {
//	        $relateItems = $allItems->inRandomOrder()->take($relateNum)->get();
//    	}    
        
        
        // Meta ====================
        $metaTitle = isset($postRel->meta_title) ? $postRel->meta_title : $postRel->big_title . '｜植木買うならグリーンロケット';
        $metaDesc = $postRel->meta_description;
        $metaKeyword = $postRel->meta_keyword;
        
        $type = 'postSingle';
		
        $postRel->increment('view_count');
        //$postRel->save();
		
        
        return view('main.post.single', compact('postRel', 'postArr', 'postCate', 'tags', 'relatePosts', 'relateItems', 'metaTitle', 'metaDesc', 'metaKeyword', 'type'));
        
        
        		
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
    
    
    public function category($slug, $slugSec=null, Request $request)
    {
    	//if(Ctm::isEnv('product') || Ctm::isEnv('alpha')) abort(404);
                
        //if($request->has('sec') && ! $request->input('sec')) abort(404);
        
        //子供カテゴリーかどうかを確認
        $isSec = isset($slugSec) ? 1 : 0;
        //Date並びか確認
        $isDate = $request->has('isdate') ? 1 : 0;
        
        $postWhere = $this->postWhere;
        
        if($isSec) {
        	$postCate = $this->postCateSec->where('slug', $slugSec)->first();
         	
            if(collect($postCate)->isEmpty()) 
            	abort(404);
                
         	$postWhere['catesec_id'] = $postCate->id;
          	$type = 'cateSec';     
        }
        else {
        	$postCate = $this->postCate->where('slug', $slug)->first();
         	if(collect($postCate)->isEmpty()) abort(404);
                
         	$postWhere['cate_id'] = $postCate->id;
          	$type = 'cate';      
        }
        
		$target = $isDate ? 'created_at' : 'view_count';
        $postRels = $this->postRel->where($postWhere)->orderBy($target, 'DESC')->paginate($this->itemPerPage);
        
        if($isDate) { //ページネーションのリンクにqueryをつける
        	$postRels->appends(['isdate' => 1]);
        }
        
        
        //bigTitle(H1)をセットする
        //$postRels = $this->setBigTitleToRel($postRels);
        
        $postCate->increment('view_count');
        
        //For Sidebar
        $postCates = $this->postCate->all();
        
        // Meta ====================
        $metaTitle = isset($postCate->meta_title) ? $postCate->meta_title : $postCate->name . '｜植木買うならグリーンロケット';
        $metaDesc = $postCate->meta_description;
        $metaKeyword = $postCate->meta_keyword;
        
        
        return view('main.post.archive', compact('postRels', 'postCate', 'postCates', 'isDate', 'type', 'metaTitle', 'metaDesc', 'metaKeyword'));
    }
    
    
    public function viewRank()
    {
    	if(Ctm::isEnv('product') || Ctm::isEnv('alpha')) abort(404);
        
        
        $postWhere = $this->postWhere;
        //$postWhere['cate_id'] = $postCate->id;
        
        $postRels = $this->postRel->where($postWhere)->orderBy('view_count','DESC')->paginate($this->itemPerPage);
        
        //bigTitle(H1)をセットする
        //$postRels = $this->setBigTitleToRel($postRels);
        
        
        // Meta ====================
        $metaTitle = '記事ランキング' . '｜植木買うならグリーンロケット';
        $metaDesc = '';
        $metaKeyword = '';
        
        
        return view('main.post.archive', compact('postRels', 'metaTitle', 'metaDesc', 'metaKeyword'));
    }
    
    
    //現在未使用：postの大タイトルをObjにセットするPrivateFunk
    private function setBigTitleToRel($postRels)
    {
    	foreach($postRels as $k => $postRel) {
        	$post = $this->post->where(['rel_id'=>$postRel->id, 'is_section'=>1, 'sort_num'=>0])->first();
            $postRel->big_title = $post->title;
            $postRels[$k] = $postRel;
        }
        
        return $postRels;
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
