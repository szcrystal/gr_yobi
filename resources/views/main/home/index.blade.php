@extends('layouts.app')

<?php
use App\Setting;
use App\Favorite;
use App\TopSetting;
?>


@section('content')
<div id="main" class="top home">

    <div class="panel panel-default">
        
        @if(Ctm::isEnv('local'))
            <div class="clearfix s-form ml-2 float-none w-100">
                <form class="my-1 my-lg-0" role="form" method="GET" action="{{ url('search') }}">
                    {{-- csrf_field() --}}

                    <input type="search" class="form-control rounded-0" name="s" placeholder="キーワードを入力して下さい" value="{{ Request::has('s') ? Request::input('s') : '' }}">
                    <button class="btn-s"><i class="fa fa-search"></i></button>

                </form>
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
                <h2>{{ $keyTitle }}</h2>
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




