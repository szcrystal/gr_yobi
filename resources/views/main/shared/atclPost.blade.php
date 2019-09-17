<?php
use App\PostCategory;

?>


<div class="clearfix mb-3 post-atcl">
    <div class="line-img-wrap">
        <a href="{{ url('post/'. $post->id) }}">
            <img src="{{ Storage::url($post->thumb_path) }}" class="img-fluid">
        </a>
    </div>
    
    <div class="line-cont-wrap">
        <span class="post-cate">{{ PostCategory::find($post->cate_id)->name }}</span>
        <span class="post-date">{{ Ctm::changeDate($post->created_at, 1) }}</span>
        <h5 class="mt-1"><a href="{{ url('post/'. $post->id) }}">{{ $post->big_title }}</a></h5>
    </div>
    
</div>

