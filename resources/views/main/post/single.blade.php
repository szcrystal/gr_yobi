@extends('layouts.app')

<?php
use App\User;
//use App\Category;
//use App\DeliveryGroupRelation;
//use App\Prefecture;
use App\Setting;
use App\TopSetting;
?>


@if(! Ctm::isAgent('sp'))
@section('belt')
<div class="tophead-wrap">
    <div class="clearfix">
        {!! nl2br(TopSetting::get()->first()->contents) !!}
    </div>
</div>
@endsection
@endif



@section('content')

    <div id="main" class="single post">
    	
        {{--
        @if(! Ctm::isAgent('sp'))
        	@include('main.shared.bread', ['type'=>'single'])
        @endif
        --}}
        
        
@if(count($postArr) > 0)

<?php

    $chunkNum = 0;
    $chunkNumArr = ['a'=>1/*, 'b'=>3, 'c'=>3*/];
    
//    print_r($postArr);
//    exit;
?>

<div class="upper-wrap">

<article>
	
    <h1>{{ $bigTitle }}</h1>
    
    <div>
    	{{ Ctm::changeDate($postRel->created_at, 1) }}
        
        <span>
        	カテゴリー名	
        </span>
    </div>
    
    @foreach($postArr as $keyMidId => $post)
        <?php
            //ここでのblockKeyは [a],[b],[c]
            $chunkNum++;
            
			$contArr = array();
        ?>
        
        <div class="block-wrap">
        
        	<?php
            	//$contArr = $postArr['contents'][$post->id];
             
//                print_r($postArr['contents']);
//                exit;
                
            ?>
            
            @if(isset($post['h2']->title))
            	<h2>{{ $post['h2']->title }}</h2>
            @endif
                
            @foreach($post['contents'] as $contPost)
                <div>
                	@if(isset($contPost->img_path))
                    	<img src="{{ Storage::url($contPost->img_path) }}" class="img-fluid d-block">
                    @endif
                    
                    @if(isset($contPost->title))
                    	<h3>{{ $contPost->title }}</h3>
                    @endif
                    
                    @if(isset($contPost->detail))
                    	<p>{{ $contPost->detail }}</p>
                    @endif
                </div>
            @endforeach
        
        </div>
        
        
    @endforeach

</article>
</div>

@endif

        
        
        
        {{--
        @if(isset($item->upper_title) || isset($item->upper_text))
            <div class="upper-introduce-wrap mb-4">
                @if(isset($item->upper_title) && $item->upper_title != '')
                    <h3 class="upper-title">{{ $item->upper_title }}</h3>
                @endif
                
                @if(isset($item->upper_text) && $item->upper_text != '')
                    <p class="upper-text px-1 m-0">{!! nl2br($item->upper_text) !!}</p>
                @endif
            
            </div>
        @endif
        --}}
        
		


            
		
    </div><!-- id -->
@endsection
