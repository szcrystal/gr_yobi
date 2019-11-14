@extends('layouts.app')

@section('content')

<?php
use App\Item;

?>

<div id="main" class="history-single confirm">

        <div class="panel panel-default">

            <div class="panel-body">
                {{-- @include('main.shared.main') --}}

				<div class="main-list clearfix">


<div class="clearfix">
{{-- @include('cart.guide') --}}

<div class="confirm-left">
	<h5 class="card-header mb-3 py-2">購入履歴詳細</h5>
	
	<div class="table-responsive table-normal">
    <table class="table table-bordered bg-white">
        <tbody>
    	<tr>
     		<th>ご注文番号</th>
       		<td>{{ $saleRel->order_number }}</td>      
        </tr>
        <tr>
            <th>ご注文日</th>
            <td>{{ Ctm::changeDate($saleRel->created_at, 0) }}</td>      
        </tr>
        <tr>
            <th>お支払い方法</th>
            <td>{{ $pm->find($saleRel->pay_method)->name }}</td>
        </tr>
 
        
        </tbody>
    </table>
    </div>
	
	<div class="table-responsive table-cart mt-4">
    <table class="table table-bordered bg-white">
        <thead>
            <tr>
                <th>購入商品</th>  
            </tr>
        </thead>  
    
        <tbody>
             
             @foreach($sales as $sale)
             
             <?php $item = Item::find($sale->item_id); ?>
             
             <tr>
                <td class="clearfix col-md-12">

					<div class="float-left col-md-2">
                		@include('main.shared.smallThumbnail')
                	</div>
                    
                    <div class="float-left col-md-10">
                    	
                        <span class="d-block">
                            <a href="{{ url('item/' . $item->id) }}">{{ Ctm::getItemTitle($item) }}</a>
                            <br>[ {{ $item->number }} ]
                            <br>¥{{ number_format($sale->single_price) }}（税込）
                        </span>
                        
                        <span class="d-block mt-2"><span class="text-small">数量</span> {{ $sale->item_count }}</span>
                        
                        <span class="d-block mt-1"><span class="text-small">金額（税込）</span> ¥{{ number_format($sale->total_price) }}</span>
                        
                        <span class="d-block mt-1">
                        	<span class="text-small">ステータス&nbsp;</span>
                        	
                            @if($sale->is_cancel)
                                <span class="text-enji">
                                キャンセル
                                @if(isset($sale->cancel_date))
                                    <span class="text-small ml-1">[{{ Ctm::changeDate($sale->cancel_date, 1) }}]</span>
                                @endif
                                
                                </span>
                            @else
                                @if($sale->is_keep)
                                    <span class="text-success">お取り置き中 
                                    @if(isset($sale->keep_date))
                                        <span class="text-small ml-1">[{{ Ctm::changeDate($sale->keep_date, 1) }}〜]</span>
                                    @endif
                                    </span>
                                @else
                                    @if($sale->deli_done)
                                        <span class="text-info">発送済<span class="text-small ml-1">[{{ Ctm::changeDate($sale->deli_start_date, 1) }}]</span></span>
                                    @else
                                        <span class="text-warn">発送準備中</span>
                                    @endif
                                @endif
                            @endif
                        </span>
                        
                        <span class="d-block mt-1"><span class="text-small">枯れ保証残期間&nbsp;</span> 
                            @if($sale->is_cancel)
                                --
                            @else
                                @if($item->is_ensure)
                                    @if($sale->deli_done)
                                        
                                        <?php $days = Ctm::getKareHosyou($sale->deli_schedule_date); ?>
                                        
                                        @if($days['diffDay'])
                                            {{ $days['limit'] }}まで&nbsp;
                                            <b class="text-big">残{{ $days['diffDay'] }}日</b>
                                        @else
                                            {{ $days['limit'] }}にて<br>
                                            <b class="text-big">枯れ保証期間終了</b>
                                        @endif
                                    @else
                                        未発送
                                    @endif
                                @else
                                    枯れ保証なし
                                @endif
                            @endif
                        </span>
                        
                        <div class="mt-3">
                             <a href="{{ url('item/' . $item->id) }}" class="btn btn-block btn-custom py-2">もう一度購入</a>
                        </div>
                        
                        {{--
                        <form class="form-horizontal" role="form" method="POST" action="{{ url('shop/cart') }}">
                            {{ csrf_field() }}
                                                                                   
                            <input type="hidden" name="item_count[]" value="1">
                            <input type="hidden" name="from_item" value="1">
                            <input type="hidden" name="item_id[]" value="{{ $item->id }}">
                            <input type="hidden" name="uri" value="{{ Request::path() }}"> 
                            
                            <div class="mt-3">
                                <button class="btn btn-block btn-custom py-1" type="submit" name="regist_off" value="1"><i class="fal fa-cart-arrow-down"></i> もう一度購入</button>
                           </div>  
                        </form>
                        --}}
                        
                    </div>
                
                </td>
            </tr>
             
            @endforeach
                          
         </tbody> 
         
    </table>
