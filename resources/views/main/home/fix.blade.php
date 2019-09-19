@extends('layouts.app')

<?php
use App\TopSetting;
?>

@section('belt')
<div class="tophead-wrap">
    <div class="clearfix">
        {!! nl2br(TopSetting::get()->first()->contents) !!}
    </div>
</div>
@endsection


@section('bread')
@include('main.shared.bread')
@endsection


@section('content')

<div id="main" class="fix-page {{ $fix->slug }}">

	{{--
	@if($fix->slug == 'about-pay' && Request::has('from-cart'))
    	<div>
            <a href="#" class="btn border border-secondary bg-white mt-2 mb-4" onClick="history.back(); return false;">
            <i class="fal fa-angle-double-left"></i> ご注文情報の入力に戻る
            </a>
        </div>
    @endif
    --}}

    <div class="panel panel-default">
        <h2 class="mb-3 card-header">{{ $fix->title }}</h2>

        <div class="panel-body">
            <div class="clearfix">
 
                {!! $fix->contents !!}
            
            </div>
		</div>
    </div>
    
    {{--
    @if($fix->slug == 'about-pay' && Request::has('from-cart'))
    	<div>
            <a href="#" class="btn border border-secondary bg-white mt-0 mb-3" onClick="history.back(); return false;">
            <i class="fal fa-angle-double-left"></i> ご注文情報の入力に戻る
            </a>
        </div>
    @endif
    --}}

</div>

@endsection

{{--
@section('leftbar')
    @include('main.shared.leftbar')
@endsection
--}}





