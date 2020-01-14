@extends('layouts.app')

@section('content')

<?php
use App\Item;
use App\DeliveryGroup;
?>

	{{-- @include('main.shared.carousel') --}}

<div id="main" class="cart-form">

<div class="clearfix">

@include('cart.shared.guide', ['active'=>2])

@if(count($cardErrors) > 0)
	<div class="alert alert-danger">
        <i class="far fa-exclamation-triangle"></i> 確認して下さい。
        <ul class="mt-2">
            @foreach ($cardErrors as $cardError)
                <li>{{ $cardError }}</li>
            @endforeach
        </ul>
    </div>
@endif

<?php
//print_r($errors);
//exit;
?>

@if (count($errors) > 0)
    <div class="alert alert-danger">
        <i class="far fa-exclamation-triangle"></i>
        @if ($errors->has('no_delivery.*'))
        	配送不可の商品があります。
        @else
        	確認して下さい。
        @endif
        
        <ul class="mt-2">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif


<div class="clearfix">

<form id="user-input" class="form-horizontal" role="form" method="POST" action="{{ url('shop/confirm') }}">
    {{ csrf_field() }}


<div class="confirm-left">

@if(Auth::check())

<div id="delivery-add" class="mb-5">
    <div class="clearfix mb-3">
        <h3>お届け先</h3>
        <div></div>
    </div>

    <div class="ml-20per pl-1">
        <?php
            $checked = '';
            if(Ctm::isOld()) {
                 if(old('destination'))
                    $checked = ' checked';
            }
            else {
                if(Session::has('all.data.destination') && session('all.data.destination'))
                    $checked = ' checked';
            }
        ?>
        
        
        <fieldset class="form-group">
            <div class="">
                <input id="radio-destination-1" type="radio" name="destination" value="0" checked form="user-input">
                <label for="radio-destination-1" class="radios">{{ $userObj->name }} 様</label>
                <div class="pl-4">
                    〒{{ Ctm::getPostNum( $userObj->post_num) }}<br>
                    住所：{{ $userObj->prefecture }}{{ $userObj->address_1 }}{{ $userObj->address_2 }}<br>
                    TEL：{{ $userObj->tel_num }}
                </div>
            </div>
            
            <div class="mt-4 mb-1">
                <input id="radio-destination-2" type="radio" name="destination" value="1"{{ $checked }} form="user-input">
                <label for="radio-destination-2" class="radios">別の住所へお届け</label>
            </div>
            
            @if ($errors->has('receiver.*'))
                <div class="help-block text-danger receiver-error">
                    <span class="fa fa-exclamation form-control-feedback"></span>
                    <span class="text-small">上記登録先住所に配送をご希望の場合は、上記先頭のチェックをONにして下さい。</span>
                </div>
            @endif
            
        </fieldset>
        
        <div class="receiver">
            @include('cart.shared.anotherAddress')
        </div>
        
    </div>
</div>{{-- id --}}

<div id="use-point" class="mb-4">
    <div class="clearfix mt-3 mb-3">
        <h3>ポイント利用</h3>
        <div></div>
    </div>
            
    <div class="ml-20per pl-1">

        <div class="mb-2">
           現在の保持ポイント：<span class="text-primary">{{ $userObj->point }}</span>ポイント
        </div>
        <div class="mb-2">
            <label>ポイント利用する</label>
            <input class="form-control d-inline col-md-5{{ $errors->has('use_point') ? ' is-invalid' : '' }}" name="use_point" value="{{ Ctm::isOld() ? old('use_point') : (Session::has('all.use_point') ? session('all.use_point') : 0) }}" placeholder="" form="user-input">
        </div>
       
        @if ($errors->has('use_point'))
            <div class="text-danger">
                <span class="fa fa-exclamation form-control-feedback"></span>
                <span>{{ $errors->first('use_point') }}</span>
            </div>
        @endif

    </div>
</div>{{-- id --}}

<?php //新規会員登録 =================================================== ?>
@else

    <input type="hidden" name="use_point" value="0">

