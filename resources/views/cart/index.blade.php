@extends('layouts.app')

@section('content')

<div id="main" class="cart-index">


<div class="top-cont">

@include('cart.shared.guide', ['active'=>1])

@if(! count($itemData))
	<div class="col-md-8 mx-auto text-center">
	<p class="">カートに商品が入っていません。</p>
	<a href="{{ url('/') }}">HOMEへ戻る <i class="fal fa-angle-double-right"></i></a>
	</div>
@else

<?php
//	print_r($errors->get('last_item_count'));
//    echo $errors->has('last_item_count');
//    exit;
?>

@if ($errors->has('no_delivery.*'))
    <div class="alert alert-danger">
        <i class="far fa-exclamation-triangle"></i> 配送不可の商品がカート内にあります。
        <ul class="mt-2">
            @foreach ($errors->get('no_delivery.*') as $error)
                <li>{{ $error[0] }}</li>
            @endforeach
        </ul>
    </div>
@endif


@if ($errors->has('last_item_count.*'))
    <div class="alert alert-danger">
        <i class="far fa-exclamation-triangle"></i> 確認して下さい。
        <ul class="mt-2">
            <li>売切れの商品がカート内にあります。</li>
            {{--
            @foreach ($errors->get('last_item_count.*') as $error)
                <li>{{ $error[0] }}</li>
            @endforeach
            --}}
        </ul>
    </div>
@endif



<div class="clearfix">
<div class="confirm-left">

<form id="with1" class="form-horizontal" role="form" method="POST" action="{{ url('shop/cart') }}">
      {{ csrf_field() }}
                        
<div class="table-responsive table-cart clearfix">
	<table class="table">
    
    	<tbody>
        	<?php $disabled = ''; ?>

     		@foreach($itemData as $key => $item)    
                <tr class="clearfix {{ $errors->has('no_delivery.'. $key) ? 'tr-danger-border' : '' }}">
                    <th>
                        @include('main.shared.smallThumbnail', ['width'=>140])
                    </th>
                    
                    <td class="clearfix">
                        
                        <?php 
                            $obj = $item;
                            $urlId = $item->is_potset ? $item->pot_parent_id : $item->id;
                        ?>
                        
                        <div>
                            <a href="{{ url('item/'. $urlId) }}">{!! Ctm::getItemTitle($item, 1) !!}</a>
                            <br>
                            <span class="mr-3">[ {{ $item->number }} ]</span>¥{{ Ctm::getItemPrice($item) }}（税込）
                        </div>
                        
                        <div class="clearfix mt-2">
                        	<div class="">
                            	
                            	<fieldset class="form-group p-0 m-0{{ $errors->has('last_item_count.'.$key) ? ' border border-danger p-1' : '' }}">
                                	<input type="hidden" name="last_item_id[]" value="{{ $item->id }}">
                                    
                                	<span class="text-small"><b>数量</b>：</span>
                                    
                                    
                                    @if(! $item->stock)
                                        <span class="text-small text-white bg-danger p-1"><i class="fas fa-exclamation-triangle text-big"></i> <b>売切れ商品です。カートから削除して進んで下さい。</b></span>
                                        <input type="hidden" name="last_item_count[]" value="0">
                                    
                                        <?php $disabled = ' disabled'; ?>
                                    
                                    @else
                                       <label class="select-wrap select-cart-count p-0"> 
                                        <select class="form-control" name="last_item_count[]">
                                                
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
                                                        if(old('last_item_count.'. $key) == $i)
                                                            $selected = ' selected';
                                                    }
                                                    else {
                                                        if($i == $item->count) {
                                                            $selected = ' selected';
                                                        }
                                                    }
                                                ?>
                                                
                                                <option value="{{ $i }}"{{ $selected }}>{{ $i }}</option>
                                            @endfor
                                        </select>
                                        </label>
                                        
                                        <button class="btn px-2" type="submit" name="re_calc" value="1"{{ $disabled }}>更新</button>
                                                                                
                                        @if ($errors->has('last_item_count.'. $key))
                                            <div class="border border-danger text-danger">
                                                <span class="fa fa-exclamation form-control-feedback"></span>
                                                <span>{{ $errors->first('last_item_count.'. $key) }}</span>
                                            </div>
                                        @endif
                                    
                                    @endif
                                    
                                </fieldset>
                            </div>
                            
                            <div class="mt-1">
                                <?php $red = ''; ?>
                                
                                @if(! isset($item->sale_price) && isset($item->is_once_down) && isset($item->once_price))
                                    <?php $red = 'text-danger'; ?>
                                    <p class="m-0 p-0 text-small {{ $red }}">同梱包割引</p>
                                @endif
                                
                                @if(isset($item->is_huzaioki))
                                    @if($item->is_huzaioki)
                                        <?php $red = 'text-danger'; ?>
                                        <p class="m-0 p-0 text-small {{ $red }}">不在置きを了承する</p>
                                    @else
                                        <p class="m-0 p-0 text-small {{ $red }}">不在置きを了承しない<br>
                                        <span class="text-danger">不在置きを了承すると¥3,300 OFF</span>
                                        </p>
                                    @endif
                                @endif

                            	<span class="text-small"><b>小計<span class="text-small">（税込）</b>：</span></span>
                                <span class="{{ $red }}">¥{{ number_format( $item->total_price ) }}</span>
                            </div>
                            
                            
                        </div>
                    </td>
                          
                    <td class="text-right">
                    	<button class="btn btn-cart-del mb-4" type="submit" name="del_item_key" value="{{ $key }}"><i class="fal fa-times"></i></button>       
                    </td>

                </tr> 
        	 @endforeach
          	            
         </tbody>
         
         </table>
    </div>
    
    
    
    @if(isset($cookieItems) && count($cookieItems) > 0)

           <div class="wrap-atcl cart-recent-check mt-5 pt-2">
               <div class="head-atcl">
                   <h2>最近チェックしたアイテム</h2>
               </div>
           
               <div class="clearfix">
                   @foreach($cookieItems as $cookieItem)
                        <div class="mb-2 clearfix">
                            @foreach($cookieItem as $item)
                                <article class="main-atcl">
                                    @include('main.shared.atcl', ['strNum'=>Ctm::isAgent('sp') ? 15 : 20])
                
                                </article>
                            @endforeach
                        </div>
                   @endforeach
               </div>
               
               <a href="{{ url('recent-items') }}" class="btn btn-block btn-custom bg-white border-secondary rounded-0">もっと見る <i class="fal fa-angle-double-right"></i></a>
               
           </div>
           
       @endif
    

