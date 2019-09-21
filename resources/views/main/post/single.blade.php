@extends('layouts.app')

<?php
use App\User;
use App\PostCategory;
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
    
@include('main.shared.bread', ['type'=>'post', 'typeSec'=>'single', 'cateId'=>$postRel->cate_id])
        
<div class="post-wrap clearfix">

<?php

//	echo count($postArr);
//    exit;

    $chunkNum = 0;
    $chunkNumArr = ['a'=>1/*, 'b'=>3, 'c'=>3*/];
    
//    print_r($postArr);
//    exit;
?>

<div class="post-main">

<article>
	
    <header>
        <h1>{{ $postRel->big_title }}</h1>
        
        <div class="mt-2 mb-3 pl-1">
            <span class="post-date">{{ Ctm::changeDate($postRel->created_at, 1) }}</span>        
            <span class="post-cate">{{ $postCate->name }}</span>
        </div>
        
        <?php 
            $n = 1;
            //$nn = 1;
        ?>
        
        @foreach($postArr as $keyMidId => $post)            
            
            <div class="intro-wrap mt-4">
                
                @if(isset($post['h2']->title) && $post['h2']->is_intro)
                    <h2 id="{{ $n }}" class="mb-3"><i class="fas fa-check text-kon"></i> {{ $post['h2']->title }}</h2>
                @endif
                
                <?php $nn = 1; ?>
                
                @foreach($post['contents'] as $contPost)
                    @if($contPost->is_intro)
                        <div id="{{ $n.'-'.$nn }}" class="pt-1 pb-3 pl-1">
                            @if(isset($contPost->title))
                                <h3>{{ $contPost->title }}</h3>
                            @endif
                            
                            @if(isset($contPost->img_path))
                                <?php 
                                    $imgTag = sprintf('<img src="%s" class="img-fluid">', Storage::url($contPost->img_path));
                                ?>
                                
                                @if(isset($contPost->url))
                                    <a href="{{ $contPost->url }}" target="_brank">{!! $imgTag !!}</a>
                                @else
                                    {!! $imgTag !!}
                                @endif
                            @endif
                            
                            @if(isset($contPost->detail))
                                <p class="mt-2">{!! nl2br($contPost->detail) !!}</p>
                            @endif
                            
                        </div>
                        
                        <?php $nn++; ?>
                    @endif
                @endforeach
            
            </div>
            
            <?php $n++; ?>
        @endforeach
        
        @if(! $postRel->is_index && count($postArr) > 0)
            
            <h2><i class="fas fa-check text-kon"></i> 目次</h2>
            <div class="post-index">    
                <?php 
                    $n = 1;
                    //$nn = 1;
                ?>
                
                <ol>
                    @foreach($postArr as $keyMidId => $post)
                
                        @if(isset($post['h2']->title))
                            <li class="mb-2">
                            	<a href="#{{ $n }}">{{ $post['h2']->title }}</a>
                        
                            @if(count($post['contents']) > 0)
                                <ul class="list-unstyled">
                                	<?php $nn = 1; ?>
                                    
                                    @foreach($post['contents'] as $contPost)
                                        @if(isset($contPost->title))
                                            <li class="mb-1"><a href="#{{ $n.'-'.$nn }}">{{ $n.'-'.$nn }}. {{ $contPost->title }}</a></li>
                                            
                                            <?php $nn++; ?>
                                        @endif   
                                    @endforeach
                                    
                                </ul>
                            @endif
                        
                        	</li>
                        
                        	<?php $n++; ?>
                        
                        @endif
                        
                    @endforeach
                
                </ol>
            </div>
        @endif
    
    </header>
    
    
    
    @foreach($postArr as $keyMidId => $post)
        <?php
            //ここでのblockKeyは [a],[b],[c]
            $chunkNum++;            
        ?>
        
        
            <section class="mt-4">
                
                @if(isset($post['h2']->title) && ! $post['h2']->is_intro)
                    <h1 id="{{ $n }}" class="mb-3"><i class="fas fa-check text-kon"></i> {{ $post['h2']->title }}</h1>
                @endif
                
                <?php $nn = 1; ?>
                
                @foreach($post['contents'] as $contPost)
                    @if(! $contPost->is_intro)
                    <div id="{{ $n.'-'.$nn }}" class="pt-1 pb-3 pl-1">
                        @if(isset($contPost->title))
                            <h2>{{ $contPost->title }}</h2>
                        @endif
                        
                        @if(isset($contPost->img_path))
                            
                            <?php 
                                $imgTag = sprintf('<img src="%s" class="img-fluid">', Storage::url($contPost->img_path));
                            ?>
                            
                            @if(isset($contPost->url))
                                <a href="{{ $contPost->url }}" target="_brank">{!! $imgTag !!}</a>
                            @else
                                {!! $imgTag !!}
                            @endif
                        @endif
                        
                        @if(isset($contPost->detail))
                            <p class="mt-2">{!! nl2br($contPost->detail) !!}</p>
                        @endif
                        
                    </div>
                    
                    <?php $nn++; ?>
                    @endif
                @endforeach
            
            </section>
            
            <?php $n++; ?>
    @endforeach

</article>
</div>


<div class="post-side">

    <div class="mb-5">
        <h4>こんな他の記事もあります</h4>
        @foreach($relatePosts as $relatePost)
            @include('main.shared.atclPost', ['post'=>$relatePost])
        @endforeach

    </div>

    <div class="mb-5">
    	<h4>この記事の関連商品</h4>
    </div>

    <div class="mb-5">
        <h4>この記事の関連タグ</h4>
        <div class="tags mt-2 mb-1">
            @include('main.shared.tag', ['num'=>0])
        </div>
    </div>


@if(Ctm::isEnv('local'))
	<a href="{{ url('post') }}" >TOP</a>
    <a href="{{ url('post/category/'. 1) }}" >post cate</a>
@endif

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
