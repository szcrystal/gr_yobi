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

@include('main.shared.bread', ['type'=>'post', 'typeSec'=>'top'])

{{--
@include('main.shared.bread', ['type'=>'post', 'typeSec'=>'cate', 'cateId'=>$cate->id])
--}}

@endsection


@section('content')

<?php
    use App\Tag;
    use App\TagRelation;
    use App\Setting;
?>

<div id="main" class="post-archive">
<div class="panel top-cont">

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
</div>

@endsection


@section('leftbar')
    @include('main.shared.leftbar')
@endsection


