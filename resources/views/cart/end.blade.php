@extends('layouts.app')

@section('content')

<?php
use App\Item;

?>

<div class="single top-cont">

<div class="clearfix">
    <h2></h2>
</div>


<div class="panel-body">

@include('cart.shared.guide', ['active'=>4])

    <div class="cont-wrap">
        
     <?php //print_r($data); ?>   

        <div class="clearfix contents text-center">
            <?php
//                     	   	$pmName = $pmModel->find($pm)->name; 
//                        	$thankStr = "お買い上げ、ありがとうございます。<br>ご注文が完了致しました。";
            ?>
            
            お買い上げ、ありがとうございます。<br>ご注文が完了致しました。<br>
            
            @if(isset($orderNumber))
                ご注文番号：[ {{ $orderNumber }} ] <br>
            @endif
            
            
            @if(isset($xml) && $xml != '')
                {!! nl2br($xml) !!}
            @endif
            
            @if(isset($regist) && $regist)
            	<p class="m-3 p-0">
                	登録された会員情報で<br>現在ログイン中です。
                </p>
            @endif
            
            
            <div class="text-center mt-5 pb-3">
                <a href="{{ url('/') }}">HOMEへ <i class="fal fa-angle-double-right"></i></a>   
            </div>    
            
            {{--
            @foreach($data as $val)
                <p>{{ $val }}</p>
            @endforeach 
          --}}     
        </div>

    </div>


</div><!-- panelbody -->

</div>
  
    
@endsection
