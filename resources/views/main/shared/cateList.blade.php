<?php
use App\Category;
?>

<div class="head-atcl">
    <h2 class="pl-0">カテゴリー</h2>
</div>

<div class="mt-2 pb-2">
    <ul class="list-unstyled clearfix img-circle-wrap">
        <?php
            $cateAlls = Category::all();
        ?>
        
        @foreach($cateAlls as $cate)
            <li class="float-left mr-5 mb-3 clearfix">
                <div style="background-image: url({{ Storage::url($cate->main_img) }})" class="img-circle float-left">
                    <a href="{{ url('category/' . $cate->slug) }}"></a>
                </div>
                
                <div class="float-left">
                    <a href="{{ url('category/' . $cate->slug) }}">{{ $cate->name }}</a>
                </div>
            </li>
        @endforeach

    </ul>
</div>
