@extends('layouts.app')

@section('content')

<?php
	use App\Setting;
    use App\Item;
?>

<div id="main" class="cart-all cart-confirm">

<div class="clearfix mb-5">

@include('cart.shared.guide', ['active'=>3])


@if (count($errors) > 0)
    <div class="alert alert-danger">
        <i class="far fa-exclamation-triangle"></i> 確認して下さい。
        
        <ul class="mt-2">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="confirm-left">
    <div class="clearfix">
        <h3>お届け先</h3>
        <div></div>
    </div>
    
    <div class="ml-20per mt-1 pl-1">
        <div class="text-right">
            <?php
                $target = isset($data['destination']) && $data['destination'] ? 'delivery-add' : 'delivery-add';
            ?>
            <a class="btn confirm-edit-btn" href="{{ url('shop/form#' . $target) }}">変更</a>
        </div>
        
        <div class="">
            @if(isset($data['destination']) && $data['destination'])
                <span>{{ $data['receiver']['name'] }} 様</span>
                <p class="mt-1 mb-0">
                〒{{ Ctm::getPostNum($data['receiver']['post_num']) }}<br>
                {{ $data['receiver']['prefecture'] }}{{ $data['receiver']['address_1'] }}{{ $data['receiver']['address_2'] }}<br>
                TEL : {{ $data['receiver']['tel_num'] }}
                </p>
            @else
                <span>{{ $userArr['name'] }} 様</span>
                <p class="mt-1 mb-0">
                〒{{ Ctm::getPostNum($userArr['post_num']) }}<br>
                {{ $userArr['prefecture'] }}{{ $userArr['address_1'] }}{{ $userArr['address_2'] }}<br>
                TEL : {{ $userArr['tel_num'] }}
                </p>
            @endif
        </div>
    </div>
    
    
    <div class="clearfix mt-4">
        <h3>お支払方法</h3>
        <div></div>
    </div>
    
    <div class="mt-1">
        <div class="table-responsive table-custom">
            <div class="text-right">
                <a class="btn confirm-edit-btn" href="{{ url('shop/form#pay-method') }}">変更</a>
            </div>
            
            <table class="table">
                <tr>
                    <th class="{{ count($errors) > 0 ? 'alert-danger' : '' }}">
                        {{ $payMethod->find($data['pay_method'])->name }}
                        
                        @if($data['pay_method'] == 3)
                            <br><span class="text-small">{{ $pmChild->find($data['net_bank'])->name }}</span>
                        @endif
                    </th>
                    <td>
                        @if(/*$regist && */$data['pay_method'] == 1 && $data['card_seq'] == 99)
                            カードの登録：{{ isset($data['is_regist_card']) ? 'する' : 'しない' }}
                        
                        @elseif($data['pay_method'] == 2)
                            {{-- コンビニ決済手数料 --}}
                            ¥{{ number_format($codFee) }}
                        
                        @elseif($data['pay_method'] == 4)
                            {{-- 後払い手数料 --}}
                            ¥{{ number_format($codFee) }}
                        
                        @elseif($data['pay_method'] == 5)
                            {{-- 代引き手数料 --}}
                            ¥{{ number_format($codFee) }}
                        @elseif($data['pay_method'] == 6)
                            手数料：お客様負担
                        @endif
                    </td>
                </tr>
                
                {{--
                @if($regist && $data['pay_method'] == 1 && $data['card_seq'] == 99)
                    <tr>
                        <th>カード番号の登録</th>
                        <td>
                            @if(isset($data['is_regist_card']))
                                する
                            @else
                                しない
                            @endif
                        </td>
                    </tr>
                @endif
                --}}
            </table>
        </div>
    </div>
    
    
    <div class="clearfix mt-3">
        <h3>商品</h3>
        <div></div>
    </div>
    
    <div class="table-responsive table-custom clearfix mt-1">
        <div class="text-right">
            <a class="btn confirm-edit-btn" href="{{ url('shop/cart') }}">変更</a>
        </div>
        
    <table class="table">
        <tbody>
            @foreach($itemData as $item)
   
                <tr>
                   <th class="pr-0">
                       @include('main.shared.smallThumbnail', ['width'=>140])
                   </th>

                   <td>
                       <div>
                           {!! Ctm::getItemTitle($item, 1) !!}
                           <br>
                           <span class="mr-2">[ {{ $item->number }} ]</span>
                           ¥{{ Ctm::getItemPrice($item) }}（税込）
                       </div>
                       
                       <p class="m-0 p-0 text-big"><span class="text-small">数量：</span>{{ $item->count }}</p>
                       
                       <?php $red = ''; ?>
                       
                       @if(! isset($item->sale_price) && isset($item->is_once_down))
                           <?php $red = 'text-danger'; ?>
                           <p class="m-0 p-0 {{ $red }}"><span class="text-small">同梱包割引</span></p>
                       @endif
                       
                       @if($item->is_huzaioki)
                           <?php $red = 'text-danger'; ?>
                           <p class="m-0 p-0 {{ $red }}"><span class="text-small">不在置きを了承する</span></p>
                       @endif
                       
                       <p class="m-0 p-0 text-big">
                           <span class="text-small">小計（税込）：</span>
                           <span class="{{ $red }}">¥{{ number_format( $item->item_total_price ) }}</span>
                       </p>
                       
                       @if(isset($item->seinou_sunday_price) && $item->seinou_sunday_price)
                            <p class="m-0 p-0 text-big">
                                <span class="text-small">日曜日指定：</span>
                                <span>+¥{{ number_format( $item->seinou_sunday_price ) }}</span>
                            </p>
                       @endif
                   </td>
                </tr>
            @endforeach
            
        </tbody>
    </table>
    </div>
    
    
    <div class="clearfix mt-3">
        <h3>配送希望日時</h3>
        <div></div>
    </div>
    
    <div class="table-responsive table-custom check-time mt-1">
        <div class="text-right">
            <a class="btn confirm-edit-btn" href="{{ url('shop/form#delivery-info') }}">変更</a>
        </div>
        
        <table class="table">
            <tbody>
                <tr>
                    <th class="font-weight-normal">ご希望日程</th>
                    <td>
                        <span class="text-big">
                        @if(isset($data['plan_date']))
                            {{ $data['plan_date'] }}<br>
                        @else
                            最短出荷<br>
                        @endif
                        </span>
                    </td>
                </tr>
                
                {{--
                @if(isset($data['is_seinou']))
                    <tr>
                        <th class="font-weight-normal" rowspan="2">不在置き</th>
                        <td>
                            <div class="">
                                <ul class="mb-1 list-unstyled text-small">
                                    @foreach($data['seinouItemTitle'] as $seinouItemTitle)
                                        <li>
                                            <i class="fal fa-angle-double-right"></i> {{ $seinouItemTitle }}
                                            
                                        </li>
                                    @endforeach
                                </ul>
                                
                                <b class="d-block">[ 不在置きを{{ $data['is_huzaioki'] ? '了承する' : '了承しない' }} ]</b>
                            </div>
                        </td>
                    </tr>
                    
                    <tr>
                        <td class="border-top-0 mt-0 pt-0">
                            <p class="text-small p-0 m-2">
                                不在置きの場所：<br>
                                <span class="text-big">{!! nl2br($data['huzai_comment']) !!}</span>
                            </p>
                            
                            @if(Ctm::isSeinouSunday($data['plan_date']))
                                <small class="d-inline-block text-enji mt-1 pt-1">＊上記１商品につきまして
                                    <ul class="pl-4 mb-0 pb-0">
                                        <li>不在置きを了承するの場合は、{{ number_format(Ctm::getSeinouObj()->huzaiokiFee) }}円引きとなります。
                                        <li>配送ご希望日程が日曜日の場合は、{{ number_format(Ctm::getSeinouObj()->sundayFee) }}円増しとなります。
                                    </ul>
                                </small>
                            @endif
                        </td>
                    </tr>
                @endif
                --}}
                
                @if(isset($data['plan_time']) && count($data['plan_time']) > 0)
                    <tr>
                        <th class="font-weight-normal">ご希望時間</th>
                        <td>
                            @foreach($data['planTimeItemTitle'] as $k => $planTimeTitleArr)
                                <div class="mb-3">
                                    <ul class="mb-1 list-unstyled">
                                        @foreach($planTimeTitleArr as $planTimeTitle)
                                            <li class="mb-2 pb-1">
                                                <i class="fal fa-angle-double-right"></i> {{ $planTimeTitle }}<br>
                                                <span class="text-big">{{ $data['plan_time'][$k] }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                    
                                    {{-- <span>{{ $data['plan_time'][$k] }}</span> --}}
                                </div>
                            @endforeach
                    
                        </td>
                    </tr>
                @endif
                
            </tbody>
        </table>
    </div>
    
    
    @if(isset($data['is_seinou']) && isset($data['huzai_comment']) && $data['huzai_comment'] != '')
        <div class="clearfix mt-3">
            <h3>不在置き場所</h3>
            <div></div>
        </div>
        
        <div class="ml-20per mt-1">
            <div class="text-right">
                <a class="btn confirm-edit-btn" href="{{ url('shop/form#huzaioki') }}">変更</a>
            </div>
            
            <div class="mb-3">
                <ul class="mb-1 list-unstyled">
                    @foreach($data['seinouHuzaiTitle'] as $shTitle)
                        <li class="mb-1">
                            <i class="fal fa-angle-double-right"></i> {{ $shTitle }}<br>
                        </li>
                    @endforeach
                </ul>
            </div>
            
            <p style="min-height:8em;" class="pt-0 pb-1 px-2">
                {!! nl2br($data['huzai_comment']) !!}
            </p>
        </div>
    @endif
    
    @if(isset($data['user_comment']) && $data['user_comment'] != '')
        <div class="clearfix mt-3">
            <h3>コメント</h3>
            <div></div>
        </div>
        
        <div class="ml-20per mt-1">
            <div class="text-right">
                <a class="btn confirm-edit-btn" href="{{ url('shop/form#other-comment') }}">変更</a>
            </div>
            
            <p style="min-height:8em;" class="pt-0 pb-1 px-2">
                {!! nl2br($data['user_comment']) !!}
            </p>
        </div>
    @endif

    @if(! Auth::check())
        <div class="clearfix mt-3">
            <h3>{{ $regist ? '会員登録情報' : 'お客様情報' }}</h3>
            <div></div>
        </div>
        
        <div class="ml-20per pl-1 mt-1">
            <div class="text-right">
                <a class="btn confirm-edit-btn" href="{{ url('shop/form#user-info') }}">変更</a>
            </div>
            
            <div>
            {{ $userArr['name'] }}（{{ $userArr['hurigana'] }}）&nbsp;様<br>
            〒{{ Ctm::getPostNum($userArr['post_num']) }}<br>
            {{ $userArr['prefecture'] }}{{ $userArr['address_1'] }}{{ $userArr['address_2'] }}<br>
            TEL：{{ $userArr['tel_num'] }}<br>
            メール：{{ $userArr['email'] }}
            
            @if($regist)
            <br>
            <p class="m-0 pt-1">メールマガジンの登録を{{ isset($userArr['magazine']) ? 'する' : 'しない' }}</p>
            @endif
            
            </div>
        </div>

        {{--
        <div class="table-responsive table-custom">
            <table class="table">
                <tbody>
                <tr>
                    <th>氏名</th>
                    <td>{{ $userArr['name'] }}</td>
                </tr>
                <tr>
                    <th>フリガナ</th>
                     <td>{{ $userArr['hurigana'] }}</td>
                </tr>
                <tr>
                    <th>電話番号</th>
                     <td>{{ $userArr['tel_num'] }}</td>
                </tr>
                <tr>
                    <th>住所</th>
                     <td>〒{{ Ctm::getPostNum($userArr['post_num']) }}<br>
                            {{ $userArr['prefecture'] }}{{ $userArr['address_1'] }}{{ $userArr['address_2'] }}
                     </td>
                </tr>
                <tr>
                    <th>メールアドレス</th>
                     <td>{{ $userArr['email'] }}</td>
                </tr>
                
                @if($regist)
                    <tr>
                        <th>パスワード</th>
                         <td>
                            **********<span class="text-small">（表示されません）</span>
                        </td>
                    </tr>
                    <tr>
                        <th>メールマガジンの登録</th>
                         <td>
                            @if(isset($userArr['magazine']))
                                する
                            @else
                                しない
                            @endif
                        </td>
                    </tr>
                @endif
                
                </tbody>
            </table>
        </div>
        --}}
        
    @endif
    
    

</div><!-- left -->



<div class="confirm-right">
    <div class="right-gray">
        
        <div class="">
        <?php
            $isCard = 0;
            if($data['pay_method'] == 1) {
                if(! isset($data['card_seq']) || (isset($data['card_seq']) && $data['card_seq'] == 99)) {
                    $isCard = 1;
                }
            }
        ?>

        @if($isCard)
            <form id="getTokenForm">

                @foreach($cardInfo as $key => $ci)
                    <input id="{{ $key }}" type="hidden" name="{{ $key }}" value="{{ $ci }}">
                @endforeach

                <div class="">
                    {{--
                    <small class="col-md-5 mx-auto d-block px-5 mb-1 confirm-small">
                        上記ご注文内容で注文を確定します。<br>
                        <b>「注文する」ボタンをクリックすると注文を確定します。</b>
                    </small>
                    --}}
                    
                    <div class="loader-wrap">
                        <span class="loader mr-3"><i class="fas fa-square mr-1"></i> 処理中..</span>
                    </div>
                
                    <?php
                        $isProduct = Setting::get()->first()->is_product ? 1 : 0;
                    ?>
                    
                    <input type="button" id="card-submit" class="btn btn-block btn-orange mt-0 mb-4 py-3" data-product="{{ $isProduct }}" value="注文を確定する">
                    
                    {{--
                    <button class="btn btn-block btn-enji col-md-4 mb-4 mx-auto py-2" type="submit" name="regist_off" value="1"{{ $disabled }} onclick="doPurchase()">注文する</button>
                    --}}
                </div>

            </form>
        @endif

        <form id="purchaseForm" class="form-horizontal" role="form" method="POST" action="{{ $actionUrl }}">
            {{ csrf_field() }}
            
            @foreach($settles as $key => $settle)
                <input type="hidden" name="{{ $key }}" value="{{ $settle }}">
            @endforeach
            
            <input type="hidden" value="" id="token" name="token">
            
            @if(! $isCard)
                <?php
                    $disabled = '';
                    if(count($errors) > 0) {
                        $disabled = ' disabled';
                    }
                ?>
                
                    @if($errors->has('konbiniLimit'))
                        <span class="mx-auto d-block px-5 confirm-small text-danger text-small">
                            {{ $errors->first('konbiniLimit') }}<br>
                            戻ってお支払い方法か購入商品を変更して下さい。
                        </span>
                    @elseif($errors->has('gmoLimit'))
                        <span class="mx-auto d-block px-5 confirm-small text-danger text-small">
                            {{ $errors->first('gmoLimit') }}<br>
                            戻ってお支払い方法か購入商品/数量を変更して下さい。
                        </span>
                    @else
                        {{--
                        <span class="mx-auto d-block px-5 confirm-small text-small">
                        上記ご注文内容で注文を確定します。<br>
                        <b>「注文する」ボタンをクリックすると注文を確定します。</b>
                        </span>
                        --}}
                    @endif

                    
                <div class="loader-wrap">
                    <span class="loader mr-3"><i class="fas fa-square mr-1"></i> 処理中..</span>
                </div>
                
                <button id="exist-submit" class="btn btn-block btn-orange mb-4 py-3" type="submit"{{ $disabled }}>注文を確定する</button>
            @endif
          
        </form>
    </div>
    
    <div class="table-responsive show-price table-foot">
        <table class="table">
            <tbody>
            <tr>
                <th>商品合計</th>
                 <td>¥{{ number_format($allPrice) }}</td>
            </tr>
            <tr>
                <th>送料</th>
                <td>¥{{ number_format($deliFee) }}</td>
            </tr>
            
            {{--
            @if($seinouHuzaiAllPrice)
            	<tr>
                	<th>不在置き割引</th>
                    <td>¥{{ number_format($seinouHuzaiAllPrice) }}</td>
                </tr>
            @endif
            --}}
            
            @if($seinouSundayAllPrice)
            	<tr>
                	<th>日曜配達</th>
                    <td>¥{{ number_format($seinouSundayAllPrice) }}</td>
                </tr>
            @endif
            
            @if($data['pay_method'] == 2)
                <tr>
                    <th>コンビニ決済手数料</th>
                    <td>¥{{ number_format($codFee) }}</td>
                </tr>
            
            @elseif($data['pay_method'] == 4)
                <tr>
                    <th>手数料</th>
                    <td>¥{{ number_format($codFee) }}</td>
                </tr>
            
            @elseif($data['pay_method'] == 5)
                <tr>
                    <th>手数料</th>
                    <td>¥{{ number_format($codFee) }}</td>
                </tr>
            @endif
            
            @if(Auth::check())
                <tr>
                    <th>利用ポイント</th>
                    <td>{{ $usePoint }}</td>
                </tr>
            @endif
            
            </tbody>
        </table>
    </div>

    <hr>

    <div class="table-responsive table-foot show-price mt-3">
        <table class="table">
            <tr>
                <th class="text-big"><b>合計<small>（税込）</small></b></th>
                <td class="text-danger text-extra-big{{ count($errors) > 0 ? ' alert-danger' : '' }}">
                     <b class="text-big"> ¥{{ number_format($totalFee) }}</b>
                </td>
            </tr>

            @if(Auth::check())
            <tr>
                <th>ポイント残高</th>
                 <td>{{ $userArr['point'] - $usePoint }}</td>
            </tr>
            @endif
            
            @if($regist || Auth::check())
            <tr>
                <th>ポイント発生</th>
                <td>{{ $addPoint }}</td>
            </tr>
            @endif
        </table>
    </div>

    {{--
    <div class="table-responsive table-normal show-price mt-3">
        <table class="table border table-borderd bg-white"> 
            <tr>
                <th>お支払い方法</th>
                <td class="{{ count($errors) > 0 ? 'alert-danger' : '' }}">
                    {{ $payMethod->find($data['pay_method'])->name }}
                    
                    @if($data['pay_method'] == 3)
                        <br><span class="text-small">{{ $pmChild->find($data['net_bank'])->name }}</span>
                    @endif
                </td>
            </tr>
            
            @if($regist && $data['pay_method'] == 1 && $data['card_seq'] == 99)
            	<tr>
                    <th>カード番号の登録</th>
                    <td>
                        @if(isset($data['is_regist_card']))
                            する
                        @else
                            しない
                        @endif
                    </td>
                </tr>
            @endif
        </table>
    </div>
    --}}

</div>
</div><!-- right -->





</div>{{-- clear --}}

@includeWhen(Ctm::isEnv('local'), 'cart.shared.backBtn', ['urlForBack'=>'form', 'textForBack'=>'お客様情報の入力に戻る'])


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


