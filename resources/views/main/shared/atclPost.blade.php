<?php
use App\PostCategory;

?>


<div class="mb-3 post-atcl">
	<div class="clearfix">
        <div class="line-img-wrap">
            <a href="{{ url('post/'. $post->id) }}">
                <img src="{{ Storage::url($post->thumb_path) }}" class="img-fluid">
            </a>
        </div>
        
        <div class="line-cont-wrap">
            <?php
                $postCate = PostCategory::find($post->cate_id);
            ?>
            
            <a href="{{ url('post/category/'.$postCate->slug) }}"><span class="post-cate">{{ PostCategory::find($post->cate_id)->name }}</span></a>
            <span class="post-date">{{ Ctm::changeDate($post->created_at, 1) }}</span>
            <h5 class="mt-1"><a href="{{ url('post/'. $post->id) }}">{{ $post->big_title }}</a></h5>
        </div>
    </div>
    
    <div class="mt-2">
    	閲覧数 <i class="fal fa-eye"></i> {{ $post->view_count }} <span class="post-date text-small ml-3">{{ Ctm::changeDate($post->created_at, 1) }}</span>
    </div>
    
</div>

