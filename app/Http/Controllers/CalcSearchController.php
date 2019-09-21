<?php

namespace App\Http\Controllers;

use App\Item;

use App\Tag;
use App\TagRelation;

//use App\DeliveryGroup;
//use App\DeliveryGroupRelation;
//use App\Prefecture;
use App\Setting;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use DB;
use Schema;
use Ctm;

    
class CalcSearchController extends Controller
{
//Global 変数
	//府中ガーデン-下草小のid（商品個数x係数の合計に応じて容量と送料区分が決まる）
//    private $sitakusaSmId = 1;
//    //府中ガーデン-下草大のid
//    private $sitakusaBgId = 2;
    
    
    
    public function __construct($searchWord)
    {
    	/***********************
         $itemDataはitemのobjectに[count]（購入個数）を足したObjectを一つずつ配列にしたもの
        ************************/
        
        $this->item = new Item; //DB::table('items')
        
        $this->tag = new Tag;
        $this->tagRel = new TagRelation;
        
//        $this->dg = new DeliveryGroup;
//        $this->dgRel = new DeliveryGroupRelation;
//        $this->prefecture = new Prefecture;
        
        $this->setting = new Setting;
        $this->set = $this->setting->first;
        
        $this->searchWord = $searchWord;
        
//        $this->itemData = $itemData;
//        $this->prefId = $prefId;
                
    }
    
    private function customQueryWhere($query, $columnArray, $word)
    {
    	foreach($columnArray as $column) {
            
            if($column != 'created_at' && $column != 'updated_at') {
                
                if($column == 'job_number' || $column == 'user_number')
                { 
                    $query -> orWhere($column, $word);
                }
                //cate
                elseif($column == 'cate_id')
                {
                	$ids = DB::table('categories')->where('name', 'like', $word)->get()->map(function($obj){
                        return $obj->id;
                    })->all();
                    
                    //$qry->whereIn('subcate_id', $ids);
                    $query -> orWhere(function($q) use($ids) {
                        $q->whereIn('cate_id', $ids);
                    });
                }
                
                //cateSec
                elseif($column == 'subcate_id')
                {
                    $ids = DB::table('category_seconds')->where('name', 'like', $word)->get()->map(function($obj){
                        return $obj->id;
                    })->all();
                    
                    //$qry->whereIn('subcate_id', $ids);
                    $query -> orWhere(function($q) use($ids) {
                        $q->whereIn('subcate_id', $ids);
                    });
                }
                elseif(
                    $column == 'name' || 
                    $column == 'title' || 
                    //$column == 'slug' || 
                    $column == 'catchcopy' || 
                    $column == 'exp_first' || 
                    $column == 'explain' || 
                    $column == 'about_ship' || 
                    $column == 'detail'
                    )
                {
                    $query -> orWhere($column, 'like', $word);
                }

                //産地直送ワード
                elseif($column == 'farm_direct')
                {
                    //if($word == "%産地%" || $word == "%直送%" || $word == "%産地直送%") {
                    if(strpos($word, '産地') !== false || strpos($word, '直送') !== false ) {
                        $query -> orWhere($column, 1);
                    }
                }
                
                //Item内にcolumnがないものをここで検索する
                else
                {
                	//Tag
                	$tagIds = $this->tag->where('name', 'like', $word)->get()->map(function($tag){
                        return $tag->id;
                    })->all();
                    
                    $itemIds = $this->tagRel->whereIn('tag_id', $tagIds)->get()->map(function($tagRel){
                    	return $tagRel->item_id;
                    })->all();
                    
                    $query -> orWhere(function($q) use($itemIds) {
                        $q->whereIn('id', $itemIds);
                    });
                    
                }

            }
        }
    }
    