<div id="user-info">
    <div class="clearfix">
        <h3>お客様情報</h3>
        <div></div>
    </div>

    <div class="table-responsive table-custom mt-3">
        <table class="table p-0 m-0">
            
            <tr class="form-group">
                 <th>氏名<em>必須</em></th>
                   <td>
                    <input class="form-control col-md-12{{ $errors->has('user.name') ? ' is-invalid' : '' }}" name="user[name]" value="{{ Ctm::isOld() ? old('user.name') : (Session::has('all.data.user') ? session('all.data.user.name') : '') }}" placeholder="例）山田太郎" form="user-input">
                   
                    @if ($errors->has('user.name'))
                        <div class="text-danger">
                            <span class="fa fa-exclamation form-control-feedback"></span>
                            <span>{{ $errors->first('user.name') }}</span>
                        </div>
                    @endif
                </td>
             </tr>
          
              <tr class="form-group">
                 <th>フリガナ<em>必須</em></th>
                   <td>
                    <input type="text" class="form-control col-md-12{{ $errors->has('user.hurigana') ? ' is-invalid' : '' }}" name="user[hurigana]" value="{{ Ctm::isOld() ? old('user.hurigana') : (Session::has('all.data.user') ? session('all.data.user.hurigana') : '') }}" placeholder="例）ヤマダタロウ" form="user-input">
                    
                    @if ($errors->has('user.hurigana'))
                        <div class="text-danger">
                            <span class="fa fa-exclamation form-control-feedback"></span>
                            <span>{{ $errors->first('user.hurigana') }}</span>
                        </div>
                    @endif
                </td>
             </tr>
             
             <tr class="form-group">
                 <th>電話番号<em>必須</em>
                    {{-- <small>例）09012345678ハイフンなし半角数字</small> --}}
                 </th>
                   <td>
                    <input type="text" class="form-control col-md-12{{ $errors->has('user.tel_num') ? ' is-invalid' : '' }}" name="user[tel_num]" value="{{ Ctm::isOld() ? old('user.tel_num') : (Session::has('all.data.user') ? session('all.data.user.tel_num') : '') }}" placeholder="例）09012345678（ハイフンなし半角数字）" form="user-input">
                    
                    @if ($errors->has('user.tel_num'))
                        <div class="help-block text-danger">
                            <span class="fa fa-exclamation form-control-feedback"></span>
                            <span>{{ $errors->first('user.tel_num') }}</span>
                        </div>
                    @endif
                </td>
             </tr>
             
             <tr class="form-group">
                 <th>郵便番号<em>必須</em>
                    {{-- <small>例）1234567ハイフンなし半角数字</small> --}}
                 </th>
                   <td>
                    <input id="zipcode" type="text" class="form-control col-md-12{{ $errors->has('user.post_num') ? ' is-invalid' : '' }}" name="user[post_num]" value="{{ Ctm::isOld() ? old('user.post_num') : (Session::has('all.data.user') ? session('all.data.user.post_num') : '') }}" placeholder="例）1234567（ハイフンなし半角数字）" form="user-input">
                    
                    @if ($errors->has('user.post_num'))
                        <div class="help-block text-danger">
                            <span class="fa fa-exclamation form-control-feedback"></span>
                            <span>{{ $errors->first('user.post_num') }}</span>
                        </div>
                    @endif
                </td>
             </tr>
             
             <tr class="form-group">
                 <th>都道府県<em>必須</em></th>
                   <td>
                    <div class="select-wrap col-md-12 p-0">
                        <select id="pref" class="form-control select-first{{ $errors->has('user.prefecture') ? ' is-invalid' : '' }}" name="user[prefecture]" form="user-input">
                            <option selected value="0">選択して下さい</option>
                            <?php
        //                        use App\Prefecture;
        //                        $prefs = Prefecture::all();
                            ?>
                            @foreach($prefs as $pref)
                                <?php
                                    $selected = '';
                                    if(Ctm::isOld()) {
                                        if(old('user.prefecture') == $pref->name)
                                            $selected = ' selected';
                                    }
                                    else {
                                        if(Session::has('all.data.user')  && session('all.data.user.prefecture') == $pref->name) {
                                            $selected = ' selected';
                                        }
                                    }
                                ?>
                                
                                <option value="{{ $pref->name }}"{{ $selected }}>{{ $pref->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    @if ($errors->has('user.prefecture'))
                        <div class="help-block text-danger">
                            <span class="fa fa-exclamation form-control-feedback"></span>
                            <span>{{ $errors->first('user.prefecture') }}</span>
                        </div>
                    @endif
                </td>
             </tr>
             
             <tr class="form-group">
                 <th>住所1<small>（都市区それ以降）</small><em>必須</em></th>
                   <td>
                    <input id="address" type="text" class="form-control col-md-12{{ $errors->has('user.address_1') ? ' is-invalid' : '' }}" name="user[address_1]" value="{{ Ctm::isOld() ? old('user.address_1') : (Session::has('all.data.user') ? session('all.data.user.address_1') : '') }}" placeholder="例）小美玉市下吉影1-1" form="user-input">
                    
                    @if ($errors->has('user.address_1'))
                        <div class="help-block text-danger">
                            <span class="fa fa-exclamation form-control-feedback"></span>
                            <span>{{ $errors->first('user.address_1') }}</span>
                        </div>
                    @endif
                </td>
             </tr>
             
             <tr class="form-group">
                 <th>住所2<small>（建物/マンション名等）</small></th>
                   <td>
                    <input type="text" class="form-control col-md-12{{ $errors->has('user.address_2') ? ' is-invalid' : '' }}" name="user[address_2]" value="{{ Ctm::isOld() ? old('user.address_2') : (Session::has('all.data.user') ? session('all.data.user.address_2') : '') }}" placeholder="例）GRビル 101号" form="user-input">
                    
                    @if ($errors->has('user.address_2'))
                        <div class="help-block text-danger">
                            <span class="fa fa-exclamation form-control-feedback"></span>
                            <span>{{ $errors->first('user.address_2') }}</span>
                        </div>
                    @endif
                </td>
             </tr>
             
             
             <tr class="form-group">
                 <th>メールアドレス<em>必須</em></th>
                   <td>
                    <input type="email" class="form-control col-md-12{{ $errors->has('user.email') ? ' is-invalid' : '' }}" name="user[email]" value="{{ Ctm::isOld() ? old('user.email') : (Session::has('all.data.user') ? session('all.data.user.email') : '') }}" placeholder="例）abcde@example.com" form="user-input">
                    
                    @if ($errors->has('user.email'))
                        <div class="help-block text-danger">
                            <span class="fa fa-exclamation form-control-feedback"></span>
                            <span>{{ $errors->first('user.email') }}</span>
                        </div>
                    @endif
                </td>
             </tr>
             
        </table>
    </div>

    </div>{{-- id --}}

    <div id="user-regist" class="pt-3">
        <div class="clearfix">
            <h3>会員登録</h3>
            <div></div>
        </div>

        <div class="table-responsive table-custom">
            <table class="table table-borderd border p-0 m-0">
                
                <tr class="form-group">
                    <th>会員登録</th>
                    <td>
                        <?php
    /*
    //                            $registChecked = ' checked';
    //
    //                            if( Ctm::isOld()) {
    //                                if(! old('regist'))
    //                                    $registChecked = '';
    //                            }
    //                            elseif(Session::has('all.regist')) {
    //                                if(! session('all.regist'))
    //                                    $registChecked = '';
    //                            }
    */
                        ?>
                        
                        {{--
                        <input type="hidden" name="regist" value="0">
                        <input id="check-regist-y" type="checkbox" name="regist" value="1"{{ $registChecked }} form="user-input">
                        <label for="check-regist-y" class="checks">会員登録する</label>
                        --}}
                            
                        <?php
                                $registChecked = '';
        
                                if( Ctm::isOld()) {
                                    if(! old('regist'))
                                        $registChecked = ' checked';
                                }
                                elseif(Session::has('all.regist')) {
                                    if(! session('all.regist'))
                                        $registChecked = ' checked';
                                }
                        ?>
                            
                            
                        <span class="deliRadioWrap">
                            <input id="radio-regist-y" type="radio" name="regist" value="1" class="registRadio" checked form="user-input">
                            <label for="radio-regist-y" class="radios">する</label>
                        </span>
                        
                        <span class="deliRadioWrap">
                            <input id="radio-regist-n" type="radio" name="regist" value="0" class="registRadio" {{ $registChecked }} form="user-input">
                            <label for="radio-regist-n" class="radios">しない</label>
                        </span>
                        
                        <p class="mt-2 mb-0 text-small">＊会員登録をすると住所やクレジットカードの登録、お気に入りの永続使用が可能です。</p>
                    </td>
                </tr>
            </table>
            </div>
            
            <div class="regist-frame">
                <div class="table-responsive table-custom">
                <table class="table table-borderd border p-0 m-0">
                    <tr class="form-group">
                        <th>パスワード<em>必須</em></th>
                        <td>
                            <input type="password" class="form-control col-md-12{{ $errors->has('user.password') ? ' is-invalid' : '' }}" name="user[password]" value="{{ Ctm::isOld() ? old('user.password') : (Session::has('all.data.user') ? session('all.data.user.password') : '') }}" placeholder="8文字以上（半角）" form="user-input">
                                                
                            @if ($errors->has('user.password'))
                                <div class="help-block text-danger">
                                    <span class="fa fa-exclamation form-control-feedback"></span>
                                    <span>{{ $errors->first('user.password') }}</span>
                                </div>
                            @endif
                        </td>
                    </tr>
                     
                    <tr class="form-group">
                         <th>パスワードの確認<em>必須</em></th>
                           <td>
                            <input type="password" class="form-control col-md-12{{ $errors->has('user.password_confirmation') ? ' is-invalid' : '' }}" name="user[password_confirmation]" value="{{ Ctm::isOld() ? old('user.password_confirmation') : (Session::has('all.data.user') ? session('all.data.user.password_confirmation') : '') }}" form="user-input">
                            
                            @if ($errors->has('user.password_confirmation'))
                                <div class="help-block text-danger">
                                    <span class="fa fa-exclamation form-control-feedback"></span>
                                    <span>{{ $errors->first('user.password_confirmation') }}</span>
                                </div>
                            @endif
                        </td>
                    </tr>
                </table>
                </div>
                            
            <div class="table-responsive table-custom">
            <p class="text-small mb-0 mt-2"><i class="fas fa-square"></i> 当店からのお知らせを希望しますか？</p>
                
            <table class="table table-borderd border p-0 m-0">
                <tr class="form-group">
                    <th class="">メールマガジン</th>
                    <td class="">
                        
                        <?php
                            $checked = '';
                            if(Ctm::isOld()) {
                                if(old('user.magazine'))
                                    $checked = ' checked';
                            }
                            else {
                                if(Session::has('all.data.user')  && session('all.data.user.magazine')) {
                                    $checked = ' checked';
                                }
                            }
                        ?>
                        
                        <input id="check-magazine" type="checkbox" name="user[magazine]" value="1"{{ $checked }} form="user-input">
                        <label for="check-magazine" class="checks">登録する</label>
                        
                        {{--
                        <input type="checkbox" name="user[magazine]" value="1"{{ $checked }}> 登録する
                        --}}
                        
                        @if ($errors->has('user.magazine'))
                            <div class="help-block text-danger">
                                <span class="fa fa-exclamation form-control-feedback"></span>
                                <span>{{ $errors->first('user.magazine') }}</span>
                            </div>
                        @endif
                    </td>
                 </tr>
            </table>
        </div>
        
        </div>{{-- regist-frame --}}

    </div>{{-- id --}}
            
        
    <div id="delivery-add" class="receiver">
        <div class="clearfix mt-3">
            <h3>お届け先</h3>
            <div></div>
        </div>
        
        <div class="ml-20per">
        <fieldset class="form-group mt-2 py-1 pl-1">
                <?php
                    $checked = '';
                    if(Ctm::isOld()) {
                         if(old('destination'))
                            $checked = ' checked';
                    }
                    else {
                        if(Session::has('all.data.destination') && session('all.data.destination'))
                            $checked = ' checked';
                            
                    }
                ?>
                
                <div class="mb-2">
                    <input id="radio-destination-1" type="radio" name="destination" value="0" checked form="user-input">
                    <label for="radio-destination-1" class="radios">お客様情報と同じ</label>
                </div>
                
                <div class="">
                    <input id="radio-destination-2" type="radio" name="destination" value="1"{{ $checked }} form="user-input">
                    <label for="radio-destination-2" class="radios">別の住所へお届け</label>
                </div>
                
                {{--
                <label class="d-block">
                    <input type="radio" name="destination" class="destinationRadio ml-2" value="0" checked> 登録先と同じ
                </label>
                 
                <label class="d-block">
                    <input type="radio" name="destination" class="destinationRadio ml-2" value="1"{{ $checked }}> 別の住所へお届け
                </label>
                --}}
                
                @if ($errors->has('receiver.*'))
                    <div class="help-block text-danger receiver-error">
                        <span class="fa fa-exclamation form-control-feedback"></span>
                        <span class="text-small">上記登録先住所に配送をご希望の場合は「お客様情報と同じ」にチェックをして下さい。</span>
                    </div>
                @endif
        </fieldset>
        </div>
    
        @include('cart.shared.anotherAddress')
        
     </div><!-- id / receiver -->

         
@endif {{-- AuthCheck --}}


<div id="delivery-info" class="pt-2">
    <div class="clearfix mt-3">
        <h3>配送希望日時</h3>
        <div></div>
    </div>
    
    <div class="ml-20per">
            
        <fieldset class="mb-3 pb-4 mt-3 pl-1 form-group{{ $errors->has('plan_date') ? ' has-error' : '' }}">
            <label for="plan_date" class="control-label">■ご希望日程<span class="text-small"></span></label>
            
            <div class="select-wrap col-md-9 p-0">
            <select class="form-control {{ $errors->has('plan_date') ? ' is-invalid' : '' }}" name="plan_date" form="user-input">
                <option value="希望なし（最短出荷）" selected>希望なし（最短出荷）</option>
                    <?php
                        $days = array();
                        $week = ['日', '月', '火', '水', '木', '金', '土'];
                    
                        for($plusDay = 4; $plusDay < 64; $plusDay++) { //現在より4日後スタート、2ヶ月表示
                            $now = date('Y-m-d', time());
                            $first = strtotime($now." +". $plusDay . " day");
                            $days[] = date('Y/m/d', $first) . '（' . $week[date('w', $first)] . '）';
                        }
                    ?>

                    @foreach($days as $day)
                        <?php
                            $selected = '';
                            if(Ctm::isOld()) {
                                if(old('plan_date') == $day)
                                    $selected = ' selected';
                            }
                            else {
                                if(Session::has('all.data.plan_date') && session('all.data.plan_date') == $day) {
                                    $selected = ' selected';
                                }
                            }
                        ?>
                        
                        <option value="{{ $day }}"{{ $selected }}>{{ $day }}</option>
                    @endforeach
            </select>
            </div>
            
            @if ($errors->has('plan_date'))
                <span class="help-block">
                    <strong>{{ $errors->first('plan_date') }}</strong>
                </span>
            @endif

        </fieldset>
        
    
        <?php
            // 西濃関連 ======================================================================
            $seinouObj = Ctm::getSeinouObj();
        ?>
        
        @if(count($seinouHuzaiSes) > 0 || count($seinouNoHuzaiSes) > 0)
        <hr>
        <div class="clearfix">
        
            <div class="pb-3">
                ■下記の商品につきまして
                <ul class="mt-1 mb-0 pl-0 list-unstyled">
                    <li class="mb-2 text-kon text-bold">「ご希望日程」が日曜日の場合は、1商品につき{{ number_format($seinouObj->sundayFee) }}円増しとなります。
                    <?php
                    /*
                    <li>不在置きを了承頂ける場合はチェックをして下さい。
                        <ul class="text-small">
                        <li class="mb-1 mt-2"><span class="text-extra-big"><b class="text-big">チェック時は1商品につき{{ number_format($seinouObj->huzaiokiFee) }}円引きとなります。</b></span></li>
                        <li class="mb-1">下に表示される枠内に不在時の荷物の置き場所を記載して下さい。</li>
                        <li class="mb-1">お支払い方法の「代金引換」はご利用出来ません。</li>
                        <ul>
                    </li>
                    */
                    ?>
                </ul>
            </div>
            
            @if(count($seinouHuzaiSes) > 0)
                <fieldset id="huzaioki" class="form-group mt-2 mb-2 pl-1{{ $errors->has('is_huzaioki.*') ? ' border border-danger' : '' }}">
                    
                    <input type="hidden" name="is_seinou" value="1" form="user-input">
                    <input type="hidden" name="is_huzaioki" value="1" form="user-input">
                    
                    <div class="">
                        @foreach($seinouHuzaiSes as $sesKey => $sesVal)
                            
                            <?php
                                $si = Item::find($sesVal['item_id']);
                                $siTitle = Ctm::getItemTitle($si);
                            ?>
                            
                            <div class="clearfix mb-2 ml-1">
                                <div class="float-left mr-2">
                                    @include('main.shared.smallThumbnail', ['item'=>$si, 'width'=>140])
                                </div>
                                
                                <span class="text-big text-bold">{{ $siTitle }}</span><br>
                                <span class="">[{{ $si->number }}]</span>
                                <p class="text-danger text-small mt-2 p-0">不在置きを了承する</p>
                                
                                <input type="hidden" name="seinouHuzaiTitle[]" value="{{ $siTitle }}" form="user-input">
                            </div>

                        @endforeach
                        
                        <?php
                        /*
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
                        */
                        ?>
                        
                        <?php
                        /*
                        <div class="mt-3 pt-1">
                            <input type="hidden" name="is_huzaioki" value="0">
                            
                            <input id="check-huzaioki-0" type="checkbox" name="is_huzaioki" value="1"{{ $checked }} form="user-input">
                            <label for="check-huzaioki-0" class="checks ml-1"><b class="text-big">不在置きを了承する</b></label>
                            
                            @if ($errors->has('is_huzaioki'))
                                <div class="help-block text-danger">
                                    <span class="fa fa-exclamation form-control-feedback"></span>
                                    <span>{{ $errors->first('is_huzaioki') }}</span>
                                </div>
                            @endif
                        
                            <input type="hidden" name="is_seinou" value="1" form="user-input">
                        </div>
                        */
                        ?>
                        
                        <div class="pt-1 huzai-comment-wrap pl-0 mb-1 pb-2">
                            <p class="ml-1 mb-2">不在時の置き場所を記載して下さい<em>必須</em></p>
                            <fieldset class="form-group">
                                <textarea id="huzai_comment" class="form-control {{ $errors->has('huzai_comment') ? ' is-invalid' : '' }}" name="huzai_comment" rows="6" placeholder="例：玄関前、門扉の裏、玄関右側入り庭ウッドデッキ付近・・など" form="user-input">{{ Ctm::isOld() ? old('huzai_comment') : (Session::has('all.data.huzai_comment') ? session('all.data.huzai_comment') : '') }}</textarea>
                                
                                @if ($errors->has('huzai_comment'))
                                    <div class="help-block text-danger receiver-error">
                                        <span class="fa fa-exclamation form-control-feedback"></span>
                                        <span>{{ $errors->first('huzai_comment') }}</span>
                                    </div>
                                @endif
                            </fieldset>
                        </div>
                        
                    </div>
                    
                </fieldset>
            @endif
        
            @if(count($seinouNoHuzaiSes) > 0)
                <fieldset class="form-group mt-2 mb-2 pl-1{{ $errors->has('is_huzaioki.*') ? ' border border-danger' : '' }}">
                    <div class="">
                        @foreach($seinouNoHuzaiSes as $sesKey => $sesVal)
                            
                            <?php
                                $si = Item::find($sesVal['item_id']);
                                $siTitle = Ctm::getItemTitle($si);
                            ?>
                            
                            <div class="clearfix mb-2 ml-1">
                                <div class="float-left mr-2">
                                    @include('main.shared.smallThumbnail', ['item'=>$si, 'width'=>140])
                                </div>
                                
                                <span class="text-big text-bold">{{ $siTitle }}</span><br>
                                <span class="">[{{ $si->number }}]</span>
                                <p class="text-small mt-2 p-0">不在置きを了承しない</p>
                                
                                <input type="hidden" name="seinouNoHuzaiTitle[]" value="{{ $siTitle }}" form="user-input">
                            </div>
                        @endforeach
                        
                    </div>
                </fieldset>
            @endif
        
        </div>
        @endif
        
        <?php
        // 西濃関連 END ======================================================================
        ?>
        
            
        @if(count($dgGroup) > 0)
            <hr>
            <fieldset class="form-group mt-3 mb-1 pl-1 py-2{{ $errors->has('plan_time.*') ? ' border border-danger' : '' }}">
                
                <p class="mb-1 pb-1">■下記の商品につきまして、ご希望配送時間の指定ができます。</p>
                
                @foreach($dgGroup as $key => $val)
                    
                    <div class="mb-0 pb-1">
                        @if(session()->has('item.data') && count(session('item.data')) > 0)
                            <div class="table-responsive table-cart clearfix">
                            <table class="table mb-0 pb-0">
                            
                             @foreach($val as $itemId)
                                <?php
                                    $i = Item::find($itemId);
                                    $iTitle = Ctm::getItemTitle($i);
                                ?>
                                
                                @if($key)
                                    <tr class="">
                                        <th class="">
                                            @include('main.shared.smallThumbnail', ['item'=>$i, 'width'=>140])
                                        </th>
                                        
                                        <td>
                                            <span class="">{{ $iTitle }}</span><br>
                                            <span class="">[{{ $i->number }}]</span>
                                        </td>
                                    </tr>
                                @endif
                                
                                <input type="hidden" name="planTimeItemTitle[{{ $key }}][]" value="{{ $iTitle }}" form="user-input">
                                
                             @endforeach
                             </table>
                             </div>
                        @endif
                    
                        {{--
                        @if(session()->has('item.data') && count(session('item.data')) > 0)
                            
                             @foreach($val as $itemId)
                        --}}
                                <?php
//                                    $i = Item::find($itemId);
//                                    $iTitle = Ctm::getItemTitle($i);
                                ?>
                        {{--
                                <div class="clearfix mb-2 ml-1">
                                    <div class="float-left mr-2">
                                        @include('main.shared.smallThumbnail', ['item'=>$i, 'width'=>140])
                                    </div>
                                    
                                    <span class="text-big text-bold">{{ $iTitle }}</span><br>
                                    <span class="">[{{ $i->number }}]</span>
                                </div>
                                
                                <input type="hidden" name="planTimeItemTitle[{{ $key }}][]" value="{{ $iTitle }}" form="user-input">
                             @endforeach
                        @endif
                        --}}
                         
                    </div>
                    
                    @if($key)
                        <div class="pb-2 mb-4 ml-1">
                            <?php
                                $timeTable = DeliveryGroup::find($key)->time_table;
                                $timeTable = explode(",", $timeTable);
                            ?>
                            
                            <span class="deliRadioWrap">
                                <input id="radio-deli-{{ $key }}-no" type="radio" name="plan_time[{{$key}}]" value="希望なし" class="deliRadio" checked form="user-input">
                                <label for="radio-deli-{{ $key }}-no" class="radios">希望なし</label>
                                
                                {{--
                                <input type="radio" name="plan_time[{{$key}}]" class="deliRadio" value="希望なし" checked><span class="mr-3"> 希望なし</span>
                                --}}
                            </span>
                            
                            @foreach($timeTable as $k => $table)
                                <?php
                                    $checked = '';
                                    
                                    if( Ctm::isOld()) {
                                        if( old('plan_time.'.$key) == $table) {
                                            $checked = ' checked';
                                        }
                                    }
                                    elseif(Session::has('all.data.plan_time.'.$key)) {
                                        if(session('all.data.plan_time.'.$key) == $table) {
                                            $checked = ' checked';
                                        }
                                    }
                                 ?>
                                
                                <span class="deliRadioWrap">
                                    <input id="radio-deli-{{ $key }}-{{ $k }}" type="radio" name="plan_time[{{$key}}]" value="{{ $table }}" class="deliRadio" {{ $checked }} form="user-input">
                                    <label for="radio-deli-{{ $key }}-{{ $k }}" class="radios">{{ $table }}</label>
                                    
                                    {{--
                                    <input type="radio" name="plan_time[{{$key}}]" class="deliRadio" value="{{ $table }}" {{ $checked }}> <span class="mr-3">{{ $table }}</span>
                                    --}}
                                </span>
                            @endforeach
                                
                        </div>
                    @endif
                    
                 @endforeach
                 
                 @if ($errors->has('plan_time.*'))
                    <div class="help-block text-danger mb-2">
                        <span class="fa fa-exclamation form-control-feedback"></span>
                        <span>{{ $errors->first('plan_time.*') }}</span>
                    </div>
                @endif
            
            </fieldset>
        @endif
        
    </div>{{-- ml-20per --}}
            
</div>{{-- id --}}


<div id="other-comment" class="pt-2 mb-5">
    <div class="clearfix mt-3">
        <h3>コメント</h3>
        <div></div>
    </div>
    
    <div class="ml-20per">
        <div class="form-group my-3">
            <textarea id="user_comment" class="form-control{{ $errors->has('user_comment') ? ' is-invalid' : '' }}" name="user_comment" rows="10" form="user-input">{{ Ctm::isOld() ? old('user_comment') : (Session::has('all.data.user_comment') ? session('all.data.user_comment') : '') }}</textarea>
            
            @if ($errors->has('user_comment'))
                <div class="help-block text-danger receiver-error">
                    <span class="fa fa-exclamation form-control-feedback"></span>
                    <span>{{ $errors->first('user_comment') }}</span>
                </div>
            @endif
        </div>
    </div>
</div>
                
                
<div id="pay-method" class="pt-2 mb-4">
    <div class="clearfix mt-3">
        <h3>お支払方法</h3>
        <div></div>
    </div>
 
    <div class="ml-20per">
    <a href="{{ url('about-pay?from-cart=1') }}" class="d-inline-block mt-2 ml-1 text-small" target="_brank">お支払についてのご注意はこちら <i class="fal fa-angle-double-right"></i></a>
        
        @if ($errors->has('pay_method'))
            <div class="help-block text-danger mt-2 mb-0">
                <span class="fa fa-exclamation form-control-feedback"></span>
                <span>{{ $errors->first('pay_method') }}</span>
            </div>
        @endif
        
        <fieldset class="form-group mt-1 pt-3 pl-1{{ $errors->has('pay_method') ? ' border border-danger' : '' }}">

            @foreach($payMethod as $method)
                <?php
                    $checked = '';
                    
                    if( Ctm::isOld()) {
                        if( old('pay_method') == $method->id) {
                            $checked = ' checked';
                        }
                    }
                    elseif(Session::has('all.data.pay_method')) {
                        if(session('all.data.pay_method') == $method->id) {
                            $checked = ' checked';
                        }
                    }
                 ?>
                 
                <div class="mb-3">
                       
                    @if($method->id == 1)
                        <input id="radio-pay-{{ $method->id }}" type="radio" name="pay_method" value="{{ $method->id }}" class="payMethodRadio"{{ $checked }} form="user-input">
                        <label for="radio-pay-{{ $method->id }}" class="radios">{{ $method->name }}</label>
                        
                        {{--
                        <input type="radio" name="pay_method" class="payMethodRadio ml-2" value="{{ $method->id }}"{{ $checked }}> {{ $method->name }}
                        --}}
                        
                        <div class="wrap-all-card">
                        
                        
                        <?php //get RegistCard ------------------------- ?>
                        
                        @if(count($regCardDatas) > 0)
                            <div class="wrap-regist-card mt-3 mb-2 ml-1 pl-3">
                                @if(isset($regCardErrors))
                                    <small class="d-inline-block ml-3 mb-3">
                                        <span class="text-danger"><i class="fal fa-exclamation-triangle"></i> 登録カード情報が取得出来ません。</span>
                                        @if(isset($regCardErrors))
                                        <br><span class="text-secondary">{{ $regCardErrors }}</span>
                                        @endif
                                    </small>
                                @else
                                    @foreach($regCardDatas['CardSeq'] as $k => $seqNum)
                                        <?php
                                            $checked = '';
                                            
                                            if( Ctm::isOld()) {
                                                if( old('card_seq') == $seqNum) {
                                                    $checked = ' checked';
                                                }
                                            }
                                            elseif(Session::has('all.data.card_seq')) {
                                                if(session('all.data.card_seq') == $seqNum) {
                                                    $checked = ' checked';
                                                }
                                            }
                                        ?>
                                         
                                        <div class="mb-4 pb-1">
                                            <input id="use-card-{{ $k }}" type="radio" name="card_seq" class="useCardRadio" value="{{ $seqNum }}"{{ $checked }} form="user-input">
                                            <label for="use-card-{{ $k }}" class="radios">
                                                カード番号： <span>{{ $regCardDatas['CardNo'][$k] }}</span>
                                            </label>
                                            
                                            <?php
                                                //wordwrap($regCardDatas['Expire'][$k], 2, '/', 1)
                                                $y = substr($regCardDatas['Expire'][$k], 0, 2); //年
                                                $m = substr($regCardDatas['Expire'][$k], 2); //月
                                            ?>
                                            
                                            <small class="d-block ml-4 mt-1">有効期限（月/年）：{{ $m.'/'.$y }}</small>
                                            
                                        </div>
                                    @endforeach
                                @endif
                                
                            </div>
                        @endif
                        
                        
                        <?php //NewCard ------------------------- ?>

                        <div class="mt-2 mb-5 ml-1 pl-3">
                            
                            @if(count($regCardDatas) > 0)
                                <?php
                                    $ml = ' ml-4';
                                    $checked = '';
                                    
                                    if( Ctm::isOld()) {
                                        if( old('card_seq') == 99) {
                                            $checked = ' checked';
                                        }
                                    }
                                    elseif(Session::has('all.data.card_seq')) {
                                        if(session('all.data.card_seq') == 99) {
                                            $checked = ' checked';
                                        }
                                    }
                                ?>
                            
                                <input id="use-new-card" type="radio" name="card_seq" class="useCardRadio" value="99"{{ $checked }} form="user-input">
                                <label for="use-new-card" class="radios">新しいクレジットカードを使う</label>
                            @else
                                <?php $ml = ' ml-2'; ?>
                                
                                <input type="hidden" name="card_seq" value="99" form="user-input">
                            @endif
                            
                            <div class="wrap-new-card mt-3 pl-1{{ $ml }}">
                                
                                @if (count($cardErrors) > 0)
                                    <p class="mb-1">
                                    <span class="fa fa-exclamation form-control-feedback text-danger"></span>
                                    <span class="text-danger">{{ $cardErrors['carderr'] }}</span>
                                    </p>
                                @endif
                                
                                <div class="{{ count($cardErrors) > 0 ? ' border border-danger pl-2' : '' }}">
                                <div class="mb-3">
                                    <label>カード番号</label>
                                    <input type="text" id="cardno" class="form-control col-md-12{{ $errors->has('cardno') ? ' is-invalid' : '' }}" name="cardno" value="{{ Ctm::isOld() ? old('cardno') : (Session::has('all.data.cardno') ? session('all.data.cardno') : '') }}" placeholder="例）1234123412341234（ハイフンなし半角数字）" form="user-input">
                           
                                    @if ($errors->has('cardno'))
                                        <div class="help-block text-danger receiver-error">
                                            <span class="fa fa-exclamation form-control-feedback"></span>
                                            <span>{{ $errors->first('cardno') }}</span>
                                        </div>
                                    @endif
                                </div>
                                
                                <div class="mb-3">
                                    <label>セキュリティコード</label>
                                    <input type="text" id="securitycode" class="form-control col-md-12{{ $errors->has('securitycode') ? ' is-invalid' : '' }}" name="securitycode" value="{{ Ctm::isOld() ? old('securitycode') : (Session::has('all.data.securitycode') ? session('all.data.securitycode') : '') }}" placeholder="例）1234（3〜4桁 半角数字）" form="user-input">
                           
                                    @if ($errors->has('securitycode'))
                                        <div class="help-block text-danger receiver-error">
                                            <span class="fa fa-exclamation form-control-feedback"></span>
                                            <span>{{ $errors->first('securitycode') }}</span>
                                        </div>
                                    @endif
                                </div>
                                
                                <div class="mb-3">
                                    <?php
                                        $time = new DateTime('now');
                                        $expireYear = $time->format('y');
                                        //$withYoubi .= ' (' . $week[$time->format('w')] . ')';
                                    
                                        $yn = 0;
                                        $mn = 1;
                                    ?>
                                
                                    <label class="d-block">有効期限（月/年）</label>
                                    
                                    <label class="select-wrap col-md-3 p-0">
                                    <select id="expire_month" class="form-control d-inline-block {{ $errors->has('expire_month') || $errors->has('expire') ? ' is-invalid' : '' }}" name="expire_month" form="user-input">
                                        
                                        @while($mn < 13)
                                            <?php
                                                $expireMonth = str_pad($mn, 2, 0, STR_PAD_LEFT); //2桁0埋め
                                                
                                                $selected = '';
                                                if(Ctm::isOld()) {
                                                    if(old('expire_month') == $expireMonth)
                                                        $selected = ' selected';
                                                }
                                                else {
                                                    if(Session::has('all.data.expire_month')  && session('all.data.expire_month') == $expireMonth) {
                                                        $selected = ' selected';
                                                    }
                                                }
                                            ?>
                                            
                                            <option value="{{ $expireMonth }}"{{ $selected }}>{{ $expireMonth }}</option>
                                            
                                            <?php $mn++; ?>
                                        @endwhile
                                    </select>
                                    </label>
                                    <span class="mr-4">月</span>
                                    
                                    
                                    @if ($errors->has('expire_month'))
                                        <div class="help-block text-danger col-md-3">
                                            <span class="fa fa-exclamation form-control-feedback"></span>
                                            <span>{{ $errors->first('expire_month') }}</span>
                                        </div>
                                    @endif
                                    
                                    <label class="select-wrap col-md-3 p-0">
                                    <select id="expire_year" class="form-control d-inline-block {{ $errors->has('expire_year') || $errors->has('expire') ? ' is-invalid' : '' }}" name="expire_year" form="user-input">
                                        
                                        @while($yn < 11)
                                            <?php
                                                $selected = '';
                                                if(Ctm::isOld()) {
                                                    if(old('expire_year') == $expireYear + $yn)
                                                        $selected = ' selected';
                                                }
                                                else {
                                                    if(Session::has('all.data.expire_year')  && session('all.data.expire_year') == $expireYear + $yn) {
                                                        $selected = ' selected';
                                                    }
                                                }
                                            ?>
                                            
                                            <option value="{{ $expireYear + $yn }}"{{ $selected }}>{{ $expireYear + $yn }}</option>
                                            
                                            <?php $yn++; ?>
                                        @endwhile
                                    </select>
                                    </label>
                                    <span>年</span>
                                    
                                    
                                    
                                    @if ($errors->has('expire_year'))
                                        <div class="help-block text-danger col-md-3">
                                            <span class="fa fa-exclamation form-control-feedback"></span>
                                            <span>{{ $errors->first('expire_year') }}</span>
                                        </div>
                                    @endif
                                    
                                    <input type="hidden" name="expire" value="" form="user-input">
                                    
                                    @if ($errors->has('expire'))
                                        <div class="help-block text-danger">
                                            <span class="fa fa-exclamation form-control-feedback"></span>
                                            <span>{{ $errors->first('expire') }}</span>
                                        </div>
                                    @endif
                                    
                                </div>
                                
                                </div><!-- ErrorBlock -->
                                
                                {{--
                                @if($regist || (isset($userObj) && $userObj->card_regist_count < 6))
                                --}}
                                    @if(isset($userObj) && $userObj->card_regist_count > 4)
                                        <span class="text-small text-secondary"><i class="fas fa-exclamation-circle"></i> 新規カード登録不可（最大5つまで）</span>
                                    @else
                                        <label class="mt-2 regist-frame">
                                            <?php
                                                $checked = '';
                                                if(Ctm::isOld()) {
                                                     if(old('is_regist_card') !== null)
                                                        $checked = ' checked';
                                                }
                                                else {
                                                    if(Session::has('all.data.temp_is_regist_card') && session('all.data.temp_is_regist_card'))
                                                        $checked = ' checked';
                                                }
                                                
                                                $word = Auth::check() ? '' : '会員登録をして、';
                                                $red = Auth::check() ? '' : 'text-enji';
                                            ?>
                                            
                                            <input id="check-regist-card" type="checkbox" name="is_regist_card" value="1"{{ $checked }} form="user-input">
                                            <label for="check-regist-card" class="checks {{ $red }}">{{ $word }}このクレジットカードを登録する</label>
                                            
                                            {{--
                                            <input type="checkbox" name="is_regist_card" value="1"{{ $checked }}> このクレジットカード情報を登録する
                                            --}}
                                            
                                            @if ($errors->has('is_regist_card'))
                                                <div class="help-block text-danger">
                                                    <span class="fa fa-exclamation form-control-feedback"></span>
                                                    <span>{{ $errors->first('is_regist_card') }}</span>
                                                </div>
                                            @endif
                                        </label>
                                    @endif
                                {{--
                                @endif
                                --}}
                                
                            </div>
                            
                            {{--
                            <div class="mb-3">
                                <label>カード名義人</label>
                                <input type="text" id="holdername" class="form-control col-md-6{{ $errors->has('holdername') ? ' is-invalid' : '' }}" name="holdername" value="{{ Ctm::isOld() ? old('holdername') : (Session::has('all.data.holdername') ? session('all.data.holdername') : '') }}" placeholder="例：tarou yamada" form="user-input">
                       
                                @if ($errors->has('holdername'))
                                    <div class="help-block text-danger receiver-error">
                                        <span class="fa fa-exclamation form-control-feedback"></span>
                                        <span>{{ $errors->first('holdername') }}</span>
                                    </div>
                                @endif
                            </div>
                            
                            <input type="hidden" value="1" name="tokennumber" id="tokennumber" form="user-input">
                            --}}
                            
                        </div>
                        
                        </div>
                    
                    
                    @elseif(! $codCheck && $method->id == 5)
                        <input id="radio-pay-{{ $method->id }}" type="radio" name="pay_method" value="{{ $method->id }}" class="payMethodRadio" disabled form="user-input">
                        <label style="cursor:default; color:#afafaf;" for="radio-pay-{{ $method->id }}" class="radios">{{ $method->name }}</label>
                        
                        {{--
                        <input type="radio" name="pay_method" class="payMethodRadio ml-2" value="{{ $method->id }}" disabled> {{ $method->name }}
                        --}}
                        
                        <span class="text-secondary text-small ml-0"><i class="fas fa-exclamation-circle"></i> ご注文商品の代金引換不可</span>
                        
                    @else
                        <input id="radio-pay-{{ $method->id }}" type="radio" name="pay_method" value="{{ $method->id }}" class="payMethodRadio"{{ $checked }} form="user-input">
                        <label for="radio-pay-{{ $method->id }}" class="radios">{{ $method->name }}</label>
                        
                        
                        @if($method->id == 3)
                            <div class="wrap-pmc mt-1 pt-1 mb-3 ml-3 pl-2{{ $errors->has('net_bank') ? ' border border-danger' : '' }}">
                                @foreach($pmChilds as $pmChild)
                                    <?php
                                        $ch = '';
                                        if( Ctm::isOld()) {
                                            if( old('net_bank') == $pmChild->id) {
                                                $ch = ' checked';
                                            }
                                        }
                                        elseif(Session::has('all.data.net_bank')) {
                                            if(session('all.data.net_bank') == $pmChild->id) {
                                                $ch = ' checked';
                                            }
                                        }
                                     ?>
                                    
                                    <span class="deliRadioWrap">
                                        <input type="radio" name="net_bank" class="pmcRadio" value="{{ $pmChild->id }}" {{ $ch }} form="user-input"> <span class="mr-3">{{ $pmChild->name }}</span>
                                    </span>
                                @endforeach
                            </div>
                        @endif
                        
                    @endif
                
                </div>
                
             @endforeach
        
        </fieldset>
     </div>{{-- ml-20per --}}
</div>{{-- id --}}
                
    <div class="ml-20per mt-5 pt-2">
        <button class="btn btn-block btn-kon mb-0 mx-auto py-3" type="submit" name="recognize" value="1">次へ進む</button>
    </div>{{-- ml-20per --}}
    
    {{--
    <input type="hidden" name="regist" value="{{ $regist }}">
    --}}

</div>{{-- left --}}


<div class="confirm-right position-relative">
<div class="right-wrap">
    
    <div class="right-blue mb-2">
        <div>
            <button class="btn btn-block btn-kon mb-4 mx-auto py-3" type="submit" name="recognize" value="1">次へ進む</button>
        </div>
        
        <div class="table-responsive table-foot">
        <table class="table mb-0 pb-0">
             <tbody class="clearfix">
                 <tr>
                    <th>商品合計</th>
                    <td>
                        ¥{{ number_format($allPrice) }}
                    </td>
                  </tr>

                <tr>
                    <th>送料</th>
                    <td>
                        @if(isset($deliFee))
                            ¥{{ number_format($deliFee) }}
                            
                            @if(Auth::check() && isset($prefName))
                                <span class="d-block text-extra-small text-enji font-weight-normal">＊ご登録先[{{ $prefName }}]への送料</span>
                            @endif
                        @else
                            <b class="text-enji">含まれておりません</b>
                            <?php $deliFee = 0; ?>
                        @endif
                    </td>
                </tr>
                
                {{--
                <tr>
                    <th class="text-left text-big">
                        <b>合計 <small>(税込)</small></b>
                    </th>
                    
                    <td class="text-extra-big text-danger">
                        <b class="text-big">¥{{ number_format($allPrice + $deliFee) }}</b>
                    </td>
                </tr>
                --}}
               
             </tbody>
        </table>
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
    
    </div>{{-- blue --}}
    
    @if(! Auth::check())
        <div class="right-gray cart-login">
            <small class="text-center d-block mt-0 mb-1 pt-0">会員登録済みの方はログインして下さい</small>
            @include('main.shared.userLogin', ['pageType'=>'cart'])
        </div>
    @endif

</div>
</div>{{-- confirm-right --}}

</form>
</div>{{-- clear --}}


@includeWhen(Ctm::isEnv('local'), 'cart.shared.backBtn', ['urlForBack'=>'cart', 'textForBack'=>'カートに戻る'])


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


