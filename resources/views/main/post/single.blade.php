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

<div id="main" class="single post-single">
    	
        {{--
        @if(! Ctm::isAgent('sp'))
        	@include('main.shared.bread', ['type'=>'single'])
        @endif
        --}}
        
<div class="clearfix">

<?php

    $chunkNum = 0;
    $chunkNumArr = ['a'=>1/*, 'b'=>3, 'c'=>3*/];
    
//    print_r($postArr);
//    exit;
?>

<div class="upper-wrap post-main float-left border border-primary w-75">

<article>
	
    <h1>{{ $bigTitle }}</h1>
    
    <div>
    	{{ Ctm::changeDate($postRel->created_at, 1) }}
        
        <span>
        	{{ $postCate->name }}	
        </span>
    </div>
    
    @if(! $postRel->is_index)
        <div class="post-index">
            <p><i class="fas fa-check text-kon"></i> 目次</p>
            
            <ol>
            
            <?php 
            	$n = 1;
                $nn = 1;
            ?>
            
            @foreach($postArr as $keyMidId => $post)
        
                @if(isset($post['h2']->title))
                    <li><a href="#{{ $n }}">{{ $post['h2']->title }}</a></li>
                @endif
                
                @if(count($post['contents']) > 0)
                    <ul class="list-unstyled">
                        @foreach($post['contents'] as $contPost)
                            @if(isset($contPost->title))
                                <li><a href="#{{ $n.'-'.$nn }}">{{ $n.'-'.$nn }}. {{ $contPost->title }}</a></li>
                            @endif
                            
                            <?php $nn++; ?>
                        @endforeach
                    </ul>
                @endif
                
                <?php $n++; ?>
                
            @endforeach
            
            </ol>
        </div>
    @endif
    
    <?php 
        $n = 1;
        $nn = 1;
    ?>
    
    @foreach($postArr as $keyMidId => $post)
        <?php
            //ここでのblockKeyは [a],[b],[c]
            $chunkNum++;            
        ?>
        
        <section class="block-wrap">
            
            @if(isset($post['h2']->title))
            	<h2 id="{{ $n }}"><i class="fas fa-check text-kon"></i> {{ $post['h2']->title }}</h2>
            @endif
                
            @foreach($post['contents'] as $contPost)
                <div id="{{ $n.'-'.$nn }}">
                	@if(isset($contPost->title))
                    	<h3>{{ $contPost->title }}</h3>
                    @endif
                    
                	@if(isset($contPost->img_path))
                    	<img src="{{ Storage::url($contPost->img_path) }}" class="img-fluid d-block">
                    @endif
                    
                    @if(isset($contPost->detail))
                    	<p>{!! nl2br($contPost->detail) !!}</p>
                    @endif
                </div>
                
                <?php $nn++; ?>
            @endforeach
        
        </section>
        
        <?php $n++; ?>
    @endforeach

</article>
</div>


<div class="post-side float-right border border-danger w-25">

<div class="mb-5">
<h4>こんな他の記事もあります</h4>

</div>

<div class="mb-5">
<h4>この記事の関連商品</h4>

</div>

<div class="mb-5">
<h4>この記事の関連タグ</h4>
<div class="tags mt-4 mb-1">
    @include('main.shared.tag', ['num'=>0])
</div>
</div>

</div>
        
        
        
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
        
		
</div>

            
		
    </div><!-- id -->
@endsection
