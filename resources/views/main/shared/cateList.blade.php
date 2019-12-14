<?php
use App\Category;
?>

<ul class="list-unstyled clearfix">
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
