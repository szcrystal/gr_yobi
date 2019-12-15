@extends('layouts.app')

<?php
use App\Setting;
use App\Favorite;
use App\TopSetting;
//use App\Category;
?>


@section('content')
<div id="main" class="top home">

    <div class="panel panel-default">
        
        @if(! Ctm::isAgent('sp'))
        <div class="clearfix s-form-top">
            <form class="d-block mb-3 clearfix" role="form" method="GET" action="{{ url('search') }}">
                {{-- csrf_field() --}}

                <input type="search" class="form-control rounded-0" name="s" placeholder="キーワードを入力して下さい" value="{{ Request::has('s') ? Request::input('s') : '' }}">
                <button class="btn-s">検 索</button>

            </form>
            
            <div class="mt-3">
                人気検索ワード
                <ul class="list-unstyled clearfix">
                    <li class="float-left mr-2"><a href="">シマトネリコ</a></li>
                    <li class="float-left mr-2"><a href="">落葉樹</a></li>
                </ul>
            </div>
        </div>
        @endif

        <div class="panel-body top-cont">

@foreach($firstItems as $keyTitle => $firstItem)
	
    <?php   
    	$rankNum = 1;
        $type = $firstItem['type'];
    ?>
    

    @if(isset($firstItem['items']) && count($firstItem['items']) > 0)

        <div class="wrap-atcl top-first">
            <div class="head-atcl">
                <h2 class="pl-0">
                @if($type == 4)
                    <span class="bg-enji py-1 px-3 text-big">{{ $keyTitle }}</span>
                @else
                    {{ $keyTitle }}
                @endif
                </h2>
            </div>
        
            <div class="clearfix">
                @foreach($firstItem['items'] as $item)

                   <article class="main-atcl">
                        @if($type == 1)
                            <span class="top-new">NEW！</span>
                        @elseif($type == 2)
                            <span class="top-rank"><i class="fas fa-crown"></i><em>{{ $rankNum }}</em></span>
                        @endif
                                                        
                        @if($type == 5)
                    		@include('main.shared.atclCateSec', ['cateSec'=>$item])
                        @else
                        	@include('main.shared.atcl', [])
                        @endif
 
                    </article>
                    
                    @if($type == 2)
                        <?php $rankNum++; ?>
                    @endif

                @endforeach
            </div>
            
            <a href="{{ url($firstItem['slug']) }}" class="btn btn-block btn-custom bg-white border-secondary rounded-0">もっと見る <i class="fal fa-angle-double-right"></i></a>
            
        </div>
        
    @endif
    
    @if($type == 1)
        @if(! Ctm::isAgent('sp'))
            <div class="top-first mb-3 pb-3">
                <div class="head-atcl">
                    <h2 class="pl-0">カテゴリー</h2>
                </div>
                
                <div class="mt-2 pb-2">
                    @include('main.shared.cateList')
                </div>
            </div>
        @endif
        
        <div class="top-first mb-3 pb-3">
            <div class="head-atcl">
                <h2 class="pl-0">人気タグ</h2>
            </div>
            
            <div class="tags mt-3 mb-1 text-small">
                @include('main.shared.tag', ['tags'=>$popTagsFirst, 'num'=>0])
            </div>
            
            <div class="mr-3 text-right">
                <span class="more-tgl">もっと見る <i class="fal fa-angle-down"></i></span>
            </div>
            
            <div class="tags mt-2 mb-1 text-small more-list">
                @include('main.shared.tag', ['tags'=>$popTagsSecond, 'num'=>0])
            </div>
        </div>
    @endif
    
@endforeach

@if(isset($allRecoms) && count($allRecoms) > 0)
<div class="wrap-atcl top-second">
	<div class="head-atcl">
        <h2>おすすめ情報</h2>
    </div>
    
	<div class="clearfix">
    	@foreach($allRecoms as $recom)
        	<article class="main-atcl clearfix"> 
            
            <?php
            	$objName = get_class($recom);
                
                if($objName == 'App\Category') {
            		$slugType = 'category';
                }
                elseif($objName == 'App\CategorySecond') {
                	$slugType = 'category/' . $cates->find($recom->parent_id)->slug;
                }
                elseif($objName == 'App\Tag') {
                	$slugType = 'tag';
                }

                
//            	if(strpos($recom->top_img_path, 'category') !== false) {
//            		$slugType = 'category';
//                }
//                elseif(strpos($recom->top_img_path, 'subcate') !== false) {
//                	$slugType = 'category/' . $cates->find($recom->parent_id)->slug;
//                }
//                elseif(strpos($recom->top_img_path, 'tag') !== false) {
//                	$slugType = 'tag';
//                }
            ?>
        
                
                <div class="img-box">
                    <a href="{{ url($slugType . '/'. $recom->slug) }}">
                    <img src="{{ Storage::url($recom->top_img_path) }}" alt="{{ $recom->top_title }}">
                    </a>
                </div>
                
                <div class="meta">
                    <h3><a href="{{ url($slugType . '/'. $recom->slug) }}">{{ $recom->top_title }}</a></h3>
                    
                    <p>{!! nl2br($recom->top_text) !!}</p>    
                </div>
                
            </article>
        @endforeach
        
    </div>
    
    <a href="{{ url('recommend-info') }}" class="btn btn-block btn-custom bg-white border-secondary rounded-0">もっと見る <i class="fal fa-angle-double-right"></i></a>
    
</div>
@endif
 


	</div><!-- top cont --> 

    </div>

</div>

@endsection



@section('leftbar')
    @if(Ctm::isEnv('local'))
        <a href="{{ url('post') }}">POST</a>
    @endif
    @include('main.shared.leftbar')
@endsection