    public function getSearchObj()
    {
    	$searchWord = $this->searchWord;

        //全角スペース時半角スペースに置き換える
        if( str_contains($searchWord, '　')) {
            $searchWord = str_replace('　', ' ', $searchWord);
        }
        
        if(str_contains($searchWord, ' ')) { //半角スペースがある時配列に
        	$searchWord = explode(' ', $searchWord);
        }
        
        $itemQuery = DB::table('items');
        $columnArr = Schema::getColumnListing('items');
        
        if(is_array($searchWord)) { //半角スペース AND検索

            foreach($searchWord as $sWord) {
                $sWord = "%".$sWord."%";
                
                $itemQuery->where( function($query) use($columnArr, $sWord) { //ここをorWhereにすればOR検索(追加検索)になりそう
                    $this->customQueryWhere($query, $columnArr, $sWord);
                });
            }
        }
        else {
        	$sWord = "%".$searchWord."%";
        	$this->customQueryWhere($itemQuery, $columnArr, $sWord);
        }
        
        
        $allResIds = $itemQuery->get()->map(function($item){
            return $item->id;
        })->all();
        
        
        $search = $this->searchWord;
        
        return compact('allResIds', 'search'); //return ['allResIds'=>$allResIds, 'search'=>$this->searchWord];
    }
    
    
    //ORG getSearchObj ============
    public function ORGgetSearchObj() //private function returnSearchObj($search)
    {
    	$searchWord = $this->searchWord;

        //全角スペース時半角スペースに置き換える
        if( str_contains($searchWord, '　')) {
            $searchWord = str_replace('　', ' ', $searchWord);
        }
        
        if(str_contains($searchWord, ' ')) { //半角スペースがある時配列に
        	$searchWord = explode(' ', $searchWord);
        }
        
    
        
        //検索queryをカラムごとに繰り返すメイン関数
        function customQueryWhere($array, $qry, $word) {
            
            foreach($array as $column) {
                if($column != 'created_at' && $column != 'updated_at') {
                    
                    if($column == 'job_number' || $column == 'user_number')
                    { 
                        $qry -> orWhere($column, $word);
                    }
//                    elseif($column == 'subcate_id')
//                    {
//                    	$ids = DB::table('category_seconds')->where('name', 'like', $word)->get()->map(function($obj){
//                        	return $obj->id;
//                        })->all();
//                        
//                        //$qry->whereIn('subcate_id', $ids);
//                        $qry -> orWhere(function($q) use($ids) {
//                        	$q->whereIn('subcate_id', $ids);
//                        });
//                    }
                    elseif(
                    	$column == 'name' || 
                        $column == 'title' || 
                        //$column == 'slug' || 
                        $column == 'catchcopy' || 
                        $column == 'exp_first' || 
                        $column == 'explain' || 
                        $column == 'about_ship' || 
                        $column == 'detail'
                        )
                    {
                        $qry -> orWhere($column, 'like', $word);
                    }

                    //産地直送ワード
                    elseif($column == 'farm_direct')
                    {
                        //if($word == "%産地%" || $word == "%直送%" || $word == "%産地直送%") {
                        if(strpos($word, '産地') !== false || strpos($word, '直送') !== false ) {
                            $qry -> orWhere($column, 1);
                        }
                    }

                }
            }
            
        } //function END        
        
        
        //$table_name = 'tags';
        //$query = DB::table($table_name); //query取得
        //カラム名の全てを取得
        //$arr = Schema::getColumnListing($table_name); //これでカラム取得が出来る
        
        
        if(is_array($searchWord)) { //半角スペース AND検索
            //$searchs = explode(' ', $search);
            //exit;

            
            //Tag Search ==================================
            $table_name = 'tags';
	        $query = DB::table($table_name); //query取得
            $arr = Schema::getColumnListing($table_name); //これでカラム取得が出来る
            
            foreach($searchWord as $sWord) {
                $sWord = "%".$sWord."%";
                
                //Tag Search ---
                $query ->where( function($query) use($arr, $sWord) { //絞り込み検索の時はwhereクロージャを使う。別途の引数はuse()を利用。
                    customQueryWhere($arr, $query, $sWord);
                });
            }
            
            $tagIds = array();
            
            if($query->count() > 0) {
                $tagIds = $query->get()->map(function($tag){
                    return $tag->id;
                })->all();
            }
            
            //get Item by tag id
            $itemIds = DB::table('tag_relations') ->whereIn('tag_id', $tagIds)->get()->map(function($tr){
                return $tr->item_id;
            })->all();
            
            //tag result
            $tagResults = DB::table('items')->whereIn('id', $itemIds);
            //Tag Search END ===============================================
            
            
            //Category Search ===============================================
            $table_name = 'categories';
            $query = DB::table($table_name);
            $columnArr = Schema::getColumnListing($table_name);
            $cateIds = array();
            
            foreach($searchWord as $sWord) {
                $sWord = "%".$sWord."%";
                
                $query ->orWhere( function($query) use($columnArr, $sWord) {
                    customQueryWhere($columnArr, $query, $sWord);
                });
            }
            
            if($query->count() > 0) {
                $cateIds = $query->get()->map(function($obj){
                    return $obj->id;
                })->all();
            }
            
            
            //cate result
            $cateResults = DB::table('items')->whereIn('cate_id', $cateIds);
            //Category Search END===============================================
            
            
            //CategorySecond Search ===============================================
            $table_name = 'category_seconds';
            $query = DB::table($table_name);
            $columnArr = Schema::getColumnListing($table_name);
            $subCateIds = array();
            
            foreach($searchWord as $sWord) {
                $sWord = "%".$sWord."%";
                
                $query ->orWhere( function($query) use($columnArr, $sWord) {
                    customQueryWhere($columnArr, $query, $sWord);
                });
            }
            
            if($query->count() > 0) {
                $subCateIds = $query->get()->map(function($obj){
                    return $obj->id;
                })->all();
            }
            
            //cate result
            $cateSecResults = DB::table('items')->whereIn('subcate_id', $subCateIds);
            //CategorySecond Search END===============================================
            
//            print_r($subCateIds);
//            exit;
            
            //Item search =======================================================
            $itemQuery = DB::table('items');
            $columnArr = Schema::getColumnListing('items');
            
            foreach($searchWord as $sWord) {
                $sWord = "%".$sWord."%";
                
                $itemQuery->where( function($qry) use($columnArr, $sWord) { //ここをorWhereにすればAND検索になりそう
                    customQueryWhere($columnArr, $qry, $sWord);
                });
            }
            
//            print_r($itemQuery->get()->all());
//            exit;
                
            //union使用（結合）なのでコレクションにする必要がある（paginationが使えない）
            //$allResults = $first->union($second)->union($atclQuery)->get()->where('open_status', 1)->all();
                
        }
        else { //1word検索
            $sWord = "%".$searchWord."%";
            
            //Tag search ==============================
            $table_name = 'tags';
	        $query = DB::table($table_name); //query取得
            $arr = Schema::getColumnListing($table_name); //これでカラム取得が出来る
            
            $tagIds = array();
            
            customQueryWhere($arr, $query, $sWord);
            
            if($query->count() > 0) {
                $tagIds = $query->get()->map(function($tag){
                    return $tag->id;
                })->all();
            }
            
            //tag search result
            $itemIds = DB::table('tag_relations') ->whereIn('tag_id', $tagIds)->get()->map(function($tr){
                return $tr->item_id;
            })->all();
            
            $tagResults = DB::table('items')->whereIn('id', $itemIds);
            //Tag search END ==============================
            
            
            //Category Search ==============================
            $table_name = 'categories';
            $query = DB::table($table_name);
            $columnArr = Schema::getColumnListing($table_name); //これでカラムネームが取得が出来る
            
            $cateIds = array();
            
            customQueryWhere($columnArr, $query, $sWord);
            
            if($query->count() > 0) {
                $cateIds = $query->get()->map(function($obj){
                    return $obj->id;
                })->all();
            }
            
            
            $cateResults = DB::table('items')->whereIn('cate_id', $cateIds);
            //Category Search END ==============================
            
            
            //CategorySecond Search ==============================
            $table_name = 'category_seconds';
            $query = DB::table($table_name);
            $columnArr = Schema::getColumnListing($table_name); //これでカラムネームが取得が出来る
            
            $subCateIds = array();
            
            customQueryWhere($columnArr, $query, $sWord);
            
            if($query->count() > 0) {
                $subCateIds = $query->get()->map(function($obj){
                    return $obj->id;
                })->all();
            }
            
            $cateSecResults = DB::table('items')->whereIn('subcate_id', $subCateIds);
            //CategorySecond Search END ==============================
            
            
            //Item Search ============================
            $itemQuery = DB::table('items');
            $columnArr = Schema::getColumnListing('items');
            customQueryWhere($columnArr, $itemQuery, $sWord);
            //Item Search END ============================
            
            //$atclQuery->where('open_status',1);
            
            //$allResults = $first->union($second)->union($atclQuery)->get()->where('open_status', 1)->all();
            
        } //1word Else
        
        
        //All Result: union使用（結合）なのでコレクションにする必要がある（paginationが使えない）
        //union()->where()が効かない
        //$allResults = $first->union($second)->union($atclQuery)->get()->where('open_status', 1)->all();
        
        /* ORG *****
        $allResults = $first->union($second)->union($atclQuery)->get()
                        ->sortByDesc('open_date')
                        ->map(function($item){
                            if($item->del_status == 0 && $item->open_status == 1 && $item->owner_id > 0) {
                                return $item;
                            }
                        })
                        ->all();
        //print_r($allResults);
        
        $allResults = array_filter($allResults); //空要素を削除
        $allResults = array_merge($allResults); //indexを振り直す
        ***** ORG END */
        
        $allResIds = $tagResults->union($cateResults)->union($cateSecResults)->union($itemQuery)->get()->map(function($item){
            return $item->id;
        })->all();
        
//        print_r($allResIds);
//        exit();
        
        
        //$count = $query->count();
        //$pages = $query->paginate($this->pg);
        //$pages -> appends(['s' => $search]); //paginateのヘルパー：urlを付ける
        
        $search = $this->searchWord;
        //return compact('allResults', 'search');
        return compact('allResIds', 'search'); //return ['allResIds'=>$allResIds, 'search'=>$this->searchWord];
        //return [$pages, $search];
    
    }
    
    
    
    
    
    

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        //
    }

    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        //
    }

    public function destroy($id)
    {
        //
    }

}