</div>{{-- confirm-left --}}
         
<div class="confirm-right">
<div class="right-blue">
    
    <div class="clearfix">
        <input type="hidden" name="from_cart" value="1">

        @if($disabled)
            <div class="text-right mb-1">
                <span class="text-small text-danger"><i class="fas fa-exclamation-triangle"></i> <b>売切れ商品をカートから削除して下さい。</b></span>
            </div>
        @endif

        
        <button class="btn btn-block btn-custom btn-kon mb-4 py-3 px-2" type="submit" name="regist_off" value="1" formaction="{{ url('shop/form') }}"{{ $disabled }}>購入手続きへ進む</button>
        
        @if(! Auth::check())
            {{--
            <a href="{{ url('login?to_cart=1') }}" class="btn btn-block btn-custom mb-2 py-3">ログインして購入手続きへ進む</a>
            --}}
            
            <button class="btn btn-block btn-custom mb-2 py-3" type="submit" name="from_login" value="1"{{ $disabled }}>ログインして進む</button>
        @endif
        
        @if(Auth::check())
            
        @else
            <?php
                $arrow = Ctm::isAgent('sp') ? '<i class="far fa-arrow-alt-down"></i>' : '<i class="far fa-arrow-alt-right"></i>';
            ?>
            
            {{--
            <div class="table-responsive">
                <table class="table">
                    <tr>
                        <th rowspan="1" class="border-0">会員登録がまだの方 {!! $arrow !!}</th>
                        <td class="border-0">
                            <button class="btn btn-block btn-kon mb-0 py-3 px-10" type="submit" name="regist_on" value="1" formaction="{{ url('shop/form') }}"{{ $disabled }}>購入手続きへ <i class="fal fa-angle-double-right"></i></button>
                        </td>
                   </tr>
             --}}
                   
               {{--
               <tr class="border-0">
                    <td class="border-0">
                        <button class="btn btn-block btn-white mb-3 py-2 px-5" type="submit" name="regist_off" value="1" formaction="{{ url('shop/form') }}"{{ $disabled }}>会員登録せずに購入手続きへ <i class="fal fa-angle-double-right"></i></button>
                    </td>
                </tr>
                --}}
             
             {{--
                    <tr>
                        <th class="border-0">会員登録がお済みの方 {!! $arrow !!}</th>
                        <td class="border-0">
                        <a href="{{ url('login?to_cart=1') }}" class="btn btn-block btn-custom mb-2 py-3 px-10">ログインする</a>
            --}}
                        {{--
                        <button class="btn btn-block btn-custom mb-3 py-2" type="submit" name="to_cart" value="shop/cart" formaction="{{ url('login') }}">ログインする</button>
                        --}}
             {{--
                        </td>
                    </tr>
                </table>
            </div>
            --}}
            
        @endif
        
    </div>
    
	<div class="table-responsive table-foot">
	<table class="table">
         <tbody class="clearfix">
         	<tr>
          		<th class="text-left">
                	商品合計
                </th>
                
            	<td class="">
                	¥{{ number_format($allPrice) }}
                </td>
             	
                {{--
                <td class="col-md-3">	
                    <input type="hidden" name="calc" value="1" form="re">
                    <button class="btn btn-line px-2 w-100" type="submit" name="re_calc" value="1"{{ $disabled }}><i class="fal fa-redo"></i> 再計算</button>
                </td>
                --}}
          	</tr>
            
            
            <tr>
                <th class="text-left">送料</th>
                
                <td class="">
                    @if(isset($deliFee))
                        ¥{{ number_format($deliFee) }}
                    @else
                        <b class="text-big text-enji">含まれておりません</b>
                    @endif
                </td>
            </tr>
            
            {{--
                <tr>
                    <th class="text-left text-big">
                    	合計 <small>(小計+送料)</small>
                    </th>
                    
                    <td class="text-big text-danger">
                    	<b>¥{{ number_format($allPrice + $deliFee) }}</b>
                    </td>
                    
                </tr>
            
                @else
                    <tr>
                        <td colspan="2" class="text-left pt-0">
                            <span class="text-enji text-small"><i class="fas fa-exclamation-triangle"></i> <b>送料は含まれておりません</b></span>
                        </td>
                    </tr>
                @endif

                <tr>
                    <th class="pt-3 text-left text-big"><span class="d-inline-block pt-1">配送先都道府県</span></th>
                    <td class="pt-3">
                        
                        
                    </td>
                </tr>
            --}}
            
         </tbody>        
	</table>
    </div>
    
    <div class="bg-white clearfix py-3 px-3 mb-4">
        
        @if(! isset($deliFee))
            <div class="cart-note text-left mb-2">
                <p class="text-enji mb-0"><i class="fas fa-exclamation-triangle"></i> 送料確認は「配送先都道府県」を選択して「送料計算」を押して下さい。</p>
            </div>
            
        @endif
        
        <div>
            <label class="control-label mb-0 text-small d-inline"><b>配送先都道府県</b></label>
            <label class="select-wrap select-pref p-0 mb-3">
            <select id="pref" class="form-control ml-1 d-inline{{ $errors->has('pref_id') ? ' is-invalid' : '' }}" name="pref_id">
                <option selected value="0">選択</option>
                <?php
    //                            use App\Prefecture;
    //                            $prefs = Prefecture::all();
                ?>
                @foreach($prefs as $pref)
                    <?php
                        $selected = '';
                        if(Ctm::isOld()) {
                            if(old('pref_id') == $pref->id)
                                $selected = ' selected';
                        }
                        else {
                            if(isset($prefId) && $prefId == $pref->id) {
                            //if(Session::has('all.data.user')  && session('all.data.user.prefecture') == $pref->name) {
                                $selected = ' selected';
                            }
                        }
                    ?>
                    
                    <option value="{{ $pref->id }}"{{ $selected }}>{{ $pref->name }}</option>
                @endforeach
            </select>
            </label>
        </div>
        
        @if ($errors->has('pref_id'))
            <div class="help-block text-danger">
                <span class="fa fa-exclamation form-control-feedback"></span>
                <span>{{ $errors->first('pref_id') }}</span>
            </div>
        @endif
    
        <button class="btn btn-block px-2 col-md-11 m-auto bg-enji mb-2" type="submit" name="re_calc" value="1"{{ $disabled }}>送料計算</button>
        
        {{--
        <button class="btn px-2 w-100 bg-enji" type="submit" name="delifee_calc" value="1"{{ $disabled }}><b>送料計算</b></button>
        --}}
    
    </div>
    
    <hr>
    
    <div class="table-responsive table-foot">
        <table class="table mb-0 pb-0">
             <tbody class="clearfix">
                <tr>
                    <th class="text-left text-big">
                        <b>合計 <small>(税込)</small></b>
                    </th>
                    
                    <td class="text-extra-big text-danger">
                        <b class="text-big">¥{{ number_format($allPrice + $deliFee) }}</b>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

