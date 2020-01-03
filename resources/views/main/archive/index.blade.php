@extends('layouts.app')

<?php
use App\TopSetting;
?>

@section('belt')
<div class="tophead-wrap">
    <div class="clearfix">
        {!! nl2br(TopSetting::get()->first()->contents) !!}
    </div>
    
    @if(isset($isTop) && $isTop)
        @include('main.shared.carousel')
    @endif
</div>
@endsection


@section('bread')
@include('main.shared.bread')
@endsection


@section('content')

<?php
    use App\Tag;
    use App\TagRelation;
    use App\Setting;
?>

<div id="main" class="archive">

<div class="panel panel-default top-cont">
	
    <?php $orgObj = null; ?>
    
    <div class="panel-heading">
        <h2 class="mb-3 card-header">
        @if($type == 'category')
            {{ $cate->name }}
            <?php $orgObj = $cate; ?>
            
        @elseif($type == 'subcategory')
            <small class="d-block pb-2">{{ $cate->name }}</small>{{ $subcate->name }}
            <?php $orgObj = $subcate; ?>
            
        @elseif($type == 'tag')
            タグ：{{ $tag->name }}
            <?php $orgObj = $tag; ?>
            
        @elseif($type=='search')
            検索ワード：
            @if($searchStr == '')
                未入力です
            @else
                @if(!count($items))
                {{ $searchStr }}の商品がありません
                @else
                {{ $searchStr }}
                @endif
            @endif
            
        @elseif($type == 'unique' || $type == 'unique-ueki' )
            {{ $title }}
        @endif
        </h2>
    </div>
        
    <div class="panel-body">
        
        @if($type == 'category' || $type == 'subcategory' || $type == 'tag')
            @if(Request::query('page') < 2)
                @include('main.shared.upper')
            
            @endif
        @endif
        
        
        <div class="pagination-wrap">
            <?php
                $paginateTarget = '';
                $pageSepNum = '';
                
                if( Ctm::isAgent('sp') && $items->total() > 126 ) {
                    $paginateTarget = 'vendor.pagination.simple-bootstrap-4';
                    $format = '<span class="d-inline-block text-small mr-3 pt-1">%s</span>';
                
                    $pageSepNum = Request::has('page') ? Request::input('page') : 1;
                    $pageSepNum .= '/' . ceil($items->total()/21);
                    
                    $pageSepNum = sprintf($format, $pageSepNum);
                }
            ?>
            
            {!! $pageSepNum !!}
            
            {{ $items->links($paginateTarget) }}
        </div>
        
        <?php
            $n = Ctm::isAgent('sp') ? 3 : 4;
            $itemArr = array_chunk($items->all(), $n); 
        ?>
        
        @foreach($itemArr as $itemVal)
            <div class="clearfix">

            @foreach($itemVal as $item)
                <article class="main-atcl">
                    @if($type == 'unique-ueki')
                        @include('main.shared.atclCateSec', ['cateSec'=>$item])
                    @else
	                    @include('main.shared.atcl', [])
    				@endif                    
                </article>
            @endforeach
            
            </div>
        @endforeach
    
    </div>
        
    <div class="pagination-wrap">
        {!! $pageSepNum !!}
        
        {{ $items->links($paginateTarget) }}
    </div>
            
</div>
</div>

@endsection


@section('leftbar')
    @include('main.shared.leftbar')
@endsection


