<?php
use App\Category;
use App\CategorySecond;
use App\PostCategory;
use App\PostCategorySecond;
?>

<div class="">

<nav aria-label="breadcrumb">
  <ol class="breadcrumb">
    
    <li class="breadcrumb-item"><a href="{{ url('/') }}"><i class="fal fa-home"></i></a></li>
    
    @if($type == 'post')
    	@if($typeSec == 'top')
        	<li class="breadcrumb-item active" aria-current="page">記事一覧</li>
        @else
        	<?php 
//         		if($typeSec == 'cate') {  
//         	   		$postCate = PostCategory::find($cateId);
//              	}
//               	else {
//                	$postCate = PostCategory::find($cateId);       
//            		$postCateSec = PostCategorySecond::find($cateId);
//             	}      
            ?>
            
        	<li class="breadcrumb-item"><a href="{{ url('post') }}">記事一覧</a></li>
            
        	@if($typeSec == 'cate')
            	<li class="breadcrumb-item active" aria-current="page">{{ $postCate->name }}</li>
            
            @elseif($typeSec == 'cateSec')
            	<?php $parentCate = PostCategory::find($postCate->parent_id); ?>
            	
                <li class="breadcrumb-item" aria-current="page"><a href="{{ url('post/category/' . $parentCate->slug) }}">{{ $parentCate->name }}</a></li>
            	<li class="breadcrumb-item active" aria-current="page">{{ $postCate->name }}</li>
            
            @elseif($typeSec == 'rank')
            	
            
            @elseif($typeSec == 'single')
                <li class="breadcrumb-item"><a href="{{ url('post/category/'. $postCate->slug) }}">{{ isset($postCate->link_name) ? $postCate->link_name : $postCate->name }}</a></li>
                @if(isset($postRel->catesec_id) && $postRel->catesec_id)
                	<?php $postCateSec = PostCategorySecond::find($postRel->catesec_id); ?>
                	<li class="breadcrumb-item"><a href="{{ url('post/category/'. $postCate->slug . '/' .$postCateSec->slug) }}">{{ $postCateSec->name }}</a></li>
                @endif
                
                <li class="breadcrumb-item active" aria-current="page">{{ $postRel->big_title }}</li>
            
            @endif
            
        @endif
        
    @elseif($type == 'single')
    	<?php $cate = Category::find($item->cate_id); ?>
    	<li class="breadcrumb-item">
    		<a href="{{ url('category/'. $cate->slug) }}">{{ $cate->name }}</a>
        </li>
        @if(isset($item->subcate_id))
        	<?php $subcate = CategorySecond::find($item->subcate_id); ?>
        	<li class="breadcrumb-item">
    			<a href="{{ url('category/'. $cate->slug.'/'. $subcate->slug) }}">{{ $subcate->name }}</a>
        	</li>
        @endif
    	<li class="breadcrumb-item active" aria-current="page">
        	{{ $item->title }}
        </li>
    @elseif($type == 'category')
        <li class="breadcrumb-item active">
        	{{ $cate->name }}
        </li>
    @elseif($type == 'subcategory')
        <li class="breadcrumb-item">
        	<a href="{{ url('category/' .$cate->slug) }}">{{ $cate->name }}</a>
        </li>
        <li class="breadcrumb-item active" aria-current="page">
            {{ $subcate->name }}
        </li>
        
    @elseif($type == 'tag')
    	<li class="breadcrumb-item active" aria-current="page">
        	タグ:{{ $tag->name }}
        </li>
    
    @elseif($type=='search')
       <li class="breadcrumb-item active" aria-current="page">検索結果</li>
        
    @elseif($type == 'user')
    	<li class="breadcrumb-item active" aria-current="page">
        	マイページ
        </li>
    
    @else
    	@if(isset($orgItem))
        	<li class="breadcrumb-item" aria-current="page">
            	<a href="{{ url('item/' . $orgItem->id) }}">{{ $orgItem->title }}</a>
            </li>
        @endif
    	
        <li class="breadcrumb-item active" aria-current="page">
        	{{ $title }}
        </li>
        
    @endif
    
    
    
  </ol>
</nav>

</div>
