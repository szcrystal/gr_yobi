<?php
use App\Setting;
use App\Item;
use App\Category;
use App\CategorySecond;
use App\Favorite;
use App\FavoriteCookie;
use App\Icon;
?>

<?php
	$isCate = (isset($type) && $type == 'category') ? 1 : 0; //categoryページならの判別
		
    $category = Category::find($cateSec->parent_id);

    $cateLink = url('/category/'. $category->slug . '/' . $cateSec->slug);
    ///$linkName = isset($category->link_name) ? $category->link_name : $category->name;
    
    
//    if( $category->id == 1 && isset($item->subcate_id) && $item->subcate_id != '') {
//        $subCate = CategorySecond::find($item->subcate_id);
//        $cateLink = url('category/'. $category->slug . '/' . $subCate->slug);
//        $cateName = isset($subCate->link_name) ? $subCate->link_name : $subCate->name;
//    }
//    else {
//        $cateLink = url('category/'. $category->slug);
//        $cateName = isset($category->link_name) ? $category->link_name : $category->name;
//    }

    $isSp = Ctm::isAgent('sp');
    $isSale = Setting::get()->first()->is_sale;
    $salePer = Setting::get()->first()->sale_per;
    
    $imgClass = '';
    
    //pot売り切れ判定
    $potsArr = Ctm::isPotParentAndStock($item->id); //親ポットか、Stockあるか、その子ポットのObjsを取る
    
    $isStock = $cateSec->is_stock; //pot親でない時は通常Itemの在庫を見る
   
?>

@if(isset($item->sale_price))
<span class="sale-belt">SALE</span>
@else
    @if($isSale && $salePer)
        <span class="sale-belt">{{ $salePer }}％OFF</span>
    @endif
@endif


<div class="img-box">
	@if(! $isStock)
    	<?php $imgClass = 'stock-zero'; ?>
    	<span>SOLD OUT</span>
    @endif

    @if(isset($cateSec->main_img))
        <a href="{{ $cateLink }}">
            <img src="{{ Storage::url($cateSec->main_img) }}" alt="{{ $cateSec->title }}" class="{{ $imgClass }}">
        </a>
    @else
        <div style="width:100%; height: 215px; line-height:215px;" class="bg-light">No Image</div>
    @endif
</div>

<div class="meta">
	<?php
    	//特殊な時のみ@includeの引数でセットする
    	if(! isset($strNum)) 
        	$strNum = Ctm::isAgent('sp') ? 22 : 25;
    ?>
    
    <h3><a href="{{ $cateLink }}">
        {{ Ctm::shortStr($cateSec->name, $strNum) }}
    </a></h3>
    
    <p>
        <a href="{{ url('category/' . $category->slug) }}">{{ $category->name }}</a>
    </p>
    
    {{--
    @if(isset($item->icon_id) && $item->icon_id != '')
        <div class="icons">
            @include('main.shared.icon', ['obj'=>$item])
        </div>
    @endif


    <div class="tags">
        @include('main.shared.tag', ['num'=>2])
    </div>
    --}}       
        
    <div class="price">
        <?php
            $isPotParent = $potsArr['isPotParent'];
            $thisItem = $item;
            
            if($isPotParent) {
                $thisItem = $potsArr['pots']->sortBy('price')->first();
            }
        ?>
        
        @if($isSale || isset($thisItem->sale_price))
            @if(! $isSp)
                <strike>{{ number_format(Ctm::getPriceWithTax($thisItem->price)) }}</strike>
                <i class="fal fa-arrow-right text-small"></i>
            @endif
        @endif
        
        @if(isset($thisItem->sale_price))
            <span class="show-price text-enji">{{ number_format(Ctm::getPriceWithTax($thisItem->sale_price)) }}
        @else
            @if($isSale)
                <span class="show-price text-enji">{{ number_format(Ctm::getSalePriceWithTax($thisItem->price)) }}
            @else
                <span class="show-price">{{ number_format(Ctm::getPriceWithTax($thisItem->price)) }}
            @endif
        @endif
        </span>
        <span class="show-yen">円(税込)
        @if($isPotParent)
        〜
        @endif
        </span>
        
    </div>
    
	
    <div class="favorite">
    
        <?php
        	//お気に入り確認
            $isFav = 0;
            
            if(Auth::check()) {
                $fav = Favorite::where(['user_id'=>Auth::id(), 'item_id'=>$item->id])->first();
                if(isset($fav)) $isFav = 1;   
            }
            else { //Cookie確認
                $favKey = Cookie::get('fav_key');
				
                if(isset($favKey) && $favKey != '') {
                	$favCookie = FavoriteCookie::where(['key'=>$favKey, 'item_id'=>$item->id])->first();
                	if(isset($favCookie)) $isFav = 1;
                }
            }

            //if(Favorite::where(['user_id'=>Auth::id(), 'item_id'=>$item->id])->first()) {
            if($isFav) {
                $on = ' d-none';
                $off = ' d-inline'; 
                $str = 'お気に入りの商品です';              
            }
            else {
                $on = ' d-inline';
                $off = ' d-none';
                $str = 'お気に入りに登録';
            } 
        ?>

		{{--
        <span class="fav fav-on{{ $on }}" data-id="{{ $item->id }}"><i class="fal fa-heart"></i></span>
        <span class="fav fav-off{{ $off }}" data-id="{{ $item->id }}"><i class="fas fa-heart"></i></span>
        <span class="loader"><i class="fas fa-square"></i></span>
        <small class="fav-str"></small>
        --}}
    </div>


    {{-- <span class="fav-temp"><a href="{{ url('login') }}"><i class="far fa-heart"></i></a></span> --}}
    
    
</div>


