<?php

namespace App\Http\Controllers;

use App\Item;
use App\Category;
use App\CategorySecond;
use App\Tag;
use App\TagGroup;
use App\TagRelation;
use App\Fix;
use App\TotalizeAll;
use App\Setting;
use App\MailTemplate;
use App\Sale;
use App\SaleRelation;

use App\ItemUpper;
use App\ItemUpperRelation;

use Mail;
use DateTime;
use Auth;
use Ctm;
use Cookie;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class CustomController extends Controller
{
	public $setting;
	
    public function __construct(Setting $setting, Item $item, Category $category, Tag $tag)
    {
    	$this->setting = $setting;
    	$this->item = $item;
        $this->category = $category;
        $this->tag = $tag;
        //$this->totalizeAll = $totalizeAll;
        
	}
    
    
    static function changeDate($arg, $rel=0)
    {
    	if(!$rel)
	        return date('Y/m/d H:i', strtotime($arg));
        else
        	return date('Y/m/d', strtotime($arg));
    }
    
    static function getPriceWithTax($price)
    {
    	$tax_per = Setting::get()->first()->tax_per;
     	
        $tax = $price * $tax_per/100;   
     	$price = floor($price + $tax);
      	
        return $price;      
    }
    
    static function getSalePriceWithTax($price)
    {
    	//$orgPrice = Self::getPriceWithTax($price);
        
        $salePer = Setting::get()->first()->sale_per;
        $tax_per = Setting::get()->first()->tax_per;
        
        //1円の時のSale計算は矛盾が出るので除外
        if($price > 1) {
            $waribiki = $price * ($salePer/100);
            $price = $price - $waribiki;
        }
        
        $tax = $price * $tax_per/100;   
     	$price = floor($price + $tax); //最終的な金額にfloorをしないとsaleの時で小数点が出る
        
//        echo $tax . "/" . $price;
//        exit;
        
        return $price;
        
    }
    
    static function getItemPrice($obj)
    {
    	$isSale = Setting::get()->first()->is_sale;
        
        //$itemPrice = isset($obj->is_once_down) ? $obj->once_price : (isset($obj->sale_price) ? $obj->sale_price : $obj->price);
                                                    
        if(isset($obj->sale_price)) {
            $price = number_format(CustomController::getPriceWithTax($obj->sale_price));
        }
        else {
            if($isSale)
                $price = number_format(CustomController::getSalePriceWithTax($obj->price));
            else
                $price = number_format(CustomController::getPriceWithTax($obj->price));
        }
        
        return $price;
    }
    
    static function getFixPage()
    {
        $set = Setting::get()->first();
        
        $fixArr['fixNeeds'] = array();
        $fixArr['fixOthers'] = array();
        
        $needIds = array();
        $otherIds = array();
        
        if(isset($set->fix_need)) {
        	$needIds = explode(',', $set->fix_need);
            $fixArr['fixNeeds'] = Fix::whereIn('id', $needIds)->where('open_status', 1)->orderByRaw("FIELD(id, $set->fix_need)")->get(); //orderByRowで配列の順番通りにする
        }
        
        if(isset($set->fix_other)) {
        	$otherIds = explode(',', $set->fix_other);
            $fixArr['fixOthers'] = Fix::whereIn('id', $otherIds)->where('open_status', 1)->orderByRaw("FIELD(id, $set->fix_other)")->get();
        }

        return $fixArr;
    }
    
    static function getTags($itemId, $num=0)
    {
        $tagIds = TagRelation::where('item_id', $itemId)->get()->map(function($obj){
            return $obj->tag_id;
        })->all();
        
        $strs = '"'. implode('","', $tagIds) .'"';

        
        if($num)
        	$tags = Tag::whereIn('id', $tagIds)->orderByRaw("FIELD(id, $strs)")->take($num)->get();
        else
        	$tags = Tag::whereIn('id', $tagIds)->orderByRaw("FIELD(id, $strs)")->get();

		
        return $tags;

    }
    
    static function getItemTitle($item, $isHtml = 0)
    {
    	$itemTitle = '';
        
    	if($item->is_potset && $item->pot_parent_id) {
            $itemTitle = Item::find($item->pot_parent_id)->title . '／'/* . "<b class=\"text-danger\">" . $item->title . "</b>"*/;
            $itemTitle .= $isHtml ? '<b class="text-danger">' . $item->title . '</b>' : $item->title;
        }
        else {
            $itemTitle = $item->title;
        }
        
        return $itemTitle;
        
    }
    
    
//    static function getHeaderTitle($type)
//    {
//    	$title = '';
//        
//    	@if($type == 'category') {
//        	$title = Category::->name;
//        }
//                    @elseif($type == 'subcategory')
//                    	<small class="d-block pb-2">{{ $cate->name }}</small>{{ $subcate->name }}
//                    @elseif($type == 'tag')
//                    	タグ：{{ $tag->name }}
//                    @elseif($type=='search')
//                        @if(!count($items))
//                        検索ワード：{{ $searchStr }}の記事がありません
//                        @else
//                        検索ワード：{{ $searchStr }}
//                        @endif
//                    @endif
//    }
    
    
    static function getArgForView($slug, $type)
    {
//    	$posts = Article::where('open_status', 1)
//               ->orderBy('open_date', 'desc')
//               ->take(30)
//               ->get();

//        foreach($rankObjs as $obj) {
//        	$objId[] = $obj->post_id;
//            //$rankObj[] = $this->articlePost->find($obj->post_id);
//        }
//    
//    	$ranks = $this->articlePost ->find($objId)->where('open_status', 1)->take(20);
        
        //非Openのグループidを取る
//        $tgIds = TagGroup::where('open_status', 0)->get()->map(function($tg){
//            return $tg->id;
//        })->all();
//        
//        //人気タグ
//        $tagLeftRanks = Tag::whereNotIn('group_id', $tgIds)->where('view_count','>',0)->orderBy('view_count', 'desc')->take(10)->get();
//        
//        //Category
//        $cateLeft = Category::all(); //open_status
		
        $rightRanks = '';
        
        //TOP20
        if($type == 'tag') {
        	$tag = Tag::where('slug', $slug)->first();
            $atclIds = TagRelation::where('tag_id', $tag->id)->get()->map(function($tr){
            	$atcl = Article::find($tr->atcl_id);
                if($atcl) {
                    if($atcl->open_status && ! $atcl->del_status && $atcl->owner_id > 0) {
                        return $tr->atcl_id;
                    }
                }
            })->all();
            
            $rightRanks = TotalizeAll::whereIn('atcl_id', $atclIds)->orderBy('total_count', 'desc')->take(20)->get();

        }
        else if($type == 'cate') {
        	$cate = Category::where('slug', $slug)->first();
        	
            $atclIds = Article::where(['open_status'=>1, 'del_status'=>0, 'cate_id'=>$cate->id])->whereNotIn('owner_id', [0])
                ->get()->map(function($al){
                    return $al->id;
                })->all();
            
            $rightRanks = TotalizeAll::whereIn('atcl_id', $atclIds)->orderBy('total_count', 'desc')->take(20)->get();
            
        }
        else { //all
            $atclIds = Article::where([
                ['open_status','=',1], ['del_status', '=', '0'], ['owner_id', '>', '0']
            ])
            ->get()->map(function($al){
                return $al->id;
            })->all();
            
            $rightRanks = TotalizeAll::whereIn('atcl_id', $atclIds)->orderBy('total_count', 'desc')->take(20)->get();

        }
        
        //return compact('tagLeftRanks', 'cateLeft', 'rightRanks');
        return $rightRanks;
    }
    
    static function getLeftbar() {
    	//非Openのグループidを取る
//        $tgIds = TagGroup::where('open_status', 0)->get()->map(function($tg){
//            return $tg->id;
//        })->all();
        
        //人気タグ
        $tagLeftRanks = Tag::where('view_count','>',0)->orderBy('view_count', 'desc')->take(10)->get();
        
        //Category
        //$cateLeft = Category::all(); //open_status
        
        return compact('tagLeftRanks', 'cateLeft');
    }
    
    static function shortStr($str, $length)
    {
    	if(mb_strlen($str) > $length) {
        	$continue = '…';
            $str = mb_substr($str, 0, $length);
            $str = $str . $continue;
        }

        return $str;
    }
    
    
    static function fixList()
    {
    	$fixes = Fix::where('not_open', 0)->get();
        
        return $fixes;
    }
    
    static function isOld()
    {
    	return count(old()) > 0;
    }
    
    static function isOldSelected($name, $obj, $objs)
    {
    	$selected = '';
        if(CustomController::isOld()) {
        	if(old($name) == $obj)
            	$selected = ' selected';
        }
        else {
        	if(isset($objs) && $objs->$name == $obj) {
            	$selected = ' selected';
            }
        }
        
        return $selected;
    }
    
    static function isOldChecked($name, $objs)
    {
    	$checked = '';
        if(CustomController::isOld()) {
        	if(old($name))
            	$checked = ' checked';
        }
        else {
        	//isset($article) && $article->del_status
        	if(isset($objs) && $objs->$name) {
            	$checked = ' checked';
            }
        }
        
        return $checked;
    }
    
    
    //郵便番号の出力
    static function getPostNum($post_code)
    {
    	$post_code = str_pad($post_code, 7, 0, STR_PAD_LEFT); //0埋め
    	return preg_replace("/^(\d{3})(\d{4})$/", "$1-$2", $post_code);
    }
    
    //注文番号 OrderNumberの作成
    static function getOrderNum($length) {
        //$eng = array_merge(range('a', 'z'), range('A', 'Z'));
        $eng = array_merge(range('a', 'z'));
        $num = array_merge(range('0', '9'));
        
        $alphaNum = $length > 12 ? 5 : 3; //13桁以上ならアルファベットを5文字にする
        $intNum = $length - $alphaNum;
        
        $r_str = null;
        
        //アルファベット部分
        for ($i = 0; $i < $alphaNum; $i++) {
            $r_str .= $eng[mt_rand(0, count($eng) - 1)];
        }
        
        //$r_str .= '-';
        //整数部分
        for ($n = 0; $n < $intNum; $n++) {
            $r_str .= $num[mt_rand(0, count($num) - 1)];
        }
        
        return $r_str;
        
    }
    
    //枯れ保証期間の書き出し
    static function getKareHosyou($deliDate)
    {
    	$kareDay = Setting::get()->first()->kare_ensure; 
        //$kareDay += 1;
        
        $limit = strtotime($deliDate." +" . $kareDay . " day");

        $limitDay = new DateTime(date('Y-m-d', $limit));
        $current = new DateTime(date('Y-m-d', time())); //DateTime('now')
        
        $diff = $current->diff($limitDay);
        //echo $diff->days;
            
//                    $limit = $limit - strtotime("now");  
//                     $days = (strtotime('Y-m-d', $limit) - strtotime("1970-01-01")) / 86400;   
  
    	return ['limit'=>date('Y/m/d', $limit), 'diffDay'=>$diff->days];
    }
    
    static function getDateWithYoubi($dateNormalFormat)
    {
    	$week = ['日', '月', '火', '水', '木', '金', '土'];
        /*
        $time = strtotime($dateNormalFormat);
        $withYoubi = date('Y/m/d', $time);
        $withYoubi .= ' (' . $week[date('w', $time)] . ')';
        */
		
        $time = new DateTime($dateNormalFormat);
        
        $ymd = $time->format('Y/m/d');
        $w = $time->format('w');
        
        $withYoubi = $ymd . ' (' . $week[$w] . ')';        
        
        return $withYoubi;
    }
    
    
    //UpperContentsの書き出しを配列で
    static function getUpperArr($parentId, $type)
    {
    	//ItemUpper
        $upperRels = null;
        $upperRelArr = array();
        $isMore = 0;
        
        $upper = ItemUpper::where(['parent_id'=>$parentId, 'type_code'=>$type, 'open_status'=>1])->first();
		
        if(isset($upper)) {
        	
            $isMore = $upper->is_more;
            
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
        
        return ['isMore'=>$isMore, 'contents'=>$upperRelArr];
    }
    
    
    //親ポットの在庫Stock判定
    static function isPotParentAndStock($itemId)
    {
        $isPotParent = 0; //このitemがpotParentなら、1
    	$isStock = 0; //このpotParentの子供ポットの在庫が全て0なら、0
        
    	$pots = Item::where(['open_status'=>1, 'is_potset'=>1, 'pot_parent_id'=>$itemId])->get();
    
        if($pots->isNotEmpty()) {
            foreach($pots as $pot) {
                if($pot->stock) {
                    $isStock = 1;
                    break;
                }
            }
            
            $isPotParent = 1;
        }
        
        return ['isPotParent'=>$isPotParent, 'isStock'=>$isStock, 'pots'=>$pots];
    }
    
    //代引き手数料計算
    static function daibikiCodFee($totalFee)
    {
    	$codFee = 0;
        $taxPer = Setting::first()->tax_per / 100;
        
    	if($totalFee <= 10000) {
            $codFee = 300;
        }
        elseif ($totalFee >= 10001 && $totalFee <= 30000) {
            $codFee = 400;
        }
        elseif ($totalFee >= 30001 && $totalFee <= 100000) {
            $codFee = 600;
        }
        elseif ($totalFee >= 100001 && $totalFee <= 300000) {
            $codFee = 1000;
        }
        elseif ($totalFee >= 300001 && $totalFee <= 500000) {
            $codFee = 2000;
        }
        elseif ($totalFee >= 500001 && $totalFee <= 1000000) {
            $codFee = 3000;
        }
        elseif ($totalFee >= 1000001 && $totalFee <= 999999999) {
            $codFee = 4000;
        }
        
        $codFee += $codFee * $taxPer;
        
        return $codFee;
    }
    
    static function isSeinouSunday($planDate)
    {
    	return isset($planDate) && strpos($planDate, '（日）') !== false;
    }
    
    static function getSeinouObj()
    {
    	//西濃運輸のIDもここで
    	$seinouId = 11;
        
        //taxPer
        $taxPer = Setting::first()->tax_per / 100;
        
    	//西濃に対する加減算の金額は消費税対象となる
        $seinouHuzaiokiFee = 3000;
        $seinouSundayFee = 1000;

		$seinouHuzaiokiFee += $seinouHuzaiokiFee * $taxPer;
        $seinouSundayFee += $seinouSundayFee * $taxPer;
        
        
        return (object) [
        	'id' => $seinouId,
        	'huzaiokiFee' => $seinouHuzaiokiFee,
            'sundayFee' => $seinouSundayFee,
            //"isSunday" => CustomController::isSeinouSunday,
        ];
    }
    
    
    static function getRankObj($cateId = null)
    {
        $rankTerm = Setting::first()->rank_term;
        
        $now = new DateTime();
        $sepDay = $now->modify('-' . $rankTerm . ' days')->format('Y-m-d');
        
        if(isset($cateId)) {
            $cateAr = ['cate_id', '=', $cateId];
        }
        else {
            $cateAr = ['cate_id', '>', 1];
        }
        
        $items = Item::where(['open_status'=>1, 'is_potset'=>0, $cateAr ])->get();
        //$itemAr = array();
        
        foreach($items as $k => $item) {
            $sum = 0;
            $saleCount = 0;
            $sale = null;
            //$stock = 0;
        
            //まずは親ポットかどうかを判定する
            //extract(Ctm::isPotParentAndStock($item->id));
            $isPotParent = $item->pot_parent_id === 0 ? 1 : 0;
            
            
            if($isPotParent) {
                $potIds = Item::where(['is_potset'=>1, 'pot_parent_id'=>$item->id])->get()->map(function($obj){
                    return $obj->id;
                })->all();
                
                $sale = Sale::whereIn('item_id', $potIds);
                //$sales = Sale::whereIn('item_id', $potIds)->whereDate('created_at', '>' , $sepDay)->get();
            }
            else {
                $sale = Sale::where('item_id', $item->id);
                //$sales = Sale::where('item_id', $item->id)->whereDate('created_at', '>' , $sepDay)->get();
            }
            
            $sales = $sale->whereDate('created_at', '>' , $sepDay)->get();
            
            if($sales->isNotEmpty()) {
                $sum = $sales->sum('total_price');
                $saleCount = $sales->sum('sale_count');
            }
            
            $item->total_price = $sum;
            $item->sale_count = $saleCount;
            //$item->is_stock = $stock ? 1 : 0; //Stockはindex表示の時に判定するのでここではセットしていない
            
            $items[$k] = $item;
            //$itemAr[] = $item;
            
        }
            
        $sorted = $items->sortByDesc('total_price');
        //$sorted = collect($itemAr)->sortByDesc('total_price');
        
        return $sorted;

    }
    
    
    static function getUekiSecObj()
    {
        $rankTerm = Setting::first()->rank_term_ueki;
        
        $now = new DateTime();
        $sepDay = $now->modify('-' . $rankTerm . ' days')->format('Y-m-d');

        $cateUekis = CategorySecond::where('parent_id', 1)->get();
        //$provAr = array();
        
        foreach($cateUekis as $k => $cateUeki) {
            
            //Rankingに関しては、open_status=>1以外も含めて集計するようにしている
            $targetItems = Item::where([/*'open_status'=>1, 'is_potset'=>0, */'subcate_id'=>$cateUeki->id])->get();
            
            //SalesDBは子ポットのIDでDBセットされているので、子ポットと通常商品のIDを取得する
            $itemIds = $targetItems->map(function($obj){
                if($obj->pot_parent_id === 0) {
                    $pots = Item::where(['is_potset'=>1, 'pot_parent_id'=>$obj->id])->get(); //->each()を使うとなぜかうまくいかない
                    
                    foreach($pots as $pot)
                        return $pot->id;
                }
                else {
                    return $obj->id;
                }
            })->all();
            
//            print_r($itemIds);
//            echo(count($itemIds));
            //exit;
            
            //親ポットを除いたこの子カテゴリーの属する商品
            $items = Item::find($itemIds);

            //pot親を除いた最小Priceを取得する
            //$noPotItems = $items->sortBy('price')->first();
            //echo $noPotItems->price;
            //print_r($noPotItems->values()->all());
            //exit;
            
            $sum = 0;
            $saleCount = 0;
            $stock = 0;
            
            $sales = Sale::whereIn('item_id', $itemIds)->whereDate('created_at', '>' , $sepDay)->get();
            
            $cateUeki->total_price = $sales->sum('total_price');
            $cateUeki->sale_count = $sales->sum('sale_count');
            
            $cateUeki->min_price = $items->min('price');
            $cateUeki->min_sale_price = $items->whereNotIn('sale_price', [null, 0])->min('sale_price');
            
            $cateUeki->is_stock = $items->sum('stock') ? 1 : 0;
            
            $cateUekis[$k] = $cateUeki;
            //$provAr[] = $cateUeki;
            
            
            /*
            //$cateUeki->min_price_item = $noPotItems;
            
            foreach($items as $item) {
                $sales = Sale::where('item_id', $item->id)->whereDate('created_at', '>' , $sepDay)->get();
                
                if($sales->isNotEmpty()) {
                    $sum += $sales->sum('total_price');
                    $saleCount += $sales->sum('sale_count');
                }
                
                //Stockを設定する（open_status 1のみ）
                $stock += $item->stock;
                
//                if($item->is_potset) {
//                    //extract(Ctm::isPotParentAndStock($item->pot_parent_id)); //[$isPotParent, $isStock, $pots] open_statusはこの関数の中で設定されている
//
//                    if($item->pot_parent_id === 0 && $item->stock)
//                        $stock += $item->stock;
//                }
//                else {
//                    if($item->open_status == 1 && $item->pot_parent_id !== 0)
//                        $stock += $item->stock;
//                }
                
            }
            
            
            $cateUeki->total_price = $sum;
            $cateUeki->sale_count = $saleCount;
            $cateUeki->is_stock = $stock ? 1 : 0;
            
            $cateUeki->min_price_item = $noPotItems;
            //$cateUeki->min_price = $noPotItems->price;
            
            $cateUekis[$k] = $cateUeki;
            //$cateSecSum[$cateUeki->id] = Item::where('subcate_id', $cateUeki->id)->sum('sale_count');
        */
        }
        
        $sorted = $cateUekis->sortByDesc('total_price');
        //$sorted = collect($provAr)->sortByDesc('total_price');
        
//        arsort($cateSecSum); //降順Sort
//        $cateSecSum = array_keys($cateSecSum);
//
//        $cateSecIds = implode(',', $cateSecSum);
        
//        $uekiSecObj = CategorySecond::whereIn('id', $cateSecSum)->orderByRaw("FIELD(id, $cateSecIds)")->get();
        
        return $sorted;
    }
    
    //最近チェックしたのCookie => ! 現在使用していない !
    static function getCookieItems($separateNum, $itemId = null)
    {
        //Cookie 最近チェックしたアイテム　最近見た CacheではなくCookieなので注意===================
        
        $cookieArr = array();
        $cookieItems = null;
        
        $getNum = $separateNum;
        $whereArr = ['open_status'=>1, 'is_potset'=>0];
        //$getNum = Ctm::isAgent('sp') ? 8 : 8;
        
        $itemId = isset($itemId) ? $itemId : 0;
        
        $cookieIds = Cookie::get('item_ids');
//        echo $cookieIds;
//        exit;
        
        if(isset($cookieIds) && $cookieIds != '') {
            $cookieArr = explode(',', $cookieIds);
            
            $chunkNum = Ctm::isAgent('sp') ? $getNum/2 : $getNum;
            
            //Viewに渡すItems
            $cookieItems = Item::whereIn('id', $cookieArr)->whereNotIn('id', [$itemId])->where($whereArr)->orderByRaw("FIELD(id, $cookieIds)")->take($getNum)->get()->chunk($chunkNum);
        }
        
        if(! in_array($itemId, $cookieArr)) { //配列にidがない時 or cachIdsが空の時
            $count = array_unshift($cookieArr, $itemId); //配列の最初に追加
            
            if($count > 16) {
                $cookieArr = array_slice($cookieArr, 0, 16); //16個分を切り取る
            }
        }
        else { //配列にidがある時
            $index = array_search($itemId, $cookieArr); //key取得
            
            //$split = array_splice($cacheIds, $index, 1); //keyからその要素を削除
            unset($cookieArr[$index]);
            $cookieArr = array_values($cookieArr);
            
            $count = array_unshift($cookieArr, $itemId); //配列の最初に追加
        }
        
        $cookieIds = implode(',', $cookieArr);
        
        Cookie::queue(Cookie::make('item_ids', $cookieIds, config('app.cookie_time') )); //43200->1ヶ月 appにcookie_timeをセットしているが、設定変更後artisan config:cacheをする必要があるので直接時間指定した方がいいのかもしれない
        
        return $cookieItems;
    }
    
    static function customPaginate($itemAll, $perPage, $request)
    {
    	//配列を1ページに表示する件数分分割する
        $chunkData = array_chunk($itemAll, $perPage);

        //ページがnullの場合は1を設定
        $currentPageNum = $request->query('page') ? $request->query('page') : 1;
        
//        if (is_null($currentPageNum)) 
//            $currentPageNum = 1;
		
        
        return new LengthAwarePaginator(
            $chunkData[$currentPageNum-1], //該当ページに表示するデータ
            count($itemAll), //全件数
            $perPage, //1ページに表示する数
            $currentPageNum, //現在のページ番号
            ['path' => $request->path()] //URLをオプションとして設定
        );
    }
    
//    static function getPointBack($item) {
//        
//        //商品に入力されているポイント還元率が最優先
//        //$setting = $this->setting->get()->first();
//        $pointBack = 0;
//        
//        if(isset($item->point_back)) {
//            $pointBack = $item->point_back / 100;
//        }
//        else {
//            if($this->set->is_point) {
//                $pointBack = $this->set->point_per / 100;
//            }
//        }
//        
//        return $pointBack;
//    }

    
    
    //管理画面：管理者権限の判定
    static function checkRole($roleName)
    {
    	$per = Auth::guard('admin')->user()->permission;
        
        //$ret = $view ? true : view('erroes.dashboard');
        
        if(
        	$roleName == 'isSuper' && $per < 5 ||
        	$roleName == 'isAdmin' && $per < 10 ||
            $roleName == 'isDesigner' && $per == 10
        ) {
        	return true;
        }
        
        else {
        	return false;
        }
        
        
    }
    
    static function gmoId()
    {
    	// 変更する場合は、script.js内にも1カ所あるので注意******* 
        // jsの読み込み部分（app.blade.phpの末尾）も本番orNotで別れているか確認 	
        
        if(Setting::get()->first()->is_product) { //本番
        	return [
            	'siteId' =>'mst2000018199',
                'sitePass' => 'ahek2dxr',
                'shopId' =>'9200000204151',
                'shopPass' => '93ry4brr',
        	];	
        }
        else { //テスト
        	return [
            	'siteId' =>'tsite00032753',
                'sitePass' => 'uu6xvemh',
                'shopId' =>'tshop00036826',
                'shopPass' => 'bgx3a3xf',
        	];
        }
    	
    }
    
    static function cUrlFunc($connectUrl, $datas)
    {
    	$productUrl = "https://p01.mul-pay.jp/payment/";
        $testUrl = "https://pt01.mul-pay.jp/payment/";
        
    	$url = Setting::get()->first()->is_product ? $productUrl : $testUrl; 
    	
        $options = [
            CURLOPT_URL => $url . $connectUrl,
            CURLOPT_RETURNTRANSFER => true, //文字列として返す
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($datas),
            CURLOPT_TIMEOUT => 120, // タイムアウト時間
        ];
        
        //curl init
        $ch = curl_init();
        
        //setOption
        curl_setopt_array($ch, $options);
        
        //response
        $response = curl_exec($ch);
        
        //close
        curl_close($ch);
        
        return $response;
    }
    
    
    static function sendMail($data, $typeCode)
    {
    	$set = Setting::get()->first();
     	$templ = MailTemplate::where(['type_code'=>$typeCode])->first();   
      
//         echo $set->admin_email;
//        exit;      
        
        $data['is_user'] = 1; //引数について　http://readouble.com/laravel/5/1/ja/mail.html
        Mail::send('emails.'. $templ->type_code, $data, function($message) use ($data, $set, $templ) 
        {
            //$dataは連想配列としてviewに渡され、その配列のkey名を変数としてview内で取得出来る
            $message -> from($set->admin_email, $set->admin_name)
                     -> to($data['user']['email'], $data['user']['name'])
                     -> subject($template->title);
            //$message->attach($pathToFile);
            
        });
        
        //for Admin
        $data['is_user'] = 0;
        //if(! env('MAIL_CHECK', 0)) { //本番時 env('MAIL_CHECK')がfalseの時
        Mail::send('emails.'. $template->type_code, $data, function($message) use ($data)
        {
            $message -> from($set->admin_email, $set->admin_name)
                     -> to($set->admin_email, $set->admin_name)
                     -> subject($template->type_name .'がありました - '. config('app.name'). ' -');
        });
    }
    
    static function isAgent($agent)
    {
        $ua_sp = array('iPhone','iPod','Mobile ','Mobile;','Windows Phone','IEMobile');
        $ua_tab = array('iPad','Kindle','Sony Tablet','Nexus 7','Android Tablet');
        $all_agent = array_merge($ua_sp, $ua_tab);
        
        switch($agent) {
            case 'sp':
                $agent = $ua_sp;
                break;
        
            case 'tab':
                $agent = $ua_tab;
                break;
            
            case 'all':
                $agent = $all_agent;
                break;
                
            default:
                //$agent = '';
                break;
        }
           
        if(is_array($agent)) {
            $agent = implode('|', $agent);
        }
        
        return preg_match('/'. $agent .'/', $_SERVER['HTTP_USER_AGENT']);
    }
    
    static function isLocal()
    {
    	return config('app.env') == 'local';
    }
    
    static function isEnv($envName)
    {
    	return config('app.env') == $envName;
    }
    
}
