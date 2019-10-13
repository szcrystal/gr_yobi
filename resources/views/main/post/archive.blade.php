@extends('layouts.app')

<?php
use App\TopSetting;
use App\Tag;
use App\TagRelation;
use App\PostCategorySecond;
use App\Setting;
?>

@section('belt')
<div class="tophead-wrap">
    <div class="clearfix">
        {!! nl2br(TopSetting::get()->first()->contents) !!}
    </div>
</div>
@endsection


@section('bread')

<div id="main" class="post-archive">

@if(Request::is('post/category/*'))
	@include('main.shared.bread', ['type'=>'post', 'typeSec'=>$type, 'cateId'=>$postCate->id])
	<?php $title = $postCate->name; ?>
	
@elseif(Request::has('post/rank-view'))
	@include('main.shared.bread', ['type'=>'post', 'typeSec'=>'rank', ])

@else
	@include('main.shared.bread', ['type'=>'post', 'typeSec'=>'top'])
	<?php $title = '記事一覧'; ?>
@endif

@endsection


@section('content')

<div class="post-wrap clearfix top-cont">

    <div class="post-main-archive">
    	<h2 class="mb-3 card-header">{{ $title }}</h2>
        
        @if($postRels->isEmpty())
			<p class="pl-1">まだ記事がありません。</p>
        @else
        
        <div class="mb-4">
        
            <div class="pagination-wrap pb-4">
                {{ $postRels->links() }}
            </div>
            
            <div class="panel-body">
            <?php 
                $n = Ctm::isAgent('sp') ? 1 : 2;
                $postRelsArr = array_chunk($postRels->all(), $n); 
            ?>
            
            @foreach($postRelsArr as $postRelsVal)
                <div class="clearfix mb-1">
                   
                    @foreach($postRelsVal as $postRel)
                        @include('main.shared.atclPost', ['post'=>$postRel])
                    @endforeach
                
                </div>
            @endforeach
            
            </div>
            
            <div class="pagination-wrap">
                {{ $postRels->links() }}
            </div>
        
        </div>
        
        	@if(isset($type) && $type != 'top' && isset($postCate->contents))
                <div class="text-small bg-light border border-gray py-2 px-3 mt-5 clearfix">
                    <p class="p-0 m-0">{!! nl2br($postCate->contents) !!}</p>
                </div>
            @endif
            
            @if(isset($type) && $type == 'top')
            @if(count($rankCates) > 0)
                <?php 
                    $nn = Ctm::isAgent('sp') ? 2 : 4;
                    $rankCatesArr = array_chunk($rankCates->all(), $nn); 
                ?>
                
            	<div class="post-rank-cate-wrap mb-5">
             		<h2 class="mb-3 card-header">人気カテゴリー</h2>
                        
             		@foreach($rankCatesArr as $rankCatesAr) 
               		<div class="clearfix">        
                        @foreach($rankCatesAr as $rankCate) 
                        	@if(isset($rankCate->thumb_path)) 
                            <div class="post-rank-cate">       
                                <div>
                                	<a href="{{ url('post/category/'. $rankCate->cate_slug . '/'.$rankCate->slug) }}">
                                    <img src="{{ Storage::url($rankCate->thumb_path) }}" class="w-100 img-fluid">
                                    </a>
                                </div>
                                <div>
                                    <h5 class="mt-1"><a href="{{ url('post/category/'. $rankCate->cate_slug . '/'.$rankCate->slug) }}">{{ $rankCate->name }}</a></h5>
                                </div>
                            </div>
                            @endif      
                        @endforeach 
                 	</div>
                  @endforeach              
             	</div>   
            @endif
            
            @if(count($rankTags) > 0)
                <div class="rnk-tag mb-5">
                    <h2 class="mb-3 card-header">人気タグ</h2>
                    <div class="tags mt-2 mb-1">
                        @include('main.shared.tag', ['tags'=>$rankTags, 'num'=>0])
                    </div>
                </div>
            @endif
            
            @endif
            
        @endif
    </div>
    
    

@endsection


@section('leftbar')
	<div id="left-bar" style="min-height: 750px;" class="post-side-archive">
		<div class="">
            @foreach($postCates as $postCate)
            	<?php 
             	   $postCateSecs = PostCategorySecond::where('parent_id', $postCate->id)->get();
                ?>
                
                <h5><a href="{{ url('post/category/' . $postCate->slug) }}">{{ $postCate->name }}</a></h5>
                    
                <ul class="list-unstyled pl-3 pt-1 mb-4 pb-1">
                    @foreach($postCateSecs as $postCateSec)   
                    <li class="mb-1"><a href="{{ url('post/category/' . $postCate->slug . '/' . $postCateSec->slug) }}">{{ $postCateSec->name }}</a>
                    @endforeach      
                </ul>   

            @endforeach 
      	</div>     
    </div>
    
    
    {{--
    @include('main.shared.leftbar')
    --}}
    
    </div>
</div>
@endsection


