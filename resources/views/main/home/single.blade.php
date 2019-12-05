@extends('layouts.app')

<?php
use App\User;
use App\Category;
use App\DeliveryGroupRelation;
use App\Prefecture;
use App\Setting;
use App\TopSetting;
?>


@if(! Ctm::isAgent('sp'))
@section('belt')
<div class="tophead-wrap">
    <div class="clearfix">
        {!! nl2br(TopSetting::get()->first()->contents) !!}
    </div>
    
    @if(isset($isTop) && $isTop)
        @include('main.shared.carousel')
    @endif
</div>
@endsection
@endif



@section('content')

    <div id="main" class="single">
    	
        @if(! Ctm::isAgent('sp'))
        	@include('main.shared.bread')
        @endif
        
        
        @include('main.shared.upper')
        
        @if(isset($item->upper_title) || isset($item->upper_text))
            <div class="upper-introduce-wrap mb-4">
                @if(isset($item->upper_title) && $item->upper_title != '')
                    <h3 class="upper-title">{{ $item->upper_title }}</h3>
                @endif
                
                @if(isset($item->upper_text) && $item->upper_text != '')
                    <p class="upper-text px-1 m-0">{!! nl2br($item->upper_text) !!}</p>
                @endif
            
            </div>
        @endif
        
		
        <div class="head-frame clearfix">
            
            <div class="single-left">
            
            	<?php //================================================================= 
                	$itemId = $item->id;
                 	$itemTitle = $item->title;   
                ?>
                
                @if($item -> main_img)
                    @include('main.shared.sliderSingle')
                @else
                    <span class="no-img">No Image</span>
                @endif
            
            	<?php //END ================================================================= ?>    

			</div><!-- left -->
            
            @if(Ctm::isAgent('sp'))
				@include('main.shared.bread')
			@endif
			
            <div class="single-right">
            	
                <?php //================================================================= ?>
            		<span>{{ $item->title_addition }}</span>
                	<h2 class="single-title">{{ $itemTitle }}<br><span>商品番号 {{ $item->number }}</span></h2>
                 	<p class="text-big">{{ $item->catchcopy }}</p>
                    
                    <?php $isPotSet = count($potSets) > 0; ?>
                    
                    @if(! $isPotSet)
                        @if(isset($item->icon_id) && $item->icon_id != '')
                            <div class="icons">
                                @include('main.shared.icon', ['obj'=>$item])
                            </div>
                        @endif
                    @endif
                 	                    
                    
                    <form method="post" action="{{ url('shop/cart') }}">
                        {{ csrf_field() }}
                    

                    @if($isPotSet)
                    	
                        <?php 
                            //Object $itemを最初に追加する時
                            //$potSets->prepend($item);
                        ?>
						
                        <div class="potset-wrap form-wrap">
                        	 
                            @foreach($potSets as $potSet)
                                <div class="potset clearfix">
                                    @if(isset($potSet->main_img))
                                    <div class="img-box">
                                        <img src="{{ Storage::url($potSet->main_img) }}" class="img-fluid">
                                    </div>
                                    @endif
                                    
                                    <div class="potset-text">
                                        <h3>
                                            {{ $potSet->title }}
                                            @if(Ctm::isEnv('local'))
                                            	<small>[{{ $potSet->id }}]</small>
                                            @endif
                                        </h3>
                                        
                                        <div class="price-meta">
                                            <?php //$obj = $potSet; ?>
                                            @include('main.shared.priceMeta', ['obj'=>$potSet])
                                        </div>
                                        
                                        @if(isset($potSet->icon_id) && $potSet->icon_id != '')
                                            <div class="icons">
                                                <?php //$obj = $potSet; ?>
                                                @include('main.shared.icon', ['obj'=>$potSet])
                                            </div>
                                        @endif
                                        
                                        <div class="clearfix">
                                            
                                            @if($potSet->stock > 0)
                                                @if($potSet->stock_show)
                                                    <span>在庫：{{ $potSet->stock }}</span>
                                                @endif
                                                
                                                <div class="potSetSelect-wrap float-right">
                                                    <fieldset class="clearfix text-right">
                                                    <label>数量</label>
                                                    
                                                    <span class="select-wrap potSetSelect d-inline-block">
                                                    <select class="form-control d-inline{{ $errors->has('item_count') ? ' is-invalid' : '' }}" name="item_count[]">
                                                        <option value="0" selected>選択</option>
                                                            <?php
                                                                $max = 100;
                                                                if($potSet->stock < 100) {
                                                                    $max = $potSet->stock;
                                                                }
                                                            ?>
                                                            @for($ii=1; $ii <= $max; $ii++)
                                                                <?php
                                                                    $selected = '';
                                                                    if(Ctm::isOld()) {
                                                                        if(old('item_count') == $ii)
                                                                            $selected = ' selected';
                                                                    }
        //                                                            else {
        //                                                                if($i == 1) {
        //                                                                    $selected = ' selected';
        //                                                                }
        //                                                            }
                                                                ?>
                                                                <option value="{{ $ii }}"{{ $selected }}>{{ $ii }}</option>
                                                            @endfor
                                                    </select>
                                                    </span>
                                                    <span class="text-warning"></span>
                                                    
                                                    @if ($errors->has('item_count'))
                                                        <div class="help-block text-danger">
                                                            <span class="fa fa-exclamation form-control-feedback"></span>
                                                            <span>{{ $errors->first('item_count') }}</span>
                                                        </div>
                                                    @endif
                                                    
                                                    <input type="hidden" name="item_id[]" value="{{ $potSet->id }}">
                                                    
                                                    </fieldset>
                                                </div>
                                            @else
                                                <span class="text-danger text-small">在庫がありません</span>
                                                @if($potSet->stock_type)
                                                    <p class="d-inline text-small ml-1">
                                                        @if($potSet->stock_type == 1)
                                                            次回{{ $potSet->stock_reset_month }}月頃入荷予定
                                                        @else
                                                            次回入荷未定
                                                        @endif
                                                    </p>
                                                @endif
                                            @endif
                                                
                                        </div>
                                    
                                    </div>
                                </div>
                            @endforeach
  
                    	</div>   	
                    
                    @else
                        <div class="price-meta">
                            @include('main.shared.priceMeta', ['obj'=>$item])
                        </div>
                    @endif
                    
                    <div class="my-3 text-small">
                    	<p>{!! nl2br($item->exp_first) !!}</p>
                    </div>
                    

                    @if(! Auth::check())
                        <div class="pl-1">
                            {{-- <span class="fav-temp"><i class="far fa-heart"></i></span> --}}
                            <small class="p-0 m-0"><a href="{{ url('login') }}"><b>ログイン<i class="fal fa-angle-double-right"></i></b></a> すると永続してお気に入りに登録できます</small>
                        </div>
                    @endif
                    
                    <div class="favorite mt-1 mb-4" data-type='single'>
                        <?php
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

                        <span class="fav fav-on{{ $on }}" data-id="{{ $item->id }}"><i class="far fa-heart"></i></span>
                        <span class="fav fav-off{{ $off }}" data-id="{{ $item->id }}"><i class="fas fa-heart"></i></span>
                        
                        <small class="fav-str"><span class="loader"><i class="fas fa-square"></i></span>{{ $str }}</small> 
                    </div>
                    

                  	<div class="form-wrap">
                    	@if(! $isPotSet)
                  			
                            @if($item->stock > 0)
                                
                                <?php $seinouObj = Ctm::getSeinouObj(); ?>
                                
                                @if($item->dg_id == $seinouObj->id)
                                    
                                    <fieldset class="mb-2 form-group clearfix text-left">
                                    
                                        <div class="ml-1 mb-3 bg-white border border-gray py-2 px-3">
                                            <p class="mb-1">不在置きを了承頂ける場合はチェックをして下さい。</p>
                                            
                                            <ul class="text-small pl-4 mb-0">
                                                <li class="mb-1"><span class="text-big"><b class="text-big">チェック時は{{ number_format($seinouObj->huzaiokiFee) }}円引きとなります。</b></span></li>
                                                <li class="mb-1">購入中に表示される枠内に不在時の置き場所を記載して下さい。</li>
                                                <li class="mb-1">お支払い方法「代金引換」はご利用出来ません。</li>
                                            <ul>
                                        </div>
                                    
                                        <?php
                                            $checked = '';
                                            if(Ctm::isOld()) {
                                                if(old('is_huzaioki'))
                                                    $checked = ' checked';
                                            }
                                            else {
                                                if(Session::has('all.data.is_huzaioki')  && session('all.data.is_huzaioki')) {
                                                    $checked = ' checked';
                                                }
                                            }
                                        ?>
                                        
                                        <div class="mt-0 pt-0 float-right w-50">
                                            <input type="hidden" name="is_huzaioki" value="0">
                                            
                                            <input id="check-huzaioki-0" type="checkbox" name="is_huzaioki" value="1"{{ $checked }}>
                                            <label for="check-huzaioki-0" class="checks ml-1 mr-0"><b class="text-big">不在置きを了承する</b></label>
                                            
                                            @if ($errors->has('is_huzaioki'))
                                                <div class="help-block text-danger">
                                                    <span class="fa fa-exclamation form-control-feedback"></span>
                                                    <span>{{ $errors->first('is_huzaioki') }}</span>
                                                </div>
                                            @endif
                                        
                                            <input type="hidden" name="is_seinou" value="1">
                                        </div>
                                    </fieldset>
                                @endif

                                <fieldset class="mb-3 form-group clearfix text-right">
                                    <label>数量
                                    @if($item->stock_show)
                                        <span>（在庫：{{ $item->stock }}）</span>
                                    @endif
                                    </label>
                                    
                                    <span class="select-wrap d-inline-block w-50">
                                    <select class="form-control {{ $errors->has('item_count') ? ' is-invalid' : '' }}" name="item_count[]">
                                        <option disabled selected>選択して下さい</option>
                                            <?php
                                                $max = 100;
                                                if($item->stock < 100) {
                                                    $max = $item->stock;
                                                }
                                            ?>
                                            @for($i=1; $i <= $max; $i++)
                                                <?php
                                                    $selected = '';
                                                    if(Ctm::isOld()) {
                                                        if(old('item_count') == $i)
                                                            $selected = ' selected';
                                                    }
                                                    else {
                                                        if($i == 1) {
                                                            $selected = ' selected';
                                                        }
                                                    }
                                                ?>
                                                <option value="{{ $i }}"{{ $selected }}>{{ $i }}</option>
                                            @endfor
                                    </select>
                                    </span>
                                    <span class="text-warning"></span>
                                    
                                    @if ($errors->has('item_count'))
                                        <div class="help-block text-danger">
                                            <span class="fa fa-exclamation form-control-feedback"></span>
                                            <span>{{ $errors->first('item_count') }}</span>
                                        </div>
                                    @endif
                                    
                                    <input type="hidden" name="item_id[]" value="{{ $item->id }}">
                                </fieldset>
                	
                        	@else
                                <div class="no-stock">
                                    <span class="text-danger text-big">在庫がありません</span>
                                    @if($item->stock_type)
                                    <p>
                                        @if($item->stock_type == 1)
                                            次回{{ $item->stock_reset_month }}月頃入荷予定
                                        @else
                                            次回入荷未定
                                        @endif
                                    </p>
                                    @endif
                                </div>
                        	@endif
                        
                        @endif  
                  	 
                    
                    	@if($item->stock > 0 || $isPotSet)
                            <input type="hidden" name="from_item" value="1">
                            <input type="hidden" name="uri" value="category/{{ Category::find($item->cate_id)->slug }}">     
                            
                            <?php
                            	$disabled = $isPotSet ? ' disabled' : '';

                            ?>
                            
                            <button id="mainCartBtn" type="submit" class="btn btn-custom btn-mizuiro text-center col-md-12"{{ $disabled }}><i class="fal fa-cart-arrow-down"></i> カートに入れる</button>
                            
                            <p class=""><b>{{ $item->deli_plan_text }}</b></p>
                            
                            @if(Ctm::isAgent('sp'))
                                <button id="spCartBtn" type="submit" class="btn btn-custom btn-mizuiro text-center col-md-6"{{ $disabled }}><i class="fal fa-cart-arrow-down"></i> この商品をカートに入れる</button>
                            @endif
                        @endif
                        
                        
                   </form>
                   
                   </div><!-- form-wrap -->
                    
                    <div class="tags mt-4 mb-1">
                        <?php $num = 0; ?>
                        @include('main.shared.tag')
                    </div>
                    
                    <div class="clearfix mt-4">
                        @include('main.shared.socialBtn', ['title'=>$itemTitle, 'naming'=>'商品'])
                    </div>
                    
                    <?php
                    	$isCaution = isset($item->caution) && $item->caution != '';
                        $dgId = $item->dg_id;
                    ?>
                    
                    @if(Ctm::isAgent('sp'))
                    	<div id="accordion" class="mt-4">
                          <div class="card">
                            <div class="card-header" id="headingOne">
                                <a class="btn clearfix" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                  <i class="fal fa-info-circle"></i> 商品詳細
                                  <i class="fal fa-angle-down float-right"></i>
                                </a>
                            </div>

                            <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordion">
                              <div class="card-body clearfix">
                                {!! nl2br($item->explain) !!}
                              </div>
                            </div>
                          </div>
                          
                          <div class="card">
                            <div class="card-header" id="headingTwo">
                                <a class="btn clearfix collapsed" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                  <i class="fal fa-truck"></i> 配送について
                                  <i class="fal fa-angle-down float-right"></i>
                                </a>
                            </div>
                            <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordion">
                              <div class="card-body clearfix">
                                {!! nl2br($item->about_ship) !!}
                                
                                @if($item->is_delifee_table)
                                    <div class="btn btn-custom mt-2 slideDeli">
                                        送料表を見る <i class="fal fa-angle-down"></i>
                                    </div>
                                	
                                    @include('main.shared.deliFeeTable')
                                @endif
                              </div>
                            </div>
                          </div>
                          
                          <div class="card">
                            <div class="card-header" id="headingThree">
                                <a class="btn clearfix collapsed" data-toggle="collapse" data-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                  <i class="fal fa-tree-alt"></i> 育て方
                                  <i class="fal fa-angle-down float-right"></i>
                                </a>
                            </div>
                            
                            <div id="collapseThree" class="collapse" aria-labelledby="headingThree" data-parent="#accordion">
                              <div class="card-body clearfix">
                                {!! nl2br($item->contents) !!}
                              </div>
                            </div>
                          </div>

                        @if($isCaution)
                        	<div class="card">
                                <div class="card-header" id="headingFour">
                                    <a class="btn clearfix" data-toggle="collapse" data-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                      <i class="fal fa-exclamation-triangle"></i> ご注意下さい
                                      <i class="fal fa-angle-down float-right"></i>
                                    </a>
                                </div>
                                
                                <div id="collapseFour" class="collapse show" aria-labelledby="headingFour" data-parent="#accordion">
                                  <div class="card-body clearfix">
                                    {!! nl2br($item->caution) !!}
                                  </div>
                                </div>
                              </div>
                            </div>	
                        @endif
                        
                        </div><!-- accordion -->
                    
                    @else
                        <div class="cont-wrap mt-5 mb-5 pb-2">
                           <ul class="nav nav-tabs">
                                <li class="nav-item">
                                  <a href="#tab1" class="nav-link active" data-toggle="tab"><i class="fal fa-info-circle"></i> 詳細</a>
                                </li>
                                <li class="nav-item">
                                  <a href="#tab2" class="nav-link" data-toggle="tab"><i class="fal fa-truck"></i> 配送</a>
                                </li>
                                <li class="nav-item">
                                  <a href="#tab3" class="nav-link" data-toggle="tab"><i class="fal fa-tree-alt"></i> 育て方</a>
                                </li>
                                @if(isset($item->caution))
                                    <li class="nav-item">
                                      <a href="#tab4" class="nav-link" data-toggle="tab"><i class="fal fa-exclamation-triangle"></i> ご注意</a>
                                    </li>
                                @endif
                            </ul> 
                            
                            <div class="tab-content mt-2">
                                  <div id="tab1" class="tab-pane active contents clearfix">
                                    {!! nl2br($item->explain) !!}
                                  </div>
                                  
                                  <div id="tab2" class="tab-pane contents">
                                    <div class="clearfix">
                                        {!! nl2br($item->about_ship) !!}
                                    </div>
                                    
                                    @if($item->is_delifee_table)
                                        <div class="btn btn-custom mt-2 slideDeli">
                                            送料表を見る <i class="fal fa-angle-down"></i>
                                        </div>
                                        
                                        @include('main.shared.deliFeeTable')
                                    @endif
                                    
                                  </div>
                                  
                                  <div id="tab3" class="tab-pane contents clearfix">
                                    {!! nl2br($item->contents) !!}
                                  </div>
                                  
                                  @if($isCaution)
                                      <div id="tab4" class="tab-pane contents clearfix">
                                        {!! nl2br($item->caution) !!}
                                      </div>
                                  @endif
                            </div> 
                        </div>
                    
                    @endif
                    
            	
                </div><!-- right -->


			<?php //================================================================= ?> 
                <div class="single-recom">

					@foreach($recomArr as $key => $recoms)
                        @if(count($recoms) > 0)
                            <div class="mt-3 floar">
                                <h4 class="text-small">{{ $key }}</h4>
                                
                                <?php 
                                    //レコメンドアイテムはItemControllerでchunkされている
                                ?>
                                
                                @foreach($recoms as $recom)
                                <div>
                                    <ul class="clearfix">
                                        @foreach($recom as $recomItem)
                                            <li class="main-atcl">
                                            	@if(strpos($key, 'ランキング') !== false && $item->cate_id == 1)
                                                    @include('main.shared.atclCateSec', ['cateSec'=>$recomItem]) 
                                                @else
                                                	@include('main.shared.atcl', ['item'=>$recomItem])
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                                @endforeach
                                
                                @if(strpos($key, '同梱包可能') !== false)
                                	<a href="{{ url('item/packing?orgId=' . $itemId) }}" class="btn btn-block btn-custom bg-white border-secondary rounded-0 mt-0 mb-5 text-center">同梱包可能な商品を全て見る <i class="fal fa-angle-double-right"></i></a>
                                @endif
                            </div>
                        @endif
                    @endforeach   

            	</div><!-- single-recom -->
            <?php //================================================================= ?>
                

        </div><!-- head-frame -->
        
        @if(count($posts) > 0)
            <div class="similar-post mt-3 mb-3 pt-1">
                <div class="mt-4 floar">
                    <h4>" {{ $itemTitle }} " に関する記事</h4>
                    <?php 
                        //キャッシュアイテムはItemControllerでchunkされている
                    ?>
                    
                    @foreach($posts as $chunkPost)
                        <div class="mb-2 clearfix">
                            @foreach($chunkPost as $post)
                                @include('main.shared.atclPost', ['post'=>$post])
                            @endforeach
                        </div>
                    @endforeach
                    
                </div>
            </div>
        @endif
        

        <div class="recent-check mt-3 pt-1">
            @if(count($cacheItems) > 0)
                <div class="mt-4 floar">
                    <h4>最近チェックしたアイテム</h4>
                    <?php 
                    	//キャッシュアイテムはItemControllerでchunkされている
                    ?>
                    
                    @foreach($cacheItems as $cacheItem)
                        <div class="mb-2">
                            <ul class="clearfix">
                                @foreach($cacheItem as $item)                            
                                    <li class="main-atcl">
                                        @include('main.shared.atcl', ['strNum'=>Ctm::isAgent('sp') ? 13 : 16])
                                    </li>    
                                @endforeach      
                            </ul>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>


            
		
    </div><!-- id -->
@endsection
