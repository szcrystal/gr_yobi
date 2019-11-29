<?php
use App\Setting;
use App\User;
use App\Prefecture;
use App\DeliveryGroupRelation;

$isSale = Setting::get()->first()->is_sale; 
?>

<div>
@if(isset($obj->sale_price))
    <small class="text-white bg-enji py-1 px-2 mr-1">セール商品</small>
    <strike class="text-small">{{ number_format(Ctm::getPriceWithTax($obj->price)) }}</strike>
    <i class="fal fa-arrow-right text-small"></i>
    <span class="text-enji text-bold">{{ number_format(Ctm::getPriceWithTax($obj->sale_price)) }}
@else
    @if($isSale)
        <small class="text-white bg-enji py-1 px-2 mr-1">セール{{ Setting::get()->first()->sale_per }}%引</small>
        <strike class="text-small">{{ number_format(Ctm::getPriceWithTax($obj->price)) }}</strike>
        <i class="fas fa-arrow-right text-small"></i>
        <span class="text-enji text-bold">{{ number_format(Ctm::getSalePriceWithTax($obj->price)) }}
    @else
        <span class="text-bold">{{ number_format(Ctm::getPriceWithTax($obj->price)) }}
    @endif
@endif
</span>
<span class="text-small">円&nbsp;(税込)</span>
</div>

@if(! isset($obj->sale_price) && isset($obj->once_price))
<div class="clearfix p-2 my-2 text-small bg-kon-light">
    <span class="float-left text-small text-kon">同梱包可能商品と同時購入で</span>
    <span class="float-right">
        <span class="text-big text-bold text-enji">{{ number_format(Ctm::getPriceWithTax($obj->once_price)) }}</span>
        <span class="text-small">円&nbsp;(税込)</span>
    </span>
</div>
@endif

<span class="text-middle">
@if($obj->is_delifee)
	送料無料</span>
@else
    @if(Auth::check())
        <?php
            $u = User::find(Auth::id());
            $pref = Prefecture::where('name', $u->prefecture)->first();
            $dgr = DeliveryGroupRelation::where(['dg_id'=>$obj->dg_id, 'pref_id'=>$pref->id])->first();
        ?>
    
    	@if(isset($dgr) && $dgr->fee != '')
        	<?php
            	$deliText = '';
                if($dgr->fee == 99999) {
                    $deliText = '配送不可';
                }
                else {
                    $taxPer = Setting::first()->tax_per;
                    $dgrFee = floor($dgr->fee * ($taxPer/100 + 1));
                    $deliText = '最低送料 ' . number_format($dgrFee) . '円';
                }
            ?>
        
        	{{ $pref->name }}への{{ $deliText }}
        
        @elseif(! $dgr->fee)
        	送料無料
        @endif
        
    @else
    	<i class="fal fa-plus"></i> 送料
    @endif
@endif
</span>


@if($obj->is_once)
    <span class="d-block text-blue text-middle">同梱包可能
@else
    <span class="d-block text-enji text-middle">同梱包不可
@endif
</span>


