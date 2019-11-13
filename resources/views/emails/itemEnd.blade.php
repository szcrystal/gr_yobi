<?php 
/* Here is mail view */
?>

<?php //$info = DB::table('siteinfos')['first(); ?>


{{ $user['name'] }} 様
@if($isUser)
<br />
<p>※このメールは配信専用メールのため、ご返信いただけません。</p>
<br>
{!! nl2br( $header ) !!}

@else
よりご注文がありました。<br><br>
{{ url('dashboard/sales/order/'. $saleRel->order_number ) }}
<br><br>
ご注文内容は下記となります。
@endif

<br /><br />
<hr>
【ご注文番号】：{{ $saleRel->order_number }}<br>
【ご注文日】：{{ date('Y/m/d', time()) }}<br>
【ご注文者】：{{ $user->name }} 様<br><br>
【お届け先】
<div style="margin: 0 0 1.5em 1.0em;">
{{ $receiver->name }} 様<br>
<p style="margin-top:0.1em">
〒{{ Ctm::getPostNum($receiver->post_num) }}<br>
{{ $receiver->prefecture }}{{ $receiver->address_1 }}{{ $receiver->address_2 }}
</p>
</div>

@if(isset($sales->first()->plan_date))
【ご希望配送日】
<div style="margin: 0 0 1.0em 1.0em;">
{{ $sales->first()->plan_date }}
</div>
@endif

【ご注文商品】
<?php
	$num = 1;
	$isHuzai = 0;
?>

@foreach($sales as $sale)
<div style="margin: 0 0 1.5em 1.0em;">
<div>{{ $num }}.</div>
商品番号：{{ $itemModel->find($sale->item_id)->number }}<br>
商品名：{{ Ctm::getItemTitle($itemModel->find($sale->item_id)) }}<br>
数量：{{ $sale->item_count}}<br>
金額：¥{{ number_format($sale->total_price) }}（税込）
@if($sale->is_once_down)
[同梱包割引]
@endif
<br>

@if(isset($sale->plan_time))
ご希望配送時間：{{ $sale->plan_time }}<br>
@endif

@if(isset($sale->is_huzaioki))
	不在置き：
    @if($sale->is_huzaioki)
    	了承する
        <?php $isHuzai = 1; ?>
    @else
    	了承しない
    @endif
@endif

@if(isset($sale->seinou_sunday) && $sale->seinou_sunday)
<br>
日曜日指定：+¥{{ number_format($sale->seinou_sunday) }}
@endif

</div>
<?php $num++; ?>
@endforeach


@if($isHuzai && isset($saleRel->huzai_comment) && $saleRel->huzai_comment != '')
    【不在置き場所】
    <div style="margin: 0 0 1.0em 1.0em;">
    {!! nl2br($saleRel->huzai_comment) !!}
    </div>
@endif

@if(isset($saleRel->user_comment) && $saleRel->user_comment != '')
    【コメント】
    <div style="margin: 0 0 1.0em 1.0em;">
    {!! nl2br($saleRel->user_comment) !!}
    </div>
@endif

【ご注文金額】
<div style="margin: 0 0 1.0em 1.0em;">
商品金額合計：￥{{ number_format($saleRel->all_price) }} <br>
送料：￥{{ number_format($saleRel->deli_fee) }}<br>

{{--
@if($saleRel->seinou_huzai)
不在置き割引：￥{{ number_format($saleRel->seinou_huzai) }}<br>
@endif
--}}

@if($saleRel->seinou_sunday)
日曜配達割増：￥{{ number_format($saleRel->seinou_sunday) }}<br>
@endif

@if($saleRel->pay_method == 2)
コンビニ決済手数料：￥{{ number_format($saleRel->cod_fee) }}<br>
@elseif($saleRel->pay_method == 4)
後払い手数料：￥{{ number_format($saleRel->cod_fee) }}<br>
@elseif($saleRel->pay_method == 5)
代引手数料：￥{{ number_format($saleRel->cod_fee) }}<br>
@endif
@if($saleRel->is_user)
利用ポイント：{{ $saleRel->use_point }} ポイント<br>
@endif


<b style="display:block; font-size:1.1em; margin-top:0.5em;">ご注文金額合計：￥{{ number_format($saleRel->total_price) }} （税込）</b>
</div>
【お支払方法】
<div style="margin: 0 0 1.0em 1.0em;">
{{ $pmModel->find($saleRel->pay_method)->name }}

@if($saleRel->pay_method == 3)
    （{{ $pmChildModel->find($saleRel->pay_method_child)->name }}）
@elseif($saleRel->pay_method == 6)
    <div style="margin: 0 0 1.5em 0.8em;">
    お振込先は、改めてお知らせ致します。
    </div>
@endif
</div>

<br><hr><br>

@if($isUser)
{!! nl2br( $footer ) !!}
@endif

<br><br><br>

{!! nl2br($setting->mail_footer) !!}