</div>
</div>{{-- confirm-right --}}

</div>{{-- clear --}}

<div class="clearfix mt-3 cart-btn-wrap">

    <div class="clearfix">
		<input type="hidden" name="uri" value="{{ $uri }}">
		<a href="{{ url($uri)}}" class="btn border border-secondary bg-white my-2"><i class="fal fa-angle-double-left"></i> 買い物を続ける</a>
	</div>
    
</div>
</form>  
@endif


{{--
<div class="col-lg-10">
        <form class="form-horizontal" role="form" method="POST" action="{{ url('cart/payment') }}">

            {{ csrf_field() }}            

		
    		<input type="hidden" name="item_id" value="{{ $data['item_id'] }}">
            <input type="hidden" name="price" value="{{ $data['price'] }}">
            <input type="hidden" name="tax" value="{{ $data['tax'] }}">      
            <input type="hidden" name="count" value="1">
        

            <fieldset class="form-group{{ $errors->has('name') ? ' has-error' : '' }}">
                <label>お名前</label>
                <input class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" name="name" value="{{ Ctm::isOld() ? old('name') : (isset($item) ? $item->name : '') }}">

                @if ($errors->has('name'))
                    <div class="text-danger">
                        <span class="fa fa-exclamation form-control-feedback"></span>
                        <span>{{ $errors->first('name') }}</span>
                    </div>
                @endif
            </fieldset>
	</form>
</div>
--}}          
 


</div>{{-- top-cont --}}


</div>

@endsection


{{--
@section('leftbar')
    @include('main.shared.leftbar')
@endsection


@section('rightbar')
	@include('main.shared.rightbar')
@endsection
--}}


