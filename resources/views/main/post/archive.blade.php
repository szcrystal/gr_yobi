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
	@include('main.shared.bread', ['type'=>'post', 'typeSec'=>'cate', 'cateId'=>$postCate->id])
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

        <div class="pagination-wrap">
            {{ $postRels->links() }}
        </div>
        
        <?php 
            $n = Ctm::isAgent('sp') ? 1 : 2;
            $postRelsArr = array_chunk($postRels->all(), $n); 
        ?>
        
        @foreach($postRelsArr as $postRelsVal)
            <div class="panel-body clearfix mb-1">
               
               	@foreach($postRelsVal as $postRel)
                    @include('main.shared.atclPost', ['post'=>$postRel])
            	@endforeach
            
            </div>
        @endforeach
            
            
        <div class="pagination-wrap">
            {{ $postRels->links() }}
        </div>
                
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
                    
                <ul class="list-unstyled pl-4 pt-1 mb-4 pb-1">
                    @foreach($postCateSecs as $postCateSec)   
                    <li class="mb-1"><a href="{{ url('post/category/' . $postCate->slug . '?sec=' .$postCateSec->id) }}">{{ $postCateSec->name }}</a>
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


