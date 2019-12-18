
    <div class="head-atcl">
        <h2 class="pl-0">人気タグ</h2>
    </div>
    
    <div class="tags mt-3 mb-1 text-small">
        @include('main.shared.tag', ['tags'=>$popTagsFirst, 'num'=>0])
    </div>
    
    <div class="mr-3 text-right">
        <span class="more-tgl">もっと見る <i class="fal fa-angle-down"></i></span>
    </div>
    
    <div class="tags mt-2 mb-1 text-small more-list">
        @include('main.shared.tag', ['tags'=>$popTagsSecond, 'num'=>0])
    </div>