</div>


<h5 class="card-header mb-3 py-2 mt-4">配送情報</h5>
<div class="table-responsive table-cart mt-3">
    <table class="table table-borderd border bg-white">
    	<thead>
     	   <tr><th>お届け先</th></tr>
        </thead>
        
        <tbody>
        	<tr>
            <td>        
                〒{{ Ctm::getPostNum($receiver->post_num) }}<br>
                {{ $receiver->prefecture }}&nbsp;
                {{ $receiver->address_1 }}&nbsp;
                {{ $receiver->address_2 }}<br>
                {{ $receiver->address_3 }}
                <span class="d-block mt-2">{{ $receiver->name }} 様</span>
                TEL : {{ $receiver->tel_num }}
            </td>
            </tr>
         </tbody> 
    </table>
</div>

<div class="table-responsive table-cart mt-3">
    <table class="table table-borderd border bg-white">
    	<thead>
     	   <tr><th>ご希望配送日時</th></tr>
        </thead>
        
        <tbody>
        	<tr>
            <td>
            	@foreach($sales as $sale)
                    @if(isset($sale->plan_time)) 
                        <b class="text-small">ご希望日</b>：
                        @if(isset($sale->plan_date))
                            {{ $sale->plan_date }}<br>
                        @else
                            最短出荷<br>
                        @endif
                        
                        @break
                    @endif
                @endforeach
                
                <ul class="px-4 mt-3">
                    @foreach($sales as $sale)
                        @if(isset($sale->plan_time)) 
                            <li class="mb-3">
                            <?php $i = Item::find($sale->item_id); ?>
                            
                            {{ Ctm::getItemTitle($i) }}<br>
                            <b class="text-small">ご希望時間</b>：[ {{ $sale->plan_time }} ]
                            </li>
                        @endif
                    @endforeach
                </ul>
            
        	</tr>
            </td>
        </tbody>
    </table>
</div>

@if(isset($saleRel->user_comment))
<h5 class="card-header mb-3 py-2 mt-4">その他コメント</h5>
<div class="table-responsive table-cart mt-3">
    <table class="table table-borderd border bg-white">
        <tbody>
        	<tr>
            <td>
            {!! nl2br($saleRel->user_comment) !!}
			</td>
			</tr>
        </tbody> 
    </table>
</div>
@endif

</div> 


<div class="confirm-right">
<h5 class="mb-4">&nbsp;</h5>
<div class="table-responsive table-normal show-price">
    <table class="table border table-borderd bg-white">
        
        <tbody>
        <tr>
            <th><label class="control-label">商品合計</label></th>
            <td>
            	¥{{ number_format($saleRel->all_price) }}
            </td>
        </tr>
        <tr>
            <th><label class="control-label">送料</label></th>
            <td>¥{{ number_format($saleRel->deli_fee) }}</td>
        </tr>
        
        @if($saleRel->pay_method == 4 || $saleRel->pay_method == 5)
            <tr>
                <th><label class="control-label">支払手数料</label></th>
                <td>¥{{ number_format($saleRel->cod_fee) }}</td>
            </tr>
        @endif
        
        @if(isset($saleRel->seinou_sunday) && $saleRel->seinou_sunday)
            <tr>
                <th><label class="control-label">日曜配達</label></th>
                <td>¥{{ number_format($saleRel->seinou_sunday) }}</td>
            </tr>
        @endif
        
        @if(Auth::check())
        <tr>
            <th><label class="control-label">利用ポイント</label></th>
             <td>{{ $saleRel->use_point }}</td>
        </tr>
        @endif
        
        <tr>
            <th><label class="control-label text-big"><b>合計（税込）</b></label></th>
             <td class="text-danger text-big">
                <span class="text-big">
                @if(isset($saleRel->total_price))
                	¥{{ number_format($saleRel->total_price) }}
                @else
	                ¥{{ number_format($saleRel->all_price + $saleRel->deli_fee + $saleRel->cod_fee - $saleRel->use_point) }}
                @endif
                </span>
            </td>
        </tr>
        </tbody>
    </table>
</div>

@if(isset($saleRel->add_point))
<div class="table-responsive table-normal show-price mt-3">
    <table class="table border table-borderd bg-white">
        <tr>
            <th><label class="control-label">ポイント発生</label></th>
            <td>
				{{ $saleRel->add_point }}
            </td>
        </tr>
    </table>
</div>
@endif

{{--
<div class="table-responsive table-normal mt-3">
    <table class="table border table-borderd bg-white">
        <tr>
            <th><label class="control-label">お支払い方法</label></th>
            <td class="w-50">
            	{{ $pm->find($saleRel->pay_method)->name }}
            </td>
        </tr>
    </table>
</div>
--}}

</div> {{-- float-right --}}

</div>

<div class="mt-4">


<a href="{{ url('mypage/history') }}" class="btn border border-secondary bg-white my-3">
<i class="fal fa-angle-double-left"></i> 購入履歴一覧に戻る
</a>

</div>




</div>
</div>
</div>
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


