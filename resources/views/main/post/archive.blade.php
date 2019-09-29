@extends('layouts.app')

<?php
use App\TopSetting;
use App\Tag;
use App\TagRelation;
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

@elseif(Request::has('post/rank-view'))
	@include('main.shared.bread', ['type'=>'post', 'typeSec'=>'rank', ])

@else
	@include('main.shared.bread', ['type'=>'post', 'typeSec'=>'top'])

@endif

@endsection


@section('content')

<div class="post-wrap clearfix border border-danger top-cont">

    <div class="post-main">

        <div class="pagination-wrap">
            {{ $postRels->links() }}
        </div>
        
        
        <div class="panel-body clearfix">
           
           @foreach($postRels as $postRel)
                @include('main.shared.atclPost', ['post'=>$postRel])
            @endforeach
        
        </div>
            
            
        <div class="pagination-wrap">
            {{ $postRels->links() }}
        </div>
                
    </div>


@endsection


@section('leftbar')
	<div class="post-side border border-danger">
    
    </div>
    
    
    {{--
    @include('main.shared.leftbar')
    --}}
    
    </div>
</div>
@endsection


