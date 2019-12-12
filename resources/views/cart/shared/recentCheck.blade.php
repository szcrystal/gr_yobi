@if(isset($cookieItems) && count($cookieItems) > 0)
    <div class="wrap-atcl cart-recent-check mb-0">
        <div class="head-atcl">
            <h2>最近チェックしたアイテム</h2>
        </div>

        <div class="clearfix">
            @foreach($cookieItems as $cookieItem)
                 <div class="mb-2 clearfix">
                     @foreach($cookieItem as $item)
                         <article class="main-atcl">
                             @include('main.shared.atcl', ['strNum'=>Ctm::isAgent('sp') ? 15 : 20])
         
                         </article>
                     @endforeach
                 </div>
            @endforeach
        </div>
        
        <a href="{{ url('recent-items') }}" class="btn btn-block btn-custom bg-white border-secondary rounded-0">もっと見る <i class="fal fa-angle-double-right"></i></a>
    </div>
@endif
