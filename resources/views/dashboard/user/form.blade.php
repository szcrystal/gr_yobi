@extends('layouts.appDashBoard')

@section('content')
	
	<div class="text-left">
        <h1 class="Title">
        @if($isUser)
        会員情報
        @else
        非会員情報
        @endif
        </h1>
        <p class="Description"></p>
    </div>

    <div class="row">
      <div class="col-sm-12 col-md-6 col-lg-6 col-xl-5 mb-5">
        <div class="bs-component clearfix">
        <div class="pull-left">
        	<?php
         		$link = $isUser ? '' : "?no_r=1";
         	?>   
            <a href="{{ url('/dashboard/users'.$link) }}" class="btn bg-white border border-1 border-round border-secondary text-primary"><i class="fa fa-angle-double-left" aria-hidden="true"></i>一覧へ戻る</a>
        </div>
        </div>
    </div>
  </div>

    @if (count($errors) > 0)
        <div class="alert alert-danger">
            <strong>Error!!</strong> 追加できません<br><br>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
        
	@if (session('status'))
        <div class="alert alert-success">
            {!! session('status') !!}
        </div>
    @endif
        
    <div class="mb-5">
        <form class="form-horizontal" role="form" method="POST" action="/dashboard/users">

            {{ csrf_field() }}
            
            <div class="form-group mb-2">
                <button type="submit" class="btn btn-primary btn-block mx-auto w-btn w-25">更　新</button>
            </div>
            
            @if($isUser)
                <input type="hidden" name="is_user" value="1">
            @else
                <input type="hidden" name="is_user" value="0">
            @endif

            <input type="hidden" name="edit_id" value="{{ $user->id }}">
            
			<h4 class="mb-1">会員情報</h4>
            	<div class="table-responsive">
                    <table class="table table-bordered">
                        <colgroup>
                            <col style="background: #dfdcdb; width: 22%;" class="cth">
                            <col style="background: #fefefe;" class="ctd">
                        </colgroup>
                        
                        <tbody>
                        	<tr>
                                <th></th>
                                <td>
                                	@if($isUser)
                                 		<span class="text-primary text-big">
                                        	<b>会員
                                            	@if(! $user->active)
                                                    <span class="text-warning"><b>[退会]</b></span>
                                                @endif
                                            </b>  
                                 	@else
                                  		<span class="text-warning text-big"><b>非会員</b>   
                                  	@endif      
                                </td>
                            </tr>
                            <tr>
                                <th>ID</th>
                                <td>{{ $user->id }}</td>
                            </tr>

                            <tr>
                                <th>名前</th>
                                <td>
                                    <input  class="form-control col-md-12{{ $errors->has('name') ? ' is-invalid' : '' }}" name="name" value="{{ Ctm::isOld() ? old('name') : (isset($user) ? $user->name : '') }}">
                                    
                                    @if ($errors->has('name'))
                                        <div class="help-block text-danger">
                                            <span class="fa fa-exclamation form-control-feedback"></span>
                                            <span>{{ $errors->first('name') }}</span>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>フリガナ</th>
                                <td>
                                    <input  class="form-control col-md-12{{ $errors->has('hurigana') ? ' is-invalid' : '' }}" name="hurigana" value="{{ Ctm::isOld() ? old('hurigana') : (isset($user) ? $user->hurigana : '') }}">
                                    
                                    @if ($errors->has('hurigana'))
                                        <div class="help-block text-danger">
                                            <span class="fa fa-exclamation form-control-feedback"></span>
                                            <span>{{ $errors->first('hurigana') }}</span>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                            
                            <tr>
                                <th>電話番号</th>
                                <td>
                                    <input  class="form-control col-md-12{{ $errors->has('tel_num') ? ' is-invalid' : '' }}" name="tel_num" value="{{ Ctm::isOld() ? old('tel_num') : (isset($user) ? $user->tel_num : '') }}">
                                    
                                    @if ($errors->has('tel_num'))
                                        <div class="help-block text-danger">
                                            <span class="fa fa-exclamation form-control-feedback"></span>
                                            <span>{{ $errors->first('tel_num') }}</span>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                            
                            
                            <tr>
                                <th>郵便番号</th>
                                <td>{{-- 〒{{ Ctm::getPostNum($user->post_num) }} --}}
                                    <input id="zipcode" class="form-control col-md-12{{ $errors->has('post_num') ? ' is-invalid' : '' }}" name="post_num" value="{{ Ctm::isOld() ? old('post_num') : (isset($user) ? $user->post_num : '') }}">
                                    
                                    @if ($errors->has('post_num'))
                                        <div class="help-block text-danger">
                                            <span class="fa fa-exclamation form-control-feedback"></span>
                                            <span>{{ $errors->first('post_num') }}</span>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>都道府県</th>
                                <td>
                                    <div class="select-wrap col-md-6 p-0 m-0">
                                        <select id="pref" class="form-control{{ $errors->has('prefecture') ? ' is-invalid' : '' }}" name="prefecture">
                                            <option selected value="0">選択して下さい</option>
                                            <?php
                                                use App\Prefecture;
                                                $prefs = Prefecture::all();
                                            ?>
                                            @foreach($prefs as $pref)
                                                <?php
                                                    $selected = '';
                                                    if(Ctm::isOld()) {
                                                        if(old('prefecture') == $pref->name)
                                                            $selected = ' selected';
                                                    }
                                                    else {
                                                        if(isset($user) && $user->prefecture == $pref->name) {
                                                            $selected = ' selected';
                                                        }
                                                    }
                                                ?>
                                                
                                                <option value="{{ $pref->name }}"{{ $selected }}>{{ $pref->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                            
                                    @if ($errors->has('prefecture'))
                                        <div class="help-block text-danger">
                                            <span class="fa fa-exclamation form-control-feedback"></span>
                                            <span>{{ $errors->first('prefecture') }}</span>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>住所1</th>
                                <td>
                                    <input id="address" class="form-control col-md-12{{ $errors->has('address_1') ? ' is-invalid' : '' }}" name="address_1" value="{{ Ctm::isOld() ? old('address_1') : (isset($user) ? $user->address_1 : '') }}">
                                    
                                    @if ($errors->has('address_1'))
                                        <div class="help-block text-danger">
                                            <span class="fa fa-exclamation form-control-feedback"></span>
                                            <span>{{ $errors->first('address_1') }}</span>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>住所2</th>
                                <td>
                                    <input  class="form-control col-md-12{{ $errors->has('address_2') ? ' is-invalid' : '' }}" name="address_2" value="{{ Ctm::isOld() ? old('address_2') : (isset($user) ? $user->address_2 : '') }}">
                                    
                                    @if ($errors->has('address_2'))
                                        <div class="help-block text-danger">
                                            <span class="fa fa-exclamation form-control-feedback"></span>
                                            <span>{{ $errors->first('address_2') }}</span>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                            
                            {{--
                            <tr>
                                <th>住所3</th>
                                <td>{{ $user->address_3 }}</td>
                            </tr>
                            --}}
                            
                            <tr>
                                <th>メールアドレス</th>
                                <td><a href="mailto:{{ $user->email }}">{{ $user->email }}</a></td>
                            </tr>
                            
                            <tr>
                            	@if($isUser)
                                    <th>メールマガジン</th>
                                    <td>
                                        @if($user->magazine)
                                        	<span class="text-info">登録済</span>
                                        @else
                                        	<span class="text-warning">未登録</span>
                                        @endif
                                        <br>
                                        <?php
                                            $checked = '';
                                            if(Ctm::isOld()) {
                                                if(old('magazine'))
                                                    $checked = ' checked';
                                            }
                                            else {
                                                if(isset($user) && $user->magazine) {
                                                    $checked = ' checked';
                                                }
                                            }
                                        ?>
                                        
                                        <input id="check-magazine" type="checkbox" name="magazine" value="1"{{ $checked }}>
                                        <label for="check-magazine" class="checks">登録する</label>
                                        
                                        {{--
                                        <input type="checkbox" name="user[magazine]" value="1"{{ $checked }}> 登録する
                                        --}}
                                        
                                        @if ($errors->has('magazine'))
                                            <div class="help-block text-danger">
                                                <span class="fa fa-exclamation form-control-feedback"></span>
                                                <span>{{ $errors->first('magazine') }}</span>
                                            </div>
                                        @endif
                                    </td>
                                @endif
                            </tr>
                            
                            @if($isUser)
                                <tr>
                                    <th>残ポイント</th>
                                    <td>
                                    	{{-- $user->point --}}
                                        
                                    	<input  class="form-control col-md-6{{ $errors->has('point') ? ' is-invalid' : '' }}" name="point" value="{{ Ctm::isOld() ? old('point') : (isset($user) ? $user->point : '') }}">
                                        
                                    </td>   
                                </tr>
                                
                                <tr>
                                    <th>GmoID<br>クレカ登録数</th>
                                    <td>
                                    	@if(isset($user->member_id))
                                    		{{ $user->member_id }}<br>
                                    		<small>登録数: </small>{{ $user->card_regist_count }}
                                        @else
                                        	未登録<br>
							            	{{-- <p class="m-0 p-0"><span class="text-small">利用可能なGmoID：</span>{{ Ctm::getOrderNum(11) }}</p> --}}
                                        @endif
                                    </td>   
                                </tr>
                            @endif
                            
                            <tr>
                                <th>登録日</th>
                            	<td>{{ Ctm::changeDate($user->created_at) }}</td>
                            </tr>
                            
                            <tr>
                                <th>性別</th>
                                <td>{{ $user->gender }}</td>
                            </tr>
                            <tr>
                                <th>生年月日</th>
                                <td>
                                    @if($user->birth_year && $user->birth_month && $user->birth_day)
                                        {{ $user->birth_year }}/{{ $user->birth_month }}/{{ $user->birth_day }}
                                    @else
                                        --
                                    @endif
                                </td>
                            </tr>
                            
                            {{--
                            <tr>
                                <th>対応状況</th>
                                <td>
                                    <div class="form-group{{ $errors->has('status') ? ' has-error' : '' }}">
                                        <div class="col-md-10">
                                            <div class="checkbox">
                                                <label>
                                                    <input type="checkbox" name="status" value="1"{{isset($contact) && $contact->status ? ' checked' : '' }}> 対応済みにする
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            --}}

                            <tr>

                            </tr>
                            

                        </tbody>
                    </table>
                </div>
   
   				@if($isUser)
                    <div class="form-group mb-5">
                        <button type="submit" class="btn btn-primary btn-block mx-auto w-btn w-25">更　新</button>
                    </div>
                @endif
                
                <h4 class="mt-3">購入商品</h4>
       
        		{{ $sales->links() }}
        
                <div class="table-responsive">
                    <table class="table table-bordered bg-white">
                    	<thead>
                     		<tr style="background: #dfdcdb;">
                       			<th>SaleID<br>注文番号</th>      
                       			<th>(ID)商品名</th>
                          		<th>個数</th>
                            	<th>商品合計</th>
                             	<th>発送状況</th>    
                             	<th>購入日</th>
                                <th></th>
                       		</tr>         
                        </thead>
                        
                        <tbody>
                        	@foreach($sales as $sale)
                            	<?php $item = $itemModel->find($sale->item_id); ?>
                                
                                <tr>
                                    <td>
                                        {{ $sale->id }}<br>
                                        <a href="{{ url('dashboard/sales/order/'. $sale->order_number) }}">{{ $sale->order_number }}</a>
                                    </td>
                                    <td>
                                        <div class="float-left mr-2">
                                            @include('main.shared.smallThumbnail')
                                        </div>
                                        
                                        <a href="{{ url('dashboard/items/'. $sale->item_id) }}">
                                            [{{ $sale->item_id }}] 
                                            {{ Ctm::getItemTitle($item) }}<br>
                                        </a>
                                        <span class="text-small">商品番号: {{ $item->number }}</span><br>
                                        <span class="text-small">¥{{ number_format($sale->single_price) }}</span>
                                        
                                    </td>
                                    
                                    <td>{{ $sale->item_count }}</td>
                                    <td>¥{{ number_format($sale->total_price) }}</td>
                                   <td>
                                    @if($sale->deli_done)
                                        <span class="text-success">発送済み</span>
                                    @else
                                        <span class="text-danger">未発送</span>
                                    @endif   
                                   </td>   
                                  <td>{{ Ctm::changeDate($sale->created_at, 0)  }}</td>   
                                  <td><a href="{{ url('dashboard/sales/'.$sale->id) }}" class="btn btn-info">注文確認</a></td>              
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

				
                
                
                
{{ $sales->links() }}

        </form>
    </div>

@endsection
