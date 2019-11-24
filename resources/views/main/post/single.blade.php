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




@section('bread')
<div id="main" class="post-single">
    
@include('main.shared.bread', ['type'=>'post', 'typeSec'=>'single', 'cateId'=>$postRel->cate_id])

@endsection

@section('content')
<div class="post-wrap clearfix mt-4">

<?php
    $chunkNum = 0;
    $chunkNumArr = ['a'=>1/*, 'b'=>3, 'c'=>3*/];
    
//    print_r($postArr);
//    exit;
?>

<div class="post-main">

<article>
	
    <header>
        <h1>{{ $postRel->big_title }}</h1>
        
        <div class="mt-2 mb-3 pl-1 social-wrap clearfix">
            <div class="">
                <span class="post-date">{{ Ctm::changeDate($postRel->created_at, 1) }}</span>        
                <a href="{{ url('post/category/'.$postCate->slug) }}"><span class="post-cate">{{ $postCate->name }}</span></a>
            </div>
        </div>
    </header>
    
    <div class="section-wrap mt-3">
        
        <?php 
            $n = 1;
            //$nn = 1;
        ?>
        
        @foreach($postArr as $keyMidId => $post)            
            @if($post['h2']->is_intro)
                
                <section class="intro-wrap">
                    
                    @if(isset($post['h2']->title))
                        <h1 id="{{ $n }}" class="mb-3"><i class="fas fa-check"></i> {{ $post['h2']->title }}</h1>
                    @endif
                    
                    <?php $nn = 1; ?>
                    
                    @foreach($post['contents'] as $contPost)
                        @if($contPost->is_intro)
                            <div id="{{ $n.'-'.$nn }}" class="pt-1 pb-4 pl-1 mb-3">
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
                                    <p class="mt-2 mb-0">{!! nl2br($contPost->detail) !!}</p>
                                @endif
                                
                            </div>
                            
                            <?php $nn++; ?>
                        @endif
                    @endforeach
                
                </section>
                
                <?php $n++; ?>
            
            @endif
        @endforeach
        
        <?php //目次 ====================================================== ?>
        
        @if(! $postRel->is_index && count($postArr) > 0)
        
            <section>
                <h1><i class="fas fa-check text-kon"></i> 目次</h1>
                <div class="post-index">
                    <?php 
                        $iNum = 1;
                        //$nn = 1;
                    ?>
                    
                    <ol>
                        @foreach($postArr as $keyMidId => $post)
                    
                            @if(isset($post['h2']->title))
                                <li class="mb-2">
                                    <a href="#{{ $iNum }}">{{ $post['h2']->title }}</a>
                            
                                @if(count($post['contents']) > 0)
                                    <ul class="list-unstyled">
                                        
                                        <?php $nn = 1; ?>
                                        
                                        @foreach($post['contents'] as $contPost)
                                            @if(isset($contPost->title))
                                                <li class="mb-1"><a href="#{{ $iNum.'-'.$nn }}">{{ $iNum.'-'.$nn }}. {{ $contPost->title }}</a></li>
                                                
                                                <?php $nn++; ?>
                                            @endif   
                                        @endforeach
                                        
                                    </ul>
                                @endif
                            
                                </li>
                            
                                <?php $iNum++; ?>
                            
                            @endif
                            
                        @endforeach
                    
                    </ol>
                </div>
            </section>
        
        @endif
    
    
        @foreach($postArr as $keyMidId => $post)
            <?php
                //ここでのblockKeyは [a],[b],[c]
                //$chunkNum++;            
            ?>
            
            @if(! $post['h2']->is_intro)
                <section class="mt-4">
                    
                    @if(isset($post['h2']->title))
                        <h1 id="{{ $n }}" class="mb-3"><i class="fas fa-check"></i> {{ $post['h2']->title }}</h1>
                    @endif
                    
                    <?php $nn = 1; ?>
                    
                    @foreach($post['contents'] as $contPost)
                        
                        <div id="{{ $n.'-'.$nn }}" class="pt-1 pb-4 pl-1 mb-3">
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
                                <p class="mt-2 mb-0">{!! nl2br($contPost->detail) !!}</p>
                            @endif
                            
                        </div>
                        
                        <?php $nn++; ?>
                        
                    @endforeach
                
                </section>
                
                <?php $n++; ?>
            
            @endif
        @endforeach
        
        <div class="clearfix">
            @include('main.shared.socialBtn', ['title'=>$postRel->big_title, 'naming'=>'記事'])
        </div>
        
	</div><!--sec-wrap -->
</article>
</div>


<div class="post-side">

    @if($postRel->cate_id != 1)
        <div class="relate-post mb-5">
            <h4>関連記事</h4>
            @foreach($relatePosts as $relatePost)
                @include('main.shared.atclPost', ['post'=>$relatePost])
            @endforeach

        </div>
    @endif

    <div class="relate-item mb-5 clearfix">
    	<h4>この記事の関連商品</h4>
        <div>
            @foreach($relateItems as $relateItem)
                <article class="main-atcl">
                    @include('main.shared.atcl', ['item'=>$relateItem, 'type'=>'post'])
                </article>
            @endforeach
        </div>
    </div>

    <div class="relate-tag mb-5">
        <h4>この記事の関連タグ</h4>
        <div class="tags mt-2 mb-1">
            @include('main.shared.tag', ['num'=>0])
        </div>
    </div>


@if(Ctm::isEnv('local'))
	<a href="{{ url('post') }}" >TOP</a>
    <a href="{{ url('post/category/'. 1) }}" >post cate</a>
    <a href="{{ url('post/view-rank') }}" >view rank</a>
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
